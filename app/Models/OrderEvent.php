<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    /** @use HasFactory<\Database\Factories\OrderEventFactory> */
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'event_type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
