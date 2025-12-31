<?php

namespace App\Filament\Resources\PrinterResource\Pages;

use App\Filament\Resources\PrinterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class ListPrinters extends ListRecords
{
    protected static string $resource = PrinterResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}

class CreatePrinter extends CreateRecord
{
    protected static string $resource = PrinterResource::class;
}

class EditPrinter extends EditRecord
{
    protected static string $resource = PrinterResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
