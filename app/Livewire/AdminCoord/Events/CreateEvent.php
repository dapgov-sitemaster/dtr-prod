<?php

namespace App\Livewire\AdminCoord\Events;

use Carbon\Carbon;
use Filament\Forms;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Enums\ScheduleType;
use Livewire\Attributes\On;
use App\Models\OfficialTime;
use App\Enums\OfficialLeaves;
use Livewire\Attributes\Reactive;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class CreateEvent extends Component implements HasForms
{
    use InteractsWithForms;
    public ?array $data = [];

    public $eventData;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.admin-coord.events.create-event');
    }

    #[On('creating-event')]
    public function openModal($date)
    {
        $this->eventData = (object) ['date' => Carbon::parse($date)];
        $this->dispatch('open-modal', id: 'create-event');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        \Filament\Forms\Components\Select::make('tag')
                            ->label('Type of Event')
                            ->options(function () {
                                $options = [];
                                foreach (Events::cases() as $case) {
                                    if ($case == Events::WFH || $case == Events::HWFH) {
                                        if ($this->eventData->date->dayOfWeek == Carbon::FRIDAY) {
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
            ])
            ->statePath('data');
    }

    public function create()
    {
        $this->dispatch('refresh')->to(Index::class);
        $data = $this->form->getState();

        $date = $this->eventData->date;


        if (Events::parse($data['tag']) == Events::ALA) {
            $description = $data['description_leave'];
        } else {
            $description = Events::tryFrom($data['tag'])->getLabel();
        }

        foreach ($data['hris_number'] as $employee) {
            $official_time = OfficialTime::where('hris_number', $employee)->where('status', 'approved')->first();
            $time_start = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '08:00:00');
            $time_end = $time_start->copy()->addHours(9);

            if ($official_time) {
                $time_start = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '08:00:00');
                $time_end = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '17:00:00');
            }

            Event::create([
                'hris_number' => $employee,
                'start' => $time_start->format('Y-m-d H:i:s'),
                'end' => $time_end->format('Y-m-d H:i:s'),
                'tag' => $data['tag'],
                'description' => $description,
                'status' => 'approved',
                'created_by' => auth()->user()->hris_number,
            ]);
        }

        Notification::make()
            ->title("Event successfully created")
            ->body(count($data['hris_number']) . " personnel has been scheduled a " . Events::tryFrom($data['tag'])->getLabel() . " event.")
            ->success()
            ->color('success')
            ->send();
        $this->dispatch('refresh-calendar')->to(Calendar::class);
        $this->dispatch('close-modal', id: 'create-event');
    }
}
