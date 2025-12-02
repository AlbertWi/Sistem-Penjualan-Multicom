<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'product_id', 'imei', 'purchase_item_id', 'status','purchase_price','inventory_id'];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class,'purchase_item_id');
    }
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
    public function saleItem()
    {
        return $this->hasOne(SaleItem::class, 'inventory_item_id');
    }
    
    public function transferItems()
    {
        return $this->hasMany(StockTransferItem::class, 'inventory_item_id');
    }
}