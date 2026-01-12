<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'address', 'branch_type'];

    // Konstanta untuk tipe cabang
    const TYPE_ONLINE = 'online';
    const TYPE_OFFLINE = 'offline';

    // Array untuk validasi atau dropdown
    public static function getBranchTypes()
    {
        return [
            self::TYPE_ONLINE => 'Online',
            self::TYPE_OFFLINE => 'Offline'
        ];
    }

    public function users() {
        return $this->hasOne(User::class);
    }

    public function inventoryItems() {
        return $this->hasMany(InventoryItem::class);
    }

    // Scope untuk query
    public function scopeOnline($query)
    {
        return $query->where('branch_type', self::TYPE_ONLINE);
    }

    public function scopeOffline($query)
    {
        return $query->where('branch_type', self::TYPE_OFFLINE);
    }

    // Helper method
    public function isOnline()
    {
        return $this->branch_type === self::TYPE_ONLINE;
    }

    public function isOffline()
    {
        return $this->branch_type === self::TYPE_OFFLINE;
    }
    public function getNameWithTypeAttribute()
    {
        $name = $this->name;
        if ($this->isOnline()) {
            $name .= ' (Online)';
        }
        return $name;
    }
}