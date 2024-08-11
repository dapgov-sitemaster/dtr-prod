<?php

namespace App\Livewire\HrAdmin\Events;

use Filament\Forms;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Event::class;

    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        return $this->model::query()
            ->where('hris_number', null)
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
                    }

                    $data['status'] = 'approved';
                    $data['created_by'] = auth()->user()->hris_number;
                    return $data;
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalHeading('Edit Event')
                ->mountUsing(
                    function (Event $record, Forms\Form $form, array $arguments) {
                        $form->fill([
                            'tag' => $record->tag,
                            'description' => $record->description,
                            'time' => ($record->start->format('H:i:s') == $record->end->format('H:i:s')) ? $record->start->format('H:i:s') : null,
                            'whole_day' => ($record->start->format('H:i:s') == $record->end->format('H:i:s')) ? false : true,
                            'starts_at' => $arguments['event']['start'] ?? $record->start->format('Y-m-d'),
                            'ends_at' => $arguments['event']['end'] ?? $record->end->format('Y-m-d')
                        ]);
                    }
                )
                ->mutateFormDataUsing(function (array $data, $record): array {
                    // $official_time = $record->official_time;
                    // $time_start = ($official_time) ? \Carbon\Carbon::parse($data['starts_at'] . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['starts_at'] . ' ' . '08:00:00');
                    // $time_end = ($official_time) ? \Carbon\Carbon::parse($data['ends_at'] . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['ends_at'] . ' ' . '17:00:00');
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

                    return $data;
                }),
            Actions\DeleteAction::make()
                ->modalHeading('Delete Event'),
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
            ]);
    }

    public function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('tag')
                ->label('Type of Event')
                ->options(Events::class)
                ->options([Events::FLAG->value => Events::FLAG->getLabel(), Events::SUS->value => Events::SUS->getLabel(), Events::HOL->value => Events::HOL->getLabel()])
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
