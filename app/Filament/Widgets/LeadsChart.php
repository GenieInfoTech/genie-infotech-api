<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class LeadsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Lead Acquisition Trend';
    protected static ?string $description = 'Daily lead capture over the last 30 days';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '14' => 'Last 14 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;

        $currentPeriodData = [];
        $previousPeriodData = [];
        $labels = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $currentDate = Carbon::now()->subDays($i);
            $previousDate = Carbon::now()->subDays($i + $days);

            $labels[] = $currentDate->format('M d');
            $currentPeriodData[] = Lead::whereDate('created_at', $currentDate)->count();
            $previousPeriodData[] = Lead::whereDate('created_at', $previousDate)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Current Period',
                    'data' => $currentPeriodData,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ],
                [
                    'label' => 'Previous Period',
                    'data' => $previousPeriodData,
                    'fill' => false,
                    'borderColor' => 'rgba(156, 163, 175, 0.5)',
                    'borderDash' => [5, 5],
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
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
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
