<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Services\Catalog\CatalogService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly CatalogService $catalog)
    {
    }

    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection($this->catalog->search($request->toFilter()));
    }

    public function show(string $slug): ProductResource
    {
        $product = $this->catalog->findBySlug($slug) ?? abort(404);

        return new ProductResource($product);
    }
}
