<?php

namespace App\Livewire\AdminCoord;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use Livewire\Attributes\Title;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class DailyTimeRecords extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    #[Title('| Daily Time Records')]
    public function render()
    {
        return view('livewire.admin-coord.daily-time-records');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->with(['official_time' => fn ($query) => $query->where('status', 'approved')])
                    ->where('department_id', auth()->user()->employee->department_id)
                    ->orderBy('last_name')
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                \Filament\Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('appointment_status')
                    ->label('Appointment Status')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                \Filament\Tables\Columns\TextColumn::make('official_time.time_in')
                    ->label('Official Time')
                    ->formatStateUsing(function ($state) {
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
                        ->url(fn ($record) => route('admin.dtr.emp-time-entries', ['hris_number' => $record->hris_number])),
                    \Filament\Tables\Actions\Action::make('generate-report')
                        ->color('secondary')
                        ->icon('heroicon-m-document-arrow-down')
                        ->labeledFrom('md')
                        ->url(fn ($record) => route('admin.dtr.emp-dtr-report', ['hris_number' => $record->hris_number, 'date_from' => '2024-07-01', 'date_to' => '2024-07-15'])),

                ])
                    ->dropdownPlacement('top-start')
            ]);
    }
}
