<?php

namespace App\Livewire\Employee\LeaveFlexiApplication;

use Carbon\Carbon;
use App\Enums\Role;
use App\Models\User;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use App\Enums\ScheduleType;
use Livewire\Attributes\On;
use App\Models\OfficialTime;
use App\Enums\OfficialLeaves;
use App\Mail\Event\Application;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Livewire\Employee\LeaveFlexiApplication\Event\Calendar;

class TableList extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;
    #[Reactive]
    public $year, $month;

    public function mount($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    #[On('refresh-table')]
    public function render()
    {
        return view('livewire.employee.leave-flexi-application.table-list');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('hris_number', auth()->user()->hris_number)
                    ->whereYear('start', $this->year)->whereMonth('start', $this->month)
                    ->orWhereIn('tag', [Events::HOL, Events::SUS, Events::FLAG])
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->getStateUsing(fn($record) => $record->start->format('M d, Y'))
                    ->searchable(['start'])
                    ->sortable(['start']),
                \Filament\Tables\Columns\TextColumn::make('tag')
                    ->label('Tag')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->formatStateUsing(function ($state) {
                        if (OfficialLeaves::tryFrom($state)) {
                            return OfficialLeaves::parse($state)->getLabel();
                        } else {
                            return $state;
                        }
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'disapproved' => 'danger',
                    }),
            ])
            ->headerActions([
                CreateAction::make('create-event')
                    ->icon('heroicon-m-document-plus')
                    ->label('Request Schedule')
                    ->modalHeading('Request Schedule')
                    ->modalIcon('heroicon-o-document-plus')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date')
                            ->label('Select Date')
                            ->displayFormat('d F Y')
                            ->native(false)
                            ->seconds(false)
                            ->weekStartsOnSunday()
                            ->closeOnDateSelection()
                            ->minDate(now()->addDays(3)->format('Y-m-d 00:00:00'))
                            ->required()
                            ->afterStateUpdated(function (Set $set) {
                                $set('tag', null);
                            })
                            ->live(),
                        \Filament\Forms\Components\Grid::make()
                            ->schema([
                                \Filament\Forms\Components\Select::make('tag')
                                    ->label('Type of Event')
                                    ->options(function (Get $get) {
                                        $options = [];
                                        foreach (Events::cases() as $case) {
                                            if ($case == Events::WFH || $case == Events::HWFH) {
                                                if (Carbon::parse($get('date'))->dayOfWeek == Carbon::FRIDAY) {
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
                            ])
                            ->visible(fn(Get $get) => $get('date') != null)
                    ])
                    ->before(function ($data, CreateAction $action) {
                        $event = Event::where('hris_number', auth()->user()->hris_number)->whereDate('start', Carbon::parse($data['date'])->format('Y-m-d'))->first();
                        if ($event) {
                            Notification::make()
                                ->warning()
                                ->color('warning')
                                ->title('Create Event has been cancelled!')
                                ->body('You have already applied for ' . $event->tag->getLabel() . ' event on ' . $event->start->format('F d, Y') . '. If you want to change your request, you can just update your current request.')
                                ->send();
                            $action->halt();
                        }
                    })
                    ->using(function ($data, string $model): Model {
                        $data['hris_number'] = auth()->user()->hris_number;
                        $data['date'] = Carbon::parse($data['date']);
                        $data['created_by'] = auth()->user()->hris_number;
                        $official_time = OfficialTime::where('hris_number', $data['hris_number'])->where('status', 'approved')->first();
                        $data['start'] = \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                        $data['end'] = $data['start']->copy()->addHours(9);

                        if ($official_time) {
                            $data['start'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                            $data['end'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '17:00:00');
                        }

                        if (Events::parse($data['tag']) == Events::ALA) {
                            $data['description'] = $data['description_leave'];
                        } else {
                            $data['description'] = Events::tryFrom($data['tag'])->getLabel();
                        }

                        return $model::create($data);
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->title('Event Created!')
                            ->body('You have successfully requested a Schedule. Kindly wait for the Admin Coordinator to approve your request.'),
                    )
                    ->after(function ($record) {
                        $admin_coord = User::whereHas('employee', fn($query) => $query->where('department_id', auth()->user()->employee->department_id))->whereIn('role', [Role::ADMINCOORD, Role::CENTERADMINCOORD, Role::GROUPADMINCOORD])->get();
                        Mail::to(auth()->user())->cc($admin_coord->pluck('email')->toArray())->send(new Application($record));
                        $this->dispatch('refresh-calendar')->to(Calendar::class);
                    })
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function ($data, $record) {
                        $data['date'] = $record->start->format('Y-m-d');
                        $data['tag'] = $record->tag;
                        $data['description_leave'] = ($record->tag == Events::ALA) ? $record->description : null;

                        return $data;
                    })
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date')
                            ->label('Select Date')
                            ->displayFormat('d F Y')
                            ->native(false)
                            ->seconds(false)
                            ->weekStartsOnSunday()
                            ->closeOnDateSelection()
                            ->minDate(now()->addDays(3)->format('Y-m-d 00:00:00'))
                            ->required()
                            ->afterStateUpdated(function (Set $set) {
                                $set('tag', null);
                            })
                            ->live(),
                        \Filament\Forms\Components\Grid::make()
                            ->schema([
                                \Filament\Forms\Components\Select::make('tag')
                                    ->label('Type of Event')
                                    ->options(function (Get $get) {
                                        $options = [];
                                        foreach (Events::cases() as $case) {
                                            if ($case == Events::WFH || $case == Events::HWFH) {
                                                if (Carbon::parse($get('date'))->dayOfWeek == Carbon::FRIDAY) {
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
                            ])
                            ->visible(fn(Get $get) => $get('date') != null)
                    ])
                    ->using(function (Model $record, $data): Model {
                        $data['date'] = Carbon::parse($data['date']);
                        $official_time = OfficialTime::where('hris_number', $record->hris_number)->where('status', 'approved')->first();
                        $data['start'] = \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                        $data['end'] = $data['start']->copy()->addHours(9);

                        if ($official_time) {
                            $data['start'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '08:00:00');
                            $data['end'] = ($official_time->schedule_type == ScheduleType::FIXED) ? \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . $official_time->time_in->copy()->addHours(9)->format('H:i:s')) : \Carbon\Carbon::parse($data['date']->format('Y-m-d') . ' ' . '17:00:00');
                        }

                        if (Events::parse($data['tag']) == Events::ALA) {
                            $data['description'] = $data['description_leave'];
                        } else {
                            $data['description'] = Events::tryFrom($data['tag'])->getLabel();
                        }


                        $record->start = $data['start'];
                        $record->end = $data['end'];
                        $record->tag = $data['tag'];
                        $record->description = $data['description'];
                        $record->save();
                        return $record;
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->title('Event updated')
                            ->body('You have successfully updated your request.'),
                    )
                    ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
                    ->visible(fn($record) => $record->status === "pending"),
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
                    ->visible(fn($record) => $record->status === "pending")
            ])
            ->defaultSort('start', 'desc');
    }
}
