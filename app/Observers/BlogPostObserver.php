<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Services\BlogCacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogPostObserver
{
    protected BlogCacheService $cacheService;

    public function __construct(BlogCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the BlogPost "creating" event.
     */
    public function creating(BlogPost $post): void
    {
        // Generate slug if not provided
        if (empty($post->slug)) {
            $post->slug = $this->generateUniqueSlug($post->title);
        }

        // Calculate word count and reading time
        $this->updateContentMetrics($post);

        // Set author if not set
        if (!$post->author_id && Auth::check()) {
            $post->author_id = Auth::id();
        }
    }

    /**
     * Handle the BlogPost "created" event.
     */
    public function created(BlogPost $post): void
    {
        // Create initial revision (commented out for seeding performance)
        // $post->createRevision();

        // Clear caches
        $this->cacheService->clearListCaches();
    }

    /**
     * Handle the BlogPost "updating" event.
     */
    public function updating(BlogPost $post): void
    {
        // Update word count and reading time if content changed
        if ($post->isDirty('content')) {
            $this->updateContentMetrics($post);
        }

        // Update slug if title changed and slug is not manually set
        if ($post->isDirty('title') && !$post->isDirty('slug')) {
            $post->slug = $this->generateUniqueSlug($post->title, $post->id);
        }
    }

    /**
     * Handle the BlogPost "updated" event.
     */
    public function updated(BlogPost $post): void
    {
        // Create revision on update
        if ($post->wasChanged(['title', 'content', 'excerpt'])) {
            $post->createRevision();
        }

        // Clear caches
        $this->cacheService->clearPost($post);
    }

    /**
     * Handle the BlogPost "deleted" event.
     */
    public function deleted(BlogPost $post): void
    {
        // Clear caches
        $this->cacheService->clearPost($post);
    }

    /**
     * Generate unique slug from title
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    protected function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = BlogPost::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Update word count and reading time
     */
    protected function updateContentMetrics(BlogPost $post): void
    {
        if (empty($post->content)) {
            $post->word_count = 0;
            $post->reading_time = 0;
            return;
        }

        // Strip HTML tags and count words
        $text = strip_tags($post->content);
        $wordCount = str_word_count($text);
        
        $post->word_count = $wordCount;
        
        // Calculate reading time (average 200 words per minute)
        $post->reading_time = max(1, ceil($wordCount / 200));
    }
}
