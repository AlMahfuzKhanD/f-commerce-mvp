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
     * Scan product by barcode.
     */
    public function scan(Request $request)
    {
        $query = $request->input('barcode');
        if (!$query) return response()->json(['message' => 'Query required'], 400);

        // Search Variants matching Barcode or SKU or Product Name
        // We want to return specific VARIANTS that match.
        
        $variants = \App\Models\ProductVariant::with('product')
            ->where(function($q) use ($query) {
                $q->where('barcode', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhereHas('product', function($pq) use ($query) {
                      $pq->where('name', 'like', "%{$query}%");
                  });
            })
            ->take(10) // Limit results for autocomplete
            ->get();

        if ($variants->isEmpty()) {
             return response()->json(['data' => [], 'message' => 'No products found'], 200);
        }

        // Map to a useful structure
        $data = $variants->map(function($v) {
             return [
                'product_id' => $v->product_id,
                'product_variant_id' => $v->id,
                'name' => $v->product->name,
                'variant_label' => $v->size_name || $v->color_name 
                                    ? "({$v->size_name} / {$v->color_name})" 
                                    : "",
                'sku' => $v->sku,
                'barcode' => $v->barcode,
                'price' => (float) $v->price,
                'stock' => $v->stock_quantity
             ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('variants');

        // Simple Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('variants', function($subQ) use ($search) {
                      $subQ->where('sku', 'like', "%{$search}%")
                           ->orWhere('barcode', 'like', "%{$search}%");
                  });
            });
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
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $data = $request->validated();
            // Create Product (only common fields)
            $product = Product::create(\Illuminate\Support\Arr::only($data, ['name', 'description', 'category_id', 'is_active', 'tenant_id']));

            // Create Variants
            if ($request->has('variants') && count($data['variants']) > 0) {
                // Front-end sends variants with price/quantity/sku/barcode
                // Make sure they handle 'price' correctly (frontend might send extra_price, need to check Request)
                $product->variants()->createMany($data['variants']);
            } else {
                // Create Default Variant from Root Fields
                // The root fields (sku, price, etc) passed in request need to be saved as a variant
                $product->variants()->create([
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'price' => $data['base_price'] ?? 0, // Frontend sends base_price
                    'cost_price' => $data['cost_price'] ?? 0,
                    'stock_quantity' => $data['stock_quantity'] ?? 0,
                ]);
            }

            return new ProductResource($product->load('variants'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['variants', 'category'])->findOrFail($id);
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateProductRequest $request, string $id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
            $product = Product::findOrFail($id);
            $data = $request->validated();

            $product->update(\Illuminate\Support\Arr::only($data, ['name', 'description', 'category_id', 'is_active']));

            // Handle Variants
            // Strategy: Delete all and recreate is easiest for MVP compliance with new structure
            // But if we want to preserve IDs...
            // Let's stick to "Delete All and Recreate" for consistency as before, 
            // BUT we must handle the "Simple to Variable" or "Variable to Simple" switch.
            
            $product->variants()->delete(); // Hard or Soft delete

            if ($request->has('variants') && count($data['variants']) > 0) {
                $product->variants()->createMany($data['variants']);
            } else {
                 // Recreate Default Variant
                $product->variants()->create([
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'price' => $data['base_price'] ?? 0,
                    'cost_price' => $data['cost_price'] ?? 0,
                    'stock_quantity' => $data['stock_quantity'] ?? 0,
                ]);
            }

            return new ProductResource($product->load('variants'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->orderItems()->exists()) {
            return response()->json(['message' => 'Cannot delete product. It has been used in orders.'], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($product) {
            // Manually delete variants to ensure events/logic runs if any (though DB cascade exists)
            $product->variants()->delete(); 
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully.']);
        });
    }
}
