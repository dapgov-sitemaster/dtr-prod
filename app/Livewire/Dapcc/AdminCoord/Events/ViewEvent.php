<?php

namespace App\Livewire\Dapcc\AdminCoord\Events;

use Carbon\Carbon;
use App\Models\Event;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use App\Enums\Dapcc\Events;
use Livewire\Attributes\On;
use App\Enums\OfficialLeaves;
use Filament\Infolists\Infolist;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Livewire\Attributes\Reactive;
use Illuminate\Database\Eloquent\Model;

class ViewEvent extends Component implements HasForms, HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithForms, InteractsWithInfolists;
    #[Reactive]
    public $events;

    public function render()
    {
        return view('livewire.dapcc.admin-coord.events.view-event');
    }

    // #[On('viewing-event')]
    // public function openModal($data)
    // {
    //     $this->events = (object) $data;
    //     $this->dispatch('open-modal', id: 'view-event');
    // }

    public function eventInfolist(Infolist $infolist): Infolist
    {
        $event = Event::query()
            ->whereDate('start', $this->events->date)
            ->where('tag', $this->events->tag)
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
                    ->whereDate('start', $this->events->date)
                    ->where('tag', $this->events->tag)
            )
            ->columns([
                \Filament\Tables\Columns\Layout\Split::make([
                    \Filament\Tables\Columns\TextColumn::make('employee.full_name')
                        ->label('Full Name')
                        ->description('Full Name', position: 'above')
                        ->sortable(['first_name', 'last_name']),
                    \Filament\Tables\Columns\TextColumn::make('start')
                        ->label('Time')
                        ->formatStateUsing(function ($state) {
                            return ($state->format('H:i:s') > '16:00:00') ? $state->format('Y-m-d g:i A') : $state->format('g:i A');
                        })
                        ->description('Time Start', position: 'above')
                        ->sortable()
                        ->visible(fn($record) => $record?->tag == Events::SHIFT),
                    \Filament\Tables\Columns\TextColumn::make('end')
                        ->label('Time')
                        ->formatStateUsing(function ($record) {
                            return ($record->start->format('H:i:s') > '16:00:00') ? $record->end->format('Y-m-d g:i A') : $record->end->format('g:i A');
                        })
                        ->description('Time End', position: 'above')
                        ->sortable()
                        ->visible(fn($record) => $record?->tag == Events::SHIFT),
                    \Filament\Tables\Columns\TextColumn::make('description')
                        ->formatStateUsing(fn($record) => ($record->tag == Events::ALA) ? OfficialLeaves::parse($record->description)->getLabel() : $record->description)
                        ->label('Description')
                        ->description('Description', position: 'above')
                        ->sortable()
                        ->visible(fn($record) => $record?->tag == Events::ALA),
                    \Filament\Tables\Columns\TextColumn::make('mov.filename')
                        ->formatStateUsing(fn() => "File uploaded")
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
                ])
                    ->from('lg')
            ])
            ->filters([
                // ...
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make()
                    ->modalHeading(fn($record) => 'Edit Event of ' . $record->tag->getLabel() . ' (' . $record->start->format('F d, Y') . ')')
                    ->mountUsing(
                        function (Event $record, \Filament\Forms\Form $form, array $arguments) {
                            $form->fill([
                                'date' => $record->start->format('Y-m-d'),
                                'timestart' => $record->start->format('H:i:s'),
                                'hris_number' => $record->hris_number,
                                'tag' => $record->tag,
                                'description_leave' => OfficialLeaves::parse($record->description) ?? $record->description,
                            ]);
                        }
                    )
                    ->form([
                        \Filament\Forms\Components\Grid::make()
                            ->schema([
                                \Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr::make('date')
                                    ->label('Date')
                                    ->required()
                                    ->live(),
                                \Filament\Forms\Components\Select::make('tag')
                                    ->label('Type of Event')
                                    ->options(function () {
                                        return collect(Events::cases())
                                            ->filter(fn($case) => $case !== Events::HOL && $case !== Events::SUS && $case !== Events::FLAG)
                                            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                                            ->toArray();
                                    })
                                    ->reactive()
                                    ->native(false)
                                    ->required()
                                    ->live(),
                                \Filament\Forms\Components\TimePicker::make('timestart')
                                    ->label('Time Start')
                                    ->live()
                                    ->format('H:i:s')
                                    ->displayFormat('g:i A')
                                    ->seconds(false)
                                    ->required()
                                    ->visible(fn(Get $get) => Events::parse($get('tag')) == Events::SHIFT),
                                \Filament\Forms\Components\Select::make('description_leave')
                                    ->label('Type of Official Leave')
                                    ->options(OfficialLeaves::class)
                                    ->native(false)
                                    ->required()
                                    ->visible(function (Get $get) {
                                        return match (Events::parse($get('tag'))) {
                                            Events::ALA => true,
                                            default => false,
                                        };
                                    }),
                                \Filament\Forms\Components\Select::make('hris_number')
                                    ->label('Employee Name')
                                    ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->isDapcc()->departmentCovered()->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
                                    ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()?->full_name)
                                    ->native(false)
                                    ->searchable(['first_name', 'last_name', 'hris_number'])
                                    ->required()
                                    ->columnSpanFull()
                                    ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
                                        Events::HOL, Events::SUS, Events::FLAG => true,
                                        default => false,
                                    }),
                            ]),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        if ($data['tag'] == Events::ALA) {
                            $description = $data['description_leave'];
                        } else {
                            $description = $data['tag']->getLabel();
                        }
                        $data['description'] = $description;

                        return $data;
                    })
                    ->using(function (Model $record, $data) {
                        $date = Carbon::parse($data['date'] . ' ' . $data['timestart']);
                        $record->start = $date->format('Y-m-d H:i:s');
                        $record->end = $date->copy()->addHours(9)->format('Y-m-d H:i:s');
                        $record->tag = $data['tag'];
                        $record->description = $data['description'];
                        $record->save();
                        return $record;
                    })
                    ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Event has been updated!')
                            ->color('success'),
                    ),
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
            ]);
    }
}
