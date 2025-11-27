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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.business_name' => ['required', 'string', 'max:255'],
            'customer.city' => ['required', 'string', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:50'],
            'customer.viber' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.sold_by_package' => ['required', 'boolean'],
            'items.*.pieces_per_package' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'totals.total_items' => ['required', 'integer', 'min:1'],
            'totals.subtotal' => ['nullable', 'numeric', 'min:0'],
            'totals.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'totals.discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'totals.discount_value' => ['nullable', 'numeric', 'min:0'],
            'totals.total_amount' => ['nullable', 'numeric', 'min:0'],
            'is_paid' => ['nullable', 'boolean'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}

