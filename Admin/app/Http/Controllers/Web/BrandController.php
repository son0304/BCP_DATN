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
            'address_detail' => 'required|string',
            'phone' => [
                'nullable',
                'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'
            ],
            'venue_types' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Normalize time to HH:MM:SS for database
        if (isset($validatedData['start_time']) && strlen($validatedData['start_time']) === 5) {
            $validatedData['start_time'] .= ':00';
        }
        if (isset($validatedData['end_time']) && strlen($validatedData['end_time']) === 5) {
            $validatedData['end_time'] .= ':00';
        }

        $validatedData['is_active'] = 0;

        $venue = Venue::create($validatedData);

        if ($request->has('venue_types')) {
            $venue->venueTypes()->sync($request->venue_types);
        }

        return redirect()->route('admin.brand.index')->with('success', 'Thêm sân thành công!');
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
            'address_detail' => 'required|string',
            'phone' => [
                'nullable',
                'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'
            ],
            'venue_types' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Normalize time to HH:MM:SS for database
        if (isset($validatedData['start_time']) && strlen($validatedData['start_time']) === 5) {
            $validatedData['start_time'] .= ':00';
        }
        if (isset($validatedData['end_time']) && strlen($validatedData['end_time']) === 5) {
            $validatedData['end_time'] .= ':00';
        }

        $validatedData['is_active'] = $venue->is_active;

        $venue->update($validatedData);

        if ($request->has('venue_types')) {
            $venue->venueTypes()->sync($request->venue_types);
        } else {
            $venue->venueTypes()->sync([]);
        }

        return redirect()->route('admin.brand.index')->with('success', 'Cập nhật sân thành công!');
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
