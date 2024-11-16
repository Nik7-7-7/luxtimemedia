<?php

namespace FluentBookingPro\App\Models;

use FluentBooking\App\Models\Meta;

class Webhook extends Meta
{
    protected $fillable = [
        'key',
        'value',
        'object_type',
        'object_id'
    ];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('type', function ($builder) {
            $builder->where('object_type', '=', 'calendar_event');
        });
    }

    public static function store($slot_id, $data)
    {
        return static::create([
            'object_id'   => $slot_id,
            'object_type' => 'calendar_event',
            'key'         => 'webhook_feeds',
            'value'       => $data,
        ]);
    }
}
