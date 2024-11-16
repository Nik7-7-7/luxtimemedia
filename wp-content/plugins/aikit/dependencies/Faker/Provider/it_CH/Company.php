<?php

namespace AIKit\Dependencies\Faker\Provider\it_CH;

class Company extends \AIKit\Dependencies\Faker\Provider\Company
{
    protected static $formats = [
        '{{lastName}} {{companySuffix}}',
        '{{lastName}} {{lastName}} {{companySuffix}}',
        '{{lastName}}',
        '{{lastName}}',
    ];

    protected static $companySuffix = ['SA', 'Sarl'];
}
