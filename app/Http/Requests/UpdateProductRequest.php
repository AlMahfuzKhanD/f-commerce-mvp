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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => [
                'nullable', 
                'string', 
                'max:100',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('tenant_id', $this->user()->tenant_id);
                })->ignore($this->route('product')) // Ignore current product id
            ],
            'base_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
