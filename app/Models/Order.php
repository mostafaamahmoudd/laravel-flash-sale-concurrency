<?php

namespace App\Models;

use App\Models\Relations\OrderRelations;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use OrderRelations;

    protected $fillable = [
        'user_id',
        'product_id',
        'hold_id',
        'qty',
        'status',
        'total_price',
    ];
}
