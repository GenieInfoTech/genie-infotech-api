<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogComment extends Model
{
    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'author_ip',
        'content',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BlogComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'parent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the author name (from user or guest data)
     */
    public function getAuthorName(): string
    {
        return $this->user ? $this->user->name : $this->author_name;
    }

    /**
     * Get the author email (from user or guest data)
     */
    public function getAuthorEmail(): ?string
    {
        return $this->user ? $this->user->email : $this->author_email;
    }

    /**
     * Check if comment is from a registered user
     */
    public function isRegisteredUser(): bool
    {
        return $this->user_id !== null;
    }
}
