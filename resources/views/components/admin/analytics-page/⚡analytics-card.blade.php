<?php

use Livewire\Component;

new class extends Component
{
    public $title;
    public $value;
    public $color = 'blue';
    public $icon = 'currency-rupee';
};
?>

<div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">

    <div class="flex items-start justify-between">

        <div>

            <p class="text-sm text-zinc-500">
                {{ $title }}
            </p>

            <h2 class="mt-3 text-3xl font-bold">
                {{ $value }}
            </h2>

        </div>

        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $color }}">

            <flux:icon :name="$icon" class="size-6" />

        </div>

    </div>

</div>