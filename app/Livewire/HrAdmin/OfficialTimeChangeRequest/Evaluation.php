<?php

namespace App\Livewire\HrAdmin\OfficialTimeChangeRequest;

use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use App\Models\OfficialTime;
use Filament\Infolists\Infolist;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Infolists\Concerns\InteractsWithInfolists;

class Evaluation extends Component implements HasForms, HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithForms, InteractsWithInfolists;

    public $official_time_id;
    public $employee;
    public $hris_number;

    public function mount($id, $hris_number)
    {
        $this->official_time_id = $id;
        $this->employee = $hris_number;
    }
    public function render()
    {
        $this->employee = Employee::where('hris_number', $this->hris_number)->first();
        return view('livewire.hr-admin.official-time-change-request.evaluation');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(OfficialTime::where('hris_number', $this->hris_number))
            ->columns([
                \Filament\Tables\Columns\Layout\Split::make([
                    \Filament\Tables\Columns\TextColumn::make('time_in')
                        ->label('Time In & Time Out')
                        ->description('Time In & Time Out', position: 'above')
                        ->formatStateUsing(fn ($state) => $state->format('g:i A') . ' - ' . $state->addHours(9)->format('g:i A'))
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('status')
                        ->description('Status', position: 'above')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'disapproved' => 'danger',
                        }),
                    \Filament\Tables\Columns\TextColumn::make('created_at')
                        ->description('Requested At', position: 'above')
                        ->label('Requested At')
                        ->dateTime('M d, Y | g:i A')
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('duration')
                        ->description('Duration from last change', position: 'above')
                        ->getStateUsing(fn ($record) => $record->created_at)
                        ->label('Duration from last change')
                        ->since()
                        ->sortable(),
                ])
                    ->from('lg')
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('approve-request')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->button()
                    ->color('primary')
                    ->action(function ($record) {
                        $record->status = 'approved';
                        $record->save();

                        Notification::make()
                            ->title('Saved successfully')
                            ->body('Request has been approved successfully.')
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->hidden(fn ($record) => $record->status !== 'pending'),
                \Filament\Tables\Actions\Action::make('disapprove-request')
                    ->label('Disapprove')
                    ->icon('heroicon-m-x-circle')
                    ->button()
                    ->color('danger')
                    ->action(function ($record) {
                        $record->status = 'disapproved';
                        $record->save();

                        Notification::make()
                            ->title('Saved successfully')
                            ->body('Request has been disapproved successfully.')
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->hidden(fn ($record) => $record->status !== 'pending'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function employeeInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state([
                'hris_number' => $this->employee->hris_number,
                'full_name' => $this->employee->full_name,
                'department' => $this->employee->department->description,
                'appointment_status' => $this->employee->appointment_status->getLabel(),
            ])
            ->schema([
                \Filament\Infolists\Components\Fieldset::make('Employee Info')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('hris_number')
                            ->label('HRIS Number'),
                        \Filament\Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name'),
                        \Filament\Infolists\Components\TextEntry::make('department')
                            ->label('Group/Center/Office'),
                        \Filament\Infolists\Components\TextEntry::make('appointment_status')
                            ->label('Appointment Status'),
                    ])
                    ->columns(4)
            ]);
    }
}
