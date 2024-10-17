<?php

namespace App\Livewire\AdminCoord\Events;

use Carbon\Carbon;
use App\Enums\Events;
use App\Enums\Role;
use App\Models\Event;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use App\Enums\ScheduleType;
use Livewire\Attributes\On;
use App\Models\OfficialTime;
use App\Enums\OfficialLeaves;
use Livewire\Attributes\Reactive;
use App\Mail\Event\EvaluationResult;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Carbon\CarbonPeriod;

class TableList extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;
    #[Reactive]
    public $year, $month;

    public function mount($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    #[On('refresh-table')]
    public function render()
    {
        return view('livewire.admin-coord.events.table-list');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->whereHas('employee', fn($query) => $query->departmentCovered())
                    ->when(auth()->user()->employee->department->office == "ICTD", fn($query) => $query->whereNotIn('hris_number', ['212469', '210798']))
                    ->whereYear('start', $this->year)->whereMonth('start', $this->month)
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
                    ->label('Full Name')
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(['last_name'])
                    ->copyable()
                    ->copyMessage('Full Name copied')
                    ->copyMessageDuration(1500),
                \Filament\Tables\Columns\TextColumn::make('employee.department.description')
                    ->label('Department')
                    ->sortable(['group', 'center', 'office'])
                    ->visible(fn() => auth()->user()->hasRole(Role::CENTERADMINCOORD) || auth()->user()->hasRole(Role::GROUPADMINCOORD)),
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->getStateUsing(fn($record) => $record->start->format('M d, Y'))
                    ->searchable(['start'])
                    ->sortable(['start']),
                \Filament\Tables\Columns\TextColumn::make('tag')
                    ->label('Tag')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->formatStateUsing(function ($state) {
                        if (OfficialLeaves::tryFrom($state)) {
                            return OfficialLeaves::parse($state)->getLabel();
                        } else {
                            return $state;
                        }
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'disapproved' => 'danger',
                    })
                    ->sortable(),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('create-event')
                    ->icon('heroicon-m-document-plus')
                    ->label('Create Event')
                    ->modalHeading('Create Event')
                    ->modalIcon('heroicon-o-document-plus')
                    ->form([
                        \Filament\Forms\Components\Grid::make()
                            ->schema([
                                \Filament\Forms\Components\ToggleButtons::make('date_type')
                                    ->label('Date Type')
                                    ->inline()
                                    ->options(['single' => 'Single Date', 'multi' => 'Date Range'])
                                    ->default('single')
                                    ->required()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('tag', null);
                                        $set('date', null);
                                        $set('daterange', null);
                                    })
                                    ->live(),
                                \Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr::make('date')
                                    ->label('Date')
                                    ->dateFormat('Y-m-d')
                                    ->minDate(now()->addDays(3)->format('Y-m-d 00:00:00'))
                                    ->theme(\Coolsam\FilamentFlatpickr\Enums\FlatpickrTheme::DEFAULT)
                                    ->required()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('tag', null);
                                    })
                                    ->live()
                                    ->visible(fn(Get $get) => $get('date_type') == 'single'),
                                \Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr::make('daterange')
                                    ->label('Date Range')
                                    ->dateFormat('Y-m-d')
                                    ->minDate(now()->addDays(3)->format('Y-m-d 00:00:00'))
                                    ->range()
                                    ->theme(\Coolsam\FilamentFlatpickr\Enums\FlatpickrTheme::DEFAULT)
                                    ->required()
                                    ->hintColor('primary')
                                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'You can not schedule Work from Home / Hybrid Work from Home using this Date Range!')
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('tag', null);
                                    })
                                    ->live()
                                    ->visible(fn(Get $get) => $get('date_type') == 'multi'),
                            ]),
                        \Filament\Forms\Components\Grid::make()
                            ->schema([
                                \Filament\Forms\Components\Select::make('tag')
                                    ->label('Type of Event')
                                    ->options(function (Get $get) {
                                        $options = [];
                                        foreach (Events::cases() as $case) {
                                            if ($case == Events::WFH || $case == Events::HWFH) {
                                                if (Carbon::parse($get('date'))->dayOfWeek == Carbon::FRIDAY && $get('date_type') == 'single') {
                                                    $options[$case->value] = $case->getLabel();
                                                }
                                            } else if ($case != Events::HOL && $case != Events::FLAG && $case != Events::SUS) {
                                                $options[$case->value] = $case->getLabel();
                                            }
                                        }
                                        return $options;
                                    })
                                    ->reactive()
                                    ->native(false)
                                    ->required()
                                    ->live(),
                                \Filament\Forms\Components\Select::make('description_leave')
                                    ->label('Type of Official Leave')
                                    ->options(OfficialLeaves::class)
                                    ->native(false)
                                    ->visible(function (Get $get) {
                                        return match (Events::parse($get('tag'))) {
                                            Events::ALA => true,
                                            default => false,
                                        };
                                    })
                                    ->required(),
                                \Filament\Forms\Components\Select::make('hris_number')
                                    ->label('Employee Name/s')
                                    ->multiple()
                                    // ->options(\App\Models\Employee::whereIn('department_id', $this->departments)->where('employment_status', true)->get()->pluck('full_name', 'hris_number'))
                                    ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->departmentCovered()->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
                                    ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()->full_name)
                                    ->native(false)
                                    ->searchable(['first_name', 'last_name', 'hris_number'])
                                    ->required()
                                    ->columnSpanFull()
                                    ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
                                        Events::HOL, Events::SUS, Events::FLAG => true,
                                        default => false,
                                    }),
                            ])
                            ->visible(fn(Get $get) => $get('date') || $get('daterange'))
                    ])
                    ->action(function ($data) {
                        $date = (array_key_exists('date', $data)) ? $data['date'] : $data['daterange'];

                        if (Events::parse($data['tag']) == Events::ALA) {
                            $description = $data['description_leave'];
                        } else {
                            $description = Events::tryFrom($data['tag'])->getLabel();
                        }

                        foreach ($data['hris_number'] as $hris_number) {
                            $date = (array_key_exists('date', $data)) ? [$data['date']] : $data['daterange'];
                            $dates = (count($date) > 1) ? CarbonPeriod::create($date[0], $date[1])->toArray() : [Carbon::parse($date)];

                            foreach ($dates as $date) {
                                $official_time = OfficialTime::where('hris_number', $hris_number)->where('status', 'approved')->first();
                                $time_start = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '08:00:00');
                                $time_end = $time_start->copy()->addHours(9);

                                if ($official_time) {
                                    $time_start = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '08:00:00');
                                    $time_end = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '17:00:00');
                                }

                                Event::create([
                                    'hris_number' => $hris_number,
                                    'start' => $time_start->format('Y-m-d H:i:s'),
                                    'end' => $time_end->format('Y-m-d H:i:s'),
                                    'tag' => $data['tag'],
                                    'description' => $description,
                                    'status' => 'approved',
                                    'created_by' => auth()->user()->hris_number,
                                ]);
                            }
                        }


                        Notification::make()
                            ->title("Event successfully created")
                            ->body(count($data['hris_number']) . " personnel has been scheduled a " . Events::tryFrom($data['tag'])->getLabel() . " event.")
                            ->success()
                            ->color('success')
                            ->send();

                        // $data['hris_number'] = auth()->user()->hris_number;
                        // $data['date'] = Carbon::parse($data['date']);
                        // $official_time = OfficialTime::where('hris_number', $data['hris_number'])->where('status', 'approved')->first();
                        // $data['start'] = \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                        // $data['end'] = $data['start']->copy()->addHours(9);

                        // if ($official_time) {
                        //     $data['start'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                        //     $data['end'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '17:00:00');
                        // }

                        // if (Events::parse($data['tag']) == Events::ALA) {
                        //     $data['description'] = $data['description_leave'];
                        // } else {
                        //     $data['description'] = Events::tryFrom($data['tag'])->getLabel();
                        // }

                        // return $model::create($data);
                    })
                    ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
                    ->visible(fn() => auth()->user()->hris_number == '211515')
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('set_status')
                    ->icon('heroicon-m-arrow-path')
                    ->label('Evaluate Requests')
                    ->modalHeading('Evaluate all requests')
                    ->color('secondary')
                    ->modalWidth('xl')
                    ->form([
                        \Filament\Forms\Components\ToggleButtons::make('evaluation_status')
                            ->label('Do you want to Approve or Disapprove this request?')
                            ->options([
                                'approved' => 'Approve',
                                'disapproved' => 'Disapprove',
                            ])
                            ->colors([
                                'approved' => 'success',
                                'disapproved' => 'danger',
                            ])
                            ->inline()
                            ->live()
                            ->required()
                            ->validationMessages([
                                'required' => 'Please, select if you want to Approve or Disapprove this request.',
                            ]),
                        \Filament\Forms\Components\Textarea::make('note')
                            ->label('Note (optional)')
                            ->visible(fn(Get $get) => $get('evaluation_status') === 'disapproved')
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data, $records) {
                        $note = (array_key_exists('note', $data) ? $data['note'] : null);

                        foreach ($records as $record) {
                            $record->status = $data['evaluation_status'];
                            $record->save();

                            Mail::to($record->employee->user)->cc(auth()->user())->send(new EvaluationResult($record, $note));
                        }

                        Notification::make()
                            ->success()
                            ->color('success')
                            ->title('Evaluation has been complete.')
                            ->body($records->count() . ' requests has been ' . str($data['evaluation_status'])->title() . '.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
            ])
            ->checkIfRecordIsSelectableUsing(function (Model $record): bool {
                return $record->status == "pending";
            })
            ->actions([
                \Filament\Tables\Actions\ActionGroup::make([
                    \Filament\Tables\Actions\Action::make('set_status')
                        ->icon('heroicon-m-arrow-path')
                        ->label('Evaluate Request')
                        ->modalHeading(fn($record) => 'Evaluate ' . $record->tag->getLabel() . ' request of ' . $record->employee->first_name)
                        ->color('secondary')
                        ->modalWidth('xl')
                        ->form([
                            \Filament\Forms\Components\ToggleButtons::make('evaluation_status')
                                ->label('Do you want to Approve or Disapprove this request?')
                                ->options([
                                    'approved' => 'Approve',
                                    'disapproved' => 'Disapprove',
                                ])
                                ->colors([
                                    'approved' => 'success',
                                    'disapproved' => 'danger',
                                ])
                                ->inline()
                                ->live()
                                ->required()
                                ->validationMessages([
                                    'required' => 'Please, select if you want to Approve or Disapprove this request.',
                                ]),
                            \Filament\Forms\Components\Textarea::make('note')
                                ->label('Note (optional)')
                                ->visible(fn(Get $get) => $get('evaluation_status') === 'disapproved')
                        ])
                        ->requiresConfirmation()
                        ->action(function (array $data, $record) {
                            $note = array_key_exists('note', $data) ? $data['note'] : null;
                            $record->status = $data['evaluation_status'];
                            $record->save();

                            Mail::to($record->employee->user)->cc(auth()->user())->send(new EvaluationResult($record, $note));

                            Notification::make()
                                ->success()
                                ->color('success')
                                ->title('Evaluation has been complete.')
                                ->body($record->employee->apostFirstName . ' ' . $record->tag->getLabel() . ' request has been ' . str($data['evaluation_status'])->title() . '.')
                                ->send();
                        })
                        ->visible(fn($record) => $record->status === 'pending'),
                    \Filament\Tables\Actions\EditAction::make()
                        ->mutateRecordDataUsing(function ($data, $record) {
                            $data['date'] = $record->start->format('Y-m-d');
                            $data['tag'] = $record->tag;
                            $data['description_leave'] = ($record->tag == Events::ALA) ? $record->description : null;

                            return $data;
                        })
                        ->form([
                            \Filament\Forms\Components\DatePicker::make('date')
                                ->label('Select Date')
                                ->displayFormat('d F Y')
                                ->native(false)
                                ->seconds(false)
                                ->weekStartsOnSunday()
                                ->closeOnDateSelection()
                                ->required()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('tag', null);
                                })
                                ->live(),
                            \Filament\Forms\Components\Grid::make()
                                ->schema([
                                    \Filament\Forms\Components\Select::make('tag')
                                        ->label('Type of Event')
                                        ->options(function (Get $get) {
                                            $options = [];
                                            foreach (Events::cases() as $case) {
                                                if ($case == Events::WFH || $case == Events::HWFH) {
                                                    if (Carbon::parse($get('date'))->dayOfWeek == Carbon::FRIDAY) {
                                                        $options[$case->value] = $case->getLabel();
                                                    }
                                                } else if ($case != Events::HOL && $case != Events::FLAG && $case != Events::SUS) {
                                                    $options[$case->value] = $case->getLabel();
                                                }
                                            }
                                            return $options;
                                        })
                                        ->reactive()
                                        ->native(false)
                                        ->required()
                                        ->live(),
                                    \Filament\Forms\Components\Select::make('description_leave')
                                        ->label('Type of Official Leave')
                                        ->options(OfficialLeaves::class)
                                        ->native(false)
                                        ->visible(fn(Get $get) => ($get('tag') === Events::ALA) ? true : false)
                                        ->required(),
                                    \Filament\Forms\Components\Select::make('hris_number')
                                        ->label('Employee Name')
                                        // ->options(\App\Models\Employee::whereIn('department_id', $this->departments)->where('employment_status', true)->get()->pluck('full_name', 'hris_number'))
                                        ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->departmentCovered()->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
                                        ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()?->full_name)
                                        ->native(false)
                                        ->searchable(['first_name', 'last_name', 'hris_number'])
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn(Get $get) => $get('date') != null)
                        ])
                        ->using(function (Model $record, $data): Model {
                            $data['date'] = Carbon::parse($data['date']);
                            $official_time = OfficialTime::where('hris_number', $record->hris_number)->where('status', 'approved')->first();
                            $data['start'] = \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                            $data['end'] = $data['start']->copy()->addHours(9);

                            if ($official_time) {
                                $data['start'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                                $data['end'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '17:00:00');
                            }

                            if (Events::parse($data['tag']) == Events::ALA) {
                                $data['description'] = $data['description_leave'];
                            } else {
                                $data['description'] = $data['tag']->getLabel();
                            }


                            $record->start = $data['start'];
                            $record->end = $data['end'];
                            $record->tag = $data['tag'];
                            $record->description = $data['description'];
                            $record->save();
                            return $record;
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->color('success')
                                ->title('Event updated')
                                ->body('You have successfully updated the event.'),
                        )
                        ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class)),
                    \Filament\Tables\Actions\DeleteAction::make()
                        ->modalHeading('Remove Event!')
                        ->label('Remove')
                        ->requiresConfirmation()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Event has been removed')
                                ->color('success'),
                        )
                        ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
                ])
                    ->label('Actions')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->button()
                    ->dropdownPlacement('top-end')
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('status')
                    ->columnSpanFull()
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('status')
                            ->label('Status')
                            ->columns(3)
                            ->afterStateHydrated(function ($component, $state) {
                                if (! filled($state)) {
                                    $component->state(['pending']);
                                }
                            })
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'disapproved' => 'Disapproved',
                            ]),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereIn('status', $data['status']);
                    }),
                \Filament\Tables\Filters\Filter::make('view_special_event')
                    ->columnSpanFull()
                    ->form([
                        \Filament\Forms\Components\ToggleButtons::make('is_included')
                            ->label('Include Holiday, Suspension, Flag Ceremony schedules in the Table?')
                            ->boolean()
                            ->inline()
                            ->grouped()
                            ->default(false),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['is_included'] == true,
                                function (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder {
                                    return $query->orWhereIn('tag', [Events::HOL, Events::SUS, Events::FLAG]);
                                },
                            );
                    }),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('start', 'desc');
    }
}
