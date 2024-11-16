<?php

namespace FluentBookingPro\App\Models;

class OrderItems extends Model
{
    protected $table = 'fcal_order_items';

    protected $guarded = ['id'];
    
    protected $fillable = [
        'order_id',
        'booking_id',
        'item_name',
        'quantity',
        'item_price',
        'item_total',
        'rate',
        'line_meta',
        'updated_at'
    ];
}