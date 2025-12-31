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
        $users = User::whereNotNull('pin_code')->get();
        
        foreach ($users as $user) {
            if (Hash::check($this->pin, $user->pin_code)) {
                Auth::login($user);
                
                // Redirection basée sur le rôle
                if ($user->role === 'kitchen') {
                    // return redirect()->route('kitchen.display'); // Future route
                    return redirect()->to('/admin'); // Fallback for now
                }
                
                return redirect()->route('pos'); // Page POS principale
            }
        }

        $this->error = 'Code PIN incorrect';
        $this->pin = '';
    }

    public function render()
    {
        return view('livewire.pos.pos-login-page');
    }
}
