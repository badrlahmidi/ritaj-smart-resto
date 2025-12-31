<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
}

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
