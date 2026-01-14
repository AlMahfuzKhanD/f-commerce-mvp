<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Payment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'amount',
        'payment_method',
        'paid_at',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
