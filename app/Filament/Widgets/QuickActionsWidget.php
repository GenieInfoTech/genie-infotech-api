<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuickActionsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $pendingFollowups = Lead::whereIn('status', ['contacted', 'qualified'])
            ->where(function ($q) {
                $q->whereNull('last_contacted_at')
                    ->orWhere('last_contacted_at', '<', now()->subDays(3));
            })->count();

        $thisWeek = Lead::whereBetween('created_at', [now()->startOfWeek(), now()])->count();

        $convertedThisMonth = Lead::where('status', 'converted')
            ->whereMonth('converted_at', now()->month)
            ->whereYear('converted_at', now()->year)
            ->count();

        $lostThisMonth = Lead::where('status', 'lost')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        return [
            Stat::make('Pending Follow-ups', $pendingFollowups)
                ->description('Need attention')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingFollowups > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.leads.index') . '?tableFilters[status][value]=contacted'),

            Stat::make('This Week', $thisWeek)
                ->description('New leads')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Won This Month', $convertedThisMonth)
                ->description('Converted leads')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
