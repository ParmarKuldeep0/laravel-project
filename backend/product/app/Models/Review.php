<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'reviewer_name',
        'rating',
        'comment'
    ];
    
    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Relationship with Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Validation rules
     */
    public static function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'reviewer_name' => 'required|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }
    
    /**
     * Scope for highly rated reviews
     */
    public function scopeHighlyRated($query, $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }
    
    /**
     * Scope for recent reviews
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    
    /**
     * Get reviews with product information
     */
    public function scopeWithProduct($query)
    {
        return $query->with('product:id,name,price');
    }
    
    /**
     * Accessor for formatted rating
     */
    protected function formattedRating(): Attribute
    {
        return Attribute::make(
            get: fn () => str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating)
        );
    }
    
    /**
     * Check if review is recent (within 7 days)
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->created_at->gt(now()->subDays(7));
    }
}