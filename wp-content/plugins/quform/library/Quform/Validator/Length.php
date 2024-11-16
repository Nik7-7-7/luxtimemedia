<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Length extends Quform_Validator_Abstract
{
    const INVALID   = 'lengthInvalid';
    const TOO_SHORT = 'lengthTooShort';
    const TOO_LONG  = 'lengthTooLong';

    /**
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is set).
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

        $min = is_numeric($this->config('min')) ? $this->config('min') : 0;
        $max = is_numeric($this->config('max')) && $this->config('max') >= $min ? $this->config('max') : '';

        $length = Quform::strlen($value);

        if ($length < $min) {
            $this->error(self::TOO_SHORT, compact('min', 'length', 'value'));
            return false;
        }

        if (is_numeric($max) && $max < $length) {
            $this->error(self::TOO_LONG, compact('max', 'length', 'value'));
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
            self::INVALID => __('Invalid data type, string expected', 'quform'),
            self::TOO_SHORT => __('Value must be at least %min% characters long', 'quform'),
            self::TOO_LONG => __('Value must be no longer than %max% characters', 'quform')
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
        $config = apply_filters('quform_default_config_validator_length', array(
            'min' => '0',
            'max' => '',
            'messages' => array(
                self::INVALID => '',
                self::TOO_SHORT => '',
                self::TOO_LONG => ''
            )
        ));

        $config['type'] = 'length';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
