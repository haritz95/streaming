<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Customer;
use App\Models\Platform;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewToday extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();

        $previousDay = $today->copy()->subDay();
        $previousYear = $previousDay->format('Y');
        $previousMonth = $previousDay->format('m');

        $currentTransactions = Transaction::whereDate('created_at', $today)->count();
        $previousTransactions = Transaction::whereYear('created_at', $previousYear)
            ->whereMonth('created_at', $previousMonth)
            ->whereDay('created_at', $previousDay->day)
            ->count();

        $currentProfit = Transaction::whereDate('created_at', $today)->sum('profit');
        $previousProfit = Transaction::whereYear('created_at', $previousYear)
            ->whereMonth('created_at', $previousMonth)
            ->whereDay('created_at', $previousDay->day)
            ->sum('profit');

        return [
            Stat::make('Customers Today', Customer::count()),

            Stat::make('Transactions Today', $currentTransactions)
                ->description($this->getComparisonDescription($currentTransactions, $previousTransactions))
                ->descriptionIcon($this->getComparisonIcon($currentTransactions, $previousTransactions))
                ->color($this->getComparisonColor($currentTransactions, $previousTransactions)),

            Stat::make('Profit Today', number_format($currentProfit, 2, ',', '.') . '$')
                ->description($this->getComparisonDescription($currentProfit, $previousProfit))
                ->descriptionIcon($this->getComparisonIcon($currentProfit, $previousProfit))
                ->color($this->getComparisonColor($currentProfit, $previousProfit)),
        ];
    }

    protected function getComparisonDescription($current, $previous): string
    {
        $difference = $current - $previous;
        $sign = $difference > 0 ? '+' : ($difference < 0 ? '' : '');
        return $sign . $difference . ' respecto al día anterior';
    }

    protected function getComparisonIcon($current, $previous): string
    {
        return $current > $previous ? 'heroicon-m-arrow-trending-up' : ($current < $previous ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrows-right-left');
    }

    protected function getComparisonColor($current, $previous): string
    {
        return $current > $previous ? 'success' : ($current < $previous ? 'danger' : 'info');
    }
}
