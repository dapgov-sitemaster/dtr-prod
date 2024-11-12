<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use App\Models\TimeEntry;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

// class TimeEntries extends Component
class TimeEntries extends Component implements HasForms, HasTable
{
    // use WithPagination;
    use InteractsWithTable, InteractsWithForms;

    #[Title('| My Daily Time Entries')]
    public function render()
    {
        // $this->timeEntries();
        return view('livewire.employee.time-entries');
        // return view('livewire.employee.time-entries', [
        //     'time_entries' => TimeEntry::query()
        //                         ->where('hris_number', auth()->user()->hris_number)
        //                         ->when($this->yearmonth, function($query) {
        //                             $expl = explode('-', $this->yearmonth);
        //                             $query->whereYear('time_start', $expl[0])->whereMonth('time_start', $expl[1]);
        //                         })
        //                         // ->when($this->month, function($query) {
        //                         //     $query->whereYear('time_start', $this->year);
        //                         // })
        //                         ->latest('time_start')->paginate(6)
        // ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(TimeEntry::where('hris_number', auth()->user()->hris_number))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->getStateUsing(fn($record) => $record->time_start->format('F d, Y'))
                    ->searchable(['time_start'])
                    ->sortable(['time_start']),
                \Filament\Tables\Columns\TextColumn::make('time_start')
                    ->label('Time In')
                    ->formatStateUsing(fn($state) => $state->format('g:i A')),
                \Filament\Tables\Columns\TextColumn::make('time_end')
                    ->label('Time Out')
                    ->formatStateUsing(fn($state) => $state->format('g:i A')),
                \Filament\Tables\Columns\TextColumn::make('tag')
                    ->label('Type of Time Entry')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'ros' => 'Report On-site',
                        'wfh' => 'Work from Home',
                        'mvpool' => 'MVPOOL'
                    })
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
                                fn(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereIn('tag', $data['tag']),
                            );
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->emptyStateHeading('No Time Entries yet')
            ->defaultSort('time_start', 'desc');
    }
}
