<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Chỉ venue_owner mới có quyền xem products
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Lấy danh sách venue_ids của owner (chỉ lấy venue không bị soft delete)
        $venueIds = Venue::where('owner_id', $user->id)->pluck('id');
        
        // Debug: Log để kiểm tra
        Log::info('ProductController::index - User ID: ' . $user->id);
        Log::info('ProductController::index - Venue IDs: ' . $venueIds->toJson());
        Log::info('ProductController::index - Venue IDs count: ' . $venueIds->count());
        
        // Build query - lấy products thuộc các venue của owner HOẶC sản phẩm chung (venue_id = null)
        $query = Product::with(['venue', 'category']);
        
        if ($venueIds->isEmpty()) {
            // Nếu owner chưa có venue nào, chỉ lấy sản phẩm chung (venue_id = null)
            // và sản phẩm có category thuộc về owner
            $query->where(function ($q) use ($user) {
                $q->whereNull('venue_id')
                  ->orWhereHas('category', function ($catQuery) use ($user) {
                      $catQuery->where('owner_id', $user->id);
                  });
            });
        } else {
            // Lấy products thuộc các venue của owner HOẶC sản phẩm chung (venue_id = null)
            $query->where(function ($q) use ($venueIds, $user) {
                $q->whereIn('venue_id', $venueIds->toArray())
                  ->orWhereNull('venue_id');
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by venue
        if ($request->filled('venue_id')) {
            // Khi filter theo venue cụ thể, chỉ hiển thị sản phẩm của venue đó
            // (không hiển thị sản phẩm chung)
            $query->where('venue_id', $request->venue_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereRaw('stock_quantity <= min_stock_level');
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', 0);
            } elseif ($request->stock_status === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            }
        }

        // Debug: Log SQL trước khi paginate
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        Log::info('ProductController::index - SQL: ' . $sql);
        Log::info('ProductController::index - Bindings: ' . json_encode($bindings));
        
        $products = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        // Debug: Log số lượng products
        Log::info('ProductController::index - Products count: ' . $products->total());
        Log::info('ProductController::index - Products items count: ' . $products->count());
        
        // Get venues và categories để filter
        $venues = Venue::where('owner_id', $user->id)->orderBy('name')->get();
        $categories = ProductCategory::where('owner_id', $user->id)->active()->orderBy('name')->get();

        return view('venue_owner.products.index', compact('products', 'venues', 'categories'));
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

        // Lấy tất cả venues của owner (kể cả không active) để có thể chọn
        $venues = Venue::where('owner_id', $user->id)
            ->orderBy('is_active', 'desc') // Active venues trước
            ->orderBy('name')
            ->get();
        $categories = ProductCategory::where('owner_id', $user->id)->active()->orderBy('name')->get();

        return view('venue_owner.products.create', compact('venues', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            Product::create([
                'venue_id' => $request->venue_id,
                'name' => $request->name,
                'description' => $request->description,
                'sku' => $request->sku ? strtoupper($request->sku) : null,
                'price' => $request->price,
                'cost_price' => $request->cost_price,
                'stock_quantity' => $request->stock_quantity,
                'min_stock_level' => $request->min_stock_level ?? 0,
                'unit' => $request->unit,
                'category_id' => $request->category_id,
                'image_url' => $request->image_url,
                'is_active' => $request->has('is_active'),
                'is_featured' => $request->has('is_featured'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();
            return redirect()->route('owner.products.index')
                ->with('success', 'Sản phẩm đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo sản phẩm: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra product thuộc về venue của owner
        if ($product->venue && $product->venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xem sản phẩm này.');
        }

        $product->load(['venue', 'category', 'items.ticket']);

        return view('venue_owner.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Kiểm tra product thuộc về venue của owner
        if ($product->venue && $product->venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa sản phẩm này.');
        }

        $venues = Venue::where('owner_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $categories = ProductCategory::where('owner_id', $user->id)->active()->orderBy('name')->get();

        return view('venue_owner.products.edit', compact('product', 'venues', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        DB::beginTransaction();
        try {
            $product->update([
                'venue_id' => $request->venue_id,
                'name' => $request->name,
                'description' => $request->description,
                'sku' => $request->sku ? strtoupper($request->sku) : null,
                'price' => $request->price,
                'cost_price' => $request->cost_price,
                'stock_quantity' => $request->stock_quantity,
                'min_stock_level' => $request->min_stock_level ?? 0,
                'unit' => $request->unit,
                'category_id' => $request->category_id,
                'image_url' => $request->image_url,
                'is_active' => $request->has('is_active'),
                'is_featured' => $request->has('is_featured'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();
            return redirect()->route('owner.products.index')
                ->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật sản phẩm: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'venue_owner') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Kiểm tra product thuộc về venue của owner
        if ($product->venue && $product->venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xóa sản phẩm này.');
        }

        DB::beginTransaction();
        try {
            // Kiểm tra xem sản phẩm đã được sử dụng trong items chưa
            if ($product->items()->count() > 0) {
                return back()->with('error', 'Không thể xóa sản phẩm đã được sử dụng trong đơn hàng.');
            }

            $product->delete();

            DB::commit();
            return redirect()->route('owner.products.index')
                ->with('success', 'Sản phẩm đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi xóa sản phẩm: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa sản phẩm: ' . $e->getMessage());
        }
    }
}
