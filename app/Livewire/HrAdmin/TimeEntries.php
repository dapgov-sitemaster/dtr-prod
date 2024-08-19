<?php

namespace App\Livewire\HrAdmin;

use Livewire\Component;
use App\Models\TimeEntry;
use Filament\Tables\Table;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Components\Tab;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TimeEntries extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    #[Title('| Time Entries')]
    public function render()
    {
        return view('livewire.hr-admin.time-entries');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TimeEntry::query()
                    ->with('employee')
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('HRIS Number copied')
                    ->copyMessageDuration(1500),
                \Filament\Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
                \Filament\Tables\Columns\TextColumn::make('department.description')
                    ->label('Department')
                    ->searchable(['department.group', 'department.center', 'department.office'])
                    ->sortable(['department.group', 'department.center', 'department.office']),
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->getStateUsing(fn ($record) => $record->time_start->format('M d, Y'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('time_start')
                    ->label('Time In')
                    ->formatStateUsing(fn ($state) => $state->format('g:i A')),
                \Filament\Tables\Columns\TextColumn::make('time_end')
                    ->label('Time Out')
                    ->formatStateUsing(fn ($state) => $state->format('g:i A')),
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
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->emptyStateHeading('No Time Entries yet');
    }
}
