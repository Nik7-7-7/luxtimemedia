<?php

namespace AIKit\Dependencies\Faker\Provider\bn_BD;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    public function phoneNumber()
    {
        $number = '+880';
        $number .= static::randomNumber(7);

        return Utils::getBanglaNumber($number);
    }
}
