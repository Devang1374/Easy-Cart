<?php

use Livewire\Component;
use Livewire\Attributes\On;

use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Banner;

use App\Services\CloudinaryService;

new class extends Component {
    #[on('banner-update')]
    public function mount()
    {
        $this->create = false;
    }

    public $search;
    #[Computed]
    public function banners()
    {
        return Banner::latest()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%")->orWhere('subtitle', 'like', "%{$this->search}%");
            })
            ->paginate(10);
    }

    public $edit_id;
    public bool $create;
    public function showCreate($edit_id = '')
    {
        $this->edit_id = $edit_id;
        $this->create = !$this->create;
    }

    public $message;
    #[on('send-message')]
    public function handleMessage($message)
    {
        $this->message = $message;
    }

    public function delete($id)
    {
        $oldPath = Banner::where('id', $id)->value('mobile_image_id');
        app(CloudinaryService::class)->destroy($oldPath);

        $oldPath = Banner::where('id', $id)->value('desktop_image_id');
        app(CloudinaryService::class)->destroy($oldPath);

        $oldPath = Banner::where('id', $id)->value('background_image_id');
        app(CloudinaryService::class)->destroy($oldPath);

        Banner::where('id', $id)->delete();
        $this->message = 'Banner Deleted Successfully';
    }
};
?>

<div
    class="relative h-full flex flex-col gap-5 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
    @if ($create)
        <livewire:admin.banner.create :edit_id="$edit_id" />
    @else
        @if ($message)
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed bottom-5 right-5 z-50 max-w-sm">
                <div
                    class="flex flex-row items-center justify-between gap-4 rounded-xl border border-indigo-100 bg-indigo-50 p-4 shadow-lg shadow-indigo-100/40 dark:border-indigo-950 dark:bg-indigo-950/50 dark:shadow-none">
                    <div class="flex items-center gap-2">
                        <span
                            class="h-2 w-2 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse shrink-0"></span>
                        <p class="text-sm font-medium text-indigo-900 dark:text-indigo-200">
                            {{ $message }}
                        </p>
                    </div>

                    <button @click="show = false"
                        class="rounded-lg p-1 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-700 dark:text-indigo-300 dark:hover:bg-indigo-900/50 dark:hover:text-indigo-200 transition-colors duration-200 focus:outline-none"
                        aria-label="Close notification">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <div class="flex flex-row justify-between rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="w-50">
                <flux:input wire:model.live="search" name="Search" type="text" placeholder="Search Categories..." />
            </div>

            <flux:button wire:click="showCreate" variant="primary" type="button" class="" data-test="Add-button">
                {{ __('Add') }}
            </flux:button>
        </div>

        <div
            class="relative h-full flex flex-row justify-between overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-2">
            <flux:table scrollable container:class="w-full" :paginate="$this->banners">

                <flux:table.columns>
                    <flux:table.column>Preview</flux:table.column>
                    <flux:table.column>Title</flux:table.column>
                    <flux:table.column>Position</flux:table.column>
                    <flux:table.column>Schedule</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Order</flux:table.column>
                    <flux:table.column>Remove</flux:table.column>
                    <flux:table.column>Update</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>

                    @foreach ($this->banners as $banner)
                        <flux:table.row>

                            {{-- Preview --}}
                            <flux:table.cell>

                                @if ($banner->desktop_image)
                                    <img src="{{ $banner->desktop_image }}" class="h-14 w-24 rounded-lg object-cover">
                                @else
                                    <div
                                        class="flex h-14 w-24 items-center justify-center rounded-lg bg-zinc-100 text-xs dark:bg-zinc-800">
                                        No Image
                                    </div>
                                @endif

                            </flux:table.cell>

                            {{-- Title --}}
                            <flux:table.cell>

                                <div class="font-semibold">
                                    {{ $banner->title }}
                                </div>

                                @if ($banner->subtitle)
                                    <div class="text-xs text-zinc-500 line-clamp-1">
                                        {{ $banner->subtitle }}
                                    </div>
                                @endif

                            </flux:table.cell>

                            {{-- Position --}}
                            <flux:table.cell>
                                <flux:badge color="blue" size="sm">
                                    {{ ucfirst($banner->position) }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Schedule --}}
                            <flux:table.cell>

                                <div class="text-sm">

                                    @if ($banner->starts_at)
                                        <div>
                                            <strong>From:</strong>
                                            {{ $banner->starts_at->format('d M Y H:i') }}
                                        </div>
                                    @endif

                                    @if ($banner->expires_at)
                                        <div>
                                            <strong>To:</strong>
                                            {{ $banner->expires_at->format('d M Y H:i') }}
                                        </div>
                                    @endif

                                    @if (!$banner->starts_at && !$banner->expires_at)
                                        <span class="text-zinc-500">
                                            Always
                                        </span>
                                    @endif

                                </div>

                            </flux:table.cell>

                            {{-- Status --}}
                            <flux:table.cell>

                                @if (!$banner->is_active)
                                    <flux:badge color="zinc" size="sm">
                                        Inactive
                                    </flux:badge>
                                @elseif($banner->expires_at && now()->gt($banner->expires_at))
                                    <flux:badge color="red" size="sm">
                                        Expired
                                    </flux:badge>
                                @elseif($banner->starts_at && now()->lt($banner->starts_at))
                                    <flux:badge color="yellow" size="sm">
                                        Upcoming
                                    </flux:badge>
                                @else
                                    <flux:badge color="green" size="sm">
                                        Active
                                    </flux:badge>
                                @endif

                            </flux:table.cell>

                            {{-- Sort Order --}}
                            <flux:table.cell>
                                {{ $banner->sort_order }}
                            </flux:table.cell>

                            {{-- Delete --}}
                            <flux:table.cell>

                                <flux:button wire:click="delete({{ $banner->id }})" variant="danger" size="sm">
                                    Delete
                                </flux:button>

                            </flux:table.cell>

                            {{-- Edit --}}
                            <flux:table.cell>

                                <flux:button wire:click="showCreate({{ $banner->id }})" variant="primary"
                                    size="sm">
                                    Edit
                                </flux:button>

                            </flux:table.cell>

                        </flux:table.row>
                    @endforeach

                </flux:table.rows>

            </flux:table>
        </div>
    @endif
</div>