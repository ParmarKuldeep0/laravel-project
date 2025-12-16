<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'image'];
    
    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get all reviews for the product
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    
    /**
     * Calculate average rating for the product
     */
    public function averageRating(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 2);
    }
    
    /**
     * Get top-rated products
     */
    public function scopeTopRated($query, $minReviews = 5)
    {
        return $query->withAvg('reviews', 'rating')
                    ->having('reviews_avg_rating', '>=', 4.0)
                    ->havingRaw('COUNT(reviews.id) >= ?', [$minReviews]);
    }
    
    /**
     * Get products within price range
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
    
    public function scopeImageRange($query){
        return $query->whereNotNull('image');
    }
    
    /**
     * Validation rules
     */
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:9999999.99',
            'image' => 'nullable|string|max:500'
        ];
    }
    
    /**
     * Format price with currency symbol (Accessor)
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format((float) $this->attributes['price'] ?? 0, 2);
    }

    /**
     * Get image URL (Accessor)
     */
    public function getImageUrlAttribute(): ?string
    {
        $image = $this->attributes['image'] ?? null;
        
        if (!$image) {
            return null;
        }
        
        // If image already has full URL or is external
        if (str_starts_with($image, 'http')) {
            return $image;
        }
        
        // If image starts with 'storage/', use asset
        if (str_starts_with($image, 'storage/')) {
            return asset($image);
        }
        
        // If image is in public/images/products
        if (str_starts_with($image, 'images/products/')) {
            return asset($image);
        }
        
        // Default: assume it's in public/images/products
        return asset('images/products/' . $image);
    }
    
    /**
     * Check if product has image
     */
    public function hasImage(): bool
    {
        return !empty($this->attributes['image'] ?? null);
    }
}