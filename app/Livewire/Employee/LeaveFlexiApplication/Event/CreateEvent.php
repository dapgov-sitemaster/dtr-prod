<?php

namespace App\Livewire\Employee\LeaveFlexiApplication\Event;

use Carbon\Carbon;
use App\Enums\Role;
use Filament\Forms;
use App\Models\User;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use Filament\Forms\Form;
use App\Enums\ScheduleType;
use Livewire\Attributes\On;
use App\Models\OfficialTime;
use App\Enums\OfficialLeaves;
use App\Mail\Event\Application;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Livewire\Employee\LeaveFlexiApplication\TableList;

class CreateEvent extends Component implements HasForms
{
    use InteractsWithForms;
    public ?array $data = [];

    #[Reactive]
    public $date;

    public function mount($date)
    {
        $this->date = $date;
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.employee.leave-flexi-application.event.create-event');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('tag')
                    ->label('Type of Event')
                    ->options(function () {
                        $options = [];
                        foreach (Events::cases() as $case) {
                            if ($case == Events::WFH || $case == Events::HWFH) {
                                if ($this->date->dayOfWeek == Carbon::FRIDAY) {
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
                        return (Events::parse($get('tag')) == Events::ALA) ? true : false;
                    })
                    ->required(),
            ])
            ->statePath('data');
    }

    #[On('event-create')]
    public function create()
    {
        $data = $this->form->getState();
        $date = $this->date;

        $official_time = OfficialTime::where('hris_number', auth()->user()->hris_number)->where('status', 'approved')->first();
        $time_start = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . '08:00:00');
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

        $event = Event::create([
            'hris_number' => auth()->user()->hris_number,
            'start' => $time_start->format('Y-m-d H:i:s'),
            'end' => $time_end->format('Y-m-d H:i:s'),
            'tag' => $data['tag'],
            'description' => $description,
            'created_by' => auth()->user()->hris_number,
        ]);

        $admin_coord = User::whereHas('employee', fn($query) => $query->where('department_id', auth()->user()->employee->department_id))->whereIn('role', [Role::ADMINCOORD, Role::CENTERADMINCOORD, Role::GROUPADMINCOORD])->get();
        Mail::to(auth()->user())->cc($admin_coord->pluck('email')->toArray())->send(new Application($event));

        Notification::make()
            ->title("Event Created!")
            ->body('You have successfully requested a ' . Events::tryFrom($data['tag'])->getLabel() . '. Kindly wait for the Admin Coordinator to approve your request.')
            ->success()
            ->color('success')
            ->send();

        $this->dispatch('refresh-calendar')->to(Calendar::class);
        $this->dispatch('refresh-table')->to(TableList::class);
        $this->dispatch('close-modal', id: 'create-event');
    }
}
