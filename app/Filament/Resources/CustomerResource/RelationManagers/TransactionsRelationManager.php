<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Customer;
use App\Models\Platform;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\Select::make('customer_id')
                        ->required()
                        ->label('Customer')
                        ->options(Customer::all()->pluck('name', 'id'))
                        ->preload()
                        ->live()
                        ->searchable(),
                    Forms\Components\Select::make('platform_id')
                        ->required()
                        ->label('Platform')
                        ->options(Platform::all()->pluck('name', 'id'))
                        ->preload()
                        //->searchable()
                        ->disabled(fn ($get) => $get('customer_id') == null)
                        ->live()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('customer_price')
                                ->required()
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\TextInput::make('reseller_price')
                                ->required()
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\Toggle::make('enable')
                                ->required()
                                ->default(1),
                        ])
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            $customer = Customer::find($get('customer_id'));
                            $platform = Platform::find($state);
                            if ($platform) {
                                $price = $customer->reseller ? $platform->reseller_price : $platform->customer_price;
                                $set('cost_price', $price ?? 0);
                                $sellPrice = $get('sell_price') ?? 0;
                                $quantity = $get('quantity') ?? 0;

                                $set('profit', $sellPrice - $price);
                                $set('total', ($price * $quantity));
                            } else {
                                $set('cost_price', 0);
                                $set('profit', 0);
                                $set('total', 0);
                            }
                        }),
                    Forms\Components\TextInput::make('quantity')
                        ->required()
                        ->minValue(1)
                        ->default(1)
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $sellPrice = $get('sell_price') ?? 0;
                            $costPrice = $get('cost_price') ?? 0;
                            $quantity = floatval($get('quantity')) ?? 0;

                            $set('total', ($costPrice * $quantity));
                            $profit = ($sellPrice * $quantity) - ($costPrice * $quantity);
                            $set('profit', $profit);
                        }),
                    Forms\Components\TextInput::make('cost_price')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('$')
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $sellPrice = floatval($get('sell_price')) ?? 0;
                            $costPrice = floatval($get('cost_price')) ?? 0;
                            $quantity = floatval($get('quantity')) ?? 0;

                            $set('total', ($costPrice * $quantity));
                            $set('profit', ($sellPrice * $quantity) - ($costPrice * $quantity));
                        }),
                    Forms\Components\TextInput::make('sell_price')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefix('$')
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $sellPrice = floatval($get('sell_price')) ?? 0;
                            $costPrice = floatval($get('cost_price')) ?? 0;
                            $quantity = floatval($get('quantity')) ?? 0;

                            $set('total', ($costPrice * $quantity));
                            floatval($set('profit', ($sellPrice * $quantity) - ($costPrice * $quantity)));
                        }),
                    Forms\Components\TextInput::make('total')
                        ->required()
                        ->readOnly()
                        ->prefix('$')
                        ->numeric(),
                ])->columns(2)
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('profit')
                                    ->required()
                                    ->readOnly()
                                    ->prefix('$')
                                    ->numeric(),
                            ]),
                        Section::make([
                            Forms\Components\ToggleButtons::make('status')
                                ->inline()
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ])
                                ->colors([
                                    'active' => 'success',
                                    'inactive' => 'danger',
                                ])
                                ->required(),
                            Forms\Components\DatePicker::make('start_date')
                                ->native(false)
                                ->prefixIcon('heroicon-m-calendar')
                                ->required(),
                            Forms\Components\DatePicker::make('end_date')
                                ->native(false)
                                ->prefixIcon('heroicon-m-calendar')
                                ->required(),
                        ])
                    ]),
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('customer_id')
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->numeric()
                    ->icon('heroicon-m-currency-dollar')
                    ->iconColor('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sell_price')
                    ->numeric()
                    ->icon('heroicon-m-currency-dollar')
                    ->iconColor('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->icon('heroicon-m-currency-dollar')
                    ->iconColor('success')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit')
                    ->numeric()
                    ->icon('heroicon-m-currency-dollar')
                    ->iconColor(fn (string $state): string => $state > 0 ? 'success' : 'danger')
                    ->color(fn (string $state): string => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active'))
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
