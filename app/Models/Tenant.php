<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'currency',
        'timezone',
        'address', // Sprint 5
        'phone',   // Sprint 5
        'logo',    // Sprint 5
        'plan_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
