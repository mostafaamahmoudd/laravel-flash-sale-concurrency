<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    public function __construct(protected StockService $service)
    {
    }

    public function handle(PaymentRequest $request)
    {
        $validated = $request->validated();
        $payload = $request->all();

        $result = DB::transaction(function () use ($validated, $payload) {
            $existing = Payment::where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if ($existing) {
                Log::info('Payment webhook deduped', [
                    'idempotency_key' => $validated['idempotency_key'],
                    'order_id' => $existing->order_id,
                ]);

                return [
                    'order' => $existing->order()->first(),
                    'event' => $existing,
                    'deduped' => true,
                ];
            }

            $order = Order::whereKey($validated['order_id'])->lockForUpdate()->firstOrFail();

            $event = Payment::create([
                'order_id' => $order->id,
                'idempotency_key' => $validated['idempotency_key'],
                'status' => $validated['status'],
                'payload' => $payload,
            ]);

            if (!in_array($order->status, ['paid', 'cancelled'], true)) {
                if ($validated['status'] === 'success') {
                    $order->update(['status' => 'paid']);

                    $this->service->forgetStock($order->product_id);
                } elseif ($validated['status'] === 'failure') {
                    $order->update(['status' => 'cancelled']);

                    $this->service->forgetStock($order->product_id);
                }
            }

            return [
                'order' => $order->fresh(),
                'event' => $event,
                'deduped' => false,
            ];
        });

        return PaymentResource::make($result)
            ->additional([
                'message' => 'Payment webhook deduped',
                'status' => 200,
            ]);
    }
}
