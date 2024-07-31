<?php

namespace App\Livewire\AdminCoord;

use Carbon\Carbon;
use App\Actions\Azure;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use Livewire\Attributes\Title;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\OfficialTime as ModelsOfficialTime;
use Illuminate\Contracts\Database\Eloquent\Builder;

class OfficialTime extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    #[Title('| Employee Masterlist')]
    public function render()
    {
        return view('livewire.admin-coord.official-time');
    }

    public function table(Table $table): Table
    {
        $time = [
            '05:00:00' => '5:00 AM',
            '05:30:00' => '5:30 AM',
            '06:00:00' => '6:00 AM',
            '06:30:00' => '6:30 AM',
            '07:00:00' => '7:00 AM',
            '07:30:00' => '7:30 AM',
            '08:00:00' => '8:00 AM',
            '08:30:00' => '8:30 AM',
            '09:00:00' => '9:00 AM',
            '09:30:00' => '9:30 AM',
            '10:00:00' => '10:00 AM',
        ];

        return $table
            ->query(Employee::with(['official_time' => fn (Builder $query) => $query->where('status', 'approved')])->where('department_id', auth()->user()->employee->department_id)->orderBy('last_name'))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('appointment_status')
                    ->label('Appointment Status')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('official_time.time_in')
                    ->label('Official Time')
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('g:i A') . ' - ' . Carbon::parse($state)->copy()->addHours(9)->format('g:i A');
                    })
                    ->placeholder('Not set')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view-request')
                    ->button()
                    ->modal()
                    ->modalWidth('lg')
                    ->modalHeading(function ($record) {
                        $name = (str($record->first_name)->endsWith('s')) ? $record->first_name . "'" : $record->first_name . "'s";
                        return 'Current status of ' . str($name)->headline() . " change request of Official Time";
                    })
                    ->hidden(function ($record) {
                        $official_time = $record->official_times;
                        if ($official_time) {
                            if ($official_time->status == 'pending') {
                                return false;
                            }
                        }
                        return true;
                    })
                    ->modalContent(function ($record): \Illuminate\Contracts\View\View {
                        return view('components.filament.pages.official-time.view-request', ['record' => $record->official_times]);
                    })
                    ->modalSubmitAction(false),
                \Filament\Tables\Actions\Action::make('set-time')
                    ->button()
                    ->modalWidth('sm')
                    ->modalHeading(function ($record) {
                        $name = (str($record->first_name)->endsWith('s')) ? $record->first_name . "'" : $record->first_name . "'s";
                        return 'Set ' . str($name)->headline() . " official time";
                    })
                    ->hidden(fn ($record) => $record->official_time)
                    ->form([
                        \Filament\Forms\Components\Select::make('official_time')
                            ->label('Official Time')
                            ->options($time)
                            ->native(false)
                            ->required(),
                        \Filament\Forms\Components\FileUpload::make('attachment')
                            ->acceptedFileTypes(['application/pdf', 'application/msword'])
                            ->required()
                            ->directory('officialtime-movs')
                            ->visibility('private')
                            ->hidden(fn ($record) => !$record->official_time)
                    ])
                    ->action(function ($record, $data, Azure $azure) {
                        if (!$record->official_time) {
                            $file = Storage::disk('public')->get($data['attachment']);
                            $file_explode = explode('/', $data['attachment']);
                            $filename = $file_explode[1];
                            $azure->put("movs", $file, $filename);
                            Storage::disk('public')->delete($data['attachment']);

                            $record->official_time()->create([
                                'hris_number' => $record->hris_number,
                                'time_in' => $data['official_time'],
                                'mov' => 'movs/' . $filename,
                                'created_by' => auth()->user()->hris_number,
                            ]);

                            $name = (str($record->first_name)->endsWith('s')) ? $record->first_name . "'" : $record->first_name . "'s";

                            Notification::make()
                                ->title("Saved Successfully!")
                                ->body($name . " official time change request has been submitted. Please wait for the HR to evaluate!")
                                ->success()
                                ->color('success')
                                ->send();
                        } else {
                            $record->official_time()->create([
                                'hris_number' => $record->hris_number,
                                'time_in' => $data['official_time'],
                                'status' => 'approved',
                                'created_by' => auth()->user()->hris_number,
                            ]);

                            $name = (str($record->first_name)->endsWith('s')) ? $record->first_name . "'" : $record->first_name . "'s";

                            Notification::make()
                                ->title("Saved Successfully!")
                                ->body($name . " official time has been saved!")
                                ->success()
                                ->color('success')
                                ->send();
                        }
                    })
            ]);
    }
}
