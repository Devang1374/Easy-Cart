<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\orderItems;

class product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'is_active',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(productImage::class)
                ->orderBy('sort_order');
    }

    public function orderItems()
    {
        return $this->hasMany(orderItems::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)
            ->where('is_approved', true);
    }

    public function averageRating()
    {
        return round(
            $this->approvedReviews()->avg('rating') ?? 0,
            1
        );
    }

    public function totalReviews()
    {
        return $this->approvedReviews()->count();
    }

    public function ratingCount($stars)
    {
        return $this->approvedReviews()
            ->where('rating', $stars)
            ->count();
    }

    public function canBeReviewedBy($user)
    {
        if (!$user) {
            return false;
        }

        $alreadyReviewed = $this->reviews()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return false;
        }

        return orderItems::where('product_id', $this->id)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'Delivered');
            })
            ->exists();
    }

    public function userReview($user)
    {
        if (!$user) {
            return null;
        }

        return $this->reviews()
            ->where('user_id', $user->id)
            ->first();
    }

    public function ratingBreakdown()
    {
        return $this->approvedReviews()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');
    }
}
