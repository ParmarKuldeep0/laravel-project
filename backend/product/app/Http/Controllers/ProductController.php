<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::query();
            
            // Search by name or description
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }
            
            // Filter by price range
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
            
            // Sort options
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['name', 'price', 'created_at', 'average_rating'])) {
                if ($sortBy === 'average_rating') {
                    $query->withAvg('reviews', 'rating')
                          ->orderBy('reviews_avg_rating', $sortOrder);
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
            }
            
            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $products = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => new ProductCollection($products),
                'message' => 'Products retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Store a newly created product
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), Product::rules());
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $product = Product::create($validator->validated());
             if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                                    ->store('products', 'public');
        }
            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
                'message' => 'Product created successfully.'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Display the specified product
     */
    public function show($id): JsonResponse
    {
        try {
            $product = Product::with('reviews')->find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
                'message' => 'Product retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Update the specified product
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string',
                'price' => 'sometimes|required|numeric|min:0|max:9999999.99',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $product->update($validator->validated());
            
            return response()->json([
                'success' => true,
                'data' => new ProductResource($product->fresh()),
                'message' => 'Product updated successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Remove the specified product
     */
    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }
            
            // Check if product has reviews
            if ($product->reviews()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing reviews.'
                ], 400);
            }
            
            $product->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get top-rated products
     */
    public function topRated(Request $request): JsonResponse
    {
        try {
            $minReviews = $request->get('min_reviews', 3);
            $limit = min($request->get('limit', 10), 50);
            
            $products = Product::withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->having('reviews_avg_rating', '>=', 4.0)
                ->having('reviews_count', '>=', $minReviews)
                ->orderBy('reviews_avg_rating', 'desc')
                ->limit($limit)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'message' => 'Top-rated products retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top-rated products.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get product statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'average_price' => round(Product::avg('price') ?? 0, 2),
                'highest_price' => round(Product::max('price') ?? 0, 2),
                'lowest_price' => round(Product::min('price') ?? 0, 2),
                'recent_products' => Product::where('created_at', '>=', now()->subDays(30))->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}