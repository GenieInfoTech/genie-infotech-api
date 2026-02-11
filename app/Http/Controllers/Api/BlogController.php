<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\BlogComment;
use App\Services\BlogAnalyticsService;
use App\Services\BlogCacheService;
use App\Services\SchemaGenerator;
use App\Services\SeoScoreCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    protected BlogCacheService $cache;
    protected BlogAnalyticsService $analytics;
    protected SchemaGenerator $schema;
    protected SeoScoreCalculator $seoCalculator;

    public function __construct(
        BlogCacheService $cache,
        BlogAnalyticsService $analytics,
        SchemaGenerator $schema,
        SeoScoreCalculator $seoCalculator
    ) {
        $this->cache = $cache;
        $this->analytics = $analytics;
        $this->schema = $schema;
        $this->seoCalculator = $seoCalculator;
    }

    /**
     * List published blog posts
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select([
                'id', 'title', 'slug', 'excerpt', 'cover_image',
                'author_id', 'category_id', 'published_at', 'views',
                'like_count', 'share_count', 'reading_time'
            ]);

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter featured posts
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $posts = $query->orderBy('published_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($posts);
    }

    /**
     * Show a single blog post
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $post = BlogPost::published()
            ->with(['author:id,name', 'category:id,name,slug', 'tags', 'media', 'seo'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Track view
        $this->analytics->trackView($post, $request);

        // Get related posts
        $related = $this->cache->getRelatedPosts($post, 3);

        // Add schema markup
        $post->schema_markup = $this->schema->generateCompleteSchema($post);

        return response()->json([
            'post' => $post,
            'related' => $related,
        ]);
    }

    /**
     * List blog categories
     */
    public function categories(): JsonResponse
    {
        $categories = $this->cache->getCategories();
        return response()->json($categories);
    }

    /**
     * Get posts by category
     */
    public function byCategory(string $slug, Request $request): JsonResponse
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $perPage = min($request->get('per_page', 15), 50);

        $posts = BlogPost::published()
            ->where('category_id', $category->id)
            ->with(['author', 'tags'])
            ->latest('published_at')
            ->paginate($perPage);

        return response()->json([
            'category' => $category,
            'posts' => $posts,
        ]);
    }

    /**
     * Get all tags
     */
    public function tags(): JsonResponse
    {
        $tags = BlogTag::withCount(['posts' => function ($q) {
                $q->published();
            }])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $tags]);
    }

    /**
     * Get posts by tag
     */
    public function byTag(string $slug, Request $request): JsonResponse
    {
        $tag = BlogTag::where('slug', $slug)->firstOrFail();
        $perPage = min($request->get('per_page', 15), 50);

        $posts = $tag->posts()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate($perPage);

        return response()->json([
            'tag' => $tag,
            'posts' => $posts,
        ]);
    }

    /**
     * Get featured posts
     */
    public function featured(): JsonResponse
    {
        $posts = $this->cache->getFeaturedPosts(5);
        return response()->json(['data' => $posts]);
    }

    /**
     * Get popular posts
     */
    public function popular(): JsonResponse
    {
        $posts = $this->cache->getPopularPosts(10);
        return response()->json(['data' => $posts]);
    }

    /**
     * Get recent posts
     */
    public function recent(): JsonResponse
    {
        $posts = $this->cache->getRecentPosts(5);
        return response()->json(['data' => $posts]);
    }

    /**
     * Get trending posts
     */
    public function trending(Request $request): JsonResponse
    {
        $period = $request->get('period', 'week');
        $limit = min($request->get('limit', 10), 50);
        
        $posts = $this->analytics->getTrendingPosts($period, $limit);
        
        return response()->json(['data' => $posts]);
    }

    /**
     * Get post comments
     */
    public function comments(string $slug): JsonResponse
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        
        $comments = $post->comments()
            ->approved()
            ->with('author')
            ->oldest()
            ->get();

        return response()->json(['data' => $comments]);
    }

    /**
     * Post a comment (requires authentication)
     */
    public function storeComment(string $slug, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = BlogPost::where('slug', $slug)->firstOrFail();

        if (!$post->allow_comments) {
            return response()->json(['message' => 'Comments are disabled for this post'], 403);
        }

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
            'status' => 'pending', // Requires moderation
        ]);

        return response()->json([
            'message' => 'Comment submitted successfully and is pending moderation',
            'comment' => $comment,
        ], 201);
    }

    /**
     * Like a post (requires authentication)
     */
    public function like(string $slug): JsonResponse
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $post->increment('like_count');

        return response()->json([
            'message' => 'Post liked successfully',
            'likes_count' => $post->like_count,
        ]);
    }

    /**
     * Share tracking (public)
     */
    public function share(string $slug): JsonResponse
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $post->increment('share_count');

        return response()->json([
            'message' => 'Share tracked successfully',
            'shares_count' => $post->share_count,
        ]);
    }

    /**
     * Generate sitemap XML
     */
    public function sitemap(): \Illuminate\Http\Response
    {
        $posts = BlogPost::published()
            ->select('slug', 'updated_at')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($posts as $post) {
            $xml .= '<url>';
            $xml .= '<loc>' . url("/blog/{$post->slug}") . '</loc>';
            $xml .= '<lastmod>' . $post->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Generate RSS feed
     */
    public function rss(): \Illuminate\Http\Response
    {
        $posts = BlogPost::published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>' . htmlspecialchars(config('app.name')) . ' Blog</title>';
        $xml .= '<link>' . url('/blog') . '</link>';
        $xml .= '<description>Latest blog posts from ' . htmlspecialchars(config('app.name')) . '</description>';
        $xml .= '<language>en-us</language>';
        
        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title><![CDATA[' . $post->title . ']]></title>';
            $xml .= '<link>' . url("/blog/{$post->slug}") . '</link>';
            $xml .= '<description><![CDATA[' . $post->excerpt . ']]></description>';
            $xml .= '<author>' . htmlspecialchars($post->author->email) . ' (' . htmlspecialchars($post->author->name) . ')</author>';
            $xml .= '<category>' . htmlspecialchars($post->category->name) . '</category>';
            $xml .= '<pubDate>' . $post->published_at->toRssString() . '</pubDate>';
            $xml .= '<guid>' . url("/blog/{$post->slug}") . '</guid>';
            $xml .= '</item>';
        }
        
        $xml .= '</channel>';
        $xml .= '</rss>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}

