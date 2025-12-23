<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'venue_type_id' => 'nullable|exists:venue_types,id',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên danh mục không được để trống.',
            'venue_type_id.exists' => 'Loại sân không hợp lệ.',
        ]);

        try {
            ServiceCategory::create([
                'owner_id' => Auth::id(),
                'name' => $request->name,
                'venue_type_id' => $request->venue_type_id,
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Đã thêm danh mục mới thành công!');
        } catch (\Exception $e) {
            Log::error('Lỗi thêm danh mục: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
}