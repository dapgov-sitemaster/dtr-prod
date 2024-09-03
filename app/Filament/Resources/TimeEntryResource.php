<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Models\TimeEntry;
use App\Models\Department;
use Filament\Tables\Table;
use App\Enums\ScheduleType;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TimeEntryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TimeEntryResource\RelationManagers;

class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

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
                Forms\Components\DateTimePicker::make('time_start')
                    ->seconds(false)
                    ->required(),
                Forms\Components\DateTimePicker::make('time_end')
                    ->seconds(false),
                Forms\Components\Select::make('department_id')
                    ->native(false)
                    ->options(Department::all()->pluck('description', 'id'))
                    ->required(),
                Forms\Components\ToggleButtons::make('schedule_type')
                    ->options(ScheduleType::class)
                    ->inline()
                    ->required(),
                Forms\Components\TimePicker::make('official_time')
                    ->seconds(false),
                Forms\Components\Select::make('timekeeper_id')
                    ->native(false)
                    ->searchable(['last_name', 'first_name', 'hris_number'])
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->full_name}")
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\ToggleButtons::make('tag')
                    ->options(['wfh' => 'Work from Home', 'ros' => 'Report on-site', 'mvpool' => 'MVPOOL'])
                    ->inline()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->searchable(['last_name', 'first_name']),
                Tables\Columns\TextColumn::make('time_start')
                    ->dateTime('Y-m-d g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_end')
                    ->dateTime('Y-m-d g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.description')
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule_type'),
                Tables\Columns\TextColumn::make('official_time')
                    ->dateTime('g:i A'),
                Tables\Columns\TextColumn::make('tag')
                    ->badge(),
                Tables\Columns\TextColumn::make('timekeeper_id')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListTimeEntries::route('/'),
            'create' => Pages\CreateTimeEntry::route('/create'),
            'edit' => Pages\EditTimeEntry::route('/{record}/edit'),
        ];
    }
}
