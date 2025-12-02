<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessoryBranchPrice extends Model
{
    use HasFactory;

    protected $fillable = ['accessory_id', 'branch_id', 'price'];

    public function accessory()
    {
        return $this->belongsTo(Accessory::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
