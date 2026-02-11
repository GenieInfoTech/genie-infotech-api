<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogPostAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BlogAnalyticsService
{
    /**
     * Track a page view
     */
    public function trackView(BlogPost $post, Request $request): void
    {
        $date = now()->toDateString();

        // Update or create analytics record for today
        $analytics = BlogPostAnalytics::firstOrNew([
            'post_id' => $post->id,
            'date' => $date,
        ]);

        $analytics->views += 1;
        
        // Track unique views (simple implementation using session)
        if (!session()->has("viewed_post_{$post->id}_{$date}")) {
            $analytics->unique_views += 1;
            session()->put("viewed_post_{$post->id}_{$date}", true);
        }

        // Track source
        $this->trackSource($analytics, $request);

        $analytics->save();

        // Update post total views
        $post->incrementViews();
    }

    /**
     * Track traffic source
     */
    protected function trackSource(BlogPostAnalytics $analytics, Request $request): void
    {
        $referer = $request->header('referer');
        
        if (!$referer) {
            $analytics->direct_views += 1;
        } elseif (str_contains($referer, 'google.com') || str_contains($referer, 'bing.com')) {
            $analytics->organic_views += 1;
        } elseif (str_contains($referer, 'facebook.com') || str_contains($referer, 'twitter.com') || str_contains($referer, 'linkedin.com')) {
            $analytics->social_views += 1;
        } else {
            $analytics->referral_views += 1;
        }
    }

    /**
     * Track engagement metrics
     */
    public function trackEngagement(BlogPost $post, array $data): void
    {
        BlogPostAnalytics::where('post_id', $post->id)
            ->where('date', now()->toDateString())
            ->update([
                'avg_time_on_page' => $data['time_on_page'] ?? 0,
                'scroll_depth' => $data['scroll_depth'] ?? 0,
                'bounce_rate' => $data['bounce_rate'] ?? 0,
            ]);
    }

    /**
     * Get analytics for a post
     */
    public function getPostAnalytics(BlogPost $post, string $period = 'week'): array
    {
        $startDate = $this->getStartDate($period);

        $analytics = BlogPostAnalytics::where('post_id', $post->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        return [
            'total_views' => $analytics->sum('views'),
            'unique_views' => $analytics->sum('unique_views'),
            'avg_time_on_page' => $analytics->avg('avg_time_on_page'),
            'avg_scroll_depth' => $analytics->avg('scroll_depth'),
            'total_shares' => $analytics->sum('shares'),
            'total_likes' => $analytics->sum('likes'),
            'engagement_rate' => $this->calculateEngagementRate($analytics),
            'chart_data' => $analytics->map(fn ($a) => [
                'date' => $a->date->format('M d'),
                'views' => $a->views,
                'unique_views' => $a->unique_views,
            ])->toArray(),
            'sources' => [
                'organic' => $analytics->sum('organic_views'),
                'social' => $analytics->sum('social_views'),
                'direct' => $analytics->sum('direct_views'),
                'referral' => $analytics->sum('referral_views'),
            ],
        ];
    }

    /**
     * Get overall blog analytics
     */
    public function getOverallAnalytics(string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        $analytics = BlogPostAnalytics::where('date', '>=', $startDate)->get();

        return [
            'total_views' => $analytics->sum('views'),
            'unique_visitors' => $analytics->sum('unique_views'),
            'avg_time_on_page' => round($analytics->avg('avg_time_on_page')),
            'total_engagement' => $analytics->sum('shares') + $analytics->sum('likes'),
            'top_posts' => BlogPost::withSum(['analytics' => function ($q) use ($startDate) {
                    $q->where('date', '>=', $startDate);
                }], 'views')
                ->orderBy('analytics_sum_views', 'desc')
                ->limit(10)
                ->get(['id', 'title', 'slug']),
        ];
    }

    /**
     * Get trending posts
     */
    public function getTrendingPosts(string $period = 'week', int $limit = 10): array
    {
        $startDate = $this->getStartDate($period);

        // Get post IDs sorted by trending score
        $postIds = BlogPost::select('blog_posts.id')
            ->join('blog_post_analytics', 'blog_posts.id', '=', 'blog_post_analytics.post_id')
            ->where('blog_post_analytics.date', '>=', $startDate)
            ->groupBy('blog_posts.id')
            ->orderByRaw('SUM(blog_post_analytics.views) DESC')
            ->limit($limit)
            ->pluck('id');

        // Load full post models with relationships in the correct order
        return BlogPost::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->whereIn('id', $postIds)
            ->get()
            ->sortBy(function ($post) use ($postIds) {
                return $postIds->search($post->id);
            })
            ->values()
            ->toArray();
    }

    /**
     * Calculate engagement rate
     */
    protected function calculateEngagementRate($analytics): float
    {
        $totalViews = $analytics->sum('unique_views');
        if ($totalViews === 0) {
            return 0;
        }

        $engaged = $analytics->sum('shares') + $analytics->sum('likes');
        return round(($engaged / $totalViews) * 100, 2);
    }

    /**
     * Get start date based on period
     */
    protected function getStartDate(string $period): Carbon
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            'all' => Carbon::create(2000, 1, 1),
            default => now()->subWeek(),
        };
    }
}
