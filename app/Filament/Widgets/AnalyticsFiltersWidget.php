<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Carbon;

class AnalyticsFiltersWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.analytics-filters-widget';

    public ?string $date_range = 'this_week';
    public ?string $start_date = null;
    public ?string $end_date = null;

    protected function getFormSchema(): array
    {
        return [
            Select::make('date_range')
                ->label('Date Range')
                ->options([
                    'today' => 'Today',
                    'yesterday' => 'Yesterday',
                    'this_week' => 'This Week',
                    'last_week' => 'Last Week',
                    'this_month' => 'This Month',
                    'last_month' => 'Last Month',
                    'custom' => 'Custom Range',
                ])
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->updateDates($state)),

            DatePicker::make('start_date')
                ->label('Start Date')
                ->reactive()
                ->hidden(fn () => $this->date_range !== 'custom')
                ->afterStateUpdated(fn () => $this->dispatchFilterUpdate()),

            DatePicker::make('end_date')
                ->label('End Date')
                ->reactive()
                ->hidden(fn () => $this->date_range !== 'custom')
                ->afterStateUpdated(fn () => $this->dispatchFilterUpdate()),
        ];
    }

    public function mount(): void
    {
        $this->updateDates($this->date_range);
    }

    public function updateDates($range): void
    {
        switch ($range) {
            case 'today':
                $this->start_date = Carbon::today()->startOfDay();
                $this->end_date   = Carbon::today()->endOfDay();
                break;
            
            case 'yesterday':
                $this->start_date = Carbon::yesterday()->startOfDay();
                $this->end_date   = Carbon::yesterday()->endOfDay();
                break;
            
            case 'this_week':
                $this->start_date = Carbon::now()->startOfWeek();
                $this->end_date   = Carbon::now()->endOfWeek();
                break;
            
            case 'last_week':
                $this->start_date = Carbon::now()->subWeek()->startOfWeek();
                $this->end_date   = Carbon::now()->subWeek()->endOfWeek();
                break;
            
            case 'this_month':
                $this->start_date = Carbon::now()->startOfMonth();
                $this->end_date   = Carbon::now()->endOfMonth();
                break;
            
            case 'last_month':
                $this->start_date = Carbon::now()->subMonth()->startOfMonth();
                $this->end_date   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $this->start_date = null;
                $this->end_date = null;
                break;
        }
        $this->dispatchFilterUpdate();
    }

    private function dispatchFilterUpdate(): void
    {
        $this->dispatch('updateAnalyticsFilters', [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);
    }
}
