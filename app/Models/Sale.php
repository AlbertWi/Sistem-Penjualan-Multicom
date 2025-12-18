<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id','user_id', 'branch_id', 'total','status',];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function items() {
        return $this->hasMany(SaleItem::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function accessories()
    {
        return $this->hasMany(\App\Models\SaleAccessory::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    public function saleAccessories()
    {
        return $this->hasMany(SaleAccessory::class);
    }
}
