<?php

namespace App\Filament\Widgets;

use App\Models\BlogPost;
use App\Models\BlogComment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalPosts = BlogPost::count();
        $publishedPosts = BlogPost::where('status', 'published')->count();
        $draftPosts = BlogPost::where('status', 'draft')->count();
        $totalViews = BlogPost::sum('views');
        $totalComments = BlogComment::count();
        $pendingComments = BlogComment::where('status', 'pending')->count();

        // Get this week's stats for trends
        $thisWeekPosts = BlogPost::where('created_at', '>=', now()->startOfWeek())->count();
        $lastWeekPosts = BlogPost::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])->count();
        $postsTrend = $lastWeekPosts > 0 
            ? round((($thisWeekPosts - $lastWeekPosts) / $lastWeekPosts) * 100)
            : 0;

        return [
            Stat::make('Total Posts', number_format($totalPosts))
                ->description("{$publishedPosts} published, {$draftPosts} drafts")
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([7, 12, 15, 18, 22, 25, $totalPosts])
                ->color('primary'),

            Stat::make('Total Views', number_format($totalViews))
                ->description('All time page views')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Comments', number_format($totalComments))
                ->description($pendingComments > 0 ? "{$pendingComments} pending review" : 'All moderated')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($pendingComments > 0 ? 'warning' : 'success'),

            Stat::make('This Week', $thisWeekPosts)
                ->description($postsTrend >= 0 ? "+{$postsTrend}% from last week" : "{$postsTrend}% from last week")
                ->descriptionIcon($postsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($postsTrend >= 0 ? 'success' : 'danger'),
        ];
    }
}
