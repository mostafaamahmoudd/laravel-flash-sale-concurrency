<?php

namespace App\Models\Relations;

use App\Models\Order;

trait PaymentRelations
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
