<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Lead Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Contact Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('company')
                        ->maxLength(255),
                ])
                ->columns(2),

            Forms\Components\Section::make('Project Details')
                ->schema([
                    Forms\Components\Select::make('project_type')
                        ->options([
                            'mobile_app' => 'Mobile App',
                            'web_app' => 'Web Application',
                            'enterprise' => 'Enterprise Software',
                            'ui_ux' => 'UI/UX Design',
                            'team_hiring' => 'Team Hiring',
                        ]),
                    Forms\Components\Select::make('budget')
                        ->options([
                            'under_5k' => 'Under $5,000',
                            '5k_15k' => '$5,000 - $15,000',
                            '15k_50k' => '$15,000 - $50,000',
                            '50k_100k' => '$50,000 - $100,000',
                            'over_100k' => 'Over $100,000',
                        ]),
                    Forms\Components\Select::make('timeline')
                        ->options([
                            'asap' => 'ASAP',
                            '1_month' => '1 Month',
                            '1_3_months' => '1-3 Months',
                            '3_6_months' => '3-6 Months',
                            'flexible' => 'Flexible',
                        ]),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull()
                        ->rows(3),
                ])
                ->columns(3),

            Forms\Components\Section::make('Status & Management')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options(Lead::getStatuses())
                        ->required()
                        ->default('new'),
                    Forms\Components\Select::make('priority')
                        ->options(Lead::getPriorities())
                        ->default('normal'),
                    Forms\Components\Select::make('source')
                        ->options(Lead::getSources())
                        ->default('website'),
                    Forms\Components\TextInput::make('assigned_to')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('notes')
                        ->columnSpanFull()
                        ->rows(2),
                ])
                ->columns(2),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Project Details')
                    ->schema([
                        Forms\Components\Select::make('project_type')
                            ->options([
                                'mobile_app' => 'Mobile App',
                                'web_app' => 'Web Application',
                                'enterprise' => 'Enterprise Software',
                                'ui_ux' => 'UI/UX Design',
                                'team_hiring' => 'Team Hiring',
                            ]),
                        Forms\Components\Select::make('budget')
                            ->options([
                                'under_5k' => 'Under $5,000',
                                '5k_15k' => '$5,000 - $15,000',
                                '15k_50k' => '$15,000 - $50,000',
                                '50k_100k' => '$50,000 - $100,000',
                                'over_100k' => 'Over $100,000',
                            ]),
                        Forms\Components\Select::make('timeline')
                            ->options([
                                'asap' => 'ASAP',
                                '1_month' => '1 Month',
                                '1_3_months' => '1-3 Months',
                                '3_6_months' => '3-6 Months',
                                'flexible' => 'Flexible',
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(4),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Status & Management')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(Lead::getStatuses())
                            ->required()
                            ->default('new'),
                        Forms\Components\Select::make('priority')
                            ->options(Lead::getPriorities())
                            ->default('normal'),
                        Forms\Components\Select::make('source')
                            ->options(Lead::getSources())
                            ->default('website'),
                        Forms\Components\TextInput::make('assigned_to')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Attribution')
                    ->schema([
                        Forms\Components\TextInput::make('utm_source')
                            ->disabled(),
                        Forms\Components\TextInput::make('utm_medium')
                            ->disabled(),
                        Forms\Components\TextInput::make('utm_campaign')
                            ->disabled(),
                        Forms\Components\TextInput::make('landing_page')
                            ->disabled(),
                    ])
                    ->columns(4)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Lead $record): ?string => $record->company),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->size('sm')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->size('sm')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Lead::getSources()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'contact_form' => 'info',
                        'exit_intent' => 'warning',
                        'referral' => 'success',
                        'paid' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Lead::getStatuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'warning',
                        'contacted' => 'info',
                        'qualified' => 'primary',
                        'proposal' => 'info',
                        'negotiation' => 'warning',
                        'converted' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Lead::getPriorities()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->size('sm')
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Lead::getStatuses())
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('source')
                    ->options(Lead::getSources())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(Lead::getPriorities()),
                Tables\Filters\Filter::make('created_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->label('Today'),
                Tables\Filters\Filter::make('created_this_week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('This Week'),
                Tables\Filters\Filter::make('hot_leads')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['urgent', 'high']))
                    ->label('Hot Leads'),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make('view')
                    ->modalHeading(fn (Lead $record) => "Lead: {$record->name}")
                    ->modalWidth('4xl')
                    ->slideOver(),
                Tables\Actions\EditAction::make()
                    ->modalHeading(fn (Lead $record) => "Edit: {$record->name}")
                    ->modalWidth('4xl')
                    ->slideOver(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('sendEmail')
                        ->label('Email')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->url(fn (Lead $record): string => "mailto:{$record->email}"),
                    Tables\Actions\Action::make('markContacted')
                        ->label('Mark Contacted')
                        ->icon('heroicon-o-check')
                        ->color('info')
                        ->action(fn (Lead $record) => $record->update([
                            'status' => 'contacted',
                            'last_contacted_at' => now(),
                        ]))
                        ->visible(fn (Lead $record): bool => $record->status === 'new')
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteAction::make(),
                ])->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsContacted')
                        ->label('Mark as Contacted')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'contacted',
                            'last_contacted_at' => now(),
                        ]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            // Export functionality can be added here
                        }),
                ]),
            ])
            ->poll('60s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->copyMessage('Email copied!'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->icon('heroicon-m-phone')
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('company')
                            ->label('Company')
                            ->icon('heroicon-m-building-office')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Lead Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => Lead::getStatuses()[$state] ?? $state)
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
                        Infolists\Components\TextEntry::make('priority')
                            ->label('Priority')
                            ->badge()
                            ->formatStateUsing(fn ($state) => Lead::getPriorities()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('source')
                            ->label('Source')
                            ->badge()
                            ->formatStateUsing(fn ($state) => Lead::getSources()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                'contact_form' => 'info',
                                'exit_intent' => 'warning',
                                'referral' => 'success',
                                'paid' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('assigned_to')
                            ->label('Assigned To')
                            ->placeholder('Unassigned'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Project Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('project_type')
                            ->label('Project Type')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'mobile_app' => 'Mobile App',
                                'web_app' => 'Web Application',
                                'enterprise' => 'Enterprise Software',
                                'ui_ux' => 'UI/UX Design',
                                'team_hiring' => 'Team Hiring',
                                default => $state ?? 'Not specified',
                            }),
                        Infolists\Components\TextEntry::make('budget')
                            ->label('Budget')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'under_5k' => 'Under $5,000',
                                '5k_15k' => '$5,000 - $15,000',
                                '15k_50k' => '$15,000 - $50,000',
                                '50k_100k' => '$50,000 - $100,000',
                                'over_100k' => 'Over $100,000',
                                default => $state ?? 'Not specified',
                            }),
                        Infolists\Components\TextEntry::make('timeline')
                            ->label('Timeline')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'asap' => 'ASAP',
                                '1_month' => '1 Month',
                                '1_3_months' => '1-3 Months',
                                '3_6_months' => '3-6 Months',
                                'flexible' => 'Flexible',
                                default => $state ?? 'Not specified',
                            }),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->markdown()
                            ->placeholder('No notes yet'),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Attribution & Tracking')
                    ->schema([
                        Infolists\Components\TextEntry::make('utm_source')
                            ->label('UTM Source')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('utm_medium')
                            ->label('UTM Medium')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('utm_campaign')
                            ->label('UTM Campaign')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('landing_page')
                            ->label('Landing Page')
                            ->placeholder('—'),
                    ])
                    ->columns(4)
                    ->collapsed(),

                Infolists\Components\Section::make('Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M j, Y g:i A'),
                        Infolists\Components\TextEntry::make('last_contacted_at')
                            ->label('Last Contacted')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Never'),
                        Infolists\Components\TextEntry::make('converted_at')
                            ->label('Converted')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Not converted'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M j, Y g:i A'),
                    ])
                    ->columns(4)
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
