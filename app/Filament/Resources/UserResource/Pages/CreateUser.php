<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\UserPin;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        
        if (!empty($data['new_pin'])) {
            UserPin::create([
                'user_id' => $this->record->id,
                'pin_code' => Hash::make($data['new_pin']),
            ]);
        }
    }
}
