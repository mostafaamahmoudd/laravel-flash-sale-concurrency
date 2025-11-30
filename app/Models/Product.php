<?php

namespace App\Models;

use App\Models\Relations\ProductRelations;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use ProductRelations;

    protected $fillable = [
        'name',
        'price',
        'stock'
    ];
}
