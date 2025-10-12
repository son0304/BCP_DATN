<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueType;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Hiển thị danh sách các thương hiệu.
     */
    public function index()
    {
        $venues = Venue::with('owner', 'province')->latest()->paginate(10);
        return view('brand.index', compact('venues'));
    }

    /**
     * Hiển thị form tạo mới.
     */
    public function create()
    {
        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();

        return view('brand.create', compact('owners', 'provinces', 'districts', 'venue_types'));
    }

    /**
     * Lưu một thương hiệu mới vào database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'nullable|string',
            'phone' => 'nullable|string|max:15',
            'venue_types' => 'nullable|array',
            'is_active' => 'required|boolean',
        ]);

        $venue = Venue::create($validatedData);

        if ($request->has('venue_types')) {
            $venue->venueTypes()->sync($request->venue_types);
        }

        return redirect()->route('brand.index')->with('success', 'Thêm sân thành công!');
    }

    /**
     * Hiển thị chi tiết một thương hiệu.
     */
    public function show(Venue $venue)
    {
        return view('brand.show', compact('venue'));
    }

    /**
     * Hiển thị form chỉnh sửa.
     */
    public function edit(Venue $venue)
    {
        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();

        return view('brand.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }

    /**
     * Cập nhật một thương hiệu trong database.
     */
    public function update(Request $request, Venue $venue)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'nullable|string',
            'phone' => 'nullable|string|max:15',
            'venue_types' => 'nullable|array',
            'is_active' => 'required|boolean',
        ]);


        $venue->update($validatedData);

        if ($request->has('venue_types')) {
            $venue->venueTypes()->sync($request->venue_types);
        } else {
            $venue->venueTypes()->sync([]);
        }

        return redirect()->route('brand.index')->with('success', 'Cập nhật sân thành công!');
    }

    /**
     * Xóa một thương hiệu.
     */
    public function destroy(Venue $venue)
    {
        $venue->delete();
        return redirect()->route('admin.brand.index')->with('success', 'Xóa sân thành công!');
    }
}
