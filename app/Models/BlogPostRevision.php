<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostRevision extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'title',
        'excerpt',
        'content',
        'revision_number',
        'created_at',
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    /**
     * Get the post that owns this revision
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    /**
     * Get the user who created this revision
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
