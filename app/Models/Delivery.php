<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryFactory> */
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'courier_name',
        'tracking_number',
        'cod_amount',
        'delivery_status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
