<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Http\Middleware\SecurityHeaders;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(env('FILAMENT_PATH', 'admin'))
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Inter')
            ->brandName('Genie InfoTech')
            ->brandLogo(asset('images/logo.png'))
            ->darkModeBrandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo-icon.png'))
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Lead Management'),
                NavigationGroup::make()
                    ->label('Content'),
                NavigationGroup::make()
                    ->label('Settings'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SecurityHeaders::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Security configurations
            ->authGuard('web')
            ->revealablePasswords(false) // Don't allow password peek
            // ->spa() // Disabled for compatibility testing
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
                    <style>
                        /* Auth pages background gradient */
                        .fi-simple-layout {
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                            min-height: 100vh;
                        }

                        /* Make the auth card stand out */
                        .fi-simple-main {
                            background: white;
                            border-radius: 1rem;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                        }

                        /* Dark mode adjustments */
                        .dark .fi-simple-layout {
                            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%) !important;
                        }

                        .dark .fi-simple-main {
                            background: rgb(30 41 59);
                        }

                        /* Sticky rich text editor toolbar */
                        .fi-fo-rich-editor trix-toolbar {
                            position: sticky !important;
                            top: 0 !important;
                            z-index: 40 !important;
                            padding: 0.5rem 0 !important;
                            border-bottom: 1px solid rgba(128, 128, 128, 0.2) !important;
                        }

                        .fi-fo-rich-editor trix-toolbar .trix-button-row {
                            background: rgb(30 41 59) !important;
                            border-radius: 0.5rem !important;
                            padding: 0.25rem !important;
                        }

                        /* Ensure the main content area scrolls, not the whole page */
                        .fi-fo-rich-editor trix-editor {
                            max-height: 70vh !important;
                            overflow-y: auto !important;
                        }
                    </style>
                ')
            );
    }
}
