<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ForgotPassword extends Component
{
    #[Title('| Forgot Password')]
    public $email;

    public $status = false;

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function updated($email)
    {
        $this->validateOnly($email);
    }

    public function submit()
    {
        $this->validate();

        Password::sendResetLink(['email' => $this->email]);

        $this->status = true;
        $this->reset('email');
    }
}
