<?php

// app/Models/InventoryAccessory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAccessory extends Model
{
    protected $fillable = ['accessory_id', 'branch_id', 'qty'];

    public function accessory()
    {
        return $this->belongsTo(Accessory::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
