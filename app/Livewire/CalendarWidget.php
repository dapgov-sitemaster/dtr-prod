<?php

namespace App\Livewire;

use Filament\Forms;
use App\Enums\Events;
use App\Models\Event;
use Illuminate\Support\Str;
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
                    ->title($event->employee->last_name. ', ' .Str::initials($event->employee->first_name))
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
                ->mutateFormDataUsing(function(array $data, $record): array {
                    $official_time = \App\Models\OfficialTime::where('hris_number', $data['hris_number'])->where('status', 'approved')->first();
                    $time_start = ($official_time) ? \Carbon\Carbon::parse($data['starts_at'].' '.$official_time->time_in) : \Carbon\Carbon::parse($data['ends_at'].' '.'08:00:00');
                    $time_end = $time_start->copy()->addHours(9);
                    // $description = Events::tryFrom($data['tag'])->getLabel();
                    $data['start'] = $time_start->format('Y-m-d H:i:s');
                    $data['end'] = $time_end->format('Y-m-d H:i:s');
                    // $data['description'] = $description;
                    // $data['created_by'] = auth()->user()->hris_number;

                    return $data;
                }),
            Actions\DeleteAction::make()
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
                            'ends_at' => $arguments['end'] ?? null
                        ]);
                    }
                )
                ->mutateFormDataUsing(function(array $data): array {
                    $official_time = \App\Models\OfficialTime::where('hris_number', $data['hris_number'])->where('status', 'approved')->first();
                    $time_start = ($official_time) ? \Carbon\Carbon::parse($data['starts_at'].' '.$official_time->time_in) : \Carbon\Carbon::parse($data['ends_at'].' '.'08:00:00');
                    $time_end = $time_start->copy()->addHours(9);
                    $description = Events::tryFrom($data['tag'])->getLabel();
                    $data['start'] = $time_start->format('Y-m-d H:i:s');
                    $data['end'] = $time_end->format('Y-m-d H:i:s');
                    $data['description'] = $description;
                    $data['status'] = 'approved';
                    $data['created_by'] = auth()->user()->hris_number;

                    return $data;
                }),
        ];
    }

    protected function viewAction(): Actions\ViewAction
    {
        return Actions\ViewAction::make()
            ->modalHeading(fn($record) => ($record->start->format('Y-m-d') == $record->end->format('Y-m-d')) ? 'Event Information ('. $record->start->format('M d, Y').')' : 'Event Information ('. $record->start->format('M d, Y'). ' - '. $record->end->format('M d, Y') .')')
            ->infolist([
                \Filament\Infolists\Components\Grid::make([
                        'sm' => 1,
                        'xl' => 3,
                    ])
                    ->schema([
                            \Filament\Infolists\Components\TextEntry::make('employee.full_name'),
                            \Filament\Infolists\Components\TextEntry::make('official_time.time_in')
                                ->formatStateUsing(function($state) {
                                    return $state->format('g:i A'). ' - ' .$state->copy()->addHours(9)->format('g:i A');
                                })
                                ->placeholder('Not set'),
                            \Filament\Infolists\Components\TextEntry::make('description'),
                        ]),
            ])
            ->extraModalFooterActions([
                \Filament\Actions\Action::make('test')
                    ->requiresConfirmation()
                    ->action(function () {
                        // ...
                    }),
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
                ->options(\App\Models\Employee::all()->pluck('full_name', 'hris_number'))
                ->native(false)
                ->searchable()
                ->required()
                ->columnSpanFull(),
            \Filament\Forms\Components\Select::make('tag')
                ->label('Type')
                ->options(Events::class)
                ->native(false)
                ->required()
                ->columnSpanFull(),
            \Filament\Forms\Components\FileUpload::make('mov')
                ->label('Upload MOV (optional)')
                ->acceptedFileTypes(['application/pdf', 'application/msword'])
                ->directory('event-movs')
                ->visibility('private'),
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
