<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class StockService
{
    public function getStock(int $productId): int
    {
        $key = "product:{$productId}:available_stock";

        return Cache::remember($key, now()->addSeconds(10), function () use ($productId) {
            $product = Product::findOrFail($productId);

            $activeHoldsQty = $product->holds()
                ->where('status', 'active')
                ->where('expires_at', '>=', now())
                ->sum('qty');

            $paidOrderQty = $product->orders()
                ->where('status', 'paid')
                ->sum('qty');

            $available = (int) ($product->stock - $activeHoldsQty - $paidOrderQty);

            return max(0, $available);
        });
    }

    public function forgetStock(int $productId): void
    {
        Cache::forget("product:{$productId}:available_stock");
    }
}
