<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_LessThan extends Quform_Validator_Abstract
{
    const INVALID = 'lessThanInvalid';
    const NOT_NUMERIC = 'lessThanNotNumeric';
    const NOT_LESS = 'notLessThan';
    const NOT_LESS_INCLUSIVE = 'notLessThanInclusive';

    /**
     * Returns true if and only if $value is less than 'max' option
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

        $max = is_numeric($this->config('max')) ? $this->config('max') : 10;

        if ($this->config('inclusive')) {
            if ($max < $value) {
                $this->error(self::NOT_LESS_INCLUSIVE, compact('max', 'value'));
                return false;
            }
        } else {
            if ($max <= $value) {
                $this->error(self::NOT_LESS, compact('max', 'value'));
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
            self::NOT_LESS => __("The value is not less than '%max%'", 'quform'),
            self::NOT_LESS_INCLUSIVE => __("The value is not less than or equal to '%max%'", 'quform')
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
        $config = apply_filters('quform_default_config_validator_less_than', array(
            'max' => '10',
            'inclusive' => false,
            'messages' => array(
                self::INVALID => '',
                self::NOT_NUMERIC => '',
                self::NOT_LESS => '',
                self::NOT_LESS_INCLUSIVE => ''
            )
        ));

        $config['type'] = 'lessThan';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
