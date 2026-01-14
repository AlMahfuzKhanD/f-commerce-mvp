<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Http\Responses\ApiResponse; // Assuming we have this
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('variants');

        // Simple Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
        }

        // Active filter (default true unless specified)
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $products = $query->latest()->paginate($request->input('per_page', 15));

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('variants')->findOrFail($id);
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete(); // Soft delete
        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
