<?php

use Livewire\Component;

use App\Models\Wishlist;
use Livewire\Attributes\On;

new class extends Component
{
    public $count;
    #[On('wishlist-updated')]
    public function mount()
    {
        $this->count = auth()->check()
            ? Wishlist::where('user_id', auth()->id())->count()
            : 0;
    }
};
?>

<div>
    @if($count > 0)
        <span class="ml-1 rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">
            {{ $count }}
        </span>
    @endif
</div>