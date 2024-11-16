<?php

namespace FluentBookingPro\App\Models;

class Transactions extends Model
{
    protected $table = 'fcal_transactions';

    protected $guarded = ['id'];

    protected $fillable = [
        'object_id',
        'object_type',
        'transaction_type',
        'subscription_id',
        'card_last_4',
        'card_brand',
        'vendor_charge_id',
        'payment_method',
        'payment_method_type',
        'status',
        'total',
        'rate',
        'uuid',
        'meta',
        'updated_at'
    ];

}