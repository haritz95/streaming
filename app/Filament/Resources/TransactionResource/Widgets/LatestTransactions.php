<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Filament\Resources\TransactionResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(TransactionResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date(),
                TextColumn::make('customer.name')
                    ->label('Customer'),
                TextColumn::make('platform.name')
                    ->label('Platform'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD', locale: 'en'),
                TextColumn::make('profit')
                    ->label('Profit')
                    ->iconColor(fn (string $state): string => $state > 0 ? 'success' : 'danger')
                    ->color(fn (string $state): string => $state > 0 ? 'success' : 'danger')
                    ->money('USD', locale: 'en'),
            ]);
    }
}
