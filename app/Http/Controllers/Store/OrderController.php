<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\PlaceOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->orderService->placeCodOrder(
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'country' => $validated['country'],
                'notes' => $validated['notes'] ?? null,
            ],
            $validated['items'],
        );

        return response()->json([
            'message' => 'Order placed successfully.',
            'order_number' => $result['order_number'],
            'total' => (float) $result['order']->total,
        ], 201);
    }
}
