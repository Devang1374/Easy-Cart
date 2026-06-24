<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component
{
    public function mount()
    {
        $this->cartCount();
    }

    #[On('cart-updated')]
    public function cartCount(){
        return collect(session('cart', []))->sum('quantity');
    }
};
?>

<span class="rounded-full bg-blue-600 px-2 py-0.5 text-xs text-white">
    {{$this->cartCount()}}
</span>