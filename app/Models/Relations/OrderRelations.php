<?php

namespace App\Models\Relations;

use App\Models\Hold;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;

trait OrderRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hold()
    {
        return $this->belongsTo(Hold::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
