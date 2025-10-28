<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVenueOwner
{

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }

        $user = Auth::user();

        if (!$user->role) {
            return redirect()->route('login')->with('error', 'Tài khoản không có quyền truy cập');
        }

        if ($user->role->name === 'admin') {
            return $next($request);
        }

        if ($user->role->name === 'venue_owner') {
            $venueId = $request->route('venue') ?? $request->route('id');

            if ($venueId) {
                $venue = \App\Models\Venue::find($venueId);

                if (!$venue || $venue->owner_id !== $user->id) {
                    abort(403, 'Bạn chỉ có thể quản lý sân của mình');
                }
            }
        } else {
            abort(403, 'Bạn không có quyền truy cập trang này');
        }

        return $next($request);
    }
}