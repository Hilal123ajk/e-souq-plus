<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function index(): View
    {
        $orders = Order::query()
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $order): array => $order->toAdminArray())
            ->values()
            ->all();

        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $status = $request->validated('status');

        $order->update(['status' => $status]);

        $this->activityLog->log('updated', 'order', $order->id, $order->order_number, "Status changed to {$status}");

        return response()->json([
            'message' => 'Order status updated.',
            'order' => $order->fresh(['items.product'])->toAdminArray(),
        ]);
    }
}
