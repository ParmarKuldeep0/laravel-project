<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ReviewCollection;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Review::withProduct();
            
            // Filter by product
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }
            
            // Filter by rating
            if ($request->has('min_rating')) {
                $query->where('rating', '>=', $request->min_rating);
            }
            
            if ($request->has('max_rating')) {
                $query->where('rating', '<=', $request->max_rating);
            }
            
            // Filter by date
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Search by reviewer name or comment
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('reviewer_name', 'LIKE', "%{$search}%")
                      ->orWhere('comment', 'LIKE', "%{$search}%");
                });
            }
            
            // Sort options
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['rating', 'created_at', 'reviewer_name'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            // Get only highly rated reviews
            if ($request->boolean('highly_rated')) {
                $query->highlyRated();
            }
            
            // Get recent reviews
            if ($request->has('recent_days')) {
                $query->recent($request->recent_days);
            }
            
            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $reviews = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => new ReviewCollection($reviews),
                'message' => 'Reviews retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reviews.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Store a newly created review
     */
    public function store(Request $request, $productId): JsonResponse
{
    // Use $productId from URL ✅
    
    try {
        // Validate product exists
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }
        
        // Validate review data
        $validator = Validator::make($request->all(), [
            'reviewer_name' => 'required|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check for duplicate review
        $existingReview = Review::where('product_id', $productId)
            ->where('reviewer_name', $request->reviewer_name)
            ->first();
            
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product.'
            ], 409);
        }
        
        // Create review with product_id from URL
        $review = Review::create([
            'product_id' => $productId,
            'reviewer_name' => $request->reviewer_name,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);
        
        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review->load('product')),
            'message' => 'Review created successfully.'
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create review.',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}
    
    /**
     * Display the specified review
     */
    public function show($id): JsonResponse
    {
        try {
            $review = Review::with('product')->find($id);
            
            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => new ReviewResource($review),
                'message' => 'Review retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve review.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Update the specified review
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $review = Review::find($id);
            
            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found.'
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'reviewer_name' => 'sometimes|required|string|max:100',
                'rating' => 'sometimes|required|integer|min:1|max:5',
                'comment' => 'sometimes|nullable|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $review->update($validator->validated());
            
            return response()->json([
                'success' => true,
                'data' => new ReviewResource($review->fresh()->load('product')),
                'message' => 'Review updated successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Remove the specified review
     */
    public function destroy($id): JsonResponse
    {
        try {
            $review = Review::find($id);
            
            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found.'
                ], 404);
            }
            
            $review->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get reviews for a specific product
     */
    public function productReviews($productId): JsonResponse
    {
        try {
            $product = Product::find($productId);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }
            
            $reviews = $product->reviews()
                ->with('product')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            
            $ratingStats = [
                'average_rating' => $product->averageRating(),
                'total_reviews' => $product->reviews()->count(),
                'rating_distribution' => $product->reviews()
                    ->selectRaw('rating, COUNT(*) as count')
                    ->groupBy('rating')
                    ->orderBy('rating', 'desc')
                    ->get()
                    ->pluck('count', 'rating')
                    ->toArray(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'product' => new \App\Http\Resources\ProductResource($product),
                    'reviews' => new ReviewCollection($reviews),
                    'statistics' => $ratingStats,
                ],
                'message' => 'Product reviews retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product reviews.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get recent reviews
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $days = min($request->get('days', 7), 365);
            $limit = min($request->get('limit', 10), 50);
            
            $reviews = Review::withProduct()
                ->recent($days)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => ReviewResource::collection($reviews),
                'message' => 'Recent reviews retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent reviews.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get review statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_reviews' => Review::count(),
                'average_rating' => round(Review::avg('rating') ?? 0, 2),
                'recent_reviews' => Review::where('created_at', '>=', now()->subDays(30))->count(),
                'reviews_with_comments' => Review::whereNotNull('comment')->count(),
                'top_reviewers' => Review::selectRaw('reviewer_name, COUNT(*) as review_count')
                    ->groupBy('reviewer_name')
                    ->orderBy('review_count', 'desc')
                    ->limit(10)
                    ->get(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Review statistics retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve review statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}