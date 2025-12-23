<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Image, Venue, Court, Post, Review};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageApiController extends Controller
{
    /**
     * Lấy danh sách ảnh (tất cả, có thể lọc theo loại)
     */
    public function index(Request $request)
    {
        $query = Image::query();

        // Nếu có filter theo loại (venue, court, review)
        if ($request->has('type') && $request->has('id')) {
            $modelClass = $this->getModelClass($request->type);
            if ($modelClass) {
                $query->where('imageable_type', $modelClass)
                    ->where('imageable_id', $request->id);
            }
        }

        $images = $query->get();

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }

    /**
     * Upload nhiều ảnh và gắn với entity (venue/court/review)
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:venue,court,review,post',
            'id'   => 'required|integer',
            'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);

        $modelClass = $this->getModelClass($request->type);

        // THAY ĐỔI Ở ĐÂY: Nếu ID > 0 thì mới tìm Model, nếu = 0 thì bỏ qua (cho phép tạo ảnh rời)
        $model = null;
        if ($request->id > 0) {
            $model = $modelClass::findOrFail($request->id);
        }

        $saved = [];

        foreach ($request->file('files', []) as $file) {
            // Lưu file vào thư mục
            $path = $file->store("uploads/{$request->type}s", 'public');

            // Tạo bản ghi trong database
            // Nếu có model thì dùng quan hệ morph, nếu không thì dùng Model Image tạo trực tiếp
            if ($model) {
                $image = $model->images()->create([
                    'url' => 'storage/' . $path,
                    'description' => $request->input('description'),
                    'is_primary' => false
                ]);
            } else {
                $image = Image::create([
                    'url' => 'storage/' . $path,
                    'imageable_type' => $modelClass, // App\Models\Post
                    'imageable_id'   => 0,            // Tạm thời để 0
                    'description' => $request->input('description'),
                    'is_primary' => false
                ]);
            }

            $saved[] = $image;
        }

        return response()->json([
            'success' => true,
            'message' => 'Ảnh đã được tải lên thành công!',
            'images' => $saved // React sẽ nhận danh sách này để lấy ID
        ]);
    }

    /**
     * Xóa ảnh
     */
    public function destroy($id)
    {
        $image = Image::findOrFail($id);

        // Xóa file thật trong storage
        if (Storage::disk('public')->exists(str_replace('storage/', '', $image->url))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $image->url));
        }

        $image->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa ảnh']);
    }

    /**
     * Helper: xác định model tương ứng với type
     */
    private function getModelClass(string $type): ?string
    {
        return match ($type) {
            'venue'  => Venue::class,
            'court'  => Court::class,
            'review' => Review::class,
            'post'   => Post::class,
            default  => null,
        };
    }
}
