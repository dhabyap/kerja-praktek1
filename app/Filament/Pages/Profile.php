<?php
namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;



class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.profile';
    protected static ?string $routeName = 'filament.admin.pages.profile';
    protected static bool $shouldRegisterNavigation = false;
    use InteractsWithForms;

    public $name;
    public $email;
    public $password;

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Name')
                ->required(),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),

            TextInput::make('password')
                ->label('Ganti Password (Optional)*')
                ->password(),
        ];
    }

    public function submit(): mixed
    {
        $data = $this->form->getState();

        $user = Auth::user();

        $passwordChanged = false;

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $passwordChanged = true;
        } else {
            unset($data['password']);
        }

        $user->update($data);

        Notification::make()
            ->title('Profile updated successfully.')
            ->success()
            ->send();

        if ($passwordChanged) {
            auth()->logout();
            return redirect()->route('filament.admin.auth.login');
        }

        return redirect()->route('filament.admin.pages.profile');
    }


}
