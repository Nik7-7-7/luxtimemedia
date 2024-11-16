<?php

namespace AmeliaBooking\Domain\ValueObjects\String;

/**
 * Class StripeAccountType
 *
 * @package AmeliaBooking\Domain\ValueObjects\String
 */
final class StripeAccountType
{
    const STANDARD = 'standard';

    const EXPRESS = 'express';

    /**
     * @var string
     */
    private $type;

    /**
     * Type constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Return the type from the value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->type;
    }
}
