<?php

namespace App\Livewire\AdminCoord\Events;

use Carbon\Carbon;
use App\Enums\Role;
use App\Enums\Events;
use App\Models\Event;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Employee;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use App\Enums\OfficialLeaves;
use Filament\Infolists\Infolist;
use Livewire\Attributes\Reactive;
use App\Mail\Event\EvaluationResult;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Infolists\Concerns\InteractsWithInfolists;

class ViewEvent extends Component implements HasForms, HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithForms, InteractsWithInfolists;

    #[Reactive]
    public $events;

    public $event;

    public function render()
    {
        $this->event = Event::query()
            ->when(($this->events->tag != Events::SUS->value && $this->events->tag != Events::HOL->value), function ($query) {
                $query->whereHas('employee', fn($q) => $q->departmentCovered());
            })
            ->whereDate('start', $this->events->date)
            ->where('tag', $this->events->tag)
            ->first();

        if (!$this->event) {
            $this->dispatch('close-modal', id: 'view-event');
        }

        return view('livewire.admin-coord.events.view-event');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->with('event_created_by', 'mov')
                    ->whereHas('employee', fn($query) => $query->departmentCovered())
                    ->whereDate('start', $this->events->date)
                    ->where('tag', $this->events->tag)
            )
            ->columns([
                \Filament\Tables\Columns\Layout\Split::make([
                    \Filament\Tables\Columns\TextColumn::make('employee.full_name')
                        ->label('Full Name')
                        ->description('Full Name', position: 'above')
                        ->searchable(['last_name', 'first_name'])
                        ->sortable(['last_name']),
                    \Filament\Tables\Columns\TextColumn::make('employee.department.description')
                        ->label('Department')
                        ->description('Department', position: 'above')
                        ->searchable(['group', 'center', 'office'])
                        ->sortable(['group', 'center', 'office'])
                        ->visible(fn() => auth()->user()->hasRole(Role::CENTERADMINCOORD) || auth()->user()->hasRole(Role::GROUPADMINCOORD)),
                    \Filament\Tables\Columns\TextColumn::make('description')
                        ->formatStateUsing(fn($record) => ($record->tag == Events::ALA) ? OfficialLeaves::parse($record->description)->getLabel() : $record->description)
                        ->label('Description')
                        ->description('Description', position: 'above')
                        ->sortable()
                        ->visible(fn($record) => $record?->tag == Events::ALA),
                    \Filament\Tables\Columns\TextColumn::make('mov.filename')
                        ->formatStateUsing(fn() => "File uploaded")
                        ->iconColor("primary")
                        ->icon("heroicon-m-arrow-down-tray")
                        ->placeholder('No uploaded file')
                        ->description('MOV File', position: 'above')
                        ->url(function ($record) {
                            if (!$record->mov) {
                                return null;
                            }
                            return route('admin.pdf.view-mov', ['mov' => $record->mov]);
                        })
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record?->tag == Events::ALA || $record?->tag == Events::CDO || $record?->tag == Events::OB),
                    \Filament\Tables\Columns\TextColumn::make('status')
                        ->label('Status')
                        ->description('Status', position: 'above')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'disapproved' => 'danger',
                        }),
                    \Filament\Tables\Columns\TextColumn::make('event_created_by')
                        ->formatStateUsing(fn($state) => $state->full_name)
                        ->label('Created by')
                        ->description('Created by', position: 'above')
                        ->sortable(),
                ])
                    ->from('lg')
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('set_status')
                    ->icon('heroicon-m-arrow-path')
                    ->label('Evaluate Requests')
                    ->modalHeading('Evaluate all requests')
                    ->color('secondary')
                    ->modalWidth('xl')
                    ->form([
                        \Filament\Forms\Components\ToggleButtons::make('evaluation_status')
                            ->label('Do you want to Approve or Disapprove this request?')
                            ->options([
                                'approved' => 'Approve',
                                'disapproved' => 'Disapprove',
                            ])
                            ->colors([
                                'approved' => 'success',
                                'disapproved' => 'danger',
                            ])
                            ->inline()
                            ->live()
                            ->required()
                            ->validationMessages([
                                'required' => 'Please, select if you want to Approve or Disapprove this request.',
                            ]),
                        \Filament\Forms\Components\Textarea::make('note')
                            ->label('Note (optional)')
                            ->visible(fn(Get $get) => $get('evaluation_status') === 'disapproved')
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data, $records) {
                        $note = (array_key_exists('note', $data) ? $data['note'] : null);

                        foreach ($records as $record) {
                            $record->status = $data['evaluation_status'];
                            $record->save();

                            Mail::to($record->employee->user)->cc(auth()->user())->send(new EvaluationResult($record, $note));
                        }

                        Notification::make()
                            ->success()
                            ->color('success')
                            ->title('Evaluation has been complete.')
                            ->body($records->count() . ' requests has been ' . str($data['evaluation_status'])->title() . '.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
            ])
            ->checkIfRecordIsSelectableUsing(function (Model $record): bool {
                return $record->status == "pending";
            })
            ->actions([
                \Filament\Tables\Actions\ActionGroup::make([
                    \Filament\Tables\Actions\Action::make('set_status')
                        ->icon('heroicon-m-arrow-path')
                        ->label('Evaluate Request')
                        ->modalHeading(fn($record) => 'Evaluate ' . $record->tag->getLabel() . ' request of ' . $record->employee->first_name)
                        ->color('secondary')
                        ->modalWidth('xl')
                        ->form([
                            \Filament\Forms\Components\ToggleButtons::make('evaluation_status')
                                ->label('Do you want to Approve or Disapprove this request?')
                                ->options([
                                    'approved' => 'Approve',
                                    'disapproved' => 'Disapprove',
                                ])
                                ->colors([
                                    'approved' => 'success',
                                    'disapproved' => 'danger',
                                ])
                                ->inline()
                                ->live()
                                ->required()
                                ->validationMessages([
                                    'required' => 'Please, select if you want to Approve or Disapprove this request.',
                                ]),
                            \Filament\Forms\Components\Textarea::make('note')
                                ->label('Note (optional)')
                                ->visible(fn(Get $get) => $get('evaluation_status') === 'disapproved')
                        ])
                        ->requiresConfirmation()
                        ->action(function (array $data, $record) {
                            $note = array_key_exists('note', $data) ? $data['note'] : null;
                            $record->status = $data['evaluation_status'];
                            $record->save();

                            Mail::to($record->employee->user)->cc(auth()->user())->send(new EvaluationResult($record, $note));

                            Notification::make()
                                ->success()
                                ->color('success')
                                ->title('Evaluation has been complete.')
                                ->body($record->employee->apostFirstName . ' ' . $record->tag->getLabel() . ' request has been ' . str($data['evaluation_status'])->title() . '.')
                                ->send();
                        })
                        ->visible(fn($record) => $record->status === 'pending'),
                    \Filament\Tables\Actions\EditAction::make()
                        ->modalHeading(fn($record) => 'Edit Event of ' . $record->tag->getLabel() . ' (' . $record->start->format('F d, Y') . ')')
                        ->mountUsing(
                            function (Event $record, \Filament\Forms\Form $form, array $arguments) {
                                $form->fill([
                                    'hris_number' => $record->hris_number,
                                    'tag' => $record->tag,
                                    'description_leave' => OfficialLeaves::parse($record->description) ?? $record->description,
                                ]);
                            }
                        )
                        ->form([
                            \Filament\Forms\Components\Grid::make()
                                ->schema([
                                    \Filament\Forms\Components\Select::make('tag')
                                        ->label('Type of Event')
                                        ->options(function ($record) {
                                            $options = [];
                                            foreach (Events::cases() as $case) {
                                                if ($case == Events::WFH || $case == Events::HWFH) {
                                                    if ($record->start->dayOfWeek != Carbon::MONDAY) {
                                                        $options[$case->value] = $case->getLabel();
                                                    }
                                                } else if ($case != Events::HOL && $case != Events::FLAG && $case != Events::SUS) {
                                                    $options[$case->value] = $case->getLabel();
                                                }
                                            }
                                            return $options;
                                        })
                                        ->reactive()
                                        ->native(false)
                                        ->required()
                                        ->live(),
                                    \Filament\Forms\Components\Select::make('description_leave')
                                        ->label('Type of Official Leave')
                                        ->options(OfficialLeaves::class)
                                        ->native(false)
                                        ->visible(fn(Get $get) => ($get('tag') === Events::ALA) ? true : false)
                                        ->required(),
                                    \Filament\Forms\Components\Select::make('hris_number')
                                        ->label('Employee Name')
                                        // ->options(\App\Models\Employee::whereIn('department_id', $this->departments)->where('employment_status', true)->get()->pluck('full_name', 'hris_number'))
                                        ->getSearchResultsUsing(fn(string $search): array => Employee::searchEmployee($search)->departmentCovered()->limit(10)->get()->pluck('full_name', 'hris_number')->toArray())
                                        ->getOptionLabelUsing(fn($value): ?string => Employee::where('hris_number', $value)->first()?->full_name)
                                        ->native(false)
                                        ->searchable(['first_name', 'last_name', 'hris_number'])
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                        ])
                        ->mutateFormDataUsing(function (array $data): array {
                            if ($data['tag'] == Events::ALA) {
                                $data['description'] = $data['description_leave'];
                            } else {
                                $data['description'] = ($data['tag'] instanceof \App\Enums\Events)  ? $data['tag']->getLabel() : Events::tryFrom($data['tag'])->getLabel();
                            }

                            return $data;
                        })
                        ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Event has been updated!')
                                ->color('success'),
                        ),
                    \Filament\Tables\Actions\DeleteAction::make()
                        ->modalHeading('Remove Event!')
                        ->label('Remove')
                        ->requiresConfirmation()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Event has been removed')
                                ->color('success'),
                        )
                        ->after(fn() => $this->dispatch('refresh-calendar')->to(Calendar::class))
                ])
                    ->label('Actions')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->button()
                    ->dropdownPlacement('top-end')
            ]);
    }

    public function eventInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->event)
            ->schema([
                \Filament\Infolists\Components\Fieldset::make('Event Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('start')
                            ->date()
                            ->label('Date'),
                        \Filament\Infolists\Components\TextEntry::make('event_created_by')
                            ->label('Created by')
                            ->formatStateUsing(fn($state) => $state->full_name)
                            ->visible(fn($record) => ($record->tag == Events::HOL || $record->tag == Events::SUS || $record->tag == Events::FLAG)),
                        \Filament\Infolists\Components\TextEntry::make('tag')
                            ->badge()
                            ->label('Event'),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->visible(fn($record) => ($record->tag == Events::HOL || $record->tag == Events::SUS || $record->tag == Events::FLAG)),
                    ])
            ]);
    }
}
