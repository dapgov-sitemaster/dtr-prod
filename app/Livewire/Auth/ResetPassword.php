<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ResetPassword extends Component
{
    #[Title('| Forgot Password')]
    public $token;

    public $email;

    public $password;

    public $password_confirmation;

    public function mount(string $token)
    {
        $this->token = $token;
        $this->email = request()->query('email');

        $user = User::where('email', $this->email)->first();

        if (! $user || ! Password::broker()->tokenExists($user, $token)) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }

    public function submit()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return null;
        }

        return redirect()->route('login');
    }
}
