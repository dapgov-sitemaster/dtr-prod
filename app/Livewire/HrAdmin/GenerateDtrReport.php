<?php

namespace App\Livewire\HrAdmin;

use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Employee;
use Filament\Forms\Form;
use Livewire\Attributes\Js;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class GenerateDtrReport extends Component implements HasForms
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
        return view('livewire.hr-admin.generate-dtr-report');
    }

    public function individualForm(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('yearmonth')
                            ->label('Select Year and Month')
                            ->validationAttribute('Year and Month')
                            ->type('month')
                            ->default(now()->format('Y-m'))
                            ->required(),
                        \Filament\Forms\Components\Select::make('cutoff')
                            ->label('Select Cut-off')
                            ->validationAttribute('Cut-off')
                            ->options([1 => "First Cut-off", 2 => "Second Cut-off"])
                            ->default(1)
                            ->native(false)
                            ->required(),
                        \Filament\Forms\Components\Select::make('hris_number')
                            ->label('Employee')
                            ->placeholder('Enter HRIS Number or Name')
                            ->validationAttribute('Employee')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (HasForms $livewire, \Filament\Forms\Components\Select $component) {
                                $livewire->validateOnly($component->getStatePath());
                            })
                            ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->limit(50)->get()->pluck('full_name', 'hris_number')->toArray())
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
                        \Filament\Forms\Components\TextInput::make('yearmonth')
                            ->label('Select Year and Month')
                            ->validationAttribute('Year and Month')
                            ->type('month')
                            ->default(now()->format('Y-m'))
                            ->required(),
                        \Filament\Forms\Components\Select::make('cutoff')
                            ->label('Select Cut-off')
                            ->validationAttribute('Cut-off')
                            ->options([1 => "First Cut-off", 2 => "Second Cut-off"])
                            ->default(1)
                            ->native(false)
                            ->required(),
                        \Filament\Forms\Components\Select::make('appointment_status')
                            ->label('Select Appointment Status')
                            ->options(AppointmentStatus::class)
                            ->native(false)
                            ->required(),
                        \Filament\Forms\Components\Select::make('office_id')
                            ->label('Office/Division')
                            ->placeholder('Enter Office/Division')
                            ->validationAttribute('Office/Division')
                            ->native(false)
                            ->live()
                            ->options(\App\Models\Department::all()->pluck('description', 'id'))
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
            $this->dispatch('redirectToDtrReport', dtrtype: 'employee', hris_number: $data['hris_number'], yearmonth: $data['yearmonth'], cutoff: $data['cutoff']);
        } else if ($type == 'office') {
            $data = $this->bulkForm->getState();
            $this->dispatch('redirectToDtrReport', dtrtype: 'bulk', office_id: $data['office_id'], yearmonth: $data['yearmonth'], cutoff: $data['cutoff'], appointment_status: $data['appointment_status']);
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
