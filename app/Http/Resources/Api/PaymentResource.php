<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order = $this->resource['order'];
        $event = $this->resource['event'];
        $deduped = $this->resource['deduped'];

        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'idempotency_key' => $event->idempotency_key,
            'deduped' => $deduped,
        ];
    }
}
