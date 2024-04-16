<?php

namespace App\Filament\Resources\PlatformResource\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TransactionsPerPlatformChart extends ChartWidget
{
    protected static ?string $heading = 'Transactions per platform chart';

    public ?string $filter;

    public function __construct()
    {
        $this->filter = Carbon::now()->year;
    }

    protected function getFilters(): ?array
    {
        $uniqueYears = Transaction::distinct('created_at')->pluck('created_at')->map(function ($created_at) {
            return Carbon::parse($created_at)->year;
        })->unique()->sort()->toArray();

        $yearFilters = [];
        foreach ($uniqueYears as $year) {
            $yearFilters[$year] = $year;
        }

        return $yearFilters;
    }

    protected function getData(): array
    {
        $selectedYear =  $this->filter;

        $salesByPlatformAndMonth = Transaction::select(
            'platforms.name as platform_name',
            DB::raw('MONTH(transactions.created_at) as month'),
            DB::raw('COUNT(transactions.profit) as total')
        )
            ->join('platforms', 'transactions.platform_id', '=', 'platforms.id')
            ->whereYear('transactions.created_at', $selectedYear)
            ->groupBy('platform_name', DB::raw('MONTH(transactions.created_at)'))
            ->orderBy('platform_name')
            ->orderBy('month')
            ->get();

        $platforms = $salesByPlatformAndMonth->pluck('platform_name')->unique()->toArray();
        $months = $salesByPlatformAndMonth->pluck('month')->unique()->toArray();

        $data = [
            'datasets' => [],
            'labels' => $months,
        ];

        foreach ($platforms as $platform) {
            $platformData = [
                'label' => $platform,
                'data' => [],
            ];

            foreach ($months as $month) {
                $totalSales = $salesByPlatformAndMonth->where('platform_name', $platform)
                    ->where('month', $month)
                    ->pluck('total')
                    ->first() ?? 0;

                $platformData['data'][] = $totalSales;
            }

            $data['datasets'][] = $platformData;
        }

        return $data;
    }



    protected function getType(): string
    {
        return 'bar';
    }
}
