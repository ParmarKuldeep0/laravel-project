<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price, // Accessor
            'image' => $this->image_url, // Accessor
            'average_rating' => $this->when(
                $this->relationLoaded('reviews'),
                fn() => $this->averageRating()
            ),
            'reviews_count' => $this->when(
                $this->relationLoaded('reviews'),
                fn() => $this->reviews()->count()
            ),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}