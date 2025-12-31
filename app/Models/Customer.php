<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    
    protected $guard = 'customer';
    protected $fillable = [
        'name',
        'phone',
        'email',
        'jenis_kelamin',
        'tanggal_lahir',
        'password',
    ];
    protected $hidden = [
        'password',
    ];
    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }
}
