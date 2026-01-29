<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,id'],
            'order_source' => ['required', 'string', 'in:manual,facebook,whatsapp,phone'],
            'status' => ['nullable', 'string', 'in:new,pending,confirmed,shipped,delivered,cancelled,returned,draft'],
            'order_date' => ['nullable', 'date'],
            
            // Financials
            'delivery_charge' => ['numeric', 'min:0'],
            'discount' => ['numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'shipping_address' => ['nullable', 'string'],
            'shipping_phone' => ['nullable', 'string'],
            
            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
