<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\AppointmentStatus;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeeRelationManager extends RelationManager
{
    protected static string $relationship = 'employee';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('hris_number')->required()->columnSpanFull(),
                Forms\Components\TextInput::make('last_name')->required(),
                Forms\Components\TextInput::make('first_name')->required(),
                Forms\Components\TextInput::make('middle_name'),
                Forms\Components\Select::make('department_id')->options(Department::all()->pluck('description', 'id'))->native(false)->searchable()->label('Department')->required()->columnSpanFull(),
                Forms\Components\ToggleButtons::make('appointment_status')->options(AppointmentStatus::class)->inline()->required(),
                Forms\Components\ToggleButtons::make('employment_status')->boolean()->inline()->required(),
                Forms\Components\TextInput::make('signature_path'),
                Forms\Components\TextInput::make('identity_photo_path'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('hris_number')
            ->columns([
                Tables\Columns\TextColumn::make('hris_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(['last_name', 'first_name']),
                Tables\Columns\TextColumn::make('department.description')
                    ->searchable(['group', 'center', 'office']),
                Tables\Columns\TextColumn::make('appointment_status')
                    ->badge(),
                Tables\Columns\IconColumn::make('employment_status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('signature_path')
                    ->url(fn($state) => $state)
                    ->placeholder('Not uploaded!'),
                Tables\Columns\TextColumn::make('identity_photo_path')
                    ->url(fn($state) => $state)
                    ->placeholder('Not uploaded!'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
