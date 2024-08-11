<?php

namespace App\Livewire\AdminCoord;

use App\Enums\Role;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class DailyTimeReport extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    public function render()
    {
        return view('livewire.admin-coord.daily-time-report');
    }

    public function table(Table $table): Table
    {
        $departments = null;

        if (auth()->user()->role == Role::CENTERADMINCOORD) {
            $departments = Department::where('center', auth()->user()->employee->department->center)->get()->pluck('id')->toArray();
        } else {
            $departments = [auth()->user()->employee->department_id];
        }

        return $table
            ->query(
                Employee::query()
                    ->with(['time_entries' => fn ($query) => $query->whereDate('time_start', now()->format('Y-m-d'))])
                    ->with(['event' => fn ($query) => $query->whereDate('start', now()->format('Y-m-d'))])
                    ->where('employment_status', true)
                    ->whereIn('department_id', $departments)
            )
            // ->heading('Division/Office Daily Time Report ' . now()->format('F d, Y (D)'))
            // ->heading(function () {
            //     return new HtmlString('<div class="lg:flex lg:justify-between"><div>Division/Office Daily Time Report</div> <div>' . now()->format('F d, Y (D)') . '</div></div>');
            // })
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(query: function ($query, string $search): Builder {
                        return $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->sortable(['first_name', 'last_name']),
                \Filament\Tables\Columns\TextColumn::make('event.tag')
                    ->label('Status')
                    ->default('Report On-site')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('time_in')
                    ->label('Time In')
                    ->getStateUsing(function ($record) {
                        return ($record->time_entries->isNotEmpty()) ? $record->time_entries->last()->time_start->format('g:i A') : null;
                    }),
                \Filament\Tables\Columns\TextColumn::make('time_out')
                    ->label('Time Out')
                    ->getStateUsing(function ($record) {
                        if ($record->time_entries->isNotEmpty()) {
                            if ($record->time_entries->first()->time_start?->format('H:i') == $record->time_entries->first()->time_end?->format('H:i')) {
                                return null;
                            } else if ($record->time_entries->count() > 1) {
                                return ($record->time_entries->first()->time_end) ? $record->time_entries->first()->time_end->format('g:i A') : $record->time_entries->first()->time_start->format('g:i A');
                            } else {
                                return $record->time_entries->first()->time_end->format('g:i A');
                            }
                        }
                        return null;
                    }),
                // \Filament\Tables\Columns\TextColumn::make('time_end')
                //     ->label('Time Out')
                //     ->formatStateUsing(fn ($state) => $state->format('g:i A'))
                //     ->searchable()
                //     ->sortable(),
            ]);
    }
}
