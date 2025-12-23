<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'tag_id',
        'title',
        'content',
        'is_active',
        'note',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // App\Models\Post.php

    public function getStatusLabelAttribute()
    {
        if ($this->is_active) {
            return [
                'text' => 'Đã duyệt',
                'class' => 'bg-success'
            ];
        }

        if (!$this->is_active && $this->note) {

            if (str_starts_with($this->note, '[CANCELLED]')) {
                return [
                    'text' => 'Đã hủy',
                    'class' => 'bg-dark'
                ];
            }

            return [
                'text' => 'Bị từ chối',
                'class' => 'bg-danger'
            ];
        }

        return [
            'text' => 'Chưa duyệt',
            'class' => 'bg-warning text-dark'
        ];
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->latest();
    }
}
