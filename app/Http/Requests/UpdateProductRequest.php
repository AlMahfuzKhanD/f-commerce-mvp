<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],

            // Variant Validation
            'variants' => ['required', 'array', 'min:1'], // Required because Controller assumes it
            'variants.*.size_id' => ['nullable', 'exists:sizes,id'],
            'variants.*.color_id' => ['nullable', 'exists:colors,id'],
            'variants.*.sku' => [
                'nullable', 
                'string', 
                'distinct',
                // Unique in Variants (ignore this product's existing variants bc we will wipe them)
                Rule::unique('product_variants', 'sku')->whereNot('product_id', $this->route('product')),
            ],
            'variants.*.barcode' => [
                'nullable', 
                'string', 
                'distinct',
                Rule::unique('product_variants', 'barcode')->whereNot('product_id', $this->route('product')),
            ],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.price' => ['nullable', 'numeric'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
