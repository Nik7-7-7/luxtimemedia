<?php

namespace AIKit\Dependencies\Faker\Provider\ka_GE;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    protected static $formats = [
        '+995 ### ## ## ##',
        '### ## ## ##',
        '#########',
        '(###) ## ## ##',
        '+995(##)#######',
    ];
}
