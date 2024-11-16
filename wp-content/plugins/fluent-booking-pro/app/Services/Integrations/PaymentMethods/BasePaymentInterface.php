<?php
namespace FluentBookingPro\App\Services\Integrations\PaymentMethods;

interface BasePaymentInterface
{
    public function isEnabled(): bool;

}
