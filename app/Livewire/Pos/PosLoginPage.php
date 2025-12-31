<?php

namespace App\Livewire\Pos;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pos-login')]
class PosLoginPage extends Component
{
    public $pin = '';
    public $error = '';
    public $selectedUser = null;
    public $showPinPad = false;

    public function mount()
    {
        // Logout if coming to login page
        if(auth()->check()) {
            auth()->logout();
        }
    }

    public function getUsersProperty()
    {
        // Only show users with appropriate roles and active status
        return User::where('is_active', true)
            ->whereIn('role', ['waiter', 'cashier', 'manager', 'kitchen'])
            ->get();
    }

    public function selectUser($userId)
    {
        $this->selectedUser = User::find($userId);
        if ($this->selectedUser) {
            $this->showPinPad = true;
            $this->pin = '';
            $this->error = '';
        }
    }

    public function closePinPad()
    {
        $this->showPinPad = false;
        $this->selectedUser = null;
        $this->pin = '';
    }

    public function append($number)
    {
        if (strlen($this->pin) < 4) {
            $this->pin .= $number;
            $this->error = '';
        }

        if (strlen($this->pin) === 4) {
            $this->login();
        }
    }

    public function clear()
    {
        $this->pin = '';
        $this->error = '';
    }

    public function backspace()
    {
        $this->pin = substr($this->pin, 0, -1);
        $this->error = '';
    }

    public function login()
    {
        if (!$this->selectedUser) return;

        if ($this->selectedUser->checkPin($this->pin)) {
            Auth::login($this->selectedUser);
            
            if ($this->selectedUser->role === 'kitchen') {
                return redirect()->to('/admin'); // Placeholder for kitchen view
            }
            
            return redirect()->route('pos');
        }

        $this->error = 'Code PIN incorrect';
        $this->pin = '';
    }

    public function render()
    {
        return view('livewire.pos.pos-login-page');
    }
}
