<?php

namespace AIKit\Dependencies\Faker\Calculator;

/**
 * @deprecated moved to tr_TR\Person, use {@link \AIKit\Dependencies\Faker\Provider\tr_TR\Person}.
 * @see \AIKit\Dependencies\Faker\Provider\tr_TR\Person
 */
class TCNo
{
    /**
     * Generates Turkish Identity Number Checksum
     * Gets first 9 digit as prefix and calculates checksum
     *
     * https://en.wikipedia.org/wiki/Turkish_Identification_Number
     *
     * @param string $identityPrefix
     *
     * @return string Checksum (two digit)
     *
     * @deprecated use {@link \AIKit\Dependencies\Faker\Provider\tr_TR\Person::tcNoChecksum()} instead
     * @see \AIKit\Dependencies\Faker\Provider\tr_TR\Person::tcNoChecksum()
     */
    public static function checksum($identityPrefix)
    {
        return \AIKit\Dependencies\Faker\Provider\tr_TR\Person::tcNoChecksum($identityPrefix);
    }

    /**
     * Checks whether a TCNo has a valid checksum
     *
     * @param string $tcNo
     *
     * @return bool
     *
     * @deprecated use {@link \AIKit\Dependencies\Faker\Provider\tr_TR\Person::tcNoIsValid()} instead
     * @see \AIKit\Dependencies\Faker\Provider\tr_TR\Person::tcNoIsValid()
     */
    public static function isValid($tcNo)
    {
        return \AIKit\Dependencies\Faker\Provider\tr_TR\Person::tcNoIsValid($tcNo);
    }
}
