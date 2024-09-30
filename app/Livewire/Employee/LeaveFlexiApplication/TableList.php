<?php

namespace App\Livewire\Employee\LeaveFlexiApplication;

use Carbon\Carbon;
use App\Enums\Role;
use App\Models\User;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use Filament\Tables\Table;
use App\Enums\ScheduleType;
use Livewire\Attributes\On;
use App\Models\OfficialTime;
use App\Enums\OfficialLeaves;
use App\Mail\Event\Application;
use App\Enums\AppointmentStatus;
use Livewire\Attributes\Reactive;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Livewire\Employee\LeaveFlexiApplication\Event\Calendar;

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
        return view('livewire.employee.leave-flexi-application.table-list');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('hris_number', auth()->user()->hris_number)
                    ->whereYear('start', $this->year)->whereMonth('start', $this->month)
            )
            ->columns([
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
                    }),
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
            ->headerActions([
                Action::make('create-event')
                    ->icon('heroicon-m-document-plus')
                    ->label('Request Schedule')
                    ->modalHeading('Request Schedule')
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
                                    ->hidden(fn(Get $get) => $get('date_type') != 'single'),
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
                                    ->hidden(fn(Get $get) => $get('date_type') != 'multi'),
                            ]),
                        // \Filament\Forms\Components\DatePicker::make('date')
                        //     ->label('Select Date')
                        //     ->displayFormat('d F Y')
                        //     ->native(false)
                        //     ->seconds(false)
                        //     ->weekStartsOnSunday()
                        //     ->closeOnDateSelection()
                        //     ->minDate(now()->addDays(3)->format('Y-m-d 00:00:00'))
                        //     ->required()
                        //     ->afterStateUpdated(function (Set $set) {
                        //         $set('tag', null);
                        //     })
                        //     ->live(),
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
                                            } else if ($case == Events::ALA) {
                                                if (auth()->user()->employee->appointment_status == AppointmentStatus::PBP) {
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
                            ])
                            ->visible(fn(Get $get) => $get('date') || $get('daterange'))
                    ])
                    ->before(function ($data, Action $action) {
                        $date = (array_key_exists('date', $data)) ? [$data['date']] : $data['daterange'];

                        $event = Event::query()
                            ->where('hris_number', auth()->user()->hris_number)
                            ->when(
                                count($date) > 1,
                                function ($query) use ($date) {
                                    $query->whereDate('start', '>=', Carbon::parse($date[0])->format('Y-m-d'))->whereDate('start', '<=', Carbon::parse($date[1])->format('Y-m-d'));
                                },
                                function ($query) use ($date) {
                                    $query->whereDate('start', Carbon::parse($date[0]));
                                }
                            )
                            ->get();

                        if ($event->isNotEmpty()) {
                            $body = '';
                            if (count($date) > 1) {
                                $imploded_event = $event->clone()->pluck('start')->map(fn($item) => $item->format('Y-m-d'))->implode(',');
                                $body .= 'You have already applied for schedule/s on ' . $imploded_event;
                            } else {
                                $body .= 'You have already applied for ' . $event->first()->tag->getLabel() . ' event on ' . $event->first()->start->format('F d, Y');
                            }

                            $body .= '. If you want to change your request, you can just update your current request.';

                            Notification::make()
                                ->warning()
                                ->color('warning')
                                ->title('Create Event has been cancelled!')
                                ->body($body)
                                ->send();

                            $action->halt();
                        }
                    })
                    ->action(function ($data) {
                        $user = auth()->user()->hris_number;
                        $date = (array_key_exists('date', $data)) ? [$data['date']] : $data['daterange'];

                        $dates = (count($date) > 1) ? CarbonPeriod::create($date[0], $date[1])->toArray() : [Carbon::parse($date[0])];
                        $data['tag'] = Events::parse($data['tag']);
                        if ($data['tag'] == Events::ALA) {
                            $data['description'] = $data['description_leave'];
                        } else {
                            $data['description'] = $data['tag']->getLabel();
                        }

                        foreach ($dates as $d) {
                            $official_time = OfficialTime::where('hris_number', $user->hris_number)->where('status', 'approved')->first();
                            $data['start'] = \Carbon\Carbon::parse($d->format('Y-m-d') . ' ' . '08:00:00');
                            $data['end'] = $data['start']->copy()->addHours(9);

                            if ($official_time) {
                                $data['start'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($d->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($d->format('Y-m-d') . ' ' . '08:00:00');
                                $data['end'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($d->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($d->format('Y-m-d') . ' ' . '17:00:00');
                            }

                            $record = Event::create([
                                'hris_number' => $user->hris_number,
                                'start' => $data['start'],
                                'end' => $data['end'],
                                'tag' => $data['tag'],
                                'description' => $data['description'],
                                'created_by' => $user->hris_number,
                            ]);
                        }

                        $admin_coord = User::whereHas('employee', fn($query) => $query->where('department_id', $user->employee->department_id))->where('role', Role::ADMINCOORD)->get();
                        $body = 'You have successfully requested a ' . $data['tag']->getLabel() . ' schedule ';

                        if (count($date) > 1) {
                            $data = (object) [
                                'employee' => $user->hris_number,
                                'tag' => Events::parse($data['tag']),
                                'description' => $data['description'],
                                'dates' => [Carbon::parse($date[0]), Carbon::parse($date[1])],
                            ];

                            Mail::to($user)->cc($admin_coord->pluck('email')->toArray())->send(new Application($data, true));

                            $body .= 'from ' . $data->dates[0]->format('Y-m-d') . ' to ' . $data->dates[1]->format('Y-m-d');
                        } else {
                            Mail::to($user)->cc($admin_coord->pluck('email')->toArray())->send(new Application($record));

                            $body .= 'on ' . $dates[0]->format('Y-m-d');
                        }

                        Notification::make()
                            ->success()
                            ->color('success')
                            ->title('Event Created!')
                            ->body($body . '. Kindly wait for the Attendance Monitor to approve your request.')
                            ->send();
                    })
                    ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
            ])
            ->actions([
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
                            ->minDate(now()->addDays(3)->format('Y-m-d 00:00:00'))
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
                                    ->visible(function (Get $get) {
                                        return match (Events::parse($get('tag'))) {
                                            Events::ALA => true,
                                            default => false,
                                        };
                                    })
                                    ->required(),
                            ])
                            ->visible(fn(Get $get) => $get('date') != null)
                    ])
                    ->using(function (Model $record, $data): Model {
                        $data['date'] = Carbon::parse($data['date']);
                        $official_time = OfficialTime::where('hris_number', $record->hris_number)->where('status', 'approved')->first();
                        $data['start'] = \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                        $data['end'] = $data['start']->copy()->addHours(9);
                        $data['tag'] = Events::parse($data['tag']);

                        if ($official_time) {
                            $data['start'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                            $data['end'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '17:00:00');
                        }

                        if ($data['tag'] == Events::ALA) {
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
                            ->body('You have successfully updated your request.'),
                    )
                    ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
                    ->visible(fn($record) => $record->status === "pending"),
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
                    ->visible(fn($record) => $record->status === "pending")
            ])
            ->defaultSort('start', 'desc');
    }
}
