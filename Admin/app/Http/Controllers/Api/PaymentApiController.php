<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
   public function payment (Request $request)
   {
       return response()->json(['message' => 'Payment API is working!']);

   }
}