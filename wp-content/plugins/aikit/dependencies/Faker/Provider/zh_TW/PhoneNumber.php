<?php

namespace AIKit\Dependencies\Faker\Provider\zh_TW;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    protected static $formats = [
        '+8869########',
        '+886-9##-###-###',
        '09########',
        '09##-###-###',
        '(02)########',
        '(02)####-####',
        '(0#)#######',
        '(0#)###-####',
        '(0##)######',
        '(0##)###-###',
    ];
}
