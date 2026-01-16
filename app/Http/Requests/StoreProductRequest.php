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
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],

            // Variant Validation
            'variants' => ['required', 'array', 'min:1'], // variants required now
            'variants.*.size_id' => ['nullable', 'exists:sizes,id'],
            'variants.*.color_id' => ['nullable', 'exists:colors,id'],
            'variants.*.sku' => [
                'nullable', 
                'string', 
                'distinct', 
                'unique:product_variants,sku', // unique in variants
            ], 
             'variants.*.barcode' => [
                'nullable', 
                'string', 
                'distinct', 
                'unique:product_variants,barcode', 
            ],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
