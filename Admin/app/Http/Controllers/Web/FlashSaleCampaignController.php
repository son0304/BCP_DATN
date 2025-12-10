<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\CheckFlashSale;
use App\Models\Availability;
use App\Models\FlashSaleCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FlashSaleCampaignController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $flashSaleCampaigns = FlashSaleCampaign::all();
        if ($user->role->name == 'admin') {
            return view('admin.flash_sale_campaigns.index', compact('flashSaleCampaigns'));
        } elseif ($user->role->name == "venue_owner") {
            return view('venue_owner.flash_sale_campaigns.index', compact('flashSaleCampaigns'));
        } else {
            return redirect()->route('home.index')->with('error', 'Unauthorized access.');
        }
    }
    public function show($campaign_id)
    {
        $campaign = FlashSaleCampaign::findOrFail($campaign_id);
        $ownerId = Auth::id();
        $query = Availability::query()
            ->whereHas('court.venue', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })
            ->where('status', 'open')
            ->with(['court.venue', 'timeSlot']);
        if ($campaign->start_datetime && $campaign->end_datetime) {
            $query->whereHas('timeSlot', function ($q) use ($campaign) {
                $q->whereRaw("TIMESTAMP(availabilities.date, time_slots.start_time) >= ?", [$campaign->start_datetime])
                    ->whereRaw("TIMESTAMP(availabilities.date, time_slots.end_time) <= ?", [$campaign->end_datetime]);
            });
        }

        $rawAvailabilities = $query->get();


        $groupedAvailabilities = $rawAvailabilities->groupBy(function ($item) {
            return $item->court->venue->name;
        })->map(function ($venueSlots) {
            // Cấp 2: Trong mỗi Venue, Group theo tên Court
            return $venueSlots->groupBy(function ($item) {
                return $item->court->name;
            });
        });

        return view('venue_owner.flash_sale_campaigns.show', compact('campaign', 'groupedAvailabilities'));
    }


    public function create()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user) {
            return view('admin.flash_sale_campaigns.create');
        } else {
            return redirect()->route('home.index')->with('error', 'Unauthorized access.');
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'status' => 'required|in:pending,active,inactive',
        ], [
            'end_datetime.after' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.',
            'required' => 'Trường này không được để trống.',
        ]);


        $campaign = FlashSaleCampaign::create($validated);

        CheckFlashSale::dispatch($campaign->id, 'active')->delay($campaign->start_datetime);
        CheckFlashSale::dispatch($campaign->id, 'inactive')->delay($campaign->end_datetime);

        return redirect()->back()->with('success', 'Flash Sale Campaign created successfully.');
    }
}
