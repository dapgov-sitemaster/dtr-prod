<?php

namespace App\Livewire\Dapcc\AdminCoord\Events;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Event;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use Filament\Forms\Form;
use App\Enums\Dapcc\Events;
use Livewire\Attributes\On;
use App\Enums\OfficialLeaves;
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
        return view('livewire.dapcc.admin-coord.events.create-event');
    }

    #[On('creating-event')]
    public function openModal()
    {
        $this->form->fill();
        $this->dispatch('open-modal', id: 'create-event');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        \Filament\Forms\Components\ToggleButtons::make('date_type')
                            ->label('Date Type')
                            ->inline()
                            ->options(['single' => 'Single Date', 'multi' => 'Date Range'])
                            ->default('single')
                            ->required()
                            ->afterStateUpdated(function (Set $set) {
                                $set('date', null);
                                $set('daterange', null);
                            })
                            ->live(),
                        \Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr::make('date')
                            ->label('Date')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('hris_number', null);
                                $set('employees', null);
                            })
                            ->visible(fn(Get $get) => $get('date_type') == 'single'),
                        \Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr::make('daterange')
                            ->label('Date Range')
                            ->range()
                            ->theme(\Coolsam\FilamentFlatpickr\Enums\FlatpickrTheme::DEFAULT)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('hris_number', null);
                                $set('employees', null);
                            })
                            ->visible(fn(Get $get) => $get('date_type') == 'multi'),
                    ]),
                Forms\Components\Grid::make()
                    ->schema([
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
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Grid::make()
                            ->schema([
                                \Filament\Forms\Components\ToggleButtons::make('set_time_all')
                                    ->label('Set Time Start to all')
                                    ->inline()
                                    ->boolean()
                                    ->default(false)
                                    ->required()
                                    ->live(),
                                // \Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr::make('date')
                                //     ->dateFormat('F j, Y')
                                //     ->required()
                                //     ->visible(fn(Get $get) => Events::parse($get('tag')) === Events::SHIFT),
                                // \Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr::make('daterange')
                                //     ->range()
                                //     ->theme(\Coolsam\FilamentFlatpickr\Enums\FlatpickrTheme::DEFAULT)
                                //     ->required()
                                //     ->visible(fn(Get $get) => Events::parse($get('tag')) === Events::MULTISHIFT),
                                Forms\Components\TimePicker::make('timestart')
                                    ->label('Time Start')
                                    ->live()
                                    ->displayFormat('g:i A')
                                    ->seconds(false)
                                    // ->afterStateUpdated(function (Set $set, $state) {
                                    //     $set('timeend', Carbon::parse($state)->addHours(9)->format('H:i'));
                                    // })
                                    ->required()
                                    ->visible(fn(Get $get) => $get('set_time_all')),
                            ])
                            ->columns(2)
                            ->visible(fn(Get $get) => Events::parse($get('tag')) == Events::SHIFT),
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
                            ->getSearchResultsUsing(fn(string $search, Get $get): array => Employee::searchEmployee($search)->isDapcc()->departmentCovered()->isScheduled(($get('date') ? $get('date') : $get('daterange')))->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
                            ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()->full_name)
                            ->native(false)
                            ->searchable(['first_name', 'last_name', 'hris_number'])
                            ->required()
                            ->reactive()
                            ->columnSpanFull()
                            ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
                                Events::HOL, Events::SUS, Events::FLAG, Events::SHIFT => true,
                                default => false,
                            }),
                        \Filament\Forms\Components\Repeater::make('employees')
                            ->schema([
                                \Filament\Forms\Components\Select::make('hris_number')
                                    ->label('Employee Name')
                                    ->getSearchResultsUsing(fn(string $search, Get $get): array => Employee::searchEmployee($search)->isDapcc()->departmentCovered()->isScheduled(($get('../../date') ? $get('../../date') : $get('../../daterange')))->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
                                    ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()->full_name)
                                    ->native(false)
                                    ->searchable(['first_name', 'last_name', 'hris_number'])
                                    ->required()
                                    ->live()
                                    ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
                                        Events::HOL, Events::SUS, Events::FLAG => true,
                                        default => false,
                                    }),
                                Forms\Components\TimePicker::make('timestart')
                                    ->label('Time Start')
                                    ->live()
                                    ->displayFormat('g:i A')
                                    ->seconds(false)
                                    ->required()
                                    ->hidden(fn(Get $get) => $get('../../set_time_all')),
                            ])
                            ->addActionLabel('Add new record')
                            ->grid(2)
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => Employee::where('hris_number', $state['hris_number'])->first()->full_name ?? null)
                            ->visible(fn(Get $get) => Events::parse($get('tag')) == Events::SHIFT),
                    ])
                    ->visible(fn(Get $get) => $get('date') || $get('daterange'))
            ])
            ->statePath('data');
    }

    public function create()
    {
        $data = $this->form->getState();

        $date = (array_key_exists('date', $data)) ? [$data['date']] : $data['daterange'];
        $tag = Events::parse($data['tag']);
        $count = 0;

        if ($tag === Events::SHIFT) {
            foreach ($data['employees'] as $employee) {
                $dates = (count($date) > 1) ? CarbonPeriod::create($date[0], $date[1])->toArray() : [Carbon::parse($date[0])];
                $time = (($data['set_time_all']) ? $data['timestart'] : $employee['timestart']);
                foreach ($dates as $d) {
                    $d = Carbon::parse($d->format('Y-m-d') . ' ' . $time);
                    Event::create([
                        'hris_number' => $employee['hris_number'],
                        'start' => $d->format('Y-m-d H:i:s'),
                        'end' => $d->copy()->addHours(9)->format('Y-m-d H:i:s'),
                        'tag' => $tag,
                        'description' => $tag->getLabel(),
                        'status' => 'approved',
                        'created_by' => auth()->user()->hris_number,
                    ]);
                }
            }
            $count = $count + count($data['employees']);
        } else {
            foreach ($data['hris_number'] as $hris_number) {
                $dates = (count($date) > 1) ? CarbonPeriod::create($date[0], $date[1])->toArray() : [Carbon::parse($date[0])];
                $description = ($tag == Events::ALA) ? $data['description_leave'] : $tag->getLabel();

                foreach ($dates as $d) {
                    $d = Carbon::parse($d->format('Y-m-d') . ' 08:00:00');
                    Event::create([
                        'hris_number' => $hris_number,
                        'start' => $d->format('Y-m-d H:i:s'),
                        'end' => $d->copy()->addHours(9)->format('Y-m-d H:i:s'),
                        'tag' => $tag,
                        'description' => $description,
                        'status' => 'approved',
                        'created_by' => auth()->user()->hris_number,
                    ]);
                }
            }
            $count = $count + count($data['hris_number']);
        }

        Notification::make()
            ->title("Event successfully created")
            ->body($count . " personnel has been scheduled a " . $tag->getLabel() . " event.")
            ->success()
            ->color('success')
            ->send();

        $this->dispatch('refresh-calendar')->to(Calendar::class);
        $this->dispatch('close-modal', id: 'create-event');
        $this->form->fill();
    }
}
