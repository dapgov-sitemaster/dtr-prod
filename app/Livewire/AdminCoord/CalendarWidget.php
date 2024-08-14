<?php

namespace App\Livewire\AdminCoord;

use App\Enums\Role;
use Filament\Forms;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use App\Models\Department;
use Illuminate\Support\Str;
use Filament\Widgets\Widget;
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
    public $departments;

    public function config(): array
    {
        return [
            'schedulerLicenseKey' => 'GPL-My-Project-Is-Open-Source',
            'firstDay' => 0,
            'headerToolbar' => [
                'left' => 'dayGridWeek,dayGridDay',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'selectable' => true,
            'editable' => true,
        ];
    }

    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        if (auth()->user()->role == Role::CENTERADMINCOORD) {
            $this->departments = Department::where('center', auth()->user()->employee->department->center)->get()->pluck('id')->toArray();
        } else {
            $this->departments = [auth()->user()->employee->department_id];
        }

        $departments = $this->departments;

        return $this->model::query()
            ->with(['employee' => function ($query) use ($departments) {
                $query->whereIn('department_id', $departments);
            }])
            ->whereDate('start', '>=', $fetchInfo['start'])
            ->whereDate('end', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (Event $event) => EventData::make()
                    ->id($event->id)
                    ->title(
                        ($event->tag == Events::HOL || $event->tag == Events::SUS || $event->tag == Events::FLAG) ?
                            $event->tag->getLabel() :
                            $event->employee->last_name . ', ' . Str::initials($event->employee->first_name)
                    )
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
                el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
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
                            'starts_at' => $arguments['event']['start'] ?? $record->start->format('Y-m-d'),
                            'ends_at' => $arguments['event']['end'] ?? $record->end->format('Y-m-d')
                        ]);
                    }
                )
                ->mutateFormDataUsing(function (array $data, $record): array {
                    // you are here!!!
                    $official_time = $record->official_time;
                    $time_start = ($official_time) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                    $time_end = ($official_time) ? \Carbon\Carbon::parse($data['ends_at'] . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['ends_at'] . ' ' . '17:00:00');
                    $data['start'] = $time_start->format('Y-m-d H:i:s');
                    $data['end'] = $time_end->format('Y-m-d H:i:s');
                    $data['description'] = $data['tag']->getLabel();

                    return $data;
                })
                ->visible(function ($record) {
                    $this->dispatch('filament-fullcalendar--refresh');
                    if ($record->start->format('Y-m-d') < now()->format('Y-m-d')) {
                        Notification::make()
                            ->title("Unable to move event!")
                            ->body("Event can't be moved! Event's date already passed.")
                            ->warning()
                            ->color('warning')
                            ->send();
                        return false;
                    }
                    return true;
                }),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Event')
                ->visible(function ($record) {
                    $this->dispatch('filament-fullcalendar--refresh');
                    if ($record->start->format('Y-m-d') < now()->format('Y-m-d')) {
                        Notification::make()
                            ->title("Unable to move event!")
                            ->body("Event can't be moved! Event's date already passed.")
                            ->warning()
                            ->color('warning')
                            ->send();
                        return false;
                    }
                    return true;
                }),
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
                            'ends_at' => $arguments['end'] ?? null
                        ]);
                    }
                )
                ->mutateFormDataUsing(function (array $data): array {
                    if (Gate::allows('special-events') && (Events::parse($data['tag']) == Events::HOL || Events::parse($data['tag']) == Events::SUS || Events::parse($data['tag']) == Events::FLAG)) {
                        if (Events::parse($data['tag']) == Events::SUS) {
                            if ($data['whole_day']) {
                                $data['start'] = $data['starts_at'] . ' 08:00:00';
                                $data['end'] = $data['ends_at'] . ' 17:00:00';
                            } else {
                                $data['start'] = $data['starts_at'] . ' ' . $data['time'] . ':00';
                                $data['end'] = $data['ends_at'] . ' ' . $data['time'] . ':00';
                            }
                        } else if (Events::parse($data['tag']) == Events::HOL || Events::parse($data['tag']) == Events::FLAG) {
                            $data['description'] = Events::parse($data['tag'])->getLabel();
                            $data['start'] = $data['starts_at'] . ' 08:00:00';
                            $data['end'] = $data['ends_at'] . ' 17:00:00';
                        }
                    } else {
                        $official_time = \App\Models\OfficialTime::where('hris_number', $data['hris_number'])->where('status', 'approved')->first();
                        $time_start = ($official_time) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                        // $time_end = $time_start->copy()->addHours(9);
                        $time_end = ($official_time) ? \Carbon\Carbon::parse($data['ends_at'] . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['ends_at'] . ' ' . '17:00:00');
                        $description = Events::tryFrom($data['tag'])->getLabel();
                        $data['start'] = $time_start->format('Y-m-d H:i:s');
                        $data['end'] = $time_end->format('Y-m-d H:i:s');
                        $data['description'] = $description;
                    }

                    $data['status'] = 'approved';
                    $data['created_by'] = auth()->user()->hris_number;

                    return $data;
                }),
        ];
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
                    ->hidden(fn ($record) => match (Events::parse($record->tag)) {
                        Events::HOL, Events::SUS, Events::FLAG => true,
                        default => false,
                    })
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('employee.full_name')
                            ->label('Employee Name'),
                        \Filament\Infolists\Components\TextEntry::make('official_time.time_in')
                            ->formatStateUsing(function ($state) {
                                return $state->format('g:i A') . ' - ' . $state->copy()->addHours(9)->format('g:i A');
                            })
                            ->placeholder('Not set'),
                        \Filament\Infolists\Components\TextEntry::make('tag')
                            ->label('Type of Event')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Event Description'),
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
                            ->hidden(fn ($record): bool => $record->tag->value === 'wfh' || $record->tag->value === 'hwfh'),
                        \Filament\Infolists\Components\TextEntry::make('created_by.first_name')
                            ->label('Created by'),
                    ]),
                \Filament\Infolists\Components\Grid::make([
                    'sm' => 1,
                    'xl' => 2,
                ])
                    ->visible(fn ($record) => match (Events::parse($record->tag)) {
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
                            ->visible(fn ($record) => Events::parse($record->tag) == Events::SUS && $record->start == $record->end)
                            ->label('Time Start'),
                        \Filament\Infolists\Components\TextEntry::make('created_by.first_name')
                            ->label('Created by'),
                    ]),
            ])
            ->modalFooterActions(
                fn (\Filament\Actions\ViewAction $action, FullCalendarWidget $livewire) => [
                    \Filament\Actions\Action::make('upload-mov')
                        ->label('Upload MOV')
                        ->color('success')
                        ->hidden(fn ($record) => match (Events::parse($record->tag)) {
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
                        ->hidden(fn ($record): bool => $record->tag->value === 'wfh' || $record->tag->value === 'hwfh'),
                    // ...$livewire->getCachedModalActions(),
                    Actions\EditAction::make()
                        ->mountUsing(
                            function (Event $record, Forms\Form $form, array $arguments) {
                                $form->fill([
                                    'hris_number' => $record->hris_number,
                                    'tag' => $record->tag,
                                    'starts_at' => $arguments['event']['start'] ?? $record->start->format('Y-m-d'),
                                    'ends_at' => $arguments['event']['end'] ?? $record->end->format('Y-m-d')
                                ]);
                            }
                        )
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
            \Filament\Forms\Components\Select::make('tag')
                ->label('Type of Event')
                ->options(Events::class)
                ->options(function () {
                    if (!Gate::allows('special-events')) {
                        return collect(Events::cases())
                            ->filter(fn ($case) => $case !== Events::SUS && $case !== Events::HOL)
                            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                            ->toArray();
                    }

                    return Events::class;
                })
                ->native(false)
                ->required()
                ->columnSpanFull()
                ->live(),
            \Filament\Forms\Components\TextInput::make('description')
                ->required()
                ->visible(fn (Get $get) => match (Events::parse($get('tag'))) {
                    Events::HOL, Events::SUS => true,
                    default => false,
                }),
            \Filament\Forms\Components\Select::make('hris_number')
                ->label('Employee Name')
                ->options(\App\Models\Employee::whereIn('department_id', $this->departments)->where('employment_status', true)->get()->pluck('full_name', 'hris_number'))
                ->native(false)
                ->searchable(['first_name', 'last_name'])
                ->required()
                ->columnSpanFull()
                ->hidden(fn (Get $get) => match (Events::parse($get('tag'))) {
                    Events::HOL, Events::SUS, Events::FLAG => true,
                    default => false,
                }),
            Forms\Components\Grid::make()
                ->visible(fn (Get $get) => Gate::allows('special-events') && Events::parse($get('tag')) == Events::SUS)
                ->schema([
                    Forms\Components\Checkbox::make('whole_day')
                        ->label('is Whole Day')
                        ->live(),
                    Forms\Components\TimePicker::make('time')
                        ->visible(fn (Get $get) => !$get('whole_day'))
                        ->label('Suspension Time')
                        ->required()
                        ->seconds(false),
                ]),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\DatePicker::make('starts_at'),

                    Forms\Components\DatePicker::make('ends_at'),
                ]),
        ];
    }

    public function plugins(): array
    {
        return [];
    }
}
