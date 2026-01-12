<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CampaignPerformanceWidget;
use App\Filament\Widgets\LatestLeads;
use App\Filament\Widgets\LeadPipelineWidget;
use App\Filament\Widgets\LeadsChart;
use App\Filament\Widgets\LeadSourcesChart;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $title = 'Lead Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getColumns(): int | string | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 3,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // Row 1: Key Stats Overview (full width)
            StatsOverview::class,

            // Row 2: Pipeline (full width)
            LeadPipelineWidget::class,

            // Row 3: Charts and Quick Actions (3 columns)
            LeadsChart::class,
            LeadSourcesChart::class,
            QuickActionsWidget::class,

            // Row 4: Campaign Performance (2/3) + Latest Leads (full width)
            CampaignPerformanceWidget::class,

            // Row 5: Latest Leads Table (full width)
            LatestLeads::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
