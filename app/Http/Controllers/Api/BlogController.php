<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * List published blog posts
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select([
                'id', 'title', 'slug', 'excerpt', 'cover_image',
                'author_id', 'category_id', 'tags', 'published_at', 'views'
            ]);

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $posts = $query->orderBy('published_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($posts);
    }

    /**
     * Show a single blog post
     */
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count
        $post->incrementViews();

        // Get related posts
        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where(function ($q) use ($post) {
                $q->where('category_id', $post->category_id)
                    ->orWhereRaw("tags LIKE CONCAT('%', ?, '%')", [explode(',', $post->tags)[0] ?? '']);
            })
            ->limit(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'cover_image', 'published_at']);

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
        $categories = BlogCategory::withCount(['posts' => function ($q) {
            $q->published();
        }])
            ->orderBy('order')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json($categories);
    }
}
