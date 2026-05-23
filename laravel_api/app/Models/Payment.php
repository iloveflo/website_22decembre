<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'transaction_id',
        'bank_code',
        'amount',
        'payment_method',
        'status',
        'payload'
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    // Ngắt updated_at vì bảng này chỉ lưu log, không update
    public $timestamps = false;
    
    // Khai báo created_at thủ công
    const CREATED_AT = 'created_at';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
