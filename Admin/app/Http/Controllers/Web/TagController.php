<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Hiển thị danh sách tags
     */
    public function index()
    {
        $tags = Tag::paginate(10);

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Hiển thị form tạo tag
     */
    public function create()
    {
        return view('admin.tags.create');
    }

    /**
     * Lưu tag mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        Tag::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Thêm tag thành công');
    }

    /**
     * Hiển thị form chỉnh sửa tag
     */
    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Cập nhật tag
     */
    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);

        $tag->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Cập nhật tag thành công');
    }

    /**
     * Xóa tag
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Xóa tag thành công');
    }
}
