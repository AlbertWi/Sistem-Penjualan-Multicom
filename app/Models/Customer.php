<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password'
    ];
    protected $hidden = [
        'password',
    ];
    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }
}
