<?php

namespace App\Livewire\Pos;

use App\Models\Table;

/**
 * Legacy wrapper for ProPos to maintain table-specific routes
 */
class PosOrderPage extends ProPos
{
    public function mount(Table $table = null)
    {
        parent::mount();
        
        if ($table && $table->exists) {
            $this->selectTable($table->id);
        }
    }
}