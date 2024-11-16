<?php

namespace AIKit\Dependencies\Faker\Provider\he_IL;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    protected static $formats = [
        '05#-#######',
        '0#-#######',
        '972-5#-#######',
        '972-#-########',
        '0#########',
    ];
}
