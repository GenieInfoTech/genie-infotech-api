<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadSourcesChart extends ChartWidget
{
    protected static ?string $heading = 'Lead Sources';
    protected static ?string $description = 'Where your leads are coming from';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $sources = Lead::selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->orderByDesc('count')
            ->pluck('count', 'source')
            ->toArray();

        if (empty($sources)) {
            return [
                'datasets' => [['data' => [1], 'backgroundColor' => ['rgba(156, 163, 175, 0.5)']]],
                'labels' => ['No data yet'],
            ];
        }

        $sourceLabels = Lead::getSources();
        $labels = [];
        $data = [];
        $total = array_sum($sources);

        foreach ($sources as $source => $count) {
            $label = $sourceLabels[$source] ?? ucfirst(str_replace('_', ' ', $source));
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $labels[] = "{$label} ({$percentage}%)";
            $data[] = $count;
        }

        $colors = [
            'rgba(59, 130, 246, 0.85)',   // Blue - Contact Form
            'rgba(251, 191, 36, 0.85)',   // Amber - Exit Intent
            'rgba(34, 197, 94, 0.85)',    // Green - Referral
            'rgba(139, 92, 246, 0.85)',   // Purple - Organic
            'rgba(249, 115, 22, 0.85)',   // Orange - Paid
            'rgba(236, 72, 153, 0.85)',   // Pink - Social
            'rgba(20, 184, 166, 0.85)',   // Teal - Chat
            'rgba(156, 163, 175, 0.85)',  // Gray - Other
        ];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => 'rgba(255, 255, 255, 1)',
                    'borderWidth' => 2,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 15,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
            'cutout' => '60%',
            'maintainAspectRatio' => true,
        ];
    }
}
