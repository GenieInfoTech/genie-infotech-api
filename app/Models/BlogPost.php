<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'password',
        'excerpt',
        'content',
        'reading_time',
        'word_count',
        'cover_image',
        'author_id',
        'last_modified_by',
        'category_id',
        'tags',
        'published',
        'status',
        'featured',
        'sticky',
        'featured_until',
        'published_at',
        'scheduled_for',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'canonical_url',
        'views',
        'allow_comments',
        'comment_count',
        'share_count',
        'like_count',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'featured' => 'boolean',
            'sticky' => 'boolean',
            'allow_comments' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'featured_until' => 'datetime',
            'views' => 'integer',
            'word_count' => 'integer',
            'reading_time' => 'integer',
            'comment_count' => 'integer',
            'share_count' => 'integer',
            'like_count' => 'integer',
        ];
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            // Calculate word count and reading time
            $post->word_count = str_word_count(strip_tags($post->content));
            $post->reading_time = max(1, ceil($post->word_count / 200));
        });

        static::updating(function ($post) {
            if ($post->isDirty('content')) {
                $post->word_count = str_word_count(strip_tags($post->content));
                $post->reading_time = max(1, ceil($post->word_count / 200));
            }
        });
    }

    /**
     * Relationships
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastModifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag', 'post_id', 'tag_id');
    }

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(BlogSeries::class, 'blog_post_series', 'post_id', 'series_id')
            ->withPivot('order');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(BlogPostRevision::class, 'post_id')->orderBy('revision_number', 'desc');
    }

    public function media(): HasMany
    {
        return $this->hasMany(BlogPostMedia::class, 'post_id')->orderBy('display_order');
    }

    public function seo(): HasOne
    {
        return $this->hasOne(BlogPostSeo::class, 'post_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(BlogPostAnalytics::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'post_id');
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'post_id')->where('status', 'approved');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')
                  ->orWhere('featured_until', '>', now());
            });
    }

    public function scopeSticky($query)
    {
        return $query->where('sticky', true);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'draft')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '>', now());
    }

    /**
     * Helper Methods
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function incrementComments(): void
    {
        $this->increment('comment_count');
    }

    public function decrementComments(): void
    {
        $this->decrement('comment_count');
    }

    public function incrementShares(): void
    {
        $this->increment('share_count');
    }

    public function incrementLikes(): void
    {
        $this->increment('like_count');
    }

    public function decrementLikes(): void
    {
        $this->decrement('like_count');
    }

    public function createRevision(): void
    {
        $latestRevision = $this->revisions()->first();
        $revisionNumber = $latestRevision ? $latestRevision->revision_number + 1 : 1;

        $userId = Auth::check() ? Auth::id() : $this->author_id;
        
        $this->revisions()->create([
            'user_id' => $userId,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'revision_number' => $revisionNumber,
        ]);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' 
            && $this->published_at 
            && $this->published_at->isPast();
    }

    public function isFeatured(): bool
    {
        return $this->featured 
            && (!$this->featured_until || $this->featured_until->isFuture());
    }

    public function isScheduled(): bool
    {
        return $this->scheduled_for && $this->scheduled_for->isFuture();
    }

    public function getUrlAttribute(): string
    {
        return url('/blog/' . $this->slug);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    /**
     * Get tags as array (legacy support)
     */
    public function getTagsArrayAttribute(): array
    {
        return $this->tags ? explode(',', $this->tags) : [];
    }
}
