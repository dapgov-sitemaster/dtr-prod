<?php

namespace App\Livewire\HrAdmin\OfficialTimeChangeRequest;

use Livewire\Component;
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

    #[Title('| Official Time Change Requests')]
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
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime('M d, Y | g:i A')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'disapproved' => 'danger',
                    })
                    ->sortable(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('evaluate-bulk-request')
                    ->label('Evaluate all selected Request')
                    ->modalHeading('Evaluate all selected Request')
                    // ->icon('heroicon-m-document-magnifying-glass')
                    ->button()
                    ->action(fn (OfficialTime $record) => $record->advance())
                    // ->modalContent(fn (OfficialTime $record) => view(
                    //     'livewire.hr-admin.official-time-change-request.evaluation',
                    //     ['record' => $record],
                    // ))
                    ->modalWidth(\Filament\Support\Enums\MaxWidth::SevenExtraLarge)
                    ->modalContent(function (Collection $records) {
                        // dd($records->pluck('hris_number')->values()->toArray());
                        $ids = $records->pluck('id')->values()->toJson();
                        return new HtmlString(Blade::render('@livewire(\'hr-admin.official-time-change-request.evaluation\',["type" => "bulk", "data" => ["ids" => ' . $ids . ']])'));
                    })
                    ->modalSubmitAction(false)
                // ->hidden(function (Collection $records) {
                //     $ids = $records->filter(fn ($item) => $item->status == "pending")->pluck('id')->values();
                //     return $ids->count() == 0;
                // })
            ])
            ->checkIfRecordIsSelectableUsing(function (Model $record): bool {
                return $record->status == "pending";
            })
            ->selectCurrentPageOnly()
            ->actions([
                \Filament\Tables\Actions\Action::make('evaluate-request')
                    ->modalHeading('Evaluate Request')
                    ->icon('heroicon-m-document-magnifying-glass')
                    ->button()
                    ->action(fn (OfficialTime $record) => $record->advance())
                    // ->modalContent(fn (OfficialTime $record) => view(
                    //     'livewire.hr-admin.official-time-change-request.evaluation',
                    //     ['record' => $record],
                    // ))
                    ->modalWidth(\Filament\Support\Enums\MaxWidth::SevenExtraLarge)
                    ->modalContent(function ($record) {
                        return new HtmlString(Blade::render('@livewire(\'hr-admin.official-time-change-request.evaluation\',["type" => "individual", "data" => ["hris_number" => ' . $record->hris_number . ']])'));
                    })
                    ->hidden(fn ($record) => $record->status !== 'pending')
                    ->modalSubmitAction(false)
            ])
            ->defaultSort('created_at', 'desc');
    }
}
