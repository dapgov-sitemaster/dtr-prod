<?php

namespace App\Livewire\Employee\LeaveFlexiApplication\Event;

use Carbon\Carbon;
use App\Enums\Events;
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
use Filament\Infolists\Infolist;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Livewire\Employee\LeaveFlexiApplication\Index;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use App\Livewire\Employee\LeaveFlexiApplication\TableList;

class ViewEvent extends Component implements HasForms, HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithForms, InteractsWithInfolists;
    public $events;

    public function render()
    {
        return view('livewire.employee.leave-flexi-application.event.view-event');
    }

    #[On('viewing-event')]
    public function openModal($data)
    {
        $this->events = (object) $data;
        $this->dispatch('open-modal', id: 'view-event');
    }

    public function eventInfolist(Infolist $infolist): Infolist
    {
        $event = Event::query()
            ->whereDate('start', $this->events?->date)
            ->where('tag', $this->events?->tag)
            ->first();

        if (!$event) {
            $this->dispatch('refresh-calendar')->to(Calendar::class);
            $this->dispatch('close-modal', id: 'view-event');
        }
        return $infolist
            ->state([
                'created_by' => $event?->event_created_by->full_name,
                'description' => $event?->description,
                'tag' => $event?->tag,
                'date' => $event?->start->format('F d, Y')
            ])
            ->schema([
                \Filament\Infolists\Components\Fieldset::make('Event Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('date')
                            ->label('Date'),
                        \Filament\Infolists\Components\TextEntry::make('created_by')
                            ->label('Created by')
                            ->visible(fn() => ($event?->tag == Events::HOL || $event?->tag == Events::SUS || $event?->tag == Events::FLAG)),
                        \Filament\Infolists\Components\TextEntry::make('tag')
                            ->badge()
                            ->label('Event'),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->visible(fn() => ($event?->tag == Events::HOL || $event?->tag == Events::SUS || $event?->tag == Events::FLAG)),
                    ])
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->with('event_created_by', 'mov')
                    ->whereHas('employee', fn($query) => $query->departmentCovered())
                    ->whereDate('start', $this->events?->date)
                    ->where('tag', $this->events?->tag)
            )
            ->columns([
                \Filament\Tables\Columns\Layout\Split::make([
                    \Filament\Tables\Columns\TextColumn::make('employee.full_name')
                        ->label('Full Name')
                        ->description('Full Name', position: 'above')
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('description')
                        ->formatStateUsing(fn($record) => ($record->tag == Events::ALA) ? OfficialLeaves::parse($record->description)->getLabel() : $record->description)
                        ->label('Description')
                        ->description('Description', position: 'above')
                        ->sortable()
                        ->visible(fn($record) => $record?->tag == Events::ALA),
                    \Filament\Tables\Columns\TextColumn::make('mov.filename')
                        ->formatStateUsing(fn(string $state) => "File uploaded")
                        ->iconColor("primary")
                        ->icon("heroicon-m-arrow-down-tray")
                        ->placeholder('No uploaded file')
                        ->description('MOV File', position: 'above')
                        ->url(function ($record) {
                            if (!$record->mov) {
                                return null;
                            }
                            return route('admin.pdf.view-mov', ['mov' => $record->mov]);
                        })
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record?->tag == Events::ALA || $record?->tag == Events::CDO),
                    \Filament\Tables\Columns\TextColumn::make('event_created_by')
                        ->formatStateUsing(fn($state) => $state->full_name)
                        ->label('Created by')
                        ->description('Created by', position: 'above')
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('status')
                        ->label('Status')
                        ->description('Status', position: 'above')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'disapproved' => 'danger',
                        }),
                ])
                    ->from('lg')
            ])
            ->filters([
                // ...
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make()
                    ->modalHeading(fn($record) => 'Edit Event of ' . $record->tag->getLabel() . ' (' . $record->start->format('F d, Y') . ')')
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

                        if ($official_time) {
                            $data['start'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                            $data['end'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '17:00:00');
                        }

                        if (Events::parse($data['tag']) == Events::ALA) {
                            $data['description'] = $data['description_leave'];
                        } else {
                            $data['description'] = Events::parse($data['tag'])->getLabel();
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
                    ->after(function () {
                        $this->dispatch('refresh-calendar')->to(Calendar::class);
                        $this->dispatch('refresh-table')->to(TableList::class);
                    })
                    ->visible(fn($record) => $record->created_by === auth()->user()->hris_number && $record->status === 'pending'),
                \Filament\Tables\Actions\DeleteAction::make()
                    ->modalHeading('Remove Event!')
                    ->label('Remove')
                    ->requiresConfirmation()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Event has been removed')
                            ->color('success')
                    )
                    ->after(function () {
                        $this->dispatch('refresh-calendar')->to(Calendar::class);
                        $this->dispatch('refresh-table')->to(TableList::class);
                    })
                    ->visible(fn($record) => $record->created_by === auth()->user()->hris_number && $record->status === 'pending'),
            ]);
    }
}
