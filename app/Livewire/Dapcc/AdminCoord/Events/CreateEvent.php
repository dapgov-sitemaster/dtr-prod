<?php

namespace App\Livewire\Dapcc\AdminCoord\Events;

use Carbon\Carbon;
use Filament\Forms;
use App\Enums\Dapcc\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Enums\ScheduleType;
use Livewire\Attributes\On;
use App\Models\OfficialTime;
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
    public function openModal($date)
    {
        $this->eventData = (object) ['date' => Carbon::parse($date)];
        $this->dispatch('open-modal', id: 'create-event');
    }

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\Grid::make()
    //                 ->schema([
    //                     \Filament\Forms\Components\Select::make('tag')
    //                         ->label('Type of Event')
    //                         ->options(function () {
    //                             return collect(Events::cases())
    //                                 ->filter(fn($case) => $case !== Events::HOL && $case !== Events::SUS && $case !== Events::FLAG)
    //                                 ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
    //                                 ->toArray();
    //                         })
    //                         ->reactive()
    //                         ->native(false)
    //                         ->required()
    //                         ->live(),
    //                     \Filament\Forms\Components\Select::make('description_leave')
    //                         ->label('Type of Official Leave')
    //                         ->options(OfficialLeaves::class)
    //                         ->native(false)
    //                         ->visible(function (Get $get) {
    //                             return match (Events::parse($get('tag'))) {
    //                                 Events::ALA => true,
    //                                 default => false,
    //                             };
    //                         })
    //                         ->required(),
    //                     \Filament\Forms\Components\Select::make('hris_number')
    //                         ->label('Employee Name/s')
    //                         ->multiple()
    //                         // ->options(\App\Models\Employee::whereIn('department_id', $this->departments)->where('employment_status', true)->get()->pluck('full_name', 'hris_number'))
    //                         ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->departmentCovered()->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
    //                         ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()->full_name)
    //                         ->native(false)
    //                         ->searchable(['first_name', 'last_name', 'hris_number'])
    //                         ->required()
    //                         ->columnSpanFull()
    //                         ->hidden(fn(Get $get) => match (Events::parse($get('tag'))) {
    //                             Events::HOL, Events::SUS, Events::FLAG => true,
    //                             default => false,
    //                         }),
    //                 ])
    //         ])
    //         ->statePath('data');
    // }
}
