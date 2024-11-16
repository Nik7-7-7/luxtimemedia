<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Identical extends Quform_Validator_Abstract
{
    const INVALID = 'identicalInvalid';
    const NOT_SAME = 'notSame';

    /**
     * Returns true if the given value is equal to the set value
     * Return false otherwise.
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

        $token = $this->config('token');

        if ($value !== $token) {
            $this->error(self::NOT_SAME, compact('token', 'value'));
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
            self::NOT_SAME => __('The value does not match',  'quform')
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
        $config = apply_filters('quform_default_config_validator_identical', array(
            'token' => '',
            'messages' => array(
                self::INVALID => '',
                self::NOT_SAME => ''
            )
        ));

        $config['type'] = 'identical';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
