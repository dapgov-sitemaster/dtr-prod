<?php

namespace App\Livewire\AdminCoord;

use Carbon\Carbon;
use App\Enums\Role;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Filament\Tables\Table;
use Livewire\Attributes\Title;
use App\Enums\AppointmentStatus;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class DailyTimeRecords extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    public $departments;

    #[Title('| Daily Time Records')]
    public function mount()
    {
        $user_dept = auth()->user()->employee->department;
        $this->departments = match (auth()->user()->role) {
            Role::ADMINCOORD => [$user_dept->id],
            Role::CENTERADMINCOORD => Department::select('id')->where('center', $user_dept->center)->get()->pluck('id')->toArray(),
            Role::GROUPADMINCOORD => Department::select('id')->where('group', $user_dept->group)->get()->pluck('id')->toArray(),
            default => [],
        };
    }

    public function render()
    {
        return view('livewire.admin-coord.daily-time-records');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->with(['official_time' => fn($query) => $query->where('status', 'approved')])
                    ->where('employment_status', true)
                    ->departmentCovered()
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
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
                    ->sortable(['group', 'center', 'office'])
                    ->visible(fn() => auth()->user()->hasRole(Role::CENTERADMINCOORD) || auth()->user()->hasRole(Role::GROUPADMINCOORD)),
                \Filament\Tables\Columns\TextColumn::make('appointment_status')
                    ->label('Appointment Status')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                \Filament\Tables\Columns\TextColumn::make('official_time.schedule_type')
                    ->label('Schedule Type')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('official_time.time_in')
                    ->label('Official Time')
                    ->formatStateUsing(function ($state) {
                        // dd($record->official_time->time_in);
                        return Carbon::parse($state)->format('g:i A') . ' - ' . Carbon::parse($state)->copy()->addHours(9)->format('g:i A');
                    })
                    ->placeholder('Not set')
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->actions([
                \Filament\Tables\Actions\ActionGroup::make([
                    \Filament\Tables\Actions\Action::make('time-entries')
                        ->color('info')
                        ->icon('heroicon-m-eye')
                        ->labeledFrom('md')
                        ->url(fn($record) => route('admin.dtr.emp-time-entries', ['hris_number' => $record->hris_number])),
                    \Filament\Tables\Actions\Action::make('generate-report')
                        ->modalHeading(fn($record) => 'Set Employment Status of ' . $record->apost_first_name . " information")
                        ->color('secondary')
                        ->icon('heroicon-m-document-arrow-down')
                        ->labeledFrom('md')
                        ->form([
                            \Filament\Forms\Components\Grid::make(2)
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('yearmonth')
                                        ->label('Select Year and Month')
                                        ->type('month')
                                        ->default(now()->format('Y-m'))
                                        ->required(),
                                    \Filament\Forms\Components\Select::make('cutoff')
                                        ->label('Select Cut-off')
                                        ->options([1 => "First Cut-off", 2 => "Second Cut-off"])
                                        ->native(false)
                                        ->required(),
                                ])
                        ])
                        ->action(function ($data, $record) {
                            $this->generate('individual', ['hris_number' => $record->hris_number, 'yearmonth' => $data['yearmonth'], 'cutoff' => $data['cutoff']]);
                            // $livewire->redirectRoute('admin.dtr.emp-dtr-report', ['hris_number' => $record->hris_number, 'date_from' => '2024-07-01', 'date_to' => '2024-07-15']);
                            // return redirect()->route('admin.dtr.emp-dtr-report', ['hris_number' => $record->hris_number, 'yearmonth' => $data['yearmonth'], 'cutoff' => $data['cutoff']]);
                        })
                    // ->url(function ($data) {
                    //     dd($data);
                    // })
                    // ->url(fn ($record) => route('admin.dtr.emp-dtr-report', ['hris_number' => $record->hris_number, 'date_from' => '2024-07-01', 'date_to' => '2024-07-15']))
                    // ->openUrlInNewTab(),

                ])
                    ->dropdownPlacement('top-start')
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('generate-bulk-report')
                    ->modalHeading('Generate division/office DTR Report')
                    ->label('Generate DTR Report')
                    ->icon('heroicon-m-document-arrow-down')
                    ->labeledFrom('md')
                    ->form([
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\Select::make('department_id')
                                    ->label('Select Office/Division')
                                    ->options(Department::whereIn('id', $this->departments)->get()->pluck('description', 'id'))
                                    ->native(false)
                                    ->visible(fn() => auth()->user()->hasRole(Role::CENTERADMINCOORD) || auth()->user()->hasRole(Role::GROUPADMINCOORD))
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('yearmonth')
                                    ->label('Select Year and Month')
                                    ->type('month')
                                    ->default(now()->format('Y-m'))
                                    ->required(),
                                \Filament\Forms\Components\Select::make('cutoff')
                                    ->label('Select Cut-off')
                                    ->options([1 => "First Cut-off", 2 => "Second Cut-off"])
                                    ->native(false)
                                    ->required(),
                                \Filament\Forms\Components\Select::make('appointment_status')
                                    ->label('Select Appointment Status')
                                    ->options(AppointmentStatus::class)
                                    ->native(false)
                                    ->required(),
                            ])
                    ])
                    ->action(function ($data) {
                        if (auth()->user()->hasRole(Role::ADMINCOORD)) {
                            $department = auth()->user()->employee->department_id;
                        } else {
                            $department = $data['department_id'];
                        }
                        $this->generate('office', ['office_id' => $department, 'yearmonth' => $data['yearmonth'], 'cutoff' => $data['cutoff'], 'appointment_status' => $data['appointment_status']]);
                        // $livewire->redirectRoute('admin.dtr.emp-dtr-report', ['hris_number' => $record->hris_number, 'date_from' => '2024-07-01', 'date_to' => '2024-07-15']);
                        // return redirect()->route('admin.dtr.bulk-dtr-report', ['department' => auth()->user()->employee->department_id, 'yearmonth' => $data['yearmonth'], 'cutoff' => $data['cutoff'], 'appointment_status' => $data['appointment_status']]);
                    })
            ])
            ->defaultSort('last_name');
    }

    public function generate($type, $data)
    {
        if ($type == 'individual') {
            // $data = $this->individualForm->getState();
            $this->dispatch('redirectToDtrReport', dtrtype: 'employee', hris_number: $data['hris_number'], yearmonth: $data['yearmonth'], cutoff: $data['cutoff']);
        } else if ($type == 'office') {
            // $data = $this->bulkForm->getState();
            $this->dispatch('redirectToDtrReport', dtrtype: 'bulk', office_id: $data['office_id'], yearmonth: $data['yearmonth'], cutoff: $data['cutoff'], appointment_status: $data['appointment_status']);
            // return redirect()->route('admin.dtr.bulk-dtr-report', ['department' => $data['office_id'], 'yearmonth' => $data['yearmonth'], 'cutoff' => $data['cutoff'], 'appointment_status' => $data['appointment_status']]);
        }
    }
}
