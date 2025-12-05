<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionApiController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json([
            'success' => true,
            'message' => 'Transaction API is working!',
            'data' => $transactions
        ]);
    }
}