<?php

namespace AIKit\Dependencies\Faker\Provider\hu_HU;

class Company extends \AIKit\Dependencies\Faker\Provider\Company
{
    protected static $formats = [
        '{{lastName}} {{companySuffix}}',
        '{{lastName}}',
    ];

    protected static $companySuffix = ['Kft.', 'és Tsa', 'Kht', 'Zrt.', 'Nyrt.', 'Bt.'];
}
