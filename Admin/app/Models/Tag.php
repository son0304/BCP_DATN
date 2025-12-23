<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * 1 Tag có nhiều Post
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Quan hệ many-to-many (qua post_tag)
     */
    public function postTags()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}
