<?php

namespace AIKit\Dependencies\Faker\Provider\is_IS;

class Internet extends \AIKit\Dependencies\Faker\Provider\Internet
{
    /**
     * @var array Some email domains in Denmark.
     */
    protected static $freeEmailDomain = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'visir.is', 'simnet.is', 'internet.is',
    ];

    /**
     * @var array Some TLD.
     */
    protected static $tld = [
        'com', 'com', 'com', 'net', 'is', 'is', 'is',
    ];
}
