<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\User;

#[Layout('components.layouts.guest')]
class ResetPassword extends Component
{
    #[Title('| Forgot Password')]

    public $token = null;
    public $password;
    public $password_confirmation;

    public function mount($token)
    {
        $this->token = DB::table('password_reset_tokens')->where('token', $token)->first();
        if ($this->token == null) {
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
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::where('email', $this->token->email)->update([
            'password' => bcrypt($this->password)
        ]);

        if ($user) {
            return redirect()->to('/');
        }
    }
}
