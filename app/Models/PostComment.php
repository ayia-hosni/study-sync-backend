<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;
    protected $fillable = ['post_id', 'commenter_id', 'parent_id', 'content', 'media_urls'];


    protected $casts = [
        'media_urls' => 'array', // store as JSON array
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function commenter()
    {
        return $this->belongsTo(User::class, 'commenter_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
