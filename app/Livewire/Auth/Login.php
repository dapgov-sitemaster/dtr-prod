<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Title('| Login')]
    #[Validate('required')]
    public $email;

    #[Validate('required')]
    public $password;

    public $remember = false;

    public function render()
    {
        return view('livewire.auth.login');
    }

    public function login()
    {
        $validated = $this->validate();

        if (!Auth::attempt($validated, $this->remember)) {
            return $this->addError('credentials', 'Invalid email or password.');
        }

        $this->redirect('/home');
    }
}
