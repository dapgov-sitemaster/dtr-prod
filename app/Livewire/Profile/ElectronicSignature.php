<?php

namespace App\Livewire\Profile;

use App\Actions\Azure;
use Livewire\Component;
use Livewire\Attributes\Title;
use Filament\Notifications\Notification;

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
            $image = explode(";base64,", $result);
            $image_type_aux = explode("image/", $image[0]);
            $image_base64 = base64_decode($image[1]);

            $employee = auth()->user()->employee;

            try {
                if ($employee->signature_path) {
                    $azure->delete($employee->signature_path);
                }

                $filename = $employee->hris_number . '-' . uniqid() . '.' . $image_type_aux[1];
                $path = 'signatures/' . $filename;

                $azure->put("signatures", $image_base64, $filename);

                $employee->signature_path = $path;
                $employee->save();

                Notification::make()
                    ->title('Saved Successfully!')
                    ->body('Identity Photo has been saved!')
                    ->success()
                    ->color('success')
                    ->send();
            } catch (\Exception $e) {
                dd($e->getMessage());
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
