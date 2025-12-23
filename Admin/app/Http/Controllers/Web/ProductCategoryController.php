<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Chỉ venue_owner mới có quyền quản lý danh mục
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = ProductCategory::with(['products'])
            ->where('owner_id', $user->id);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('venue_owner.product_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        return view('venue_owner.product_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $user = Auth::user();

        // Tự động tạo slug nếu không có
        $slug = $request->slug ?: Str::slug($request->name);
        
        // Đảm bảo slug là unique trong phạm vi owner
        $originalSlug = $slug;
        $counter = 1;
        while (ProductCategory::where('slug', $slug)->where('owner_id', $user->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        DB::beginTransaction();
        try {
            ProductCategory::create([
                'owner_id' => $user->id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'image_url' => $request->image_url,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();
            return redirect()->route('owner.product_categories.index')
                ->with('success', 'Danh mục đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi tạo danh mục: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra category thuộc về owner
        if ($productCategory->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xem danh mục này.');
        }

        $productCategory->load(['products']);

        return view('venue_owner.product_categories.show', compact('productCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Kiểm tra category thuộc về owner
        if ($productCategory->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa danh mục này.');
        }

        return view('venue_owner.product_categories.edit', compact('productCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Kiểm tra category thuộc về owner
        if ($productCategory->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa danh mục này.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_categories')->where(function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })->ignore($productCategory->id)],
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Tên danh mục không được bỏ trống.',
            'slug.unique' => 'Slug này đã tồn tại trong danh mục của bạn.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'image_url.url' => 'URL hình ảnh không hợp lệ.',
        ]);

        // Tự động tạo slug nếu không có
        $slug = $request->slug ?: Str::slug($request->name);
        
        // Đảm bảo slug là unique trong phạm vi owner (trừ chính nó)
        if ($slug !== $productCategory->slug) {
            $originalSlug = $slug;
            $counter = 1;
            while (ProductCategory::where('slug', $slug)->where('owner_id', $user->id)->where('id', '!=', $productCategory->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        DB::beginTransaction();
        try {
            $productCategory->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'image_url' => $request->image_url,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();
            return redirect()->route('owner.product_categories.index')
                ->with('success', 'Danh mục đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi cập nhật danh mục: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Kiểm tra category thuộc về owner
        if ($productCategory->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xóa danh mục này.');
        }

        DB::beginTransaction();
        try {
            // Kiểm tra xem danh mục có sản phẩm không (chỉ sản phẩm của owner)
            $ownerProducts = $productCategory->products()
                ->whereHas('venue', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                })
                ->count();
            
            if ($ownerProducts > 0) {
                return back()->with('error', 'Không thể xóa danh mục đã có sản phẩm. Vui lòng xóa hoặc di chuyển các sản phẩm trước.');
            }

            $productCategory->delete();

            DB::commit();
            return redirect()->route('owner.product_categories.index')
                ->with('success', 'Danh mục đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi xóa danh mục: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa danh mục: ' . $e->getMessage());
        }
    }
}
