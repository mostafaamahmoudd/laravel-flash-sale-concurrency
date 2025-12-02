<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Hold;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(protected StockService $service)
    {
    }

    public function store(OrderRequest $request)
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {
            $hold = Hold::whereKey($validated['hold_id'])->lockForUpdate()->firstOrFail();

            if (Order::where('hold_id', $hold->id)->exists()) {
                throw ValidationException::withMessages([
                    'hold_id' => ['Hold was already used to create an order.'],
                ]);
            }

            if ($hold->status !== 'active' || $hold->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'hold_id' => ['Hold is not valid (inactive or expired).'],
                ]);
            }

            $product = $hold->product()->lockForUpdate()->firstOrFail();

            $order = Order::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'hold_id' => $hold->id,
                'qty' => $hold->qty,
                'status' => 'pending_payment',
                'total_price' => $hold->qty * $product->price,
            ]);

            $hold->update(['status' => 'used']);

            $this->service->forgetStock($product->id);

            return $order;
        });

        return OrderResource::make($order)
            ->additional([
                'message' => "Order #{$order->id} has been created!",
                'status' => 201,
            ]);
    }
}
