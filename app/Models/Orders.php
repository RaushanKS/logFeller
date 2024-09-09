<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orders extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'couponId',
        'order_id',
        'address_id',
        'total_amount',
        'pay_amount',
        'discount_amount',
        'shipping_amount',
        'transaction_id',
        'payment_type',
        'payment_status',
        'status',
    ];

    protected $dates = ['deleted_at'];
}
