<?php

namespace App\Livewire\HrAdmin;

use Carbon\Carbon;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Employee;
use App\Models\TimeEntry;
use Filament\Tables\Table;
use App\Enums\ScheduleType;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Components\Tab;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TimeEntries extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    #[Title('| Time Entries')]
    public function render()
    {
        return view('livewire.hr-admin.time-entries');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TimeEntry::query()
                    ->with('employee')
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('HRIS Number copied')
                    ->copyMessageDuration(1500),
                \Filament\Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
                \Filament\Tables\Columns\TextColumn::make('department.description')
                    ->label('Department')
                    ->searchable(['group', 'center', 'office'])
                    ->sortable(['group', 'center', 'office']),
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->getStateUsing(fn ($record) => $record->time_start->format('M d, Y'))
                    ->searchable(['time_start'])
                    ->sortable(['time_start']),
                \Filament\Tables\Columns\TextColumn::make('time_start')
                    ->label('Time In')
                    ->formatStateUsing(fn ($state) => $state->format('g:i A')),
                \Filament\Tables\Columns\TextColumn::make('time_end')
                    ->label('Time Out')
                    ->formatStateUsing(fn ($state) => $state->format('g:i A')),
                \Filament\Tables\Columns\TextColumn::make('tag')
                    ->label('Type of Time Entry')
                    ->formatStateUsing(fn ($state) => ($state === 'ros') ? 'Report On-site' : 'Work from Home')
                    ->searchable()
                    ->sortable(),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('create-time-entry')
                    ->icon('heroicon-m-clock')
                    ->label('Add new Time Entry')
                    ->modalHeading('Add new Time Entry')
                    ->modalIcon('heroicon-o-clock')
                    ->slideOver()
                    ->form([
                        \Filament\Forms\Components\Select::make('hris_number')
                            ->label('Employee')
                            ->placeholder('Enter HRIS Number or Name')
                            ->validationAttribute('Employee')
                            ->native(false)
                            ->getSearchResultsUsing(fn (string $search): array => Employee::searchEmployee($search)->limit(50)->get()->pluck('full_name', 'hris_number')->toArray())
                            ->getOptionLabelUsing(fn ($value): ?string => Employee::find($value)?->full_name)
                            ->searchable(['first_name', 'last_name', 'hris_number'])
                            ->required()
                            ->columnSpanFull(),
                        // \Filament\Forms\Components\ToggleButtons::make('type')
                        //     ->label('Select Type')
                        //     ->inline()
                        //     ->live()
                        //     ->options(['time_in' => 'Time In', 'time_out' => 'Time Out', 'both' => 'Time in and out'])
                        //     ->required(),
                        \Filament\Forms\Components\Grid::make([
                            'sm' => 1,
                            'lg' => 2,
                        ])
                            // ->visible(fn (Get $get) => $get('type') != null)
                            ->schema([
                                \Filament\Forms\Components\DatePicker::make('date')
                                    ->label('Select Date')
                                    ->default(now()->format('Y-m-d'))
                                    ->required(),
                                // \Filament\Forms\Components\TimePicker::make('time')
                                //     ->label('Select Time')
                                //     ->seconds(false)
                                //     ->required()
                                //     ->hidden(fn (Get $get) => $get('type') == 'both' && $get('type') != ''),
                                \Filament\Forms\Components\ToggleButtons::make('tag')
                                    ->label('Select Type of Entry')
                                    ->inline()
                                    ->live()
                                    ->options(['wfh' => 'Work from Home', 'ros' => 'Report on-site'])
                                    ->default('ros')
                                    ->required(),
                                // ->visible(fn (Get $get) => $get('type') == 'both' && $get('type') != ''),
                                \Filament\Forms\Components\TimePicker::make('timein')
                                    ->label('Select Time In')
                                    ->seconds(false)
                                    ->required(),
                                // ->visible(fn (Get $get) => $get('type') == 'both' && $get('type') != ''),
                                \Filament\Forms\Components\TimePicker::make('timeout')
                                    ->label('Select Time Out')
                                    ->seconds(false),
                                // ->visible(fn (Get $get) => $get('type') == 'both' && $get('type') != ''),
                            ]),
                    ])
                    // ->mutateFormDataBeforeCreate(function($data) {

                    // })
                    ->before(function ($data, \Filament\Tables\Actions\Action $action) {
                        // if ($data['type'] == 'time_in' || $data['type'] == 'time_out') {
                        //     $time_entry = TimeEntry::where('hris_number', $data['hris_number'])->whereDate('time_start', $data['date'])->orderBy('time_start', 'ASC')->get();
                        //     if ($time_entry->isNotEmpty()) {
                        //         $new = Carbon::parse($data['date'] . ' ' . $data['time']);
                        //         if ($data['type'] == 'time_in' && $new->format('A') == 'AM' && $time_entry->first()->time_end == null) {
                        //             $temp = $time_entry->first()->time_start;
                        //             $time_entry->first()->time_start = $new;
                        //             $time_entry->first()->time_end = $temp;
                        //             $time_entry->save();
                        //         } else if ($data['type'] == 'time_out' && $new->format('A') == 'PM' && $time_entry->last()->time_end == null) {
                        //             if ($time_entry->last()->time_end == null) {
                        //                 $time_entry->last()->time_end = $new;
                        //                 $time_entry->save();
                        //             }
                        //             Notification::make()
                        //                 ->title("Time Entry has been added!")
                        //                 ->success()
                        //                 ->color('success')
                        //                 ->send();
                        //         } else {
                        //             Notification::make()
                        //                 ->title("Conflict to add Time Entry!")
                        //                 ->body('Try to add Time in and Time out.')
                        //                 ->warning()
                        //                 ->color('warning')
                        //                 ->send();
                        //             $action->halt(true);
                        //         }
                        //     } else {
                        //         return Notification::make()
                        //             ->title("No Time Entry found!")
                        //             ->body('Try to add Time in and Time out.')
                        //             ->warning()
                        //             ->color('warning')
                        //             ->send();
                        //         $action->halt(true);
                        //     }
                        // } else {
                        $employee = Employee::with('department', 'official_time')->where('hris_number', $data['hris_number'])->first();
                        $time_entry = new TimeEntry();
                        $time_entry->hris_number = $employee->hris_number;
                        $time_entry->time_start = $data['date'] . ' ' . $data['timein'];
                        $time_entry->time_end = $data['timeout'] ?? $data['date'] . ' ' . $data['timeout'];
                        $time_entry->department_id = $employee->department_id;
                        $time_entry->schedule_type = ($employee->official_time) ? $employee->official_time->schedule_type : ScheduleType::FULLFLEXI;
                        $time_entry->official_time = ($employee->official_time?->schedule_type == ScheduleType::FIXED) ?? $employee->official_time->official_time;
                        $time_entry->tag = $data['tag'];
                        $time_entry->timekeeper_id = auth()->user()->id;
                        $time_entry->save();

                        Notification::make()
                            ->title("New Time Entry has been added!")
                            ->success()
                            ->color('success')
                            ->send();
                        // }
                    })
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make('edit-time-entry')
                    ->icon('heroicon-m-clock')
                    ->button()
                    ->label('Update')
                    ->modalHeading('Update Time Entry')
                    ->modalIcon('heroicon-o-clock')
                    ->mutateRecordDataUsing(function ($data, $record) {
                        $data['time_start'] = $record->time_start;
                        $data['time_end'] = $record->time_end;
                        return $data;
                    })
                    ->form([
                        \Filament\Forms\Components\Grid::make([
                            'sm' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                \Filament\Forms\Components\TimePicker::make('time_start')
                                    ->label('Select Time In')
                                    ->seconds(false)
                                    ->required(),
                                \Filament\Forms\Components\TimePicker::make('time_end')
                                    ->label('Select Time Out')
                                    ->seconds(false),
                            ])
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'time_start' => $data['time_start'],
                            'time_end' => $data['time_end']
                        ]);

                        Notification::make()
                            ->title("Time Entry has been updated!")
                            ->success()
                            ->color('success')
                            ->send();
                    })
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('yearmonth')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('yearmonth')
                            ->label('Select Year and Month')
                            ->type('month')
                            ->default(now()->format('Y-m')),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['yearmonth'],
                                function (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder {
                                    $expl = explode('-', $date);
                                    return $query->whereYear('time_start', $expl[0])->whereMonth('time_start', $expl[1]);
                                },
                            );
                    }),
                \Filament\Tables\Filters\Filter::make('tag')
                    ->columnSpanFull()
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('tag')
                            ->label('Type of Time Entry')
                            ->columns(2)
                            ->options([
                                'ros' => 'Report On-site',
                                'wfh' => 'Work from Home',
                            ]),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['tag'],
                                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereIn('tag', $data['tag']),
                            );
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->emptyStateHeading('No Time Entries yet')
            ->defaultSort('time_start', 'ASC');
    }
}
