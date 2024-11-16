<?php

namespace AIKit\Dependencies\Faker\Provider\es_PE;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    protected static $formats = [
        '+51 9## ### ###',
        '+51 9########',
        '9## ### ###',
        '9########',
        '+51 1## ####',
        '+51 1######',
        '1## ####',
        '1######',
    ];
}
