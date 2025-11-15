<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalVenueCount = Venue::count();
        $activeVenueCount = Venue::where('is_active', 1)->count();
        $totalPromotionCount = Promotion::count();
        $usedPromotionCount = Promotion::where('used_count', '>', 0)->count();
        $totalUserCount = User::count();
        $newUserThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();
        return view('home.index', compact(
            'totalVenueCount',
            'activeVenueCount',
            'totalPromotionCount',
            'usedPromotionCount',
            'totalUserCount',
            'newUserThisMonth'
        ));
    }
}
