<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class ProductUnavailableException extends RuntimeException
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
