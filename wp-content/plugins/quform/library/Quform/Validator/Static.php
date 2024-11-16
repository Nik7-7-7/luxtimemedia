<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Static
{
    /**
     * Returns true if the given value passes validation
     *
     * @param   string  $validator  Lowercase name of the validator
     * @param   mixed   $value      The value to validate against
     * @param   array   $options    Options to pass to the validator
     * @return  bool
     */
    public static function isValid($validator, $value, array $options = array())
    {
        $instance = null;

        if ( ! empty($validator)) {
            $className = 'Quform_Validator_' . ucfirst($validator);
            if (class_exists($className)) {
                $instance = new $className($options);
            }
        }

        if ( ! $instance instanceof Quform_Validator_Abstract) {
            throw new InvalidArgumentException("Validator '$validator' does not exist");
        }

        return $instance->isValid($value);
    }
}
