<?php

use Livewire\Component;

use Livewire\Attributes\On;  
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

use App\Models\Category;

new class extends Component
{
    use WithPagination;

    #[on('category-updated')]
    public function mount(){
        $this->create = false;
        $this->message = "";
    }

    public string $search = "";
    public function updatingSearch(){
        $this->resetPage();
    }

    #[Computed]
    public function categories(){
        return Category::latest()->when($this->search, function($query){
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%");
        })->paginate(10);
    }

    public $edit_id;
    public bool $create;
    public function showCreate($edit_id = ""){
        $this->edit_id = $edit_id;
        $this->create = !$this->create;
    }

    public $message;
    #[on('send-message')]
    public function handleMessage($message){
        $this->message = $message;
    }

    public function delete($id){
        Category::where('id', $id)->delete();
        $this->message = "Category Deleted Successfully";
    }
};
?>

<div class="relative h-full flex flex-col gap-5 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
    @if($create)
        <livewire:admin.category.create :edit_id="$edit_id" />
    @else

    @if($message)
    <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 5000)" 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-5 right-5 z-50 max-w-sm"
        >
        <div class="flex flex-row items-center justify-between gap-4 rounded-xl border border-indigo-100 bg-indigo-50 p-4 shadow-lg shadow-indigo-100/40 dark:border-indigo-950 dark:bg-indigo-950/50 dark:shadow-none">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse shrink-0"></span>
                <p class="text-sm font-medium text-indigo-900 dark:text-indigo-200">
                    {{$message}}
                </p>
            </div>
    
            <button 
                @click="show = false" 
                class="rounded-lg p-1 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-700 dark:text-indigo-300 dark:hover:bg-indigo-900/50 dark:hover:text-indigo-200 transition-colors duration-200 focus:outline-none"
                aria-label="Close notification"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    <div class="flex flex-row justify-between rounded-xl border border-neutral-200 dark:border-neutral-700">
       <div class="w-50">
           <flux:input wire:model.live="search" name="Search" type="text" placeholder="Search Categories..."/>
       </div>

        <flux:button wire:click="showCreate" variant="primary" type="button" class="" data-test="Add-button">
                {{ __('Add') }}
        </flux:button>
    </div>

    <div class="relative h-full flex flex-row justify-between overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-2">
        <flux:table scrollable container:class="w-full" :paginate="$this->categories">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Slug</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Image Url</flux:table.column>
                <flux:table.column>Remove</flux:table.column>
                <flux:table.column>Update</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->categories as $category)
                <flux:table.row>
                    <flux:table.cell>{{$category['name']}}</flux:table.cell>
                    <flux:table.cell>{{$category['slug']}}</flux:table.cell>
                    @if($category['is_active'])
                    <flux:table.cell><flux:badge color="green" size="sm" inset="top bottom">Active</flux:badge></flux:table.cell>
                    @else
                    <flux:table.cell><flux:badge color="zinc" size="sm" inset="top bottom">Inactive</flux:badge></flux:table.cell>
                    @endif

                    @if($category['image'])
                        <flux:table.cell class="whitespace-normal" variant="strong">{{$category['image']}}</flux:table.cell>
                    @else
                        <flux:table.cell variant="strong">NULL</flux:table.cell>
                    @endif
                        <flux:table.cell variant="strong">
                            <flux:button wire:click="delete({{$category['id']}})" variant="danger" size="sm">Delete</flux:button>
                        </flux:table.cell>
                        <flux:table.cell variant="strong">
                            <flux:button wire:click="showCreate({{$category['id']}})" variant="primary" color="blue" size="sm">Edit</flux:button>
                        </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    
    @endif
</div>