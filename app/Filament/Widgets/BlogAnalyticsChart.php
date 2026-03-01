<?php

namespace App\Filament\Widgets;

use App\Models\BlogPostAnalytics;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class BlogAnalyticsChart extends ChartWidget
{
    protected static ?string $heading = 'Blog Analytics (Last 30 Days)';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $startDate = now()->subDays(30);
        
        // Get daily analytics for the last 30 days
        $analytics = BlogPostAnalytics::where('date', '>=', $startDate)
            ->selectRaw('date, SUM(views) as total_views, SUM(unique_views) as total_unique_views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zeros
        $dates = collect();
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dates->push($date);
        }

        $viewsData = $dates->map(function ($date) use ($analytics) {
            $record = $analytics->firstWhere('date', $date);
            return $record ? $record->total_views : 0;
        });

        $uniqueViewsData = $dates->map(function ($date) use ($analytics) {
            $record = $analytics->firstWhere('date', $date);
            return $record ? $record->total_unique_views : 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Views',
                    'data' => $viewsData->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Unique Views',
                    'data' => $uniqueViewsData->toArray(),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $dates->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
