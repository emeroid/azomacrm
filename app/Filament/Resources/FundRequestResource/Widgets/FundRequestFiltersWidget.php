<?php

namespace App\Filament\Resources\FundRequestResource\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\User;
use App\Enums\Role;

class FundRequestFiltersWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.resources.fund-request-resource.widgets.fund-request-filters-widget';

    public ?array $filterData = [
        'user_id' => null,
        'start_date' => null,
        'end_date' => null,
    ];

    public function mount(): void
    {
        $this->filterData['start_date'] = now()->startOfMonth()->format('Y-m-d');
        $this->filterData['end_date']   = now()->endOfDay()->format('Y-m-d');

        if ($userId = request()->query('user')) {
            $this->filterData['user_id'] = $userId;
        }

        // initialize the Filament form with our defaults
        $this->form->fill($this->filterData);
    }

    public function form(Form $form): Form
    {
        $requesterRoles = [Role::MARKETER->value, Role::CALL_AGENT->value];
        
        $users = User::whereIn('role', $requesterRoles)
                        ->get()
                        ->mapWithKeys(fn ($user) => [$user->id => $user->full_name]);

        return $form
            ->statePath('filterData')
            ->schema([
                Select::make('user_id')
                    ->label('Filter by Requester')
                    ->options($users)
                    ->placeholder('All Requesters')
                    ->searchable()
                    ->nullable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('filterUpdated', $this->filterData)),
                    
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('filterUpdated', $this->filterData)),
                    
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('filterUpdated', $this->filterData)),
            ])
            ->columns(3);                    
    }

    // When the widget's filterData changes, dispatch an event to the parent page
    public function updatedFilterData(): void
    {
        $this->dispatch('filterUpdated', $this->filterData);
    }
}
