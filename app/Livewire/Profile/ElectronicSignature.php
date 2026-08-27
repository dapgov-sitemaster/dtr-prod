<?php

namespace App\Livewire\Profile;

use App\Actions\Azure;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

class ElectronicSignature extends Component
{
    public $image;

    public $current_image;

    #[Title('| e-Signature')]
    public function render(Azure $azure)
    {
        if (auth()->user()->employee->signature_path) {
            $this->current_image = $azure->get(auth()->user()->employee->signature_path);
        }

        return view('livewire.profile.electronic-signature');
    }

    public function saveSignature($result, Azure $azure)
    {
        if ($result) {
            if (! is_string($result) || ! preg_match('/^data:image\/(png|jpeg);base64,([A-Za-z0-9+\/=]+)$/', $result, $matches)) {
                $this->addError('image', 'The signature must be a PNG or JPEG image.');

                return;
            }

            $imageBase64 = base64_decode($matches[2], true);

            if ($imageBase64 === false || strlen($imageBase64) > 2 * 1024 * 1024) {
                $this->addError('image', 'The signature must not exceed 2 MB.');

                return;
            }

            $employee = auth()->user()->employee;

            try {
                if ($employee->signature_path) {
                    $azure->delete($employee->signature_path);
                }

                $filename = $employee->hris_number.'-'.Str::uuid().'.'.($matches[1] === 'jpeg' ? 'jpg' : 'png');
                $path = 'signatures/'.$filename;

                $status = $azure->put('signatures', $imageBase64, $filename);

                if ($status < 200 || $status >= 300) {
                    throw new \RuntimeException('Signature upload failed.');
                }

                $employee->signature_path = $path;
                $employee->save();

                Notification::make()
                    ->title('Saved Successfully!')
                    ->body('Identity Photo has been saved!')
                    ->success()
                    ->color('success')
                    ->send();
            } catch (\Exception $e) {
                Log::warning('Electronic signature upload failed.', ['exception' => $e::class]);

                Notification::make()
                    ->title('Upload Failed!')
                    ->body('Failed to upload your Photo!')
                    ->danger()
                    ->color('danger')
                    ->send();
            }
        }
    }
}
