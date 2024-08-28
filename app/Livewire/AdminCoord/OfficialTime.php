<?php

namespace App\Livewire\AdminCoord;

use Carbon\Carbon;
use App\Enums\Role;
use App\Actions\Azure;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Filament\Tables\Table;
use App\Enums\ScheduleType;
use Livewire\Attributes\Title;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\OfficialTime as ModelsOfficialTime;
use Illuminate\Contracts\Database\Eloquent\Builder;

class OfficialTime extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    #[Title('| Official Time')]
    public function render()
    {
        return view('livewire.admin-coord.official-time');
    }

    public function table(Table $table): Table
    {
        $time = [
            // '05:00:00' => '5:00 AM',
            // '05:30:00' => '5:30 AM',
            // '06:00:00' => '6:00 AM',
            // '06:30:00' => '6:30 AM',
            '07:00:00' => '7:00 AM to 4:00 PM',
            '07:30:00' => '7:30 AM to 4:30 PM',
            '08:00:00' => '8:00 AM to 5:00 PM',
            '08:30:00' => '8:30 AM to 5:30 PM',
            '09:00:00' => '9:00 AM to 6:00 PM',
            '09:30:00' => '9:30 AM to 6:30 PM',
            // '10:00:00' => '10:00 AM',
        ];

        return $table
            ->query(
                Employee::query()
                    ->with(['official_time' => fn($query) => $query->where('status', 'approved')])
                    ->where('employment_status', true)
                    ->departmentCovered()
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
                \Filament\Tables\Columns\TextColumn::make('department.description')
                    ->label('Department')
                    ->sortable(['group', 'center', 'office'])
                    ->visible(fn() => auth()->user()->hasRole(Role::CENTERADMINCOORD) || auth()->user()->hasRole(Role::GROUPADMINCOORD)),
                \Filament\Tables\Columns\TextColumn::make('appointment_status')
                    ->label('Appointment Status')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('official_time.type')
                    ->label('Schedule Type')
                    ->badge()
                    ->placeholder('Not set')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('official_time.time_in')
                    ->label('Official Time')
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('g:i A') . ' - ' . Carbon::parse($state)->copy()->addHours(9)->format('g:i A');
                    })
                    ->placeholder('Not set')
                    ->sortable(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('set-bulk-time')
                    ->label('Bulk set of Official Time')
                    ->button()
                    ->modalWidth('sm')
                    ->modalHeading('Set Official Time of all the selected.')
                    ->form([
                        \Filament\Forms\Components\Select::make('schedule_type')
                            ->label('Schedule Type')
                            ->options(ScheduleType::class)
                            ->native(false)
                            ->reactive()
                            ->required(),
                        \Filament\Forms\Components\Select::make('official_time')
                            ->label('Official Time')
                            ->options($time)
                            ->native(false)
                            ->required()
                            ->hidden(fn(Get $get) => $get('schedule_type') != ScheduleType::FIXED->value),
                        \Filament\Forms\Components\DatePicker::make('effectivity_date')
                            ->label('Effectivity Date')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->required(),
                        \Filament\Forms\Components\FileUpload::make('attachment')
                            ->acceptedFileTypes(['application/pdf', 'application/msword'])
                            ->required()
                            ->directory('officialtime-movs')
                            ->visibility('private')
                        // ->hidden(fn ($record) => !$record->official_time)
                    ])
                    ->action(function (Collection $records, $data, Azure $azure) {
                        $file = Storage::disk('public')->get($data['attachment']);
                        $file_explode = explode('/', $data['attachment']);
                        $filename = $file_explode[1];
                        // $azure->put("movs", $file, $filename);
                        Storage::disk('public')->delete($data['attachment']);

                        foreach ($records as $record) {
                            if ($data['schedule_type'] == ScheduleType::FULLFLEXI->value) {
                                $official_time = $record->official_time()->create([
                                    'hris_number' => $record->hris_number,
                                    'schedule_type' => $data['schedule_type'],
                                    'effectivity_date' => $data['effectivity_date'],
                                    'created_by' => auth()->user()->hris_number,
                                ]);
                            } else if ($data['schedule_type'] == ScheduleType::FIXED->value) {
                                $official_time = $record->official_time()->create([
                                    'hris_number' => $record->hris_number,
                                    'time_in' => $data['official_time'],
                                    'schedule_type' => $data['schedule_type'],
                                    'effectivity_date' => $data['effectivity_date'],
                                    'created_by' => auth()->user()->hris_number,
                                ]);
                            }

                            $official_time->movs()->create(['filename' => 'movs/' . $filename]);
                        }

                        Notification::make()
                            ->title("Saved Successfully!")
                            ->body($records->count() . " Official Time change request has been submitted. Please wait for the HR to evaluate!")
                            ->success()
                            ->color('success')
                            ->send();
                    })
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view-request')
                    ->button()
                    ->modal()
                    ->modalWidth('xl')
                    ->modalHeading(fn($record) => 'Set Employment Status of ' . $record->apost_first_name . " information")
                    ->hidden(function ($record) {
                        if ($record->latest_official_time) {
                            if ($record->latest_official_time->status == 'pending') {
                                return false;
                            }
                        }
                        return true;
                    })
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('hris_number')
                            ->label('HRIS Number'),
                        \Filament\Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name'),
                        \Filament\Infolists\Components\TextEntry::make('latest_official_time.schedule_type')
                            ->badge()
                            ->label('Requested Schedule Type'),
                        \Filament\Infolists\Components\TextEntry::make('latest_official_time.time_in')
                            ->label('Requested Official Time')
                            ->formatStateUsing(function ($state) {
                                $parsed = Carbon::parse($state);
                                return $parsed->format('g:i A') . ' - ' . $parsed->copy()->addHours(9)->format('g:i A');
                            })
                            ->hidden(fn($record) => $record->latest_official_time->schedule_type == ScheduleType::FULLFLEXI),
                        \Filament\Infolists\Components\TextEntry::make('latest_official_time.status')
                            ->label('Status')
                            ->formatStateUsing(function ($state) {
                                if ($state === 'pending') {
                                    return 'Change request is still in process. Kindly wait for the approval from HR Admin.';
                                } else if ($state === 'disapproved') {
                                    return 'Change request has been disapproved. You can submit again for approval to HR Admin.';
                                }
                            }),
                    ])
                    ->modalSubmitAction(false),
                \Filament\Tables\Actions\Action::make('set-time')
                    ->label('Set Schedule')
                    ->button()
                    ->modalWidth('sm')
                    ->modalHeading(fn($record) => 'Set Employment Status of ' . $record->apost_first_name . " information")
                    ->hidden(function ($record): bool {
                        if ($record->latest_official_time) {
                            if ($record->latest_official_time->status === 'pending') {
                                return true;
                            }
                        }
                        return false;
                    })
                    ->form([
                        \Filament\Forms\Components\Select::make('schedule_type')
                            ->label('Schedule Type')
                            ->options(ScheduleType::class)
                            ->native(false)
                            ->reactive()
                            ->required(),
                        \Filament\Forms\Components\Select::make('official_time')
                            ->label('Official Time')
                            ->options($time)
                            ->native(false)
                            ->required()
                            ->hidden(fn(Get $get) => $get('schedule_type') != ScheduleType::FIXED->value),
                        \Filament\Forms\Components\DatePicker::make('effectivity_date')
                            ->label('Effectivity Date')
                            ->native(false)
                            ->displayFormat('F d, Y')
                            ->weekStartsOnSunday()
                            ->closeOnDateSelection()
                            ->required(),
                        \Filament\Forms\Components\FileUpload::make('attachment')
                            ->acceptedFileTypes(['application/pdf', 'application/msword'])
                            ->required()
                            ->directory('officialtime-movs')
                            ->visibility('private')
                        // ->hidden(fn ($record) => !$record->official_time)
                    ])
                    ->action(function ($record, $data, Azure $azure) {
                        $file = Storage::disk('public')->get($data['attachment']);
                        $file_explode = explode('/', $data['attachment']);
                        $filename = $file_explode[1];
                        // $azure->put("movs", $file, $filename);
                        Storage::disk('public')->delete($data['attachment']);

                        if ($data['schedule_type'] == ScheduleType::FULLFLEXI->value) {
                            $official_time = $record->official_time()->create([
                                'hris_number' => $record->hris_number,
                                'schedule_type' => $data['schedule_type'],
                                'effectivity_date' => $data['effectivity_date'],
                                'created_by' => auth()->user()->hris_number,
                            ]);
                        } else if ($data['schedule_type'] == ScheduleType::FIXED->value) {
                            $official_time = $record->official_time()->create([
                                'hris_number' => $record->hris_number,
                                'time_in' => $data['official_time'],
                                'schedule_type' => $data['schedule_type'],
                                'effectivity_date' => $data['effectivity_date'],
                                'created_by' => auth()->user()->hris_number,
                            ]);
                        }

                        $official_time->movs()->create(['filename' => 'movs/' . $filename]);

                        Notification::make()
                            ->title("Saved Successfully!")
                            ->body($record->apost_first_name . " official time change request has been submitted. Please wait for the HR to evaluate!")
                            ->success()
                            ->color('success')
                            ->send();
                    })
            ])
            ->defaultSort('last_name');
    }
}
