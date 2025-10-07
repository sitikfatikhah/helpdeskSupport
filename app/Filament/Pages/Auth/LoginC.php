<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login;
use Illuminate\Validation\ValidationException;

class LoginC extends Login
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getLoginFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login') // ubah jadi "login", bukan "NIK"
            ->label('NIK / Name')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->required()
            ->revealable()
            ->autocomplete('current-password')
            ->extraInputAttributes(['tabindex' => 2]);
    }

    /**
     * Ambil kredensial login dari form
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        // Jika input angka → pakai nik, kalau tidak → pakai name
        $login_type = ctype_digit($data['login']) ? 'nik' : 'name';

        return [
            $login_type    => $data['login'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('Login failed.'),
        ]);
    }
}
