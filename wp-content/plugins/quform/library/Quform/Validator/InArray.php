<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_InArray extends Quform_Validator_Abstract
{
    const INVALID = 'inArrayInvalid';
    const NOT_IN_ARRAY = 'notInArray';

    /**
     * Returns true if the given value is in the haystack array
     * Return false otherwise.
     *
     * @param   string  $value
     * @return  boolean
     */
    public function isValid($value)
    {
        $this->reset();

        if ( ! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $inArray = in_array($value, $this->config('haystack'), true);

        if (( ! $inArray && ! $this->config('invert')) || ($inArray && $this->config('invert'))) {
            $this->error(self::NOT_IN_ARRAY, compact('value'));
            return false;
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
            self::INVALID => __('Invalid data type, string expected',  'quform'),
            self::NOT_IN_ARRAY => __('This value is not valid',  'quform')
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
        $config = apply_filters('quform_default_config_validator_in_array', array(
            'haystack' => array(),
            'invert' => false,
            'messages' => array(
                self::INVALID => '',
                self::NOT_IN_ARRAY => ''
            )
        ));

        $config['type'] = 'inArray';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
