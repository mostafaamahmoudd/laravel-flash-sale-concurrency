<?php

namespace Tests\Feature;

use App\Models\Hold;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\ParallelTesting;
use Tests\TestCase;

class FlashSaleFeatureTest extends TestCase
{
    public function test_product_endpoint_returns_available_stock()
    {
        $product = Product::create(['stock' => 10]);

        Hold::create([
            'product_id' => $product->id,
            'qty' => 3,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.available_stock', 7);
    }

    public function test_parallel_holds_never_exceed_stock()
    {
        $product = Product::create(['stock' => 10]);

        $requests = 20;

        $this->runInParallel(function () use ($product) {
            $this->postJson('/api/holds', [
                'product_id' => $product->id,
                'qty' => 1,
            ]);
        }, $requests);

        $totalHeld = Hold::where('product_id', $product->id)
            ->where('status', 'active')
            ->sum('qty');

        $this->assertLessThanOrEqual(10, $totalHeld);
    }

    protected function runInParallel(callable $callback, int $times): void
    {
        ParallelTesting::setUpProcess(function () use ($callback, $times) {
            for ($i = 0; $i < $times; $i++) {
                $callback();
            }
        });
    }

    public function test_expiry_command_expires_holds_and_updates_stock()
    {
        $product = Product::create(['stock' => 10]);

        $hold = Hold::create([
            'product_id' => $product->id,
            'qty'        => 4,
            'status'     => 'active',
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('holds:expire')->assertExitCode(0);

        $hold->refresh();
        $this->assertEquals('expired', $hold->status);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.available_stock', 10);
    }

    public function test_payment_webhook_is_idempotent()
    {
        $order = Order::create(['status' => 'pending_payment']);

        $payload = [
            'order_id'        => $order->id,
            'idempotency_key' => 'test-key-123',
            'status'          => 'success',
        ];

        $first = $this->postJson('/api/payments/webhook', $payload)
            ->assertOk()
            ->json();

        $this->assertEquals('paid', $first['status']);
        $this->assertFalse($first['deduped']);

        $second = $this->postJson('/api/payments/webhook', $payload)
            ->assertOk()
            ->json();

        $this->assertEquals('paid', $second['status']);
        $this->assertTrue($second['deduped']);

        $this->assertEquals(1, Payment::where('idempotency_key', 'test-key-123')->count());
    }
}
