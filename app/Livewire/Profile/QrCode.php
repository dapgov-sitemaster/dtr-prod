<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\Attributes\Title;

class QrCode extends Component
{
    #[Title('| QR Code')]
    public function render()
    {
        return view('livewire.profile.qr-code');
    }
}
