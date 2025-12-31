<?php

namespace App\Providers\Filament;

use App\Filament\Pages\CashRegister;
use App\Filament\Pages\FinancialReport;
use App\Filament\Pages\KitchenDisplay;
use App\Filament\Pages\ManageSettings;
use App\Filament\Pages\WaiterPos;
use App\Filament\Resources\AreaResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\IngredientResource;
use App\Filament\Resources\OptionGroupResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\PrinterResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\SupplierResource;
use App\Filament\Resources\TableResource;
use App\Filament\Resources\UserResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(function () {
                try {
                    return app(\App\Settings\GeneralSettings::class)->site_name ?? 'Ritaj Smart Resto';
                } catch (\Exception $e) {
                    return 'Ritaj Smart Resto';
                }
            })
            ->brandLogo(function () {
                try {
                    $logo = app(\App\Settings\GeneralSettings::class)->site_logo;
                    return $logo ? Storage::url($logo) : null;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->colors([
                'primary' => Color::Amber,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            // Professional, business-oriented navigation taxonomy
            ->navigationGroups([
                'Exploitation',
                'Logistique & Achats',
                'Ingénierie Menu',
                'Pilotage & Finance',
                'Infrastructure & Sécurité',
            ])
            // Force a professional order & classification (independent from each Resource/Page settings)
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->groups([
                        NavigationGroup::make('Exploitation')->items([
                            ...WaiterPos::getNavigationItems(),
                            ...KitchenDisplay::getNavigationItems(),
                            ...CashRegister::getNavigationItems(),
                            ...OrderResource::getNavigationItems(),
                        ]),

                        NavigationGroup::make('Logistique & Achats')->items([
                            ...IngredientResource::getNavigationItems(),
                            ...SupplierResource::getNavigationItems(),
                            ...PurchaseOrderResource::getNavigationItems(),
                        ]),

                        NavigationGroup::make('Ingénierie Menu')->items([
                            ...CategoryResource::getNavigationItems(),
                            ...ProductResource::getNavigationItems(),
                            ...OptionGroupResource::getNavigationItems(),
                        ]),

                        NavigationGroup::make('Pilotage & Finance')->items([
                            ...Pages\Dashboard::getNavigationItems(),
                            ...FinancialReport::getNavigationItems(),
                        ]),

                        NavigationGroup::make('Infrastructure & Sécurité')->items([
                            ...AreaResource::getNavigationItems(),
                            ...TableResource::getNavigationItems(),
                            ...PrinterResource::getNavigationItems(),
                            ...ManageSettings::getNavigationItems(),
                            ...UserResource::getNavigationItems(),
                        ]),
                    ]);
            })
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
}
