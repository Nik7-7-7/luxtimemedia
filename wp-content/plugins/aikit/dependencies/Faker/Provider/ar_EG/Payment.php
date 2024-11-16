<?php

namespace AIKit\Dependencies\Faker\Provider\ar_EG;

class Payment extends \AIKit\Dependencies\Faker\Provider\Payment
{
    /**
     * International Bank Account Number (IBAN)
     *
     * @see https://www.upiqrcode.com/iban-generator/eg/egypt
     */
    public function bankAccountNumber(): string
    {
        return self::iban('EG', '', 25);
    }
}
