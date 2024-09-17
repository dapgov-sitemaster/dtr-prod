<?php

namespace App\Livewire\AdminCoord\Events;

use Carbon\Carbon;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use App\Enums\OfficialLeaves;
use Filament\Infolists\Infolist;
use Livewire\Attributes\Reactive;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Infolists\Concerns\InteractsWithInfolists;

class ViewEvent extends Component implements HasForms, HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithForms, InteractsWithInfolists;
    public $events;

    public function render()
    {
        return view('livewire.admin-coord.events.view-event');
    }

    #[On('viewing-event')]
    public function openModal($data)
    {
        $this->events = (object) $data;
        $this->dispatch('open-modal', id: 'view-event');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->with(['event_created_by', 'mov'])
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
                    \Filament\Tables\Columns\TextColumn::make('mov')
                        ->placeholder('No uploaded file')
                        ->url(function ($record) {
                            dd($record);
                            if (!$record->mov) {
                                return null;
                            }
                            return route('admin.pdf.view-mov', ['mov' => $record->mov]);
                        })
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record?->tag == Events::ALA),
                    \Filament\Tables\Columns\TextColumn::make('event_created_by.full_name')
                        ->label('Created by')
                        ->description('Created by', position: 'above')
                        ->sortable(),
                ])
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
                                'hris_number' => $record->hris_number,
                                'tag' => $record->tag,
                                'description_leave' => OfficialLeaves::parse($record->description) ?? $record->description,
                            ]);
                        }
                    )
                    ->form([
                        \Filament\Forms\Components\Grid::make()
                            ->schema([
                                \Filament\Forms\Components\Select::make('tag')
                                    ->label('Type of Event')
                                    ->options(function ($record) {
                                        $options = [];
                                        foreach (Events::cases() as $case) {
                                            if ($case == Events::WFH || $case == Events::HWFH) {
                                                if ($record->start->dayOfWeek == Carbon::FRIDAY) {
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
                                    ->label('Employee Name')
                                    // ->options(\App\Models\Employee::whereIn('department_id', $this->departments)->where('employment_status', true)->get()->pluck('full_name', 'hris_number'))
                                    ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->departmentCovered()->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
                                    ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()?->full_name)
                                    ->native(false)
                                    ->searchable(['first_name', 'last_name', 'hris_number'])
                                    ->required()
                                    ->columnSpanFull()
                                    ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
                                        Events::HOL, Events::SUS, Events::FLAG => true,
                                        default => false,
                                    }),
                            ])
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        if (Events::parse($data['tag']) == Events::ALA) {
                            $description = $data['description_leave'];
                        } else {
                            $description = Events::tryFrom($data['tag'])->getLabel();
                        }
                        $data['description'] = $description;

                        return $data;
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

    public function eventInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state([
                'tag' => Events::parse($this->events?->tag),
                'date' => Carbon::parse($this->events?->date)->format('F d, Y')
            ])
            ->schema([
                \Filament\Infolists\Components\Fieldset::make('Event Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('date')
                            ->label('Date'),
                        \Filament\Infolists\Components\TextEntry::make('tag')
                            ->badge()
                            ->label('Event'),
                    ])
            ]);
    }
}
