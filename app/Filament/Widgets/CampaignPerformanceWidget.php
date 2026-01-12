<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class CampaignPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Campaign Performance';
    protected static ?string $description = 'Lead conversion by UTM campaign';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $campaigns = Lead::selectRaw("COALESCE(utm_campaign, 'Direct') as campaign, COUNT(*) as total")
            ->groupBy('utm_campaign')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('total', 'campaign')
            ->toArray();

        if (empty($campaigns)) {
            return [
                'datasets' => [['data' => [1], 'backgroundColor' => ['rgba(156, 163, 175, 0.5)']]],
                'labels' => ['No campaigns yet'],
            ];
        }

        $labels = array_map(function ($campaign) {
            return strlen($campaign) > 15 ? substr($campaign, 0, 15) . '...' : $campaign;
        }, array_keys($campaigns));

        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => array_values($campaigns),
                    'backgroundColor' => array_slice($colors, 0, count($campaigns)),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
