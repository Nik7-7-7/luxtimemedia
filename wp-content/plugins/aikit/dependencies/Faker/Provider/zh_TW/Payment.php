<?php

namespace AIKit\Dependencies\Faker\Provider\zh_TW;

/**
 * @deprecated Use {@link \AIKit\Dependencies\Faker\Provider\Payment} instead
 * @see \AIKit\Dependencies\Faker\Provider\Payment
 */
class Payment extends \AIKit\Dependencies\Faker\Provider\Payment
{
    /**
     * @return array
     *
     * @deprecated Use {@link \AIKit\Dependencies\Faker\Provider\Payment::creditCardDetails()} instead
     * @see \AIKit\Dependencies\Faker\Provider\Payment::creditCardDetails()
     */
    public function creditCardDetails($valid = true)
    {
        return parent::creditCardDetails($valid);
    }
}
