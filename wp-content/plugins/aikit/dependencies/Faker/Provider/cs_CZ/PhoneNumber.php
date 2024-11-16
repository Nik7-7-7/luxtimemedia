<?php

namespace AIKit\Dependencies\Faker\Provider\cs_CZ;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    protected static $formats = [
        '+420 %## ### ###',
        '%## ### ###',
        '00420%########',
        '+420%########',
        '%########',
    ];
}
