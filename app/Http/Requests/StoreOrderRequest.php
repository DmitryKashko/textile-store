<?php

namespace App\Http\Requests;

use App\Data\PlaceOrderData;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'between:1,1000'],
        ];
    }

    public function toData(): PlaceOrderData
    {
        return PlaceOrderData::fromArray($this->validated('items'));
    }
}
