<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'size_id' => $this->size_id,
            'color_id' => $this->color_id,
            'size_name' => $this->size_name, // Accessor
            'color_name' => $this->color_name, // Accessor
            'stock_quantity' => $this->stock_quantity,
            'sku' => $this->sku,
            'extra_price' => (float) $this->extra_price,
        ];
    }
}
