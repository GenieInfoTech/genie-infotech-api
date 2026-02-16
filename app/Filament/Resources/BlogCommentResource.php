<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogCommentResource\Pages;
use App\Filament\Resources\BlogCommentResource\RelationManagers;
use App\Models\BlogComment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class BlogCommentResource extends Resource
{
    protected static ?string $model = BlogComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? 'warning' : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Comment Details')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->relationship('post', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'spam' => 'Spam',
                                'trash' => 'Trash',
                            ])
                            ->required()
                            ->default('pending')
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Author Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Registered User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Guest comment')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('author_name')
                            ->label('Guest Name')
                            ->maxLength(100)
                            ->placeholder('For guest comments')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('author_email')
                            ->label('Guest Email')
                            ->email()
                            ->maxLength(150)
                            ->placeholder('For guest comments')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('author_ip')
                            ->label('IP Address')
                            ->maxLength(45)
                            ->disabled()
                            ->columnSpan(1),
                    ])->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->url(fn ($record) => $record->post ? route('filament.admin.resources.blog-posts.edit', $record->post) : null),
                Tables\Columns\TextColumn::make('author_name')
                    ->label('Author')
                    ->getStateUsing(fn ($record) => $record->getAuthorName())
                    ->searchable(['author_name', 'users.name'])
                    ->icon(fn ($record) => $record->isRegisteredUser() ? 'heroicon-o-user-circle' : 'heroicon-o-user')
                    ->iconColor(fn ($record) => $record->isRegisteredUser() ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('content')
                    ->label('Comment')
                    ->limit(60)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => fn ($state) => in_array($state, ['spam', 'trash']),
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-exclamation-circle' => fn ($state) => in_array($state, ['spam', 'trash']),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'spam' => 'Spam',
                        'trash' => 'Trash',
                    ])
                    ->default('pending'),
                Tables\Filters\Filter::make('registered_users')
                    ->label('Registered Users Only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('user_id')),
                Tables\Filters\Filter::make('guest_comments')
                    ->label('Guest Comments Only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('user_id')),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation(false)
                    ->visible(fn ($record) => $record->status !== 'approved')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved', 'approved_at' => now()]);
                        Notification::make()
                            ->success()
                            ->title('Comment approved')
                            ->send();
                    }),
                Tables\Actions\Action::make('spam')
                    ->label('Spam')
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'spam']);
                        Notification::make()
                            ->warning()
                            ->title('Marked as spam')
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['status' => 'approved', 'approved_at' => now()]));
                            Notification::make()
                                ->success()
                                ->title('Comments approved')
                                ->body(count($records) . ' comments approved successfully')
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('spam')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-exclamation-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['status' => 'spam']));
                            Notification::make()
                                ->warning()
                                ->title('Marked as spam')
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBlogComments::route('/'),
            'create' => Pages\CreateBlogComment::route('/create'),
            'edit' => Pages\EditBlogComment::route('/{record}/edit'),
        ];
    }
}
