<?php

namespace App\Livewire;

use Livewire\Component;

class Profit extends Component
{
    public $sellPrice;
    public $costPrice;
    public $quantity;

    public function calculateProfit()
    {
        $profit = ($this->sellPrice * $this->quantity) - ($this->costPrice * $this->quantity);
        $this->emit('profitCalculated', $profit);
    }

    public function render()
    {
        return view('livewire.profit');
    }
}
