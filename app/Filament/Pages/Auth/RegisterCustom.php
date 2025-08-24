<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Pages\Auth\Register;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;

class RegisterCustom extends Register
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNamaDesaFormComponent(),
                        $this->getKecamatanFormComponent(),
                        $this->getKabupatenFormComponent(),
                        $this->getNamaKetuaPKKFormComponent(),
                        $this->getTeleponFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getNamaDesaFormComponent(): Component
    {
        return TextInput::make('nama_desa')
            ->label('Nama Desa')
            ->required()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getKecamatanFormComponent(): Component
    {
        return TextInput::make('kecamatan')
            ->label('Kecamatan')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getKabupatenFormComponent(): Component
    {
        return TextInput::make('kabupaten')
            ->label('Kabupaten')
            ->required()
            ->extraInputAttributes(['tabindex' => 3]);
    }

    protected function getNamaKetuaPKKFormComponent(): Component
    {
        return TextInput::make('nama_ketua_pkk')
            ->label('Nama Ketua PKK')
            ->required()
            ->extraInputAttributes(['tabindex' => 4]);
    }

    protected function getTeleponFormComponent(): Component
    {
        return TextInput::make('telepon')
            ->label('Nomor Telepon')
            ->tel()
            ->nullable()
            ->extraInputAttributes(['tabindex' => 5]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email Kader')
            ->email()
            ->required()
            ->autocomplete()
            ->extraInputAttributes(['tabindex' => 6]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->required()
            ->autocomplete('new-password')
            ->extraInputAttributes(['tabindex' => 7]);
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label('Konfirmasi Password')
            ->password()
            ->required()
            ->autocomplete('new-password')
            ->extraInputAttributes(['tabindex' => 8]);
    }

    protected function handleRegistration(array $data): User
    {
        return User::create([
            'name' => $data['nama_desa'], // <- isi kolom name dari nama_desa
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'nama_desa' => $data['nama_desa'],
            'kecamatan' => $data['kecamatan'],
            'kabupaten' => $data['kabupaten'],
            'nama_ketua_pkk' => $data['nama_ketua_pkk'],
            'telepon' => $data['telepon'] ?? null,
        ]);
    }
}
