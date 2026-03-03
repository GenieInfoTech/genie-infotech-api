<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->title('Validation failed')
            ->body('Please fix the highlighted errors and try again.')
            ->danger()
            ->send();
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Post created')
            ->body('Blog post has been created successfully.')
            ->success();
    }
}
