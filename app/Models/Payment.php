<?php

namespace App\Models;

use App\Models\Relations\PaymentRelations;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use PaymentRelations;

    protected $fillable = [
        'order_id',
        'idempotency_key',
        'payload',
        'status',
    ];
}
