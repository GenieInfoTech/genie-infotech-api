<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\Cache;

class BlogCacheService
{
    protected int $ttl = 3600; // 1 hour default

    /**
     * Get cached post by slug
     */
    public function getPost(string $slug): ?BlogPost
    {
        return Cache::remember(
            "blog.post.{$slug}",
            $this->ttl,
            fn() => BlogPost::published()
                ->with(['category', 'author', 'tags', 'series', 'media', 'seo'])
                ->where('slug', $slug)
                ->first()
        );
    }

    /**
     * Get cached published posts with pagination
     */
    public function getPosts(int $perPage = 10, int $page = 1, ?int $categoryId = null): array
    {
        $cacheKey = "blog.posts.page_{$page}.per_{$perPage}";
        if ($categoryId) {
            $cacheKey .= ".category_{$categoryId}";
        }

        return Cache::remember(
            $cacheKey,
            $this->ttl,
            function() use ($perPage, $page, $categoryId) {
                $query = BlogPost::published()
                    ->with(['category', 'author', 'tags'])
                    ->latest('published_at');

                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }

                return $query->paginate($perPage, ['*'], 'page', $page)
                    ->toArray();
            }
        );
    }

    /**
     * Get featured posts
     */
    public function getFeaturedPosts(int $limit = 5): array
    {
        return Cache::remember(
            "blog.featured.limit_{$limit}",
            $this->ttl,
            fn() => BlogPost::featured()
                ->published()
                ->with(['category', 'author'])
                ->limit($limit)
                ->get()
                ->toArray()
        );
    }

    /**
     * Get popular posts
     */
    public function getPopularPosts(int $limit = 10): array
    {
        return Cache::remember(
            "blog.popular.limit_{$limit}",
            $this->ttl * 2, // Cache for 2 hours
            fn() => BlogPost::published()
                ->with(['category', 'author'])
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get()
                ->toArray()
        );
    }

    /**
     * Get recent posts
     */
    public function getRecentPosts(int $limit = 5): array
    {
        return Cache::remember(
            "blog.recent.limit_{$limit}",
            $this->ttl / 2, // Cache for 30 minutes
            fn() => BlogPost::published()
                ->with(['category', 'author'])
                ->latest('published_at')
                ->limit($limit)
                ->get()
                ->toArray()
        );
    }

    /**
     * Get related posts
     */
    public function getRelatedPosts(BlogPost $post, int $limit = 5): array
    {
        return Cache::remember(
            "blog.post.{$post->id}.related.limit_{$limit}",
            $this->ttl,
            function() use ($post, $limit) {
                // Get posts in same category, excluding current post
                $related = BlogPost::published()
                    ->where('category_id', $post->category_id)
                    ->where('id', '!=', $post->id)
                    ->with(['category', 'author'])
                    ->limit($limit)
                    ->get();

                // If not enough, get recent posts
                if ($related->count() < $limit) {
                    $additional = BlogPost::published()
                        ->where('id', '!=', $post->id)
                        ->whereNotIn('id', $related->pluck('id'))
                        ->with(['category', 'author'])
                        ->latest('published_at')
                        ->limit($limit - $related->count())
                        ->get();

                    $related = $related->merge($additional);
                }

                return $related->toArray();
            }
        );
    }

    /**
     * Get categories with post counts
     */
    public function getCategories(): array
    {
        return Cache::remember(
            'blog.categories',
            $this->ttl * 2, // Cache for 2 hours
            fn() => BlogCategory::withCount(['posts' => function($q) {
                    $q->published();
                }])
                ->orderBy('name')
                ->get()
                ->toArray()
        );
    }

    /**
     * Clear all post-related cache
     */
    public function clearPost(BlogPost $post): void
    {
        // Clear specific post cache
        Cache::forget("blog.post.{$post->slug}");
        Cache::forget("blog.post.{$post->id}.related.*");
        
        // Clear list caches
        $this->clearListCaches();
    }

    /**
     * Clear all list caches
     */
    public function clearListCaches(): void
    {
        Cache::forget('blog.posts.*');
        Cache::forget('blog.featured.*');
        Cache::forget('blog.popular.*');
        Cache::forget('blog.recent.*');
        Cache::forget('blog.categories');
    }

    /**
     * Clear all blog caches
     */
    public function clearAll(): void
    {
        Cache::tags(['blog'])->flush();
    }

    /**
     * Warm up cache for common queries
     */
    public function warmUp(): void
    {
        $this->getPosts();
        $this->getFeaturedPosts();
        $this->getPopularPosts();
        $this->getRecentPosts();
        $this->getCategories();
    }

    /**
     * Set custom TTL
     */
    public function setTtl(int $seconds): self
    {
        $this->ttl = $seconds;
        return $this;
    }
}
