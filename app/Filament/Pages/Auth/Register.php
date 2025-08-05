<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Auth;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                     ->schema([
                         FileUpload::make('avatar')
                             ->directory('avatares')
                             ->image()
                             ->imageEditor()
                             ->imageEditorAspectRatios([
                                 '1:1',
                             ])
                             ->label('Foto do Perfil'),
                         $this->getNameFormComponent(),
                         $this->getEmailFormComponent(),
                         $this->getPasswordFormComponent(),
                         $this->getPasswordConfirmationFormComponent(),
                     ])
                     ->statePath('data'),
            ),
        ];
    }

    public function register(): RegistrationResponse|null
    {
        try {
            $this->rateLimit(2);
        }
        catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        Auth::login($user);

        session()->regenerate();

        if (Auth::check()) {
            Notification::make()
                        ->success()
                        ->title('Usuário logado com sucesso!')
                        ->seconds(5)
                        ->send();

            return app(RegistrationResponse::class);
        }

        return app(RegistrationResponse::class);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        Filament::auth()->logout();

        parent::mount();
    }
}
