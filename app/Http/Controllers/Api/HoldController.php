<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HoldRequest;
use App\Models\Hold;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HoldController extends Controller
{


    public function __construct(protected StockService $service)
    {
    }

    public function store(HoldRequest $request)
    {
        $validated = $request->validated();

        $hold = DB::transaction(function () use ($validated) {
            $product = Product::whereKey($validated['product_id'])->lockForUpdate()->firstOrFail();

            $available = $this->service->getStock($product->id);

            if ($available < $validated['qty']) {
                throw ValidationException::withMessages([
                    'qty' => ['Insufficient stock.'],
                ]);
            }

            $hold = Hold::create([
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
                'qty' => $validated['qty'],
                'status' => 'active',
                'expires_at' => now()->addMinutes(2),
            ]);

            $this->service->forgetStock($product->id);

            return $hold;
        });

        return response()->json([
            'hold_id' => $hold->id,
            'expires_at' => $hold->expires_at->toDateTimeString(),
            'message' => 'Hold created successfully.',
        ], 201);
    }
}
