<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection($this->orders->listFor($request->user()));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orders->place($request->user(), $request->toData());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        return new OrderResource($order->load('items'));
    }
}
