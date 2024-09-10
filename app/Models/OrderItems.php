<?php

namespace App\Models;

use App\Models\Products;
use App\Models\ProductVariant;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItems extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'variation_id',
        'quantity',
        'status',
        'order_number',
        'sale_price',
    ];

    protected $dates = ['deleted_at'];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariant::class, 'variation_id');
    }

}
