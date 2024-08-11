<?php

namespace App\Livewire\HrAdmin;

use App\Enums\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use App\Enums\AppointmentStatus;
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
            ->query(Employee::latest())
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable(),
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
                    // ->formatStateUsing(fn ($record) => $record->department->description)
                    ->searchable(['group', 'center', 'office'])
                    ->sortable(['group', 'center', 'office']),
                \Filament\Tables\Columns\TextColumn::make('official_time.time_in')
                    ->label('Official Time')
                    ->dateTime('g:i A'),
                \Filament\Tables\Columns\TextColumn::make('appointment_status')
                    ->label('Appointment Status')
                    ->badge(),
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
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->placeholder('Enter DAP Email Address')
                                    ->email()
                                    ->autocomplete(false)
                                    ->required(),
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
                                            ->filter(fn ($case) => $case !== Role::SUPERADMIN)
                                            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                            ->toArray();
                                    })
                                    ->native(false)
                                    ->required(),
                            ]),
                        // ...
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
                            'role' => $data['role'],
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
                // \Filament\Tables\Actions\ActionGroup::make([
                \Filament\Tables\Actions\EditAction::make('edit-employee')
                    ->slideOver()
                    ->modalHeading(function ($record) {
                        $name = (str($record->first_name)->endsWith('s')) ? $record->first_name . "'" : $record->first_name . "'s";
                        return 'Edit ' . str($name)->headline() . " information";
                    })
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
                                            ->filter(fn ($case) => $case !== Role::SUPERADMIN)
                                            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                            ->toArray();
                                    })
                                    ->native(false)
                                    ->hidden(fn ($record) => ($record->user->role->value === 'superadmin'))
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
                // ])
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('department_id')
                    ->label('Select Department')
                    ->multiple()
                    ->preload()
                    ->options(Department::all()->pluck('description', 'id'))
                    ->searchable()
                    ->native(false),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent);
    }
}
