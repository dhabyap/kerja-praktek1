<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([Pages\Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([Widgets\AccountWidget::class, Widgets\FilamentInfoWidget::class])
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function registerNavigationItems(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        if ($user->level_id == 1) {
            return [
                NavigationGroup::make('Management')
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->url(route('filament.admin.pages.dashboard'))
                            ->icon('heroicon-o-home'),
                        NavigationItem::make('Users')
                            ->url('/admin/users')
                            ->icon('heroicon-o-user-group'),
                        NavigationItem::make('Apartments')
                            ->url('/admin/appartements')
                            ->icon('heroicon-o-building-office'),
                        NavigationItem::make('Transactions')
                            ->url('/admin/transactions')
                            ->icon('heroicon-o-currency-dollar'),
                    ]),
            ];
        } elseif ($user->level_id == 2) { // Admin Global
            return [
                NavigationGroup::make('Data Management')
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->url(route('filament.admin.pages.dashboard'))
                            ->icon('heroicon-o-home'),
                        NavigationItem::make('Transactions')
                            ->url('/admin/transactions')
                            ->icon('heroicon-o-currency-dollar'),
                    ]),
            ];
        } elseif ($user->level_id == 3) { // Admin Lokal
            return [
                NavigationGroup::make('Local Data')
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->url(route('filament.admin.pages.dashboard'))
                            ->icon('heroicon-o-home'),
                        NavigationItem::make('My Bookings')
                            ->url('/admin/bookings')
                            ->icon('heroicon-o-calendar'),
                    ]),
            ];
        }

        return [];
    }

}
