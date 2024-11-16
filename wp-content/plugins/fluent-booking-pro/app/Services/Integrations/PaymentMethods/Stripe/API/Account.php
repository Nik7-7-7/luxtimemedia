<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe\API;

use FluentBooking\Framework\Support\Arr;

if (!defined('ABSPATH')) {
    exit;
}

class Account
{
    use RequestProcessor;
    public static function retrive($accountId, $key)
    {
        ApiRequest::set_secret_key($key);
        $account = ApiRequest::retrieve('accounts/'.$accountId);
        return self::processResponse($account);
    }
}
