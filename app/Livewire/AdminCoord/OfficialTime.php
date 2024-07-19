<?php

namespace App\Livewire\AdminCoord;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use Livewire\Attributes\Title;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

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
            ->query(Employee::where('department_id', auth()->user()->employee->department_id)->orderBy('last_name'))
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
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('g:i A').' - '.Carbon::parse($state)->addHours(9)->format('g:i A'))
                    ->placeholder('Not set')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('set-time')
                    ->button()
                    ->modalWidth('sm')
                    ->modalHeading(function($record) {
                        $name = (str($record->first_name)->endsWith('s')) ? $record->first_name."'" : $record->first_name."'s";
                        return 'Set '.str($name)->headline()." official time";
                    })
                    ->form([
                        \Filament\Forms\Components\Select::make('official_time')
                            ->label('Official Time')
                            ->options($time)
                            ->native(false)
                            ->required(),
                        \Filament\Forms\Components\FileUpload::make('attachment')
                            ->acceptedFileTypes(['application/pdf', 'application/msword'])
                            ->required()
                            ->hidden(fn($record) => $record->official_time->isEmpty())
                    ])
                    ->action(function($record, $data) {
                        dd($data);
                        if(!$record->official_time->isEmpty()) {
                            $record->save();
                        }
                        else {
                            $record->official_time()->create([
                                'hris_number' => $record->hris_number,
                                'time_in' => $data['official_time'],
                                'status' => 'approved',
                                'created_by' => auth()->user()->hris_number,
                            ]);
                        }

                        $name = (str($record->first_name)->endsWith('s')) ? $record->first_name."'" : $record->first_name."'s";

                        Notification::make()
                            ->title("Saved Successfully!")
                            ->body($name." official time has been saved!")
                            ->success()
                            ->color('success')
                            ->send();
                    })
            ]);
    }
}
