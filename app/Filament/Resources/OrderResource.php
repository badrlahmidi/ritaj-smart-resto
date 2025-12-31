<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Opérations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('table_id')
                                    ->relationship('table', 'name')
                                    ->required(),
                                Forms\Components\Select::make('waiter_id')
                                    ->relationship('waiter', 'name')
                                    ->label('Serveur')
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'En attente',
                                        'sent_to_kitchen' => 'Envoyé en cuisine',
                                        'ready' => 'Prêt',
                                        'paid' => 'Payé',
                                        'cancelled' => 'Annulé',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('payment_method')
                                    ->options([
                                        'cash' => 'Espèces',
                                        'card' => 'Carte Bancaire',
                                    ]),
                                Forms\Components\Toggle::make('sync_status')
                                    ->label('Synchronisé Cloud')
                                    ->disabled(),
                            ])->columns(2),

                        Forms\Components\Section::make('Articles')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit_price', \App\Models\Product::find($state)?->price ?? 0)),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => $set('total_price', $state * $get('unit_price'))),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->disabled()
                                            ->dehydrated()
                                            ->numeric()
                                            ->required(),
                                        Forms\Components\TextInput::make('total_price')
                                            ->disabled()
                                            ->dehydrated()
                                            ->numeric()
                                            ->required(),
                                    ])
                                    ->columns(4)
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Créé le')
                                    ->content(fn (Order $record): ?string => $record->created_at?->diffForHumans()),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Modifié le')
                                    ->content(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('local_id')
                    ->label('# Ticket')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('table.name')
                    ->label('Table')
                    ->sortable(),
                Tables\Columns\TextColumn::make('waiter.name')
                    ->label('Serveur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('mad')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'pending',
                        'info' => 'sent_to_kitchen',
                        'success' => 'paid',
                    ]),
                Tables\Columns\IconColumn::make('sync_status')
                    ->boolean()
                    ->label('Sync'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'cancelled' => 'Annulé',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
