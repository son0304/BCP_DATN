<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use App\Models\VenueType;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function index()
    {
        $courts = Court::with(['venue', 'venue_type'])->latest()->paginate(10);
        return view('courts.index', compact('courts'));
    }
    public function create()
    {
        $venues = Venue::orderBy('name')->get();
        $venueTypes = VenueType::orderBy('name')->get();
        return view('courts.create', compact('venues', 'venueTypes'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'price_per_hour' => 'required|numeric|min:0',
            'surface' => 'nullable|string|max:255',
        ]);

        Court::create($request->all());
        return redirect()->route('courts.index')->with('success', 'Court created successfully.');
    }
    public function edit(Court $court)
    {
        $venues = Venue::orderBy('name')->get();
        $venueTypes = VenueType::orderBy('name')->get();
        return view('courts.edit', compact('court', 'venues', 'venueTypes'));
    }
    public function update(Request $request, Court $court)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'price_per_hour' => 'required|numeric|min:0',
            'is_indoor' => 'required|boolean',
        ]);

        $court->update($validatedData);

        return redirect()->route('courts.index')->with('success', 'Cập nhật sân thành công!');
    }
    public function destroy(Court $court)
    {
        $court->delete();
        return redirect()->route('courts.index')->with('success', 'Xóa sân thành công!');
    }
}
