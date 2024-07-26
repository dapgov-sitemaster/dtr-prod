<?php

namespace App\Livewire;

use App\Models\Event;
use Filament\Forms;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    // protected static string $view = 'livewire.calendar-widget';
    public Model | string | null $model = Event::class;

    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        // You can use $fetchInfo to filter events by date.
        // This method should return an array of event-like objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#returning-events
        // You can also return an array of EventData objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#the-eventdata-class
        return Event::query()
            ->with('employee')
            ->whereDate('start', '>=', $fetchInfo['start'])
            ->whereDate('end', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (Event $event) => EventData::make()
                    ->id($event->id)
                    ->title($event->description)
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
                ->mountUsing(
                    function (Forms\Form $form, array $arguments) {
                        $form->fill([
                            'starts_at' => $arguments['start'] ?? null,
                            'ends_at' => $arguments['end'] ?? null
                        ]);
                    }
                ),
        ];
    }

    protected function viewAction(): Actions\ViewAction
    {
        return Actions\ViewAction::make()
            ->modalHeading('Event Information')
            ->infolist([
                \Filament\Infolists\Components\TextEntry::make('employee.full_name'),
            ]);
    }

    // public function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             \Filament\Infolists\Components\TextEntry::make('hris_number')
    //         ]);
    // }

    public function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('hris_number')
                ->label('Employee Name')
                ->options(\App\Models\Employee::all()->pluck('full_name', 'id'))
                ->native(false)
                ->searchable()
                ->required()
                ->columnSpanFull(),
            \Filament\Forms\Components\Select::make('tag')
                ->label('Type')
                ->options([
                    'wfh' => 'Work from Home',
                    'hwfh' => 'Hybrid Work from Home',
                    'ala' => 'Official Leave',
                    'cdo' => 'Compensatory Day-off',
                ])
                ->live()
                ->native(false)
                ->searchable()
                ->required()
                ->columnSpanFull()
                ->afterStateUpdated(function(\Filament\Forms\Set $set, ?string $state) {
                    if($state == "wfh") {
                        $set('description', 'Work from Home');
                    }
                    else if($state == "hwfh") {
                        $set('description', 'Hybrid Work from Home');
                    }
                    else if($state == "ala") {
                        $set('description', 'Official Leave');
                    }
                    else if($state == "cdo") {
                        $set('description', 'Compensatory Day-off');
                    }
                }),
            \Filament\Forms\Components\TextInput::make('description')
                ->label('Description')
                ->columnSpanFull()
                ->disabled(),
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
