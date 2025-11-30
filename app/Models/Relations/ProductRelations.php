<?php

namespace App\Models\Relations;

use App\Models\Hold;
use App\Models\Order;

trait ProductRelations
{
    public function holds()
    {
        return $this->hasMany(Hold::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
