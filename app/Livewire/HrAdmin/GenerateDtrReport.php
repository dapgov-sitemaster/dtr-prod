<?php

namespace App\Livewire\HrAdmin;

use Filament\Forms\Set;
use Livewire\Component;
use Filament\Forms\Form;
use Livewire\Attributes\Title;
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
                \Filament\Forms\Components\Fieldset::make('Employee DTR Report')
                    ->columns(2)
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
                        \Filament\Forms\Components\Select::make('hris-number')
                            ->label('HRIS Number')
                            ->placeholder('Enter HRIS Number')
                            ->validationAttribute('HRIS Number')
                            ->native(false)
                            ->options(\App\Models\Employee::all()->pluck('full_name', 'hris_number'))
                            ->searchable()
                            ->required()
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('test')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->action(function () {
                                        $this->generate();
                                    })
                            )
                            ->columnSpanFull(),

                    ]),
                // ...
            ])
            ->statePath('individualData');
    }

    public function bulkForm(Form $form): Form
    {
        return $form
            ->schema([
                // \Filament\Forms\Components\Fieldset::make('Employee DTR Report')
                //     ->columns(2)
                //     ->schema([
                //         \Filament\Forms\Components\TextInput::make('yearmonth')
                //             ->label('Select Year and Month')
                //             ->validationAttribute('Year and Month')
                //             ->type('month')
                //             ->default(now()->format('Y-m'))
                //             ->required(),
                //         \Filament\Forms\Components\Select::make('cutoff')
                //             ->label('Select Cut-off')
                //             ->validationAttribute('Cut-off')
                //             ->options([1 => "First Cut-off", 2 => "Second Cut-off"])
                //             ->default(1)
                //             ->native(false)
                //             ->required(),
                //         \Filament\Forms\Components\Select::make('hris-number')
                //             ->label('HRIS Number')
                //             ->placeholder('Enter HRIS Number')
                //             ->validationAttribute('HRIS Number')
                //             ->native(false)
                //             ->options(\App\Models\Employee::all()->pluck('full_name', 'hris_number'))
                //             ->searchable()
                //             ->required()
                //             ->suffixAction(
                //                 \Filament\Forms\Components\Actions\Action::make('test')
                //                     ->icon('heroicon-m-magnifying-glass')
                //                     ->action(function () {
                //                         $this->generate();
                //                     })
                //             )
                //             ->columnSpanFull(),

                //     ]),
                // ...
            ])
            ->statePath('individualData');
    }

    public function generate()
    {
        dd($this->individualForm->getState());
    }

    protected function getForms(): array
    {
        return [
            'individualForm',
            'bulkForm',
        ];
    }
}
