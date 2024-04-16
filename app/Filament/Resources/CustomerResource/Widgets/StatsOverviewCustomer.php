<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class StatsOverviewCustomer extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        $customerId = $this->record->id;

        return [
            Stat::make('Total Transactions', Transaction::where('customer_id', $customerId)->count())
                ->description('Amount of transactions with this customer'),
            Stat::make('Total Earned', '$' . Transaction::where('customer_id', $customerId)->sum('profit'))
                ->description('Amount earned with this customer')
                ->icon('heroicon-m-currency-dollar'),
            Stat::make('Active platforms', Transaction::where('customer_id', $customerId)->where('status', 'active')->sum('quantity'))
                ->description('Amount of active platforms this customer has'),
        ];
    }
}
