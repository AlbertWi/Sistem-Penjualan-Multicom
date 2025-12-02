<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{

    protected $fillable = ['name'];

    public function prices()
    {
        return $this->hasMany(AccessoryBranchPrice::class);
    }
    public function inventories()
    {
        return $this->hasMany(AccessoryInventory::class);
    }
}
