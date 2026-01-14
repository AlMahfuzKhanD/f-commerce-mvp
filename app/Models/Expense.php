<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'tenant_id',
        'category',
        'amount',
        'description',
        'expense_date',
        'reference_no'
    ];
}
