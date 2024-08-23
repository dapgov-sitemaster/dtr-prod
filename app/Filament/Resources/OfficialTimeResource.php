<?php

namespace App\Filament\Resources;

use App\Enums\ScheduleType;
use Filament\Forms;
use Filament\Tables;
use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\OfficialTime;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OfficialTimeResource\Pages;
use App\Filament\Resources\OfficialTimeResource\RelationManagers;

class OfficialTimeResource extends Resource
{
    protected static ?string $model = OfficialTime::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hris_number')
                    ->native(false)
                    ->searchable(['last_name', 'first_name', 'hris_number'])
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->full_name}")
                    ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->departmentCovered()->limit(50)->get()->pluck('full_name', 'hris_number')->toArray())
                    ->required(),
                Forms\Components\ToggleButtons::make('schedule_type')
                    ->options(ScheduleType::class)
                    ->inline()
                    ->required(),
                Forms\Components\TimePicker::make('time_in')
                    ->seconds(false),
                Forms\Components\DatePicker::make('effectivity_date'),
                Forms\Components\ToggleButtons::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'disapproved' => 'Disapproved'])
                    ->inline()
                    ->required(),
                Forms\Components\Select::make('created_by')
                    ->native(false)
                    ->searchable(['last_name', 'first_name', 'hris_number'])
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->full_name}")
                    ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->departmentCovered()->limit(50)->get()->pluck('full_name', 'hris_number')->toArray())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hris_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->searchable(['last_name', 'first_name']),
                Tables\Columns\TextColumn::make('time_in')
                    ->dateTime('g:i A')
                    ->placeholder('Null'),
                Tables\Columns\TextColumn::make('schedule_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('effectivity_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficialTimes::route('/'),
            'create' => Pages\CreateOfficialTime::route('/create'),
            'edit' => Pages\EditOfficialTime::route('/{record}/edit'),
        ];
    }
}
