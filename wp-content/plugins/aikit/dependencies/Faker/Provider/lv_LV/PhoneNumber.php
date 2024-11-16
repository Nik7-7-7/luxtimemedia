<?php

namespace AIKit\Dependencies\Faker\Provider\lv_LV;

class PhoneNumber extends \AIKit\Dependencies\Faker\Provider\PhoneNumber
{
    /**
     * {@link} https://en.wikipedia.org/wiki/Telephone_numbers_in_Latvia
     */
    protected static $formats = [
        '########',
        '## ### ###',
        '+371 ########',
    ];
}
