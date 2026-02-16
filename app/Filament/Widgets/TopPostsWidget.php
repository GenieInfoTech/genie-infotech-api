<?php

namespace App\Filament\Widgets;

use App\Models\BlogPost;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopPostsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Top Performing Posts (Last 30 Days)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BlogPost::query()
                    ->where('status', 'published')
                    ->withSum(['analytics' => function ($q) {
                        $q->where('date', '>=', now()->subDays(30));
                    }], 'views')
                    ->orderBy('analytics_sum_views', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->weight('bold')
                    ->url(fn (BlogPost $record): string => route('filament.admin.resources.blog-posts.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('analytics_sum_views')
                    ->label('Views (30d)')
                    ->sortable()
                    ->icon('heroicon-o-eye')
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('views')
                    ->label('Total Views')
                    ->sortable()
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('like_count')
                    ->label('Likes')
                    ->sortable()
                    ->icon('heroicon-o-heart')
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Comments')
                    ->counts('comments')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (BlogPost $record): string => route('filament.admin.resources.blog-posts.view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
                Tables\Actions\Action::make('edit')
                    ->url(fn (BlogPost $record): string => route('filament.admin.resources.blog-posts.edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil'),
            ])
            ->paginated(false);
    }
}
