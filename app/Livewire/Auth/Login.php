<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Title('| Login')]
    #[Validate('required|email|max:255')]
    public $email;

    #[Validate('required|string|max:255')]
    public $password;

    public $remember = false;

    public function render()
    {
        return view('livewire.auth.login');
    }

    public function login()
    {
        $validated = $this->validate();
        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return $this->addError('credentials', "Too many login attempts. Try again in {$seconds} seconds.");
        }

        if (! Auth::attempt($validated, $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            return $this->addError('credentials', 'Invalid email or password.');
        }

        RateLimiter::clear($throttleKey);
        request()->session()->regenerate();

        $this->redirect('/home');
    }
}
