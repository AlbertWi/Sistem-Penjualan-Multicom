<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseAccessory extends Model
{
    protected $fillable = [
        'purchase_id',
        'accessory_id',
        'qty',
        'price',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function accessory()
    {
        return $this->belongsTo(Accessory::class);
    }
}
