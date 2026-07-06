<?php

use Livewire\Component;
use Livewire\Attributes\Validate;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

use App\Models\Category;

use App\Services\CloudinaryService;

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
            $upload = app(CloudinaryService::class)
                ->upload($this->image, 'easycart/categories');

            $path = $upload['secure_url'];
            $publicId = $upload['public_id'];
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
                "image_id" => $publicId,
                "is_active" => $this->active,
            ]);

            $this->dispatch("category-updated");
            $this->dispatch("send-message", message:"Category Created Successfully");
        }else{
            $this->validate([
                'title' => 'required|min:3',
            ]);

            if(!empty($this->image)){
                $image_id = Category::where('id', $this->edit_id)->value('image_id');
                app(CloudinaryService::class)
                    ->destroy($image_id);
            }

            Category::where('id', $this->edit_id)->update([
                "name" => $this->title,
                "slug" => $this->slug,
                "image" => $path,
                "image_id" => $publicId,
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
                    src="{{ $image_path }}"
                    class="w-32 h-32 object-cover rounded"
                >
            @elseif(!empty($image))
                <img
                    src="{{ $image->temporaryUrl() }}"
                    class="w-32 h-32 object-cover rounded"
                >
            @endif

            <div 
                x-data="{ isUploading: false, progress: 0 }"
                x-on:livewire-upload-start="isUploading = true; progress = 0"
                x-on:livewire-upload-finish="isUploading = false"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                class="space-y-3"
            >
                <!-- Base Flux Input -->
                <flux:input
                    wire:model="image"
                    name="image"
                    :label="__('Category Image')"
                    type="file"
                    :placeholder="__('Category Image...')"
                    accept="image/*"
                />
            
                <!-- Reactive Progress Bar Wrapper -->
                <div x-show="isUploading" x-collapse x-cloak class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-medium text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('Uploading file...') }}</span>
                        <span x-text="progress + '%'">0%</span>
                    </div>
                    
                    <!-- Progress Bar Track -->
                    <div class="w-full bg-zinc-200/60 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <!-- Progress Bar Fill (Matches Flux Accent Color) -->
                        <div 
                            class="bg-zinc-900 dark:bg-white h-1.5 rounded-full transition-all duration-150 ease-out" 
                            :style="`width: ${progress}%`"
                        ></div>
                    </div>
                </div>
            </div>
            

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