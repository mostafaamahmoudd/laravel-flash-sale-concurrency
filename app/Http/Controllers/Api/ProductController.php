<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use App\Services\StockService;

class ProductController extends Controller
{
    public function __construct(protected StockService $service)
    {
    }

    public function show(Product $product)
    {
        $available = $this->service->getStock($product->id);

        return ProductResource::make($product)->additional([
            'available' => $available
        ]);
    }
}
