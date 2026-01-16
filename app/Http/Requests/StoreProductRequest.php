<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable', 
                'string', 
                'max:100',
                // Unique sku per tenant in products table
                Rule::unique('products')->where(function ($query) {
                    return $query->where('tenant_id', $this->user()->tenant_id);
                }),
                // Also unique in variants table
                'unique:product_variants,sku'
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
            
            // Variant Validation
            'variants' => ['nullable', 'array'],
            'variants.*.size_id' => ['nullable', 'exists:sizes,id'],
            'variants.*.color_id' => ['nullable', 'exists:colors,id'],
            'variants.*.sku' => [
                'nullable', 
                'string', 
                'distinct', 
                'unique:product_variants,sku', // unique in variants
                'unique:products,sku' // unique in products
            ], // distinct in array, unique in DB coverage
        ];
    }
}
