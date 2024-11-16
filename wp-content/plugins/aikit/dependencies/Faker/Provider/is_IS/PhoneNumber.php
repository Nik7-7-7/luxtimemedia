<?php

namespace AIKit\Dependencies\Faker\Provider\is_IS;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    /**
     * @var array Icelandic phone number formats.
     */
    protected static $formats = [
        '+354 ### ####',
        '+354 #######',
        '+354#######',
        '### ####',
        '#######',
    ];
}
