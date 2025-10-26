<?php

namespace App\Helpers;

use App\Models\User;

class PermissionHelper
{

    public static function isAdmin(User $user = null): bool
    {
        $user = $user ?? auth()->user();
        return $user && $user->role && $user->role->name === 'admin';
    }


    public static function isVenueOwner(User $user = null): bool
    {
        $user = $user ?? auth()->user();
        return $user && $user->role && $user->role->name === 'venue_owner';
    }


    public static function isCustomer(User $user = null): bool
    {
        $user = $user ?? auth()->user();
        return $user && $user->role && $user->role->name === 'customer';
    }


    public static function canManageCourts(User $user = null): bool
    {
        $user = $user ?? auth()->user();
        return self::isAdmin($user) || self::isVenueOwner($user);
    }


    public static function ownsVenue(int $venueId, User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (self::isAdmin($user)) {
            return true;
        }

        if (self::isVenueOwner($user)) {
            return \App\Models\Venue::where('id', $venueId)
                ->where('owner_id', $user->id)
                ->exists();
        }

        return false;
    }


    public static function getAccessibleVenues(User $user = null)
    {
        $user = $user ?? auth()->user();

        if (self::isAdmin($user)) {
            return \App\Models\Venue::orderBy('name')->get();
        }

        if (self::isVenueOwner($user)) {
            return \App\Models\Venue::where('owner_id', $user->id)
                ->orderBy('name')
                ->get();
        }

        return collect();
    }


    public static function getAccessibleCourts(User $user = null)
    {
        $user = $user ?? auth()->user();

        if (self::isAdmin($user)) {
            return \App\Models\Court::with(['venue', 'venueType'])->latest()->get();
        }

        if (self::isVenueOwner($user)) {
            return \App\Models\Court::with(['venue', 'venueType'])
                ->whereHas('venue', function($query) use ($user) {
                    $query->where('owner_id', $user->id);
                })
                ->latest()
                ->get();
        }

        return collect();
    }
}
