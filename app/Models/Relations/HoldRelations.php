<?php

namespace App\Models\Relations;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

trait HoldRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
