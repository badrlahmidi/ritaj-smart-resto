<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationGroup = '📦 Stock & Achats';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $label = 'Bon de Commande';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails Facture')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('phone'),
                            ]),
                        Forms\Components\TextInput::make('reference_no')
                            ->label('N° Facture Fournisseur'),
                        Forms\Components\DatePicker::make('date')
                            ->default(now())
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Marchandises')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->relationship('ingredient', 'name')
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                        $set('unit_cost', Ingredient::find($state)?->cost_per_unit ?? 0)
                                    )
                                    ->columnSpan(4),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->live() // Recalculate total instantly
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('unit_cost')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->columnSpan(2),
                                
                                Forms\Components\Placeholder::make('total_line')
                                    ->content(fn (Forms\Get $get) => number_format((float)$get('quantity') * (float)$get('unit_cost'), 2) . ' DH')
                                    ->columnSpan(2),
                            ])
                            ->columns(10)
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                // Auto calculate total invoice amount
                                $total = collect($get('items'))->sum(fn($item) => ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0));
                                $set('total_amount', $total);
                            }),
                    ]),
                
                Forms\Components\TextInput::make('total_amount')
                    ->disabled() // Calculated automatically
                    ->dehydrated()
                    ->numeric()
                    ->prefix('DH TOTAL'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_no')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->sortable(),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('total_amount')->money('mad')->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'ordered' => 'warning',
                        'received' => 'success',
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('receive')
                    ->label('Valider Réception')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        if ($record->status === 'received') return;

                        // Stock Logic: Increment Stock & Update PUMP
                        foreach ($record->items as $item) {
                            $ingredient = $item->ingredient;
                            $ingredient->updateCostPrice($item->quantity, $item->unit_cost);
                        }

                        $record->update([
                            'status' => 'received',
                            'received_at' => now(),
                        ]);
                    })
                    ->visible(fn (PurchaseOrder $record) => $record->status !== 'received'),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
