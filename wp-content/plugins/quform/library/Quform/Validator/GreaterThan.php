<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_GreaterThan extends Quform_Validator_Abstract
{
    const INVALID = 'greaterThanInvalid';
    const NOT_NUMERIC = 'greaterThanNotNumeric';
    const NOT_GREATER = 'notGreaterThan';
    const NOT_GREATER_INCLUSIVE = 'notGreaterThanInclusive';

    /**
     * Returns true if and only if $value is numerically greater than 'min' option
     *
     * @param   string   $value
     * @return  boolean
     */
    public function isValid($value)
    {
        $this->reset();

        if ( ! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        if ( ! is_numeric($value)) {
            $this->error(self::NOT_NUMERIC);
            return false;
        }

        $min = is_numeric($this->config('min')) ? $this->config('min') : 0;

        if ($this->config('inclusive')) {
            if ($min > $value) {
                $this->error(self::NOT_GREATER_INCLUSIVE, compact('min', 'value'));
                return false;
            }
        } else {
            if ($min >= $value) {
                $this->error(self::NOT_GREATER, compact('min', 'value'));
                return false;
            }
        }

        return true;
    }

    /**
     * Get all message templates or the single message with the given key
     *
     * @param   string|null   $key
     * @return  array|string
     */
    public static function getMessageTemplates($key = null)
    {
        $messageTemplates = array(
            self::INVALID => __('Invalid data type, string expected', 'quform'),
            self::NOT_NUMERIC => __('A numeric value is required', 'quform'),
            self::NOT_GREATER => __("The value is not greater than '%min%'", 'quform'),
            self::NOT_GREATER_INCLUSIVE => __("The value is not greater than or equal to '%min%'", 'quform')
        );

        if (is_string($key)) {
            return array_key_exists($key, $messageTemplates) ? $messageTemplates[$key] : null;
        }

        return $messageTemplates;
    }

    /**
     * Get the default config for this validator
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_validator_greater_than', array(
            'min' => '0',
            'inclusive' => false,
            'messages' => array(
                self::INVALID => '',
                self::NOT_NUMERIC => '',
                self::NOT_GREATER => '',
                self::NOT_GREATER_INCLUSIVE => ''
            )
        ));

        $config['type'] = 'greaterThan';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
