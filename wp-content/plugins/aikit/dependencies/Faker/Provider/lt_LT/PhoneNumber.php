<?php

namespace AIKit\Dependencies\Faker\Provider\lt_LT;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    protected static $formats = [
        '86#######',
        '8 6## #####',
        '+370 6## ## ###',
        '+3706#######',
        '(8 5) ### ####',
        '+370 5 ### ####',
        '+370 46 ## ## ##',
        '(8 46) ## ## ##',
    ];
}
