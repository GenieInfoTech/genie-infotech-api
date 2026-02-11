<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\SeoScoreCalculator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Content')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Content')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('excerpt')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('content')
                                    ->required()
                                    ->columnSpanFull()
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('blog-images'),
                            ]),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->maxLength(60)
                                    ->helperText('Max 60 characters for optimal display')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('meta_description')
                                    ->maxLength(160)
                                    ->rows(3)
                                    ->helperText('Max 160 characters for optimal display')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('focus_keyword')
                                    ->label('Focus Keyword')
                                    ->helperText('Primary keyword for SEO optimization'),
                                Forms\Components\TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->url()
                                    ->helperText('Leave blank to use default URL'),
                                Forms\Components\Placeholder::make('seo_score')
                                    ->label('SEO Score')
                                    ->content(function ($record) {
                                        if (!$record) return 'N/A';
                                        $calculator = app(SeoScoreCalculator::class);
                                        $score = $calculator->calculate($record);
                                        return $score . '/100';
                                    }),
                            ]),

                        Forms\Components\Tabs\Tab::make('Media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\FileUpload::make('cover_image')
                                    ->image()
                                    ->directory('blog-covers')
                                    ->maxSize(2048)
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Publishing')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'scheduled' => 'Scheduled',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->live(),
                                Forms\Components\Select::make('author_id')
                                    ->relationship('author', 'name')
                                    ->required()
                                    ->default(fn() => Auth::check() ? Auth::id() : null)
                                    ->searchable(),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->seconds(false)
                                    ->visible(fn ($get) => in_array($get('status'), ['published', 'scheduled'])),
                                Forms\Components\Toggle::make('featured')
                                    ->label('Featured Post')
                                    ->helperText('Featured posts appear in special sections'),
                                Forms\Components\Toggle::make('sticky')
                                    ->label('Sticky Post')
                                    ->helperText('Sticky posts always appear at the top'),
                                Forms\Components\Toggle::make('allow_comments')
                                    ->label('Allow Comments')
                                    ->default(true),
                                Forms\Components\TextInput::make('password')
                                    ->label('Password Protection')
                                    ->password()
                                    ->helperText('Leave blank for public access'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Organize')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                        Forms\Components\TextInput::make('slug')
                                            ->required(),
                                        Forms\Components\Textarea::make('description'),
                                    ])
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('tags')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                        Forms\Components\TextInput::make('slug')
                                            ->required(),
                                    ])
                                    ->searchable(),
                                Forms\Components\Select::make('series')
                                    ->relationship('series', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                        Forms\Components\TextInput::make('slug')
                                            ->required(),
                                        Forms\Components\Textarea::make('description'),
                                    ])
                                    ->searchable(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Advanced')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Placeholder::make('word_count')
                                    ->content(fn ($record) => $record?->word_count ?? 'Auto-calculated on save'),
                                Forms\Components\Placeholder::make('reading_time')
                                    ->label('Reading Time (minutes)')
                                    ->content(fn ($record) => $record?->reading_time ?? 'Auto-calculated on save'),
                                Forms\Components\Placeholder::make('views')
                                    ->label('Total Views')
                                    ->content(fn ($record) => $record?->views ?? 0),
                                Forms\Components\Placeholder::make('like_count')
                                    ->label('Total Likes')
                                    ->content(fn ($record) => $record?->like_count ?? 0),
                                Forms\Components\Placeholder::make('share_count')
                                    ->label('Total Shares')
                                    ->content(fn ($record) => $record?->share_count ?? 0),
                                Forms\Components\Placeholder::make('comments_count')
                                    ->label('Total Comments')
                                    ->content(fn ($record) => $record?->comments()->count() ?? 0),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                        'warning' => 'scheduled',
                        'danger' => 'archived',
                    ]),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('sticky')
                    ->label('Sticky')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->sortable()
                    ->icon('heroicon-o-eye')
                    ->numeric(),
                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Comments')
                    ->counts('comments')
                    ->sortable()
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Featured'),
                Tables\Filters\TernaryFilter::make('sticky')
                    ->label('Sticky'),
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from'),
                        Forms\Components\DatePicker::make('published_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['published_from'], fn ($q, $date) => $q->whereDate('published_at', '>=', $date))
                            ->when($data['published_until'], fn ($q, $date) => $q->whereDate('published_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (BlogPost $record): string => (env('FRONTEND_URL') ?? config('app.url')) . '/blog/' . $record->slug)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('draft')
                        ->icon('heroicon-o-document')
                        ->action(fn ($records) => $records->each->update(['status' => 'draft']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('feature')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['featured' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['featured' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'view' => Pages\ViewBlogPost::route('/{record}'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
