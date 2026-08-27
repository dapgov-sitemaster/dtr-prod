<?php

namespace App\Livewire\HrAdmin\OfficialTimeChangeRequest;

use App\Enums\Role;
use Livewire\Component;
use App\Models\Department;
use Filament\Tables\Table;
use App\Models\OfficialTime;
use Livewire\Attributes\Title;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Index extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    #[Title('| Official Time Changes')]

    public function render()
    {
        return view('livewire.hr-admin.official-time-change-request.index');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(OfficialTime::with('employee'))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('employee.hris_number')
                    ->label('HRIS Number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
                \Filament\Tables\Columns\TextColumn::make('employee.department.description')
                    ->label('Department')
                    ->searchable(['group', 'center', 'office'])
                    ->sortable(['group', 'center', 'office']),
                \Filament\Tables\Columns\TextColumn::make('schedule_type')
                    ->label('Schedule Type')
                    ->badge()
                    ->placeholder('Not set')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('time_in')
                    ->label('Time In')
                    ->formatStateUsing(fn($state) => $state->format('g:i A') . ' - ' . $state->addHours(9)->format('g:i A'))
                    ->placeholder('Not set')
                    ->sortable(),
                // \Filament\Tables\Columns\TextColumn::make('created_at')
                //     ->label('Requested At')
                //     ->dateTime('M d, Y | g:i A')
                //     ->sortable(),
                // \Filament\Tables\Columns\TextColumn::make('status')
                //     ->badge()
                //     ->color(fn(string $state): string => match ($state) {
                //         'pending' => 'warning',
                //         'approved' => 'success',
                //         'disapproved' => 'danger',
                //     })
                //     ->sortable(),
            ])
            // ->bulkActions([
            //     \Filament\Tables\Actions\BulkAction::make('evaluate-bulk-request')
            //         ->label('Evaluate all selected Request')
            //         ->modalHeading('Evaluate all selected Request')
            //         // ->icon('heroicon-m-document-magnifying-glass')
            //         ->button()
            //         ->action(fn(OfficialTime $record) => $record->advance())
            //         // ->modalContent(fn (OfficialTime $record) => view(
            //         //     'livewire.hr-admin.official-time-change-request.evaluation',
            //         //     ['record' => $record],
            //         // ))
            //         ->modalWidth(\Filament\Support\Enums\MaxWidth::SevenExtraLarge)
            //         ->modalContent(function (Collection $records) {
            //             // dd($records->pluck('hris_number')->values()->toArray());
            //             $ids = $records->pluck('id')->values()->toJson();
            //             return new HtmlString(Blade::render('@livewire(\'hr-admin.official-time-change-request.evaluation\',["type" => "bulk", "data" => ["ids" => ' . $ids . ']])'));
            //         })
            //         ->modalSubmitAction(false)
            //     // ->hidden(function (Collection $records) {
            //     //     $ids = $records->filter(fn ($item) => $item->status == "pending")->pluck('id')->values();
            //     //     return $ids->count() == 0;
            //     // })
            // ])
            // ->checkIfRecordIsSelectableUsing(function (Model $record): bool {
            //     return $record->status == "pending";
            // })
            // ->selectCurrentPageOnly()
            ->actions([
                // \Filament\Tables\Actions\Action::make('evaluate-request')
                //     ->modalHeading('Evaluate Request')
                //     ->icon('heroicon-m-document-magnifying-glass')
                //     ->button()
                //     ->action(fn(OfficialTime $record) => $record->advance())
                //     // ->modalContent(fn (OfficialTime $record) => view(
                //     //     'livewire.hr-admin.official-time-change-request.evaluation',
                //     //     ['record' => $record],
                //     // ))
                //     ->modalWidth(\Filament\Support\Enums\MaxWidth::SevenExtraLarge)
                //     ->modalContent(function ($record) {
                //         return new HtmlString(Blade::render('@livewire(\'hr-admin.official-time-change-request.evaluation\',["type" => "individual", "data" => ["hris_number" => ' . $record->hris_number . ']])'));
                //     })
                //     ->hidden(fn($record) => $record->status !== 'pending')
                //     ->modalSubmitAction(false)
                \Filament\Tables\Actions\ViewAction::make()
                    ->infolist([
                        \Filament\Infolists\Components\Fieldset::make('Employee Information')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('hris_number')
                                    ->label('HRIS Number'),
                                \Filament\Infolists\Components\TextEntry::make('employee.full_name')
                                    ->label('Name'),
                                \Filament\Infolists\Components\TextEntry::make('employee.department.description')
                                    ->label('Department'),
                                \Filament\Infolists\Components\TextEntry::make('created_by.full_name')
                                    ->label('Craeted by'),
                            ]),
                        \Filament\Infolists\Components\Fieldset::make('Updated Official Time')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('schedule_type')
                                    ->badge()
                                    ->label('Schedule Type'),
                                \Filament\Infolists\Components\TextEntry::make('time_in')
                                    ->formatStateUsing(fn($state) => $state->format('g:i A') . ' - ' . $state->addHours(9)->format('g:i A'))
                                    ->placeholder('Not set')
                                    ->label('Time_in'),
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->formatStateUsing(fn($state) => $state->format('M d, Y'))
                                    ->label('Changed at'),
                                \Filament\Infolists\Components\TextEntry::make('mov.filename')
                                    ->formatStateUsing(fn() => "View uploaded file")
                                    ->iconColor("primary")
                                    ->icon("heroicon-m-arrow-down-tray")
                                    ->url(function ($record) {
                                        if (!$record->mov) {
                                            return null;
                                        }
                                        return route('hr-admin.pdf.view-mov', ['mov' => $record->mov]);
                                    })
                                    ->openUrlInNewTab()
                                    ->placeholder('No uploaded file')
                                    ->label('MOV Upload'),
                            ])
                    ])
            ])
            ->defaultSort('created_at', 'desc');
    }
}
