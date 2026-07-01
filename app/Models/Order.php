<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const ORDER_STATUS_LABELS = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Terkirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'disputed' => 'Disengketakan',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Sudah Dibayar',
        'failed' => 'Gagal',
        'expired' => 'Kedaluwarsa',
        'refunded' => 'Dikembalikan',
    ];

    protected $fillable = [
        'order_number', 'buyer_id', 'farmer_id',
        'subtotal', 'shipping_cost', 'tax_amount', 'total_amount',
        'payment_status', 'order_status',
        'shipping_address', 'shipping_method', 'tracking_number',
        'shipped_at', 'delivered_at',
        'payment_method', 'payment_reference', 'payment_gateway',
        'payment_token', 'payment_checkout_url', 'payment_expires_at', 'paid_at',
        'buyer_notes', 'farmer_notes',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'shipped_at'    => 'datetime',
        'delivered_at'  => 'datetime',
        'paid_at'       => 'datetime',
        'payment_expires_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getOrderStatusLabelAttribute(): string
    {
        return self::ORDER_STATUS_LABELS[$this->order_status] ?? ucfirst((string) $this->order_status);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ucfirst((string) $this->payment_status);
    }
}
