<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLeads extends BaseWidget
{
    protected static ?string $heading = 'Recent Leads';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (Lead $record): ?string => $record->company),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->icon('heroicon-m-envelope')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->icon('heroicon-m-phone')
                    ->size('sm')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Lead::getSources()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'contact_form' => 'info',
                        'exit_intent' => 'warning',
                        'referral' => 'success',
                        'paid' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Lead::getStatuses()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'warning',
                        'contacted' => 'info',
                        'qualified' => 'primary',
                        'proposal' => 'info',
                        'negotiation' => 'warning',
                        'converted' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Lead::getPriorities()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'normal' => 'gray',
                        'low' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update')
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray')
                    ->size('sm')
                    ->modalHeading('Update Lead Status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options(Lead::getStatuses())
                            ->required(),
                        Select::make('priority')
                            ->label('Priority')
                            ->options(Lead::getPriorities()),
                        Textarea::make('notes')
                            ->label('Add Note')
                            ->rows(2),
                    ])
                    ->fillForm(fn (Lead $record): array => [
                        'status' => $record->status,
                        'priority' => $record->priority,
                    ])
                    ->action(function (Lead $record, array $data): void {
                        $updateData = ['status' => $data['status']];

                        if (!empty($data['priority'])) {
                            $updateData['priority'] = $data['priority'];
                        }

                        if (!empty($data['notes'])) {
                            $updateData['notes'] = ($record->notes ? $record->notes . "\n\n" : '') .
                                "[" . now()->format('Y-m-d H:i') . "] " . $data['notes'];
                        }

                        if ($data['status'] === 'contacted' && $record->status !== 'contacted') {
                            $updateData['last_contacted_at'] = now();
                        }

                        if ($data['status'] === 'converted' && $record->status !== 'converted') {
                            $updateData['converted_at'] = now();
                        }

                        $record->update($updateData);

                        Notification::make()
                            ->title('Lead updated successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('primary')
                    ->size('sm')
                    ->url(fn (Lead $record): string => route('filament.admin.resources.leads.view', $record)),
            ])
            ->paginated(false)
            ->poll('30s');
    }
}
