<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('sendEmail')
                ->label('Send Email')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->url(fn () => "mailto:{$this->record->email}"),
        ];
    }
}
