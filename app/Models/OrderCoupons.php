<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderCoupons extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'order_coupons';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'couponId',
        'name',
        'discount_type',
        'max_discount',
        'min_order_amount',
        'discount_amount',
        'discount_percent',
        'description',
        'start_date',
        'end_date',
    ];

    protected $dates = ['deleted_at'];
}
