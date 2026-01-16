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
            'sku' => [
                'nullable', 
                'string', 
                'max:100',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('tenant_id', $this->user()->tenant_id);
                })->ignore($this->route('product')),
                // Also check against variants (excluding variants of THIS product)
                // Actually, if we change Main SKU to X, make sure X is not a variant of ANY product (including this one, unless we swap?? safer to block all)
                 Rule::unique('product_variants', 'sku')->whereNot('product_id', $this->route('product'))
                 // OR simply unique everywhere? If I accidentally use one of my own variant SKUs as Main SKU, it should fail.
            ],
            'base_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],

            // Variant Validation
            'variants' => ['nullable', 'array'],
            'variants.*.size_id' => ['nullable', 'exists:sizes,id'],
            'variants.*.color_id' => ['nullable', 'exists:colors,id'],
            'variants.*.sku' => [
                'nullable', 
                'string', 
                'distinct',
                // Unique in Variants (ignore this product's existing variants bc we will wipe them)
                Rule::unique('product_variants', 'sku')->whereNot('product_id', $this->route('product')),
                // Unique in Products (ignore this product's main sku)
                Rule::unique('products', 'sku')->ignore($this->route('product'))
            ],
            'variants.*.stock_quantity' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.extra_price' => ['nullable', 'numeric'],
        ];
    }
}
