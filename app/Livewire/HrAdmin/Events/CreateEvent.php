<?php

namespace App\Livewire\HrAdmin\Events;

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
use Illuminate\Support\Facades\Gate;
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
        return view('livewire.hr-admin.events.create-event');
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
                            ->options(Events::class)
                            ->reactive()
                            ->native(false)
                            ->required()
                            ->live(),
                        \Filament\Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->visible(function (Get $get) {
                                return match (Events::parse($get('tag'))) {
                                    Events::HOL => true,
                                    Events::SUS => true,
                                    default => false,
                                };
                            })
                            ->columnSpanFull()
                            ->required(),
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

        if (Gate::allows('special-events') && (Events::parse($data['tag']) == Events::HOL || Events::parse($data['tag']) == Events::SUS || Events::parse($data['tag']) == Events::FLAG)) {
            if (Events::parse($data['tag']) == Events::SUS) {
                if ($data['whole_day']) {
                    $data['start'] = $date->format('Y-m-d') . ' 08:00:00';
                    $data['end'] = $date->format('Y-m-d') . ' 17:00:00';
                    // $data['end'] = $data['ends_at'] . ' 17:00:00';
                } else {
                    $data['start'] = $date->format('Y-m-d') . ' ' . $data['time'] . ':00';
                    $data['end'] = $date->format('Y-m-d') . ' ' . $data['time'] . ':00';
                    // $data['end'] = $data['ends_at'] . ' ' . $data['time'] . ':00';
                }
            } else if (Events::parse($data['tag']) == Events::HOL || Events::parse($data['tag']) == Events::FLAG) {
                $data['description'] = (Events::parse($data['tag']) == Events::HOL) ? $data['description'] : Events::parse($data['tag'])->getLabel();
                $data['start'] = $date->format('Y-m-d') . ' 08:00:00';
                $data['end'] = $date->format('Y-m-d') . ' 17:00:00';
                // $data['end'] = $data['ends_at'] . ' 17:00:00';
            }

            Event::create([
                'start' => $data['start'],
                'end' => $data['end'],
                'tag' => $data['tag'],
                'description' => $data['description'],
                'status' => 'approved',
                'created_by' => auth()->user()->hris_number,
            ]);

            Notification::make()
                ->title("Event successfully created")
                ->body("You have schedule a " . Events::tryFrom($data['tag'])->getLabel() . " event.")
                ->success()
                ->color('success')
                ->send();
        } else {
            $official_time = OfficialTime::where('hris_number', $data['hris_number'])->where('status', 'approved')->first();
            $time_start = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '08:00:00');
            // $time_end = \Carbon\Carbon::parse($data['ends_at'] . ' ' . '17:00:00');
            $time_end = $time_start->copy()->addHours(9);

            if ($official_time) {
                $time_start = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '08:00:00');
                $time_end = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '17:00:00');
            }

            if (Events::parse($data['tag']) == Events::ALA) {
                $description = $data['description_leave'];
            } else {
                $description = Events::tryFrom($data['tag'])->getLabel();
            }

            foreach ($data['hris_number'] as $employee) {
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
        }



        $this->dispatch('refresh-calendar')->to(Calendar::class);
        $this->dispatch('close-modal', id: 'create-event');
    }
}
