<?php

namespace App\Livewire\AdminCoord;

use App\Models\Employee;
use App\Models\TimeEntry;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

class EmployeeTimeEntries extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public $employee;

    #[Title('| Employee Time Entries')]
    public function mount($hris_number)
    {
        $this->employee = Employee::with('department')->where('hris_number', $hris_number)->firstOrFail();
        Gate::authorize('view', $this->employee);
    }

    public function render()
    {
        return view('livewire.admin-coord.employee-time-entries');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TimeEntry::where('hris_number', $this->employee->hris_number)->latest()
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->getStateUsing(fn ($record) => $record->time_start->format('Y-m-d'))
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('time_start')
                    ->label('Time In')
                    ->formatStateUsing(fn ($state) => $state->format('g:i A'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('time_end')
                    ->label('Time Out')
                    ->formatStateUsing(fn ($state) => $state->format('g:i A'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('tag')
                    ->label('Type of Time Entry')
                    ->formatStateUsing(fn ($state) => ($state === 'ros') ? 'Report On-site' : 'Work from Home')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('yearmonth')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('yearmonth')
                            ->label('Select Year and Month')
                            ->type('month')
                            ->default(now()->format('Y-m')),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['yearmonth'],
                                function (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder {
                                    $expl = explode('-', $date);

                                    return $query->whereYear('time_start', $expl[0])->whereMonth('time_start', $expl[1]);
                                },
                            );
                    }),
                \Filament\Tables\Filters\Filter::make('tag')
                    ->columnSpanFull()
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('tag')
                            ->label('Type of Time Entry')
                            ->columns(2)
                            ->options([
                                'ros' => 'Report On-site',
                                'wfh' => 'Work from Home',
                            ]),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['tag'],
                                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereIn('tag', $data['tag']),
                            );
                    }),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->emptyStateHeading('No Time Entries yet');
    }
}
