<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Regex extends Quform_Validator_Abstract
{
    const INVALID = 'regexInvalid';
    const NOT_MATCH = 'regexNotMatch';

    /**
     * Returns true if the given value matches the regular expression pattern
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

        $pattern = $this->config('pattern');

        if (Quform::isNonEmptyString($pattern)) {
            $result = preg_match($pattern, $value);
            $invert = $this->config('invert');

            if ($invert && $result || ! $invert && ! $result) {
                $this->error(self::NOT_MATCH, compact('pattern', 'value'));
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
            self::INVALID => __('Invalid data type, string expected',  'quform'),
            self::NOT_MATCH => __('Invalid value given',  'quform')
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
        $config = apply_filters('quform_default_config_validator_regex', array(
            'pattern' => '',
            'invert' => false,
            'messages' => array(
                self::INVALID => '',
                self::NOT_MATCH => ''
            )
        ));

        $config['type'] = 'regex';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
