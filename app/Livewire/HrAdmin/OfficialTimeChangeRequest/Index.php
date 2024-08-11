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
                        return new HtmlString(Blade::render('@livewire(\'hr-admin.official-time-change-request.evaluation\',["id" => ' . $record->id . ', "hris_number" => ' . $record->hris_number . '])'));
                    })
                    ->hidden(fn ($record) => $record->status !== 'pending')
            ])
            ->defaultSort('created_at', 'desc');
    }
}
