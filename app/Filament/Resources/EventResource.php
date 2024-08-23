<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Set;
use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EventResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\OfficialTime;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

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
                Forms\Components\DateTimePicker::make('start')
                    ->seconds(false)
                    ->required(),
                Forms\Components\DateTimePicker::make('end')
                    ->seconds(false),
                Forms\Components\Select::make('tag')
                    ->native(false)
                    ->options(Events::class)
                    ->live()
                    ->afterStateUpdated(fn(Set $set, $state) => $set('description', Events::parse($state)->getLabel()))
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('created_by')
                    ->label('Created by')
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
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->searchable(['last_name', 'first_name']),
                Tables\Columns\TextColumn::make('start')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tag')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\MovRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
