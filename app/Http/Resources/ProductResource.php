<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Determine aggregate values
        $variants = $this->whenLoaded('variants', function () {
            return $this->variants;
        }, collect()); // Ensure $variants is a collection even if not loaded

        $firstVariant = $variants->first();
        $totalStock = $variants->sum('stock_quantity');
        
        // Price: For now, if multiple, show range or min. Let's show min price.
        $minPrice = $variants->min('price');
        
        // SKU/Barcode: if Single variant, show it. Else show null/Multiple?
        // Frontend expects a string.
        $sku = $variants->count() === 1 ? $firstVariant->sku : 'VARIANTS';
        $barcode = $variants->count() === 1 ? $firstVariant->barcode : '';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $sku,
            'barcode' => $barcode,
            'base_price' => (float) $minPrice, // mapping to 'base_price' for frontend compatibility
            'cost_price' => (float) ($firstVariant ? $firstVariant->cost_price : 0),
            'stock_quantity' => $totalStock,
            'is_active' => (bool) $this->is_active,
            'category_id' => $this->category_id, // Retained from original
            'description' => $this->description, // Retained from original
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
