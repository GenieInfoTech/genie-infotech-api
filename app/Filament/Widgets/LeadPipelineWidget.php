<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadPipelineWidget extends ChartWidget
{
    protected static ?string $heading = 'Sales Pipeline';
    protected static ?string $description = 'Lead distribution across pipeline stages';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = Lead::getStatuses();
        $data = [];
        $labels = [];
        $backgroundColors = [];

        $colorMap = [
            'new' => 'rgba(251, 191, 36, 0.8)',        // Amber
            'contacted' => 'rgba(59, 130, 246, 0.8)',  // Blue
            'qualified' => 'rgba(139, 92, 246, 0.8)', // Purple
            'proposal' => 'rgba(236, 72, 153, 0.8)',   // Pink
            'negotiation' => 'rgba(249, 115, 22, 0.8)', // Orange
            'converted' => 'rgba(34, 197, 94, 0.8)',   // Green
            'lost' => 'rgba(239, 68, 68, 0.8)',        // Red
        ];

        foreach ($statuses as $key => $label) {
            $count = Lead::where('status', $key)->count();
            $data[] = $count;
            $labels[] = $label . " ({$count})";
            $backgroundColors[] = $colorMap[$key] ?? 'rgba(156, 163, 175, 0.8)';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => array_map(fn($c) => str_replace('0.8', '1', $c), $backgroundColors),
                    'borderWidth' => 2,
                    'borderRadius' => 8,
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
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
