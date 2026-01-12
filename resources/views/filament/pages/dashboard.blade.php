<x-filament-panels::page>
    {{-- Header Widgets --}}
    @if (count($this->getHeaderWidgets()))
        <x-filament-widgets::widgets
            :widgets="$this->getHeaderWidgets()"
            :columns="$this->getHeaderWidgetsColumns()"
        />
    @endif

    {{-- Main Dashboard Widgets --}}
    <x-filament-widgets::widgets
        :widgets="$this->getWidgets()"
        :columns="$this->getColumns()"
    />

    {{-- Footer Widgets --}}
    @if (count($this->getFooterWidgets()))
        <x-filament-widgets::widgets
            :widgets="$this->getFooterWidgets()"
            :columns="$this->getColumns()"
        />
    @endif
</x-filament-panels::page>
