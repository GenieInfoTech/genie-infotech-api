<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Core metrics
        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', 'new')->count();
        $todayLeads = Lead::whereDate('created_at', today())->count();
        $thisMonthLeads = Lead::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Conversion metrics
        $convertedLeads = Lead::where('status', 'converted')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Week-over-week comparison
        $lastWeekLeads = Lead::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])->count();
        $thisWeekLeads = Lead::whereBetween('created_at', [
            now()->startOfWeek(),
            now()
        ])->count();
        $weekChange = $lastWeekLeads > 0
            ? round((($thisWeekLeads - $lastWeekLeads) / $lastWeekLeads) * 100, 1)
            : ($thisWeekLeads > 0 ? 100 : 0);

        // Month-over-month comparison
        $lastMonthLeads = Lead::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $monthChange = $lastMonthLeads > 0
            ? round((($thisMonthLeads - $lastMonthLeads) / $lastMonthLeads) * 100, 1)
            : ($thisMonthLeads > 0 ? 100 : 0);

        // Pipeline metrics
        $inPipeline = Lead::whereIn('status', ['contacted', 'qualified', 'proposal', 'negotiation'])->count();
        $hotLeads = Lead::where('priority', 'urgent')
            ->orWhere('priority', 'high')
            ->whereNotIn('status', ['converted', 'lost'])
            ->count();

        // Response time (average time to first contact)
        $avgResponseHours = $this->calculateAverageResponseTime();

        // Generate sparkline data (last 7 days)
        $sparklineData = $this->getSparklineData(7);

        return [
            Stat::make('Total Leads', number_format($totalLeads))
                ->description($this->formatChange($weekChange) . ' vs last week')
                ->descriptionIcon($weekChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weekChange >= 0 ? 'success' : 'danger')
                ->chart($sparklineData),

            Stat::make('This Month', number_format($thisMonthLeads))
                ->description($this->formatChange($monthChange) . ' vs last month')
                ->descriptionIcon($monthChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthChange >= 0 ? 'success' : 'danger')
                ->chart($this->getSparklineData(30)),

            Stat::make('New Leads', number_format($newLeads))
                ->description('Awaiting first contact')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color($newLeads > 0 ? 'warning' : 'gray'),

            Stat::make('In Pipeline', number_format($inPipeline))
                ->description('Active opportunities')
                ->descriptionIcon('heroicon-m-funnel')
                ->color('info'),

            Stat::make('Conversion Rate', "{$conversionRate}%")
                ->description("{$convertedLeads} of {$totalLeads} converted")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($conversionRate >= 20 ? 'success' : ($conversionRate >= 10 ? 'warning' : 'danger')),

            Stat::make('Hot Leads', number_format($hotLeads))
                ->description('High priority opportunities')
                ->descriptionIcon('heroicon-m-fire')
                ->color($hotLeads > 0 ? 'danger' : 'gray'),
        ];
    }

    private function formatChange(float $change): string
    {
        if ($change >= 0) {
            return "+{$change}%";
        }
        return "{$change}%";
    }

    private function getSparklineData(int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $data[] = Lead::whereDate('created_at', now()->subDays($i))->count();
        }
        return $data;
    }

    private function calculateAverageResponseTime(): ?float
    {
        $leadsWithResponse = Lead::whereNotNull('last_contacted_at')
            ->whereNotNull('created_at')
            ->get();

        if ($leadsWithResponse->isEmpty()) {
            return null;
        }

        $totalHours = $leadsWithResponse->sum(function ($lead) {
            return $lead->created_at->diffInHours($lead->last_contacted_at);
        });

        return round($totalHours / $leadsWithResponse->count(), 1);
    }
}
