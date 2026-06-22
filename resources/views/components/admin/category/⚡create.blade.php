<?php

use Livewire\Component;
use Livewire\Attributes\Validate;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

use App\Models\Category;

new class extends Component
{
    use WithFileUploads;
    
    public string $title;
    public string $slug;
    public bool $active = true;
    
    public $image;

    public $image_path;
    
    public $edit_id;
    public function mount(){

        if(!empty($this->edit_id)){
            $category = Category::where('id', $this->edit_id)->first();
            $this->title = $category['name'];
            $this->slug = $category['slug'];
            $this->image_path = $category['image'];
            $this->active = $category['is_active'];
        }
    }

    public function save(){

        if(empty($this->slug)){
            $this->slug = $this->title;
        }

        $path = "not set";

        if(!empty($this->image)){
            $path = $this->image->store(
                            'category',
                            'public'
                        );
        }

        if(empty($this->edit_id)){
            $this->validate([
                'title' => 'required|min:3',
                'image' => 'required',
            ]);

            Category::create([
                "name" => $this->title,
                "slug" => $this->slug,
                "image" => $path,
                "is_active" => $this->active,
            ]);

            $this->dispatch("category-updated");
            $this->dispatch("send-message", message:"Category Created Successfully");
        }else{
            $this->validate([
                'title' => 'required|min:3',
            ]);

            $path = Category::where('id', $this->edit_id)->value('image');

            if(!empty($this->image)){
                

                $path = $this->image->store(
                                'category',
                                'public'
                            );
            }

            Category::where('id', $this->edit_id)->update([
                "name" => $this->title,
                "slug" => $this->slug,
                "image" => $path,
                "is_active" => $this->active,
            ]);

            $this->reset(['title', 'slug', 'image']);
            $this->dispatch("category-updated");
            $this->dispatch("send-message", message:"Category Updated Successfully");
        }

    }

    public function cancel(){
        $this->dispatch("category-updated");
        $this->reset();
    }
};
?>

<div>
    <form wire:submit.prevent="save" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                wire:model="title"
                name="title"
                :label="__('Category Title')"
                :value="old('title')"
                type="text"
                required
                autofocus
                autocomplete="title"
                placeholder="Category Title..."
            />
            
            <flux:input
                wire:model="slug"
                name="slug"
                :label="__('Category Slug')"
                type="test"
                autocomplete="slug"
                :placeholder="__('Category Slug')"
            />
            
            <!-- Remember Me -->
            <flux:checkbox wire:model="active" name="active" :label="__('Activate')" :checked="old('active')" />
            
            @if(!empty($image_path) && empty($image))
                <img
                    src="{{asset('storage/'.$image_path) }}"
                    class="w-32 h-32 object-cover rounded"
                >
            @elseif(!empty($image))
                <img
                    src="{{ $image->temporaryUrl() }}"
                    class="w-32 h-32 object-cover rounded"
                >
            @endif

            <flux:input
                wire:model="image"
                name="image"
                :label="__('Category Image')"
                type="file"
                :placeholder="__('Category Image...')"
            />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="create-button">
                    @if(empty($edit_id))
                        {{ __('Add') }}
                    @else
                        {{__('Update')}}
                    @endif
                </flux:button>

            </div>
            <flux:button wire:click="cancel" variant="danger" type="button" class="w-full">
                {{ __('Cancel') }}
            </flux:button>
        </form>
</div>