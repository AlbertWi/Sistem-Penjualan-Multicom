<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEcomSetting extends Model
{
    protected $fillable = ['product_id', 'is_listed', 'ecom_price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
