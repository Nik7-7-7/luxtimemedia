<?php

namespace AIKit\Dependencies\Faker\Provider\de_CH;

class Company extends \AIKit\Dependencies\Faker\Provider\Company
{
    protected static $formats = [
        '{{lastName}} {{companySuffix}}',
        '{{lastName}} {{lastName}} {{companySuffix}}',
        '{{lastName}}',
        '{{lastName}}',
    ];

    protected static $companySuffix = ['AG', 'GmbH'];
}
