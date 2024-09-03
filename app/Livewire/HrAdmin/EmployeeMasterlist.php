<?php

namespace App\Livewire\HrAdmin;

use App\Enums\Role;
use App\Models\User;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class EmployeeMasterlist extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    #[Title('| Employee Masterlist')]
    public function render()
    {
        return view('livewire.hr-admin.employee-masterlist');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->withoutGlobalScopes()
                    ->departmentCovered()
                    ->with('department', 'official_time', 'user')
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('HRIS Number copied')
                    ->copyMessageDuration(1500),
                \Filament\Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(query: function ($query, string $search): Builder {
                        return $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->sortable(['first_name', 'last_name']),
                \Filament\Tables\Columns\TextColumn::make('department.description')
                    ->label('Department')
                    ->sortable(['group', 'center', 'office']),
                \Filament\Tables\Columns\TextColumn::make('appointment_status')
                    ->label('Appointment Status')
                    ->badge(),
                \Filament\Tables\Columns\IconColumn::make('employment_status')
                    ->label('Employment Status')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('user.role')
                    ->label('Role')
                    ->badge(),
            ])
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make('create-employee')
                    ->icon('heroicon-m-user-plus')
                    ->label('Add new Employee')
                    ->modalHeading('Add new Employee')
                    ->modalIcon('heroicon-o-user-plus')
                    ->createAnother(false)
                    ->slideOver()
                    // ->model(Post::class)
                    ->form([
                        \Filament\Forms\Components\Section::make()
                            ->columns([
                                'sm' => 1,
                                'xl' => 2,
                            ])
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('hris_number')
                                    ->label('HRIS Number')
                                    ->mask('999999')
                                    ->placeholder('Enter 6 digit HRIS number')
                                    ->autocomplete(false)
                                    ->length(6)
                                    ->validationAttribute('HRIS Number')
                                    ->unique(table: User::class)
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->placeholder('Enter DAP Email Address')
                                    ->email()
                                    ->validationAttribute('DAP Email Address')
                                    ->unique(table: User::class)
                                    ->autocomplete(false)
                                    ->required(fn(Get $get) => $get('appointment_status') != AppointmentStatus::JOBBER),
                                \Filament\Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->options(Department::all()->pluck('description', 'id'))
                                    ->native(false)
                                    ->searchable()
                                    ->required()
                                    ->columnSpanFull(),

                                \Filament\Forms\Components\Grid::make([
                                    'default' => 1,
                                    'xl'    => 3,
                                ])
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('last_name')
                                            ->label('Last Name')
                                            ->placeholder('Enter Last Name')
                                            ->autocomplete(false)
                                            ->required(),
                                        \Filament\Forms\Components\TextInput::make('first_name')
                                            ->label('First Name')
                                            ->placeholder('Enter First Name')
                                            ->autocomplete(false)
                                            ->required(),
                                        \Filament\Forms\Components\TextInput::make('middle_name')
                                            ->label('Middle Name')
                                            ->placeholder('Enter Middle Name')
                                            ->autocomplete(false),
                                    ]),
                                \Filament\Forms\Components\Select::make('appointment_status')
                                    ->options(AppointmentStatus::class)
                                    ->native(false)
                                    ->required(),
                                \Filament\Forms\Components\Select::make('role')
                                    ->options(function () {
                                        return collect(Role::cases())
                                            ->filter(fn($case) => $case !== Role::SUPERADMIN)
                                            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                                            ->toArray();
                                    })
                                    ->native(false)
                                    ->required(),
                            ]),
                    ])
                    ->action(function (array $data): Model {
                        $user = User::create([
                            'hris_number' => $data['hris_number'],
                            'email' => $data['email'],
                            'password' => Hash::make('dap12345'),
                            'role' => $data['role'],
                        ]);

                        $user->employee()->create([
                            'hris_number' => $data['hris_number'],
                            'last_name' => $data['last_name'],
                            'first_name' => $data['first_name'],
                            'middle_name' => $data['middle_name'],
                            'appointment_status' => $data['appointment_status'],
                            'department_id' => $data['department_id'],
                        ]);

                        $name = (str($user->employee->first_name)->endsWith('s')) ? $user->employee->first_name . "'" : $user->employee->first_name . "'s";

                        Notification::make()
                            ->title("Saved Successfully!")
                            ->body($name . " information has been saved!")
                            ->success()
                            ->color('success')
                            ->send();
                        return $user;
                    })
            ])
            ->actions([
                \Filament\Tables\Actions\ActionGroup::make([
                    \Filament\Tables\Actions\Action::make('employee-info')
                        ->color('success')
                        ->modalWidth('5xl')
                        ->icon('heroicon-m-identification')
                        ->modalIcon('heroicon-m-identification')
                        ->label('Employee Info')
                        ->modalHeading(fn($record) => 'Information of ' . $record->first_name)
                        ->infolist([
                            \Filament\Infolists\Components\Fieldset::make('Employee Info')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('hris_number')
                                        ->label('HRIS Number'),
                                    \Filament\Infolists\Components\TextEntry::make('full_name')
                                        ->label('Employee Name'),
                                    \Filament\Infolists\Components\TextEntry::make('user.email')
                                        ->label('Email'),
                                    \Filament\Infolists\Components\TextEntry::make('user.role')
                                        ->badge()
                                        ->label('Role'),
                                    \Filament\Infolists\Components\TextEntry::make('department.description')
                                        ->label('Department'),
                                    \Filament\Infolists\Components\TextEntry::make('appointment_status')
                                        ->badge()
                                        ->label('Appointment Status'),
                                ])
                                ->columns(3),
                            \Filament\Infolists\Components\Fieldset::make('Official Time')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('official_time.schedule_type')
                                        ->placeholder('Not Set')
                                        ->label('Schedule Type'),
                                    \Filament\Infolists\Components\TextEntry::make('official_time.time_in')
                                        ->placeholder('Not Available')
                                        ->label('Official Time In')
                                        ->formatStateUsing(function ($state) {
                                            return $state->format('g:i A') . ' - ' . $state->copy()->addHours(9)->format('g:i A');
                                        }),
                                ])
                                ->columns(2),
                            \Filament\Infolists\Components\ViewEntry::make('hris_number')
                                ->label('QR Code')
                                ->view('infolists.components.qr-code')
                                ->registerActions([
                                    \Filament\Infolists\Components\Actions\Action::make('download-qr-code')
                                        ->label('Download ID Card')
                                        ->icon('heroicon-m-arrow-down-tray')
                                        ->url(function ($record) {
                                            return route('pdf.empqrcode', ['employee' => $record]);
                                        })
                                        ->openUrlInNewTab(),
                                ])

                        ])
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false),
                    \Filament\Tables\Actions\Action::make('edit-emp-status')
                        ->color('primary')
                        ->modalWidth('lg')
                        ->label('Update Status')
                        ->modalHeading(fn($record) => 'Set Employment Status of ' . $record->apost_first_name . " information")
                        ->requiresConfirmation()
                        ->form([
                            \Filament\Forms\Components\ToggleButtons::make('role')
                                ->inline()
                                ->default(fn($record) => $record->employment_status)
                                ->options([1 => 'Active', 0 => 'Inactive'])
                                ->colors([0 => 'danger', 1 => 'success'])
                                ->icons([1 => 'heroicon-o-check-circle', 0 => 'heroicon-o-x-circle'])
                                ->required(),
                        ])
                        ->action(function ($record, $data) {
                            if ($record->employment_status == $data['role']) {
                                Notification::make()
                                    ->title("No changes has made!")
                                    ->warning()
                                    ->color('warning')
                                    ->send();
                            } else {
                                $record->employment_status = $data['role'];
                                $record->save();
                                $stats = [1 => 'Active', 0 => 'Inactive'];
                                Notification::make()
                                    ->title($record->full_name . " has been set as " . $stats[$data['role']] . "!")
                                    ->success()
                                    ->color('success')
                                    ->send();
                            }
                        }),
                    \Filament\Tables\Actions\EditAction::make('edit-employee')
                        ->color('secondary')
                        ->slideOver()
                        ->modalHeading(fn($record) => 'Set Employment Status of ' . $record->apost_first_name . " information")
                        ->modalIcon('heroicon-o-pencil-square')
                        // ->mutateFormDataUsing(function (array $data, $record): array {
                        //     dd($data);
                        //     $data['email'] = $record->first_name;

                        //     return $data;
                        // })
                        ->fillForm(function (Employee $employee): array {
                            return [
                                'email' => $employee->user->email,
                                'department_id' => $employee->department_id,
                                'last_name' => $employee->last_name,
                                'first_name' => $employee->first_name,
                                'middle_name' => $employee->middle_name,
                                'appointment_status' => $employee->appointment_status,
                                'role' => $employee->user->role,
                            ];
                        })
                        ->form([
                            \Filament\Forms\Components\Section::make()
                                ->columns([
                                    'sm' => 1,
                                    'xl' => 2,
                                ])
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('email')
                                        ->placeholder('Enter DAP Email Address')
                                        ->email()
                                        ->validationAttribute('DAP Email Address')
                                        ->autocomplete(false)
                                        ->required(),
                                    \Filament\Forms\Components\Select::make('department_id')
                                        ->label('Department')
                                        ->options(Department::all()->pluck('description', 'id'))
                                        ->native(false)
                                        ->searchable()
                                        ->required(),

                                    \Filament\Forms\Components\Grid::make([
                                        'default' => 1,
                                        'xl'    => 3,
                                    ])
                                        ->schema([
                                            \Filament\Forms\Components\TextInput::make('last_name')
                                                ->label('Last Name')
                                                ->placeholder('Enter Last Name')
                                                ->autocomplete(false)
                                                ->required(),
                                            \Filament\Forms\Components\TextInput::make('first_name')
                                                ->label('First Name')
                                                ->placeholder('Enter First Name')
                                                ->autocomplete(false)
                                                ->required(),
                                            \Filament\Forms\Components\TextInput::make('middle_name')
                                                ->label('Middle Name')
                                                ->placeholder('Enter Middle Name')
                                                ->autocomplete(false),
                                        ]),
                                    \Filament\Forms\Components\Select::make('appointment_status')
                                        ->options(AppointmentStatus::class)
                                        ->native(false)
                                        ->required(),
                                    \Filament\Forms\Components\Select::make('role')
                                        ->options(function () {
                                            return collect(Role::cases())
                                                ->filter(fn($case) => $case !== Role::SUPERADMIN)
                                                ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                                                ->toArray();
                                        })
                                        ->native(false)
                                        ->hidden(fn($record) => ($record->user->role->value === 'superadmin'))
                                        ->required(),
                                ]),
                        ])
                        ->action(function ($data, Employee $employee) {

                            $employee->department_id = $data['department_id'];
                            $employee->last_name = $data['last_name'];
                            $employee->first_name = $data['first_name'];
                            $employee->middle_name = $data['middle_name'];
                            $employee->appointment_status = $data['appointment_status'];
                            $employee->save();

                            $employee->user->email = $data['email'];
                            $employee->user->role = $data['role'];
                            $employee->user->save();

                            $name = (str($employee->first_name)->endsWith('s')) ? $employee->first_name . "'" : $employee->first_name . "'s";

                            Notification::make()
                                ->title("Saved Successfully!")
                                ->body($name . " information has been saved!")
                                ->success()
                                ->color('success')
                                ->send();
                        }),
                ])
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('department_id')
                    ->label('Select Department')
                    ->multiple()
                    ->preload()
                    ->options(Department::all()->pluck('description', 'id'))
                    ->searchable()
                    ->native(false),
                \Filament\Tables\Filters\Filter::make('appointment_status')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('appointment_status')
                            ->label('Select Appointment Status')
                            ->options(AppointmentStatus::class)
                            ->bulkToggleable()
                            ->default(['jobber', 'npp', 'pbp'])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['appointment_status'],
                                fn(Builder $query, $data): Builder => $query->whereIn('appointment_status', $data),
                            );
                    })
                    ->columnSpanFull(),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('created_at', 'desc');
    }

    // public function employeeAction()
    // {
    //     if() {

    //     }
    //     else {
    //         return [
    //             'create' => [

    //             ]
    //         ];
    //     }
    // }
}
