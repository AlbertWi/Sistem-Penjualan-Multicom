<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_notes',
        'payment_proof',
        'notes',
        'order_date',
        'paid_at',
        'cancelled_at',
        'cancellation_reason',
        'stock_picked_at',
        'stock_picked_by',
        'completed_at',
        'completed_by',
        'cancelled_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'stock_picked_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockPickedByUser()
    {
        return $this->belongsTo(User::class, 'stock_picked_by');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}