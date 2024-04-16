<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Customer;
use App\Models\Platform;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', Customer::count()),
            Stat::make('Total Platforms', Platform::count()),
            Stat::make('Total Transactions', Transaction::count()),
        ];
    }
}
