<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\UiAvatarsProvider;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\EditarPerfil;
use App\Filament\Widgets\GitBranchWidget;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\UserMenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Platform;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Guava\FilamentKnowledgeBase\Enums\TableOfContentsPosition;
use Guava\FilamentKnowledgeBase\Filament\Panels\KnowledgeBasePanel;
use Guava\FilamentKnowledgeBase\KnowledgeBasePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MarcoGermani87\FilamentCookieConsent\FilamentCookieConsent;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;

class AdminPanelProvider extends PanelProvider
{
    public function boot()
    {
        Filament::serving(function () {
            Filament::registerUserMenuItems([
                'profile' =>
                    UserMenuItem::make()
                        ->label("Perfil")
                        ->url(route('filament.admin.auth.profile')),
                'logout'  =>
                    UserMenuItem::make()
                        ->label('Sair')
                        ->url(route('filament.admin.auth.logout')),

                'roles' => UserMenuItem::make()
                    ->label(function () {
                        $usuario = auth()->user();
                        $roles   = $usuario->roles->pluck('name')->toArray();
                        return " Funções: " . implode(", ", $roles);
                    }),
            ]);
        });
    }

    public function register(): void
    {
        parent::register();
        KnowledgeBasePanel::configureUsing(
            fn(KnowledgeBasePanel $panel) => $panel
                ->colors([
                    'primary' => Color::Red,
                ])
                ->topNavigation()
                ->viteTheme('resources/css/filament/knowledge-base/theme.css')
                ->brandName('Base de Conhecimento')
                ->guestAccess()
                ->disableBreadcrumbs()
                ->tableOfContentsPosition(TableOfContentsPosition::End),
        );
    }

    public function panel(Panel $panel): Panel
    {
        $logoPreta = "https://federalassociados.com.br/Corporates/images/federalassociados.png";
        $logoBranca = "https://federalassociados.com.br/logos/aguiab.png";
        return $panel
            ->default()
            ->path('')
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile(EditarPerfil::class)
            ->databaseNotifications()
            //->registration(Register::class)
            ->defaultAvatarProvider(UiAvatarsProvider::class)
            ->colors([
                'primary' => Color::Zinc,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                GitBranchWidget::class,
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
            ])
            ->plugins([
                FilamentCookieConsent::make(),
                KnowledgeBasePlugin::make()
                    ->helpMenuRenderHook(PanelsRenderHook::TOPBAR_END)
                    ->openDocumentationInNewTab()
                    ->modalPreviews()
                    ->slideOverPreviews(),
                FilamentBackgroundsPlugin::make()
                    ->imageProvider(
                        MyImages::make()
                            ->directory('images/backgrounds')
                    ),
                GlobalSearchModalPlugin::make()
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15rem')
            //->collapsedSidebarWidth('9rem')
            //->sidebarFullyCollapsibleOnDesktop() esconde tudo
            //->breadcrumbs(false)
            ->globalSearchKeyBindings([
                'ctrl+k',
                'command+k'
            ])
            ->globalSearchDebounce('750ms')
            ->globalSearchFieldSuffix(fn(): ?string => match (Platform::detect()) {
                Platform::Windows, Platform::Linux => 'CTRL+K',
                Platform::Mac                      => '⌘K',
                default                            => null,
            })
            ->favicon($logoBranca)
            //->brandName(env('APP_NAME', "Federal"))
            ->brandLogo(fn () => new HtmlString(
                "<img src='{$logoPreta}'/> " . env('APP_NAME', "Federal")
            ))
            ->darkModeBrandLogo(fn () => new HtmlString(
                "<img src='{$logoBranca}'/> " . env('APP_NAME', "Federal")
            ))
            ->brandLogoHeight("2rem")
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
