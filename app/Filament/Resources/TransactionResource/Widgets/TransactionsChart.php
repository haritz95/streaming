<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TransactionsChart extends ChartWidget
{
    protected static ?string $heading = 'Transactions chart';

    public ?string $filter;

    public function __construct()
    {
        $this->filter = Carbon::now()->year;
    }

    protected function getFilters(): ?array
    {
        $uniqueYears = Transaction::distinct('created_at')->pluck('created_at')->map(function ($date) {
            return Carbon::parse($date)->year;
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

        $expensesByMonth = Transaction::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(profit) as total')
        )
            ->whereYear('created_at', $selectedYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $data = [
            'datasets' => [
                [
                    'label' => 'Profit',
                    'data' => $this->fillMissingMonths($expensesByMonth),
                ],
            ],
            'labels' => $this->getMonthLabels(),
        ];

        return $data;
    }

    protected function getMonthLabels(): array
    {
        return [
            'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic',
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function fillMissingMonths($expensesByMonth): array
    {
        $filledData = [];
        $months = range(1, 12);

        foreach ($months as $month) {
            $expense = $expensesByMonth->where('month', $month)->first();

            if ($expense) {
                $filledData[] = $expense->total;
            } else {
                $filledData[] = 0;
            }
        }

        return $filledData;
    }
}
