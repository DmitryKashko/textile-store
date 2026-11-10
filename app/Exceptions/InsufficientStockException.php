<?php

namespace App\Exceptions;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class InsufficientStockException extends OrderException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requested,
    ) {
        parent::__construct("Not enough stock for product #{$product->id}: requested {$requested}, available {$product->stock}.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Not enough stock.',
            'errors' => [
                'items' => [[
                    'product_id' => $this->product->id,
                    'requested' => $this->requested,
                    'available' => $this->product->stock,
                ]],
            ],
        ], 422);
    }
}
