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
        'plan_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
