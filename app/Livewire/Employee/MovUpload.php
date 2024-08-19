<?php

namespace App\Livewire\Employee;

use App\Models\Event;
use Livewire\Component;
use Filament\Tables\Table;
use App\Enums\OfficialLeaves;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class MovUpload extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    public function render()
    {
        return view('livewire.employee.mov-upload');
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(Event::with('mov')->where('hris_number', auth()->user()->hris_number)->whereNotIn('tag', [\App\Enums\Events::WFH, \App\Enums\Events::HWFH]))
            ->columns([
                // \Filament\Tables\Columns\TextColumn::make('date')
                //     ->label('Date')
                //     ->getStateUsing(fn ($record) => $record->start->format('Y-m-d'))
                //     ->searchable(['start'])
                //     ->sortable(['start']),
                \Filament\Tables\Columns\TextColumn::make('start')
                    ->label('Date Start')
                    ->formatStateUsing(fn($state) => $state->format('Y-m-d g:i A'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('end')
                    ->label('Date End')
                    ->formatStateUsing(fn($state) => $state->format('Y-m-d g:i A'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('tag')
                    ->label('Event')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->formatStateUsing(function ($state) {
                        $leave_type = OfficialLeaves::tryFrom($state);
                        return ($leave_type) ? $leave_type->getLabel() : $state;
                    })
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('mov.filename')
                    ->label('MOV')
                    ->badge()
                    ->formatStateUsing(fn($state) => ($state) ? 'Uploaded Already' : '')
                    ->placeholder('Not yet uploaded')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('upload-mov')
                    ->label('Upload MOV')
                    ->button()
                    ->modalHeading(function ($record) {
                        return 'Upload MOV of your ' . $record->tag->getLabel();
                    })
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('attachment')
                            ->label('Upload MOV')
                            ->acceptedFileTypes(['application/pdf', 'application/msword'])
                            ->directory('event-movs')
                            ->visibility('private')
                            ->required(),
                    ])
                    ->action(function ($data, \App\Actions\Azure $azure, $record) {
                        // if ($record->mov) {
                        //     $azure->delete($record->mov);
                        // }

                        $file = Storage::disk('public')->get($data['attachment']);
                        $file_explode = explode('/', $data['attachment']);
                        $filename = $file_explode[1];
                        // $azure->put("movs", $file, $filename);
                        Storage::disk('public')->delete($data['attachment']);

                        $record->mov()->create(['filename' => 'movs/' . $filename]);
                        // $record->mov = 'movs/' . $filename;
                        // $record->save();

                        Notification::make()
                            ->title("Saved Successfully!")
                            ->body("MOV uploaded successfully!")
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->hidden(fn($record): bool => ($record->mov) ? true : false),
            ])
            ->defaultSort('start', 'desc')
            ->emptyStateHeading('No MOV Pending this week');
    }
}
