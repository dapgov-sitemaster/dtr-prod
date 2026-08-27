<?php

namespace App\Livewire\Profile;

use App\Actions\Azure;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Filament\Notifications\Notification;

class IdentityPhoto extends Component
{
    use WithFileUploads;

    #[Title('| Identity Photo')]
    public $current_image;
    #[Validate('required|file|mimes:png,jpg|max:5000')]
    public $photo;

    public function save(Azure $azure)
    {
        $validated = $this->validate();
        $employee = auth()->user()->employee;

        try {
            if ($employee->identity_photo_path) {
                $azure->delete($employee->identity_photo_path);
            }

            $filename = $employee->hris_number . '-' . uniqid() . '.' . $validated['photo']->getClientOriginalExtension();

            $azure->put("avatar", file_get_contents($validated['photo']->getRealPath()), $filename);

            $employee->identity_photo_path = 'avatar/' . $filename;
            $employee->save();

            Notification::make()
                ->title('Saved Successfully!')
                ->body('Identity Photo has been saved!')
                ->success()
                ->color('success')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Upload Failed!')
                ->body('Failed to upload your Photo!')
                ->danger()
                ->color('danger')
                ->send();
        }
        // $response = $azure->put("avatar", file_get_contents($validated['photo']->getRealPath()), $filename);
        // if($response == 201) {
        //     Notification::make()
        //         ->title('Identity Photo has been saved!')
        //         ->success()
        //         ->send();
        // }
        // else {
        //     Notification::make()
        //         ->title('Failed to upload your Photo!')
        //         ->danger()
        //         ->send();

        // }
    }

    public function render(Azure $azure)
    {
        // dd(auth()->user()->employee);
        if (auth()->user()->employee->identity_photo_path) {
            $this->current_image = $azure->get(auth()->user()->employee->identity_photo_path);

            // dd($this->current_image);
        }
        return view('livewire.profile.identity-photo');
    }
}
