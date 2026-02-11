<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BlogPostMedia extends Model
{
    protected $fillable = [
        'post_id',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'alt_text',
        'title',
        'caption',
        'width',
        'height',
        'is_featured',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
