<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Venue, Court, Review, Image};

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        // venue
        Venue::all()->each(function ($venue) {
            $venue->images()->save(Image::factory()->make(['is_primary' => true]));
            $venue->images()->saveMany(Image::factory()->count(2)->make());
        });

        // court
        Court::all()->each(function ($court) {
            $court->images()->save(Image::factory()->make(['is_primary' => true]));
            $court->images()->saveMany(Image::factory()->count(2)->make());
        });

        // review (nếu có)
        if (class_exists(Review::class)) {
            Review::all()->each(function ($review) {
                $review->images()->saveMany(Image::factory()->count(1)->make());
            });
        }
    }
}