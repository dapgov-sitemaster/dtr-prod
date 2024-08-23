<?php

namespace App\Livewire\Dapcc\HrAdmin\Events;

use Carbon\Carbon;
use App\Enums\Role;
use Filament\Forms;
use App\Models\Event;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Employee;
use App\Models\Department;
use App\Enums\Dapcc\Events;
use App\Enums\ScheduleType;
use Illuminate\Support\Str;
use Filament\Widgets\Widget;
use App\Enums\OfficialLeaves;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Saade\FilamentFullCalendar\Actions;
use Filament\Notifications\Notification;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    // protected static string $view = 'livewire.calendar-widget';
    public Model | string | null $model = Event::class;

    public function config(): array
    {
        return [
            'schedulerLicenseKey' => 'GPL-My-Project-Is-Open-Source',
            'firstDay' => 0,
            'headerToolbar' => [
                'left' => 'dayGridWeek,dayGridDay,dayGridMonth',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            // 'selectable' => true,
            'editable' => true,
        ];
    }

    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $start = Carbon::parse($fetchInfo['start'])->subDays(20);
        $end = Carbon::parse($fetchInfo['end'])->addDays(20);
        return $this->model::query()
            ->whereHas('employee', fn($query) => $query->isDapcc())
            ->whereDate('start', '>=', $start)
            ->whereDate('end', '<=', $end)
            ->get()
            ->map(
                fn(Event $event) => EventData::make()
                    ->id($event->id)
                    ->title(
                        ($event->tag == Events::HOL || $event->tag == Events::SUS || $event->tag == Events::FLAG) ?
                            $event->tag->getLabel() :
                            $event->employee->last_name . ', ' . Str::initials($event->employee->first_name)
                    )
                    ->extraProperties([
                        'tag' => $event->tag->getLabel()
                    ])
                    ->backgroundColor($event->tag->getColorT())
                    ->borderColor($event->tag->getColorT())
                    ->start($event->start)
                    ->end($event->end)
                    ->toArray()
            )
            ->all();
    }

    public function eventDidMount(): string
    {
        return <<<JS
            function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
                el.setAttribute("x-tooltip", "tooltip");
                el.setAttribute("x-data", "{ tooltip: '"+event.extendedProps.tag+"' }");
            }
        JS;
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalHeading('Edit Event')
                ->mountUsing(
                    function (Event $record, Forms\Form $form, array $arguments) {
                        $form->fill([
                            'hris_number' => $record->hris_number,
                            'tag' => $record->tag,
                            'timestarts_at' => $record->start->format('H:i:s'),
                            'timeends_at' => $record->end->format('H:i:s'),
                            'starts_at' => $arguments['event']['start'] ?? $record->start->format('Y-m-d'),
                            // 'ends_at' => $arguments['event']['end'] ?? $record->end->format('Y-m-d')
                        ]);
                    }
                )
                ->form($this->editActionFormSchema())
                ->mutateFormDataUsing(function (array $data, $record): array {
                    $official_time = $record->official_time;
                    $time_start = \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                    $time_end = $time_start->copy()->addHours(9);
                    // $time_end = \Carbon\Carbon::parse($data['ends_at'] . ' ' . '17:00:00');

                    if ($official_time) {
                        $time_start = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                        $time_end = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '17:00:00');
                    } else if (Events::parse($data['tag']) == Events::SHIFT || Events::parse($data['tag']) == Events::DAYOFF) {
                        $time_start = \Carbon\Carbon::parse($data['starts_at'] . ' ' . $data['timestarts_at']);
                        $time_end = $time_start->copy()->addHours(9);
                    }

                    if (Events::parse($data['tag']) == Events::ALA) {
                        $description = $data['description_leave'];
                    } else {
                        $description = $data['tag']->getLabel();
                    }

                    $data['start'] = $time_start->format('Y-m-d H:i:s');
                    $data['end'] = $time_end->format('Y-m-d H:i:s');
                    $data['description'] = $description;

                    return $data;
                }),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Event'),
        ];
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create new Event')
                ->mountUsing(
                    function (Forms\Form $form, array $arguments) {
                        $form->fill([
                            'starts_at' => $arguments['start'] ?? null,
                            // 'ends_at' => $arguments['end'] ?? null
                        ]);
                    }
                )
                ->mutateFormDataUsing(function (array $data): array {
                    if (Gate::allows('special-events') && (Events::parse($data['tag']) == Events::HOL || Events::parse($data['tag']) == Events::SUS || Events::parse($data['tag']) == Events::FLAG)) {
                        if (Events::parse($data['tag']) == Events::SUS) {
                            if ($data['whole_day']) {
                                $data['start'] = $data['starts_at'] . ' 08:00:00';
                                $data['end'] = $data['starts_at'] . ' 17:00:00';
                            } else {
                                $data['start'] = $data['starts_at'] . ' ' . $data['time'] . ':00';
                                $data['end'] = $data['start'];
                            }
                        } else if (Events::parse($data['tag']) == Events::HOL || Events::parse($data['tag']) == Events::FLAG) {
                            $data['description'] = Events::parse($data['tag'])->getLabel();
                            $data['start'] = $data['starts_at'] . ' 08:00:00';
                            $data['end'] = $data['starts_at'] . ' 17:00:00';
                        }
                    } else {
                        $time_start = \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                        // $time_end = \Carbon\Carbon::parse($data['ends_at'] . ' ' . '17:00:00');
                        $time_end = $time_start->copy()->addHours(9);
                        $official_time = \App\Models\OfficialTime::where('hris_number', $data['hris_number'])->where('status', 'approved')->first();

                        if ($official_time) {
                            $time_start = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                            $time_end = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '17:00:00');
                        } else if (Events::parse($data['tag']) == Events::SHIFT || Events::parse($data['tag']) == Events::DAYOFF) {
                            $time_start = \Carbon\Carbon::parse($data['starts_at'] . ' ' . $data['timestarts_at']);
                            // $time_end = \Carbon\Carbon::parse($data['ends_at'] . ' ' . $data['timeends_at']);
                            $time_end = $time_start->copy()->addHours(9);
                        }

                        if (Events::parse($data['tag']) == Events::ALA) {
                            $description = $data['description_leave'];
                        } else {
                            $description = Events::tryFrom($data['tag'])->getLabel();
                        }

                        $data['start'] = $time_start->format('Y-m-d H:i:s');
                        $data['end'] = $time_end->format('Y-m-d H:i:s');
                        $data['description'] = $description;
                    }

                    $data['status'] = 'approved';
                    $data['created_by'] = auth()->user()->hris_number;

                    return $data;
                })
                ->using(function (array $data, string $model) {
                    foreach ($data['hris_number'] as $hris_number) {
                        $model::create([
                            'hris_number' => $hris_number,
                            'start' => $data['start'],
                            'end' => $data['end'],
                            'tag' => $data['tag'],
                            'description' => $data['description'],
                            'status' => $data['status'],
                            'created_by' => $data['created_by'],
                        ]);
                    }
                }),
        ];
    }

    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }

        if (Carbon::parse($event['start'])->format('Y-m-d') < $this->record->start->format('Y-m-d') && $this->record->start->format('Y-m-d') < now()->format('Y-m-d')) {
            Notification::make()
                ->title("Unable to move event!")
                ->body("Event can't be move backwards from the current Date.")
                ->warning()
                ->color('warning')
                ->send();
        } else if ($this->record->start->format('Y-m-d') < now()->format('Y-m-d')) {
            Notification::make()
                ->title("Unable to move event!")
                ->body("Event can't be moved! Event's date already passed.")
                ->warning()
                ->color('warning')
                ->send();
        } else {
            $this->mountAction('edit', [
                'type' => 'drop',
                'event' => $event,
                'oldEvent' => $oldEvent,
                'relatedEvents' => $relatedEvents,
                'delta' => $delta,
                'oldResource' => $oldResource,
                'newResource' => $newResource,
            ]);
        }

        $this->refreshRecords();


        return false;
    }

    protected function viewAction(): Actions\ViewAction
    {
        return Actions\ViewAction::make()
            ->modalHeading(function ($record) {
                return match (Events::parse($record->tag)) {
                    Events::HOL, Events::SUS, Events::FLAG => $record->tag->getLabel(),
                    default => ($record->start->format('Y-m-d') == $record->end->format('Y-m-d')) ? 'Event Information (' . $record->start->format('M d, Y') . ')' : 'Event Information (' . $record->start->format('M d, Y') . ' - ' . $record->end->format('M d, Y') . ')',
                };
            })
            ->infolist([
                \Filament\Infolists\Components\Grid::make([
                    'sm' => 1,
                    'xl' => 2,
                ])
                    ->hidden(fn($record) => match (Events::parse($record->tag)) {
                        Events::HOL, Events::SUS, Events::FLAG => true,
                        default => false,
                    })
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('employee.full_name')
                            ->label('Employee Name'),
                        \Filament\Infolists\Components\TextEntry::make('employee.department.description')
                            ->label('Office'),
                        \Filament\Infolists\Components\Grid::make([
                            'sm' => 1,
                            'xl' => 2,
                        ])
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('official_time.schedule_type')
                                    ->label('Schedule Type')
                                    ->badge()
                                    ->placeholder('Not set'),
                                \Filament\Infolists\Components\TextEntry::make('official_time.time_in')
                                    ->formatStateUsing(function ($state) {
                                        return $state->format('g:i A') . ' - ' . $state->copy()->addHours(9)->format('g:i A');
                                    })
                                    ->placeholder('Not available'),
                            ])
                            ->visible(fn($record) => $record->employee->official_time && $record->tag == Events::SHIFT),
                        \Filament\Infolists\Components\Grid::make([
                            'sm' => 1,
                            'xl' => 2,
                        ])
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('start')
                                    ->dateTime('g:i A')
                                    ->label('Time Start')
                                    ->placeholder('Not set'),
                                \Filament\Infolists\Components\TextEntry::make('end')
                                    ->dateTime('g:i A')
                                    ->label('Time End')
                                    ->placeholder('Not set'),
                            ])
                            ->visible(fn($record) => !$record->employee->official_time && $record->tag == Events::SHIFT),
                        \Filament\Infolists\Components\TextEntry::make('tag')
                            ->label('Type of Event')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Event Description')
                            ->formatStateUsing(function ($state) {
                                $leave_type = OfficialLeaves::tryFrom($state);
                                return ($leave_type) ? $leave_type->getLabel() : $state;
                            }),
                        \Filament\Infolists\Components\IconEntry::make('mov')
                            ->label('Uploaded MOV')
                            ->icon('heroicon-o-document-check')
                            ->color('info')
                            ->url(function ($record) {
                                if (!$record->mov) {
                                    return null;
                                }
                                return route('admin.pdf.view-mov', ['mov' => $record->mov]);
                            }, shouldOpenInNewTab: true)
                            ->placeholder('No uploaded file')
                            ->hidden(fn($record): bool => $record->tag === Events::SHIFT || $record->tag == Events::DAYOFF),
                        \Filament\Infolists\Components\TextEntry::make('created_by.first_name')
                            ->label('Created by'),
                    ]),
                \Filament\Infolists\Components\Grid::make([
                    'sm' => 1,
                    'xl' => 2,
                ])
                    ->visible(fn($record) => match (Events::parse($record->tag)) {
                        Events::HOL, Events::SUS, Events::FLAG => true,
                        default => false,
                    })
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('tag')
                            ->label('Type of Event')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Event Description'),
                        \Filament\Infolists\Components\TextEntry::make('start')
                            ->dateTime('g:i A')
                            ->visible(fn($record) => Events::parse($record->tag) == Events::SUS && $record->start == $record->end)
                            ->label('Time Start'),
                        \Filament\Infolists\Components\TextEntry::make('created_by.first_name')
                            ->label('Created by'),
                    ]),
            ])
            ->modalFooterActions(
                fn(\Filament\Actions\ViewAction $action, FullCalendarWidget $livewire) => [
                    \Filament\Actions\Action::make('upload-mov')
                        ->label('Upload MOV')
                        ->color('success')
                        ->hidden(fn($record) => match (Events::parse($record->tag)) {
                            Events::HOL, Events::SUS, Events::FLAG => true,
                            default => false,
                        })
                        ->modalHeading(function ($record) {
                            $name = (str($record->employee->first_name)->endsWith('s')) ? $record->employee->first_name . "'" : $record->employee->first_name . "'s";
                            return 'Upload MOV of ' . str($name)->headline() . " " . $record->tag->getLabel();
                        })
                        ->form([
                            \Filament\Forms\Components\FileUpload::make('attachment')
                                ->label('Upload MOV (optional)')
                                ->acceptedFileTypes(['application/pdf', 'application/msword'])
                                ->directory('event-movs')
                                ->visibility('private'),
                        ])
                        ->action(function ($data, \App\Actions\Azure $azure, $record) {
                            // if ($record->mov) {
                            //     $azure->delete($record->mov);
                            // }

                            $file = Storage::disk('public')->get($data['attachment']);
                            $file_explode = explode('/', $data['attachment']);
                            $filename = $file_explode[1];
                            // $azure->put("movs", $file, $filename);
                            Storage::disk('public')->delete($data['attachment']);

                            $record->mov()->create(['filename' => 'movs/' . $filename]);
                            // $record->mov = 'movs/' . $filename;
                            // $record->save();

                            Notification::make()
                                ->title("Saved Successfully!")
                                ->body("MOV uploaded successfully!")
                                ->success()
                                ->color('success')
                                ->send();
                        })
                        ->hidden(fn($record): bool => $record->tag === Events::SHIFT || $record->tag == Events::DAYOFF),
                    // ...$livewire->getCachedModalActions(),
                    Actions\EditAction::make()
                        ->mountUsing(
                            function (Event $record, Forms\Form $form, array $arguments) {
                                $form->fill([
                                    'hris_number' => $record->hris_number,
                                    'tag' => $record->tag,
                                    'timestarts_at' => ($record->tag == Events::SHIFT || $record->tag == Events::DAYOFF) ? $record->start->format('H:i') : null,
                                    'timeends_at' => ($record->tag == Events::SHIFT || $record->tag == Events::DAYOFF) ? $record->end->format('H:i') : null,
                                    'starts_at' => $arguments['event']['start'] ?? $record->start->format('Y-m-d'),
                                    // 'ends_at' => $arguments['event']['end'] ?? $record->end->format('Y-m-d')
                                ]);
                            }
                        )
                        ->form($this->editActionFormSchema())
                        ->mutateFormDataUsing(function (array $data, $record): array {
                            $official_time = $record->official_time;
                            $time_start = \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                            // $time_end = \Carbon\Carbon::parse($data['ends_at'] . ' ' . '17:00:00');
                            $time_end = $time_start->copy()->addHours(9);

                            if ($official_time) {
                                $time_start = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                                $time_end = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '17:00:00');
                            } else if (Events::parse($data['tag']) == Events::SHIFT || Events::parse($data['tag']) == Events::DAYOFF) {
                                $time_start = \Carbon\Carbon::parse($data['starts_at'] . ' ' . $data['timestarts_at']);
                                // $time_end = \Carbon\Carbon::parse($data['ends_at'] . ' ' . $data['timeends_at']);
                                $time_end = $time_start->copy()->addHours(9);
                            }

                            if (Events::parse($data['tag']) == Events::ALA) {
                                $description = $data['description_leave'];
                            } else {
                                $description = $data['tag']->getLabel();
                            }

                            $data['start'] = $time_start->format('Y-m-d H:i:s');
                            $data['end'] = $time_end->format('Y-m-d H:i:s');
                            $data['description'] = $description;

                            return $data;
                        })
                        ->visible(function ($record) {
                            if ($record->start->format('Y-m-d') >= now()->format('Y-m-d')) {
                                return true;
                            }
                            return false;
                        }),
                    Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->visible(function ($record) {
                            if ($record->start->format('Y-m-d') >= now()->format('Y-m-d')) {
                                return true;
                            }
                            return false;
                        }),
                    $action->getModalCancelAction(),
                ]
            );
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\DatePicker::make('starts_at')
                        ->weekStartsOnSunday()
                        ->native(false)
                        ->closeOnDateSelection()
                        ->minDate(now()->format('Y-m-d'))
                        ->live()
                        ->required(),
                    // Forms\Components\DatePicker::make('ends_at')
                    //     ->weekStartsOnSunday()
                    //     ->native(false)
                    //     ->closeOnDateSelection()
                    //     ->minDate(now()->format('Y-m-d'))
                    //     ->reactive()
                    //     ->maxDate(fn(Get $get) => $get('starts_at') ? Carbon::parse($get('starts_at'))->addDays(10)->format('Y-m-d') : now()->addDays(10)->format('Y-m-d'))
                    //     ->live()
                    //     ->required(),
                ]),
            Forms\Components\Grid::make()
                ->visible(fn(Get $get) => $get('starts_at') != '')
                ->schema([
                    \Filament\Forms\Components\Select::make('tag')
                        ->label('Type of Event')
                        ->options(Events::class)
                        ->native(false)
                        ->required()
                        ->live(),
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TimePicker::make('timestarts_at')
                                ->label('Time Start')
                                ->live()
                                ->displayFormat('g:i A')
                                ->seconds(false)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $set('timeends_at', Carbon::parse($state)->addHours(9)->format('H:i'));
                                })
                                ->required(),
                            Forms\Components\TimePicker::make('timeends_at')
                                ->label('Time End')
                                ->displayFormat('g:i A')
                                ->seconds(false)
                                ->required(),
                        ])
                        ->visible(fn(Get $get) => Events::parse($get('tag')) == Events::SHIFT || Events::parse($get('tag')) == Events::DAYOFF),
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
                    \Filament\Forms\Components\TextInput::make('description')
                        ->required()
                        ->visible(fn(Get $get) => match (Events::parse($get('tag'))) {
                            Events::HOL, Events::SUS => true,
                            default => false,
                        }),
                    \Filament\Forms\Components\Select::make('hris_number')
                        ->label('Employee Name')
                        ->multiple()
                        ->getSearchResultsUsing(fn(string $search): array => Employee::isDapcc()->searchEmployee($search)->limit(50)->get()->pluck('full_name', 'hris_number')->toArray())
                        ->getOptionLabelUsing(fn($value): ?string => Employee::find($value)?->full_name)
                        ->native(false)
                        ->required()
                        ->columnSpanFull()
                        ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
                            Events::HOL, Events::SUS, Events::FLAG => true,
                            default => false,
                        }),
                    Forms\Components\Grid::make()
                        ->visible(fn(Get $get) => Gate::allows('special-events') && Events::parse($get('tag')) == Events::SUS)
                        ->schema([
                            Forms\Components\Checkbox::make('whole_day')
                                ->label('is Whole Day')
                                ->live(),
                            Forms\Components\TimePicker::make('time')
                                ->visible(fn(Get $get) => !$get('whole_day'))
                                ->label('Suspension Time')
                                ->required()
                                ->seconds(false),
                        ]),
                ])
        ];
    }

    public function editActionFormSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\DatePicker::make('starts_at')
                        ->weekStartsOnSunday()
                        ->native(false)
                        ->closeOnDateSelection()
                        ->minDate(now()->format('Y-m-d'))
                        ->live()
                        ->required(),
                    // Forms\Components\DatePicker::make('ends_at')
                    //     ->weekStartsOnSunday()
                    //     ->native(false)
                    //     ->closeOnDateSelection()
                    //     ->reactive()
                    //     ->minDate(fn(Get $get) => $get('starts_at'))
                    //     ->maxDate(fn(Get $get) => $get('starts_at') ? Carbon::parse($get('starts_at'))->addDays(10)->format('Y-m-d') : now()->addDays(10)->format('Y-m-d'))
                    //     ->live()
                    //     ->required(),
                ]),
            Forms\Components\Grid::make()
                ->visible(fn(Get $get) => $get('starts_at') != '')
                ->schema([
                    \Filament\Forms\Components\Select::make('tag')
                        ->label('Type of Event')
                        ->options(Events::class)
                        ->native(false)
                        ->required()
                        ->live(),
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TimePicker::make('timestarts_at')
                                ->label('Time Start')
                                ->live()
                                ->displayFormat('g:i A')
                                ->seconds(false)
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $set('timeends_at', Carbon::parse($state)->addHours(9)->format('H:i'));
                                })
                                ->required(),
                            Forms\Components\TimePicker::make('timeends_at')
                                ->label('Time End')
                                ->displayFormat('g:i A')
                                ->seconds(false)
                                ->required(),
                        ])
                        ->visible(fn(Get $get) => Events::parse($get('tag')) == Events::SHIFT || Events::parse($get('tag')) == Events::DAYOFF),
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
                    \Filament\Forms\Components\TextInput::make('description')
                        ->required()
                        ->visible(fn(Get $get) => match (Events::parse($get('tag'))) {
                            Events::HOL, Events::SUS => true,
                            default => false,
                        }),
                    \Filament\Forms\Components\Select::make('hris_number')
                        ->label('Employee Name')
                        // ->options(\App\Models\Employee::isDapcc()->where('employment_status', true)->get()->pluck('full_name', 'hris_number'))
                        ->getSearchResultsUsing(fn(string $search): array => Employee::isDapcc()->searchEmployee($search)->limit(50)->get()->pluck('full_name', 'hris_number')->toArray())
                        ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()?->full_name)
                        ->native(false)
                        ->searchable(['first_name', 'last_name'])
                        ->required()
                        ->columnSpanFull()
                        ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
                            Events::HOL, Events::SUS, Events::FLAG => true,
                            default => false,
                        }),
                    Forms\Components\Grid::make()
                        ->visible(fn(Get $get) => Gate::allows('special-events') && Events::parse($get('tag')) == Events::SUS)
                        ->schema([
                            Forms\Components\Checkbox::make('whole_day')
                                ->label('is Whole Day')
                                ->live(),
                            Forms\Components\TimePicker::make('time')
                                ->visible(fn(Get $get) => !$get('whole_day'))
                                ->label('Suspension Time')
                                ->required()
                                ->seconds(false),
                        ]),
                ])
        ];
    }

    public function plugins(): array
    {
        return [];
    }
}
