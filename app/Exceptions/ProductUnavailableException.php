<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class ProductUnavailableException extends OrderException
{
    public function __construct(public readonly int $productId)
    {
        parent::__construct("Product #{$productId} is not available for ordering.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Product is not available.',
            'errors' => ['items' => [['product_id' => $this->productId]]],
        ], 422);
    }
}
