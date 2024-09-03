<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class ChangePassword extends Component
{
    #[Title('| Change Password')]
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $user = Auth::user();
        if (!Hash::check('dap12345', $user->password)) {
            return redirect()->route('home');
        }
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }

    public function submit()
    {
        $this->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        User::where('email', Auth::user()->email)->update([
            'password' => bcrypt($this->password)
        ]);

        Notification::make()
            ->title("Password has Changed!")
            ->success()
            ->color('success')
            ->send();

        return redirect()->route('home');
    }
}
