<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class InvoiceItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'product_name',
        'quantity',
        'unit_price',
        'total',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
