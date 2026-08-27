<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Mail\SendResetPasswordMail;
use Illuminate\Support\Facades\Mail;

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

        $user = User::where('email', $this->email)->first();
        if (!$user) {
            $this->addError('email', 'Email does not exist');
        } else {
            $tokenData = (string) str()->uuid();
            $timestamp = now();
            $prev = DB::table('password_reset_tokens')->where('email', $user->email)->first();
            if ($prev) {
                $created_at = \Carbon\Carbon::parse($prev->created_at);
                if (now()->between($created_at, $created_at->copy()->addSeconds(60))) {
                    $this->addError('spam', 'Please, try again after 60 seconds.');
                    $this->status = false;
                } else {
                    DB::table('password_reset_tokens')->where('email', $user->email)->delete();
                    DB::table('password_reset_tokens')->insert([
                        'email' => $user->email,
                        'token' => $tokenData,
                        'created_at' => $timestamp
                    ]);

                    Mail::to($user->email)->queue(new SendResetPasswordMail($tokenData));
                    $this->status = true;
                    $this->email = "";
                }
            } else {
                DB::table('password_reset_tokens')->insert([
                    'email' => $user->email,
                    'token' => $tokenData,
                    'created_at' => $timestamp
                ]);

                Mail::to($user->email)->queue(new SendResetPasswordMail($tokenData));
                $this->status = true;
                $this->email = "";
            }
        }
    }
}
