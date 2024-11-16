<?php

namespace AIKit\Dependencies\Faker\Provider\da_DK;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    /**
     * @var array Danish phonenumber formats.
     */
    protected static $formats = [
        '+45 ## ## ## ##',
        '+45 #### ####',
        '+45########',
        '## ## ## ##',
        '#### ####',
        '########',
    ];
}
