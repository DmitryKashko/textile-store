<?php

namespace App\Http\Requests;

use App\Data\ProductFilter;
use App\Enums\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'material' => ['nullable', Rule::enum(Material::class)],
            'color' => ['nullable', 'string', 'max:32'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'price_to' => ['nullable', 'integer', 'min:0', Rule::when($this->filled('price_from'), 'gte:price_from')],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toFilter(): ProductFilter
    {
        return ProductFilter::fromArray($this->validated());
    }
}
