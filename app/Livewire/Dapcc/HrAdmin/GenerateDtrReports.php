<?php

namespace App\Livewire\Dapcc\HrAdmin;

use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use Filament\Forms\Form;
use Livewire\Attributes\Title;
use App\Enums\AppointmentStatus;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class GenerateDtrReports extends Component implements HasForms
{
    use InteractsWithForms;
    #[Title('| Generate DTR Report')]

    public ?array $individualData = [];
    public ?array $bulkData = [];

    public function mount(): void
    {
        $this->individualForm->fill();
        $this->bulkForm->fill();
    }

    public function render()
    {
        return view('livewire.dapcc.hr-admin.generate-dtr-reports');
    }

    public function individualForm(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make(3)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('individual_yearmonth')
                            ->label('Select Year and Month')
                            ->validationAttribute('Year and Month')
                            ->type('month')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('individual_week') != null) {
                                    $set('individual_week', null);
                                }
                            })
                            ->required(fn(Get $get) => $get('individual_week') == null),
                        \Filament\Forms\Components\Select::make('individual_cutoff')
                            ->label('Select Cut-off')
                            ->validationAttribute('Cut-off')
                            ->options([1 => "First Cut-off", 2 => "Second Cut-off"])
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('individual_week') != null) {
                                    $set('individual_week', null);
                                }
                            })
                            ->required(fn(Get $get) => $get('individual_week') == null),
                        \Filament\Forms\Components\DatePicker::make('individual_week')
                            ->label('Select Week Start (For Jobbers weekly DTR report)')
                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d')
                            ->weekStartsOnSunday()
                            ->reactive()
                            ->native(false)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('individual_yearmonth') != null) {
                                    $set('individual_yearmonth', null);
                                }
                                if ($get('individual_cutoff') != null) {
                                    $set('individual_cutoff', null);
                                }
                            })
                            ->closeOnDateSelection()
                            ->disabledDates(function () {
                                $start = Carbon::now()->startOfYear();
                                $end = Carbon::now()->endOfYear();
                                $period = CarbonPeriod::create($start, $end);

                                $disables = [];
                                foreach ($period as $date) {
                                    if (!$date->isSunday()) {
                                        $disables[] = $date->format('Y-m-d');
                                    }
                                }

                                return $disables;
                            })
                            ->required(fn(Get $get) => $get('individual_yearmonth') == null || $get('individual_cutoff') == null),
                        \Filament\Forms\Components\Select::make('individual_hris_number')
                            ->label('Employee')
                            ->placeholder('Enter HRIS Number or Name')
                            ->validationAttribute('Employee')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (HasForms $livewire, \Filament\Forms\Components\Select $component) {
                                $livewire->validateOnly($component->getStatePath());
                            })
                            ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->isDapcc()->limit(50)->get()->pluck('full_name', 'hris_number')->toArray())
                            ->getOptionLabelUsing(fn($value): ?string => Employee::find($value)?->full_name)
                            ->searchable()
                            ->required()
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('generate-employee-dtr')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->action(function () {
                                        $this->generate(type: 'individual');
                                    })
                            )
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('individualData');
    }

    public function bulkForm(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make(3)
                    ->schema([
                        \Filament\Forms\Components\Select::make('appointment_status')
                            ->label('Select Appointment Status')
                            ->options(AppointmentStatus::class)
                            ->native(false)
                            ->live()
                            ->required(),
                        \Filament\Forms\Components\DatePicker::make('week')
                            ->label('Select Week Start')
                            ->validationAttribute('Week Start')
                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d')
                            ->weekStartsOnSunday()
                            ->native(false)
                            ->closeOnDateSelection()
                            ->disabledDates(function () {
                                $start = Carbon::now()->startOfYear();
                                $end = Carbon::now()->endOfYear();
                                $period = CarbonPeriod::create($start, $end);

                                $disables = [];
                                foreach ($period as $date) {
                                    if (!$date->isSunday()) {
                                        $disables[] = $date->format('Y-m-d');
                                    }
                                }

                                return $disables;
                            })
                            ->required()
                            ->visible(fn(Get $get) => $get('appointment_status') == AppointmentStatus::JOBBER->value),
                        \Filament\Forms\Components\TextInput::make('yearmonth')
                            ->label('Select Year and Month')
                            ->validationAttribute('Year and Month')
                            ->type('month')
                            ->default(now()->format('Y-m'))
                            ->required()
                            ->hidden(fn(Get $get) => $get('appointment_status') == AppointmentStatus::JOBBER->value),
                        \Filament\Forms\Components\Select::make('cutoff')
                            ->label('Select Cut-off')
                            ->validationAttribute('Cut-off')
                            ->options([1 => "First Cut-off", 2 => "Second Cut-off"])
                            ->default(1)
                            ->native(false)
                            ->required()
                            ->hidden(fn(Get $get) => $get('appointment_status') == AppointmentStatus::JOBBER->value),
                        \Filament\Forms\Components\Select::make('office_id')
                            ->label('Office/Division')
                            ->placeholder('Enter Office/Division')
                            ->validationAttribute('Office/Division')
                            ->native(false)
                            ->live()
                            ->options(\App\Models\Department::isDapcc()->get()->pluck('description', 'id'))
                            ->searchable()
                            ->afterStateUpdated(function (HasForms $livewire, \Filament\Forms\Components\Select $component) {
                                $livewire->validateOnly($component->getStatePath());
                            })
                            ->required()
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('generate-office-dtr')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->action(function () {
                                        $this->generate(type: 'office');
                                    })
                            )
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('individualData');
    }

    public function generate($type)
    {
        if ($type == 'individual') {
            $data = $this->individualForm->getState();
            $this->dispatch('redirectToDtrReport', dtrtype: 'employee', hris_number: $data['individual_hris_number'], yearmonth: $data['individual_yearmonth'], cutoff: $data['individual_cutoff'], week: $data['individual_week']);
        } else if ($type == 'office') {
            $data = $this->bulkForm->getState();
            $this->dispatch('redirectToDtrReport', dtrtype: 'bulk', office_id: $data['office_id'], yearmonth: (array_key_exists('yearmonth', $data) ? $data['yearmonth'] : null), cutoff: (array_key_exists('cutoff', $data) ? $data['cutoff'] : null), appointment_status: (array_key_exists('appointment_status', $data) ? $data['appointment_status'] : null), week: (array_key_exists('week', $data) ? $data['week'] : null));
            // return redirect()->route('admin.dtr.bulk-dtr-report', ['department' => $data['office_id'], 'yearmonth' => $data['yearmonth'], 'cutoff' => $data['cutoff'], 'appointment_status' => $data['appointment_status']]);
        }
    }

    protected function getForms(): array
    {
        return [
            'individualForm',
            'bulkForm',
        ];
    }
}
