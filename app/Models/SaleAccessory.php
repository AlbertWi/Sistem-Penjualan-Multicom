<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleAccessory extends Model
{
    protected $fillable = [
        'sale_id',
        'accessory_id',
        'purchase_accessory_id',
        'qty',
        'price',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function accessory()
    {
        return $this->belongsTo(Accessory::class);
    }
    public function purchaseAccessory()
    {
        return $this->belongsTo(\App\Models\PurchaseAccessory::class, 'purchase_accessory_id');
    }
}
