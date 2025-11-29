<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletApiController extends Controller
{
    public function myWallet(Request $request)
    {

        $userId = Auth::id();

        $wallet = Wallet::where('user_id', $userId)
            ->with(['logs' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'status' => 'active']
            );

        return response()->json([
            'success' => true,
            'data' => $wallet
        ]);
    }
}