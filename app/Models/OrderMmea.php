<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMmea extends Model
{
    use HasFactory;
    protected $table = 'order_mmea';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
