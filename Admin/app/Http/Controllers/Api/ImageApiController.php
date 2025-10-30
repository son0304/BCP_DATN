<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Image, Venue, Court, Review};
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
            'type' => 'required|string|in:venue,court,review',
            'id'   => 'required|integer',
            'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);

        $modelClass = $this->getModelClass($request->type);
        if (!$modelClass) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $model = $modelClass::findOrFail($request->id);
        $saved = [];

        foreach ($request->file('files', []) as $file) {
            // Lưu file vào thư mục tương ứng
            $path = $file->store("uploads/{$request->type}s", 'public');

            // Lưu bản ghi vào bảng images
            $image = $model->images()->create([
                'url' => 'storage/' . $path,
                'description' => $request->input('description'),
                'is_primary' => false
            ]);

            $saved[] = $image;
        }

        return response()->json([
            'success' => true,
            'message' => 'Ảnh đã được tải lên thành công!',
            'images' => $saved
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
            default  => null,
        };
    }
}