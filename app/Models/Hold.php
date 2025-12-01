<?php

namespace App\Models;

use App\Models\Relations\HoldRelations;
use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    use HoldRelations;

    protected $fillable = [
        'user_id',
        'product_id',
        'qty',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
