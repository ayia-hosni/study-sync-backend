<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\Visibility;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id', 'room_id', 'content', 'media_urls',
        'visibility', 'type',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'visibility' => Visibility::class,
    ];

    public function author() {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function room() {
        return $this->belongsTo(Room::class);
    }

    public function comments() {
        return $this->hasMany(PostComment::class);
    }

    public function getMediaUrlsAttribute($value)
    {
        $data = json_decode($value, true);

        if (!is_array($data)) {
            return [];
        }

        return array_map(fn($item) => $item ?? '', $data);
    }

    public function reactions() {
        return $this->hasMany(PostReaction::class);
    }

    public function my_reaction() {
        return $this->hasOne(PostReaction::class)->where('user_id', \Illuminate\Support\Facades\Auth::id());
    }
}

