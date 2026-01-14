<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_number',
        'status',
        'order_source',
        'subtotal',
        'delivery_charge',
        'discount',
        'total_amount',
        'cost_amount',
        'profit_amount',
        'payment_type',
        'payment_status', // Sprint 3
        'paid_amount',    // Sprint 3
        'due_amount',     // Sprint 3
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
