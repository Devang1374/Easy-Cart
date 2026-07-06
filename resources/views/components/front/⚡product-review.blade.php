<?php

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\Review;
use App\Models\Product;
use App\Models\OrderItems;

use App\Services\CloudinaryService;

new class extends Component
{
    use WithFileUploads;

    public Product $product;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function getReviewsProperty()
    {
        return $this->product
            ->approvedReviews()
            ->with(['user', 'images'])
            ->latest()
            ->get();
    }

    public function getAverageRatingProperty()
    {
        return $this->product->averageRating();
    }

    public function getTotalReviewsProperty()
    {
        return $this->product->totalReviews();
    }

    public function getCanReviewProperty()
    {
        return $this->product->canBeReviewedBy(auth()->user());
    }

    public function getUserReviewProperty()
    {
        return $this->product->userReview(auth()->user());
    }

    public function getRatingBreakdownProperty()
    {
        return $this->product->ratingBreakdown();
    }

    public function ratingPercentage($stars)
    {
        if ($this->totalReviews == 0) {
            return 0;
        }

        return round(
            (($this->ratingBreakdown[$stars] ?? 0) / $this->totalReviews) * 100
        );
    }

    public $images = [];
    public int $rating = 0;
    public string $title = '';
    public string $comment = '';
    public bool $showReviewForm = false;
    public function toggleReviewForm()
    {
        if (! $this->canReview) {
            return;
        }

        $this->showReviewForm = ! $this->showReviewForm;
    }

    public function submitReview()
    {
        if (! $this->canReview) {
            return;
        }

        $this->validate();

        $review = Review::create([
            'product_id' => $this->product->id,
            'user_id' => auth()->id(),

            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,

            // Auto approve for now
            'is_approved' => true,
        ]);

        if (!empty($this->images)) {

            foreach ($this->images as $image) {
            $upload = app(CloudinaryService::class)
                ->upload($image, 'easycart/reviews');

            $path = $upload['secure_url'];
            $publicId = $upload['public_id'];

                $review->images()->create([
                    'image' => $path,
                    'image_id' => $publicId,
                ]);

            }

        }

        $this->reset([
            'rating',
            'title',
            'comment',
            'images',
            'showReviewForm',
        ]);

        $this->product->refresh();

        Flux::toast(
            heading: 'Thank you!',
            text: 'Your review has been submitted.'
        );
    }

    protected function rules()
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|max:255',
            'comment' => 'required|min:20|max:2000',
            'images.*' => 'nullable|image|max:2048',
        ];
    }

    public int $hoverRating = 0;
};
?>

<div>
    <section class="rounded-3xl border border-zinc-200 bg-white p-8 dark:border-zinc-800 dark:bg-zinc-900">

        <div class="grid gap-10 lg:grid-cols-2">

            {{-- Left --}}
            <div>

                <h2 class="text-3xl font-bold">
                    Customer Reviews ({{ $this->totalReviews }})
                </h2>

                <div class="mt-6 flex items-end gap-4">

                    <span class="text-6xl font-black">
                        {{ $this->averageRating }}
                    </span>

                    <div>

                        <div class="text-yellow-500 text-2xl">
                            ★★★★★
                        </div>

                        <p class="text-zinc-500">
                            Based on {{ $this->totalReviews }} reviews
                        </p>

                    </div>

                </div>

            </div>

            {{-- Right --}}
            <div class="space-y-4">

                @for($i = 5; $i >= 1; $i--)

                    <div class="flex items-center gap-4">

                        <span class="w-8 text-sm font-medium">
                            {{ $i }}★
                        </span>

                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">

                            <div
                                class="h-full rounded-full bg-yellow-400"
                                style="width: {{ $this->ratingPercentage($i) }}%;"
                            ></div>

                        </div>

                        <span class="w-8 text-right text-sm text-zinc-500">
                            {{ $this->ratingBreakdown[$i] ?? 0 }}
                        </span>

                    </div>

                @endfor

            </div>

        </div>

        @if($this->canReview)
            <div class="mt-10 rounded-3xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>

                        <h3 class="text-xl font-bold">
                            Share your experience
                        </h3>

                        <p class="mt-1 text-zinc-500">
                            Tell other customers what you think.
                        </p>

                    </div>

                    <flux:button
                        wire:click="toggleReviewForm"
                        variant="primary"
                    >
                        {{ $showReviewForm ? 'Close' : 'Write Review' }}
                    </flux:button>

                </div>

            </div>

        @endif

        @if($showReviewForm)

            <div class="mt-6 rounded-3xl border border-zinc-200 bg-white p-8 dark:border-zinc-800 dark:bg-zinc-900">
            
                <form wire:submit="submitReview" class="space-y-6">

                    {{-- Rating --}}
                    <div>

                        <label class="mb-2 block font-medium">
                            Rating
                        </label>

                <div
                    class="flex gap-1 text-4xl"
                    x-data="{ hover: @entangle('hoverRating') }"
                >

                    @for($i = 1; $i <= 5; $i++)

                        <button
                            type="button"
                            wire:click="$set('rating', {{ $i }})"
                            x-on:mouseenter="hover={{ $i }}"
                            x-on:mouseleave="hover=0"
                            class="transition duration-200 hover:scale-125"
                            :class="(hover >= {{ $i }} || {{ $rating }} >= {{ $i }})
                                ? 'text-yellow-400'
                                : 'text-zinc-300'"
                        >
                            ★
                        </button>

                    @endfor

                </div>

                        @error('rating')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror

                    </div>

                    <flux:input
                        wire:model="title"
                        label="Review Title"
                        placeholder="Amazing product"
                    />

                    <flux:textarea
                        wire:model="comment"
                        label="Your Review"
                        rows="5"
                    />

                    <flux:input
                        wire:model="images"
                        type="file"
                        multiple
                        label="Photos (optional)"
                    />

                    @if($images)

                        <div class="flex flex-wrap gap-3">

                            @foreach($images as $image)

                                <img
                                    src="{{ $image->temporaryUrl() }}"
                                    class="h-24 w-24 rounded-xl object-cover"
                                >

                            @endforeach

                        </div>

                    @endif

                    <flux:button
                        type="submit"
                        variant="primary"
                    >
                        Submit Review
                    </flux:button>

                </form>
            
            </div>
            
        @endif

        <div class="mt-12 space-y-6">

            @forelse($this->reviews as $review)

                <div class="rounded-3xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

                    <div class="flex items-start justify-between">

                        <div>

                            <div class="flex flex-wrap items-center gap-2">

                                <h4 class="font-bold">
                                    {{ $review->user->name }}
                                </h4>

                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    ✓ Verified Purchase
                                </span>

                            </div>

                            <div class="mt-1 flex items-center gap-2">

                                <div class="text-yellow-400">

                                    @for($i = 1; $i <= 5; $i++)

                                        {{ $i <= $review->rating ? '★' : '☆' }}

                                    @endfor

                                </div>

                                <span class="text-sm text-zinc-500">
                                    {{ $review->created_at->diffForHumans() }}
                                </span>

                            </div>

                        </div>

                    </div>

                    <h3 class="mt-4 text-lg font-semibold">
                        {{ $review->title }}
                    </h3>

                    <p class="mt-3 whitespace-pre-line text-zinc-600 dark:text-zinc-300">
                        {{ $review->comment }}
                    </p>

                    @if($review->images->count())

                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">

                            @foreach($review->images as $image)

                                <img
                                    src="{{ $image->image }}"
                                    class="aspect-square w-full cursor-pointer rounded-2xl object-cover transition duration-300 hover:scale-105 hover:shadow-lg"
                                >

                            @endforeach

                        </div>

                    @endif

                </div>

            @empty

                <div class="rounded-3xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">

                    <div class="text-6xl">
                        ⭐
                    </div>

                    <h3 class="mt-4 text-xl font-bold">
                        No reviews yet
                    </h3>

                    <p class="mt-2 text-zinc-500">
                        Be the first customer to review this product.
                    </p>

                </div>

            @endforelse

        </div>
    </section>
</div>