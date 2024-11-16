<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Required extends Quform_Validator_Abstract
{
    const INVALID = 'requiredInvalid';
    const REQUIRED = 'required';

    /**
     * Checks whether the given value is not empty. Also sets the error message if value is empty.
     *
     * @param   string|array  $value  The value to check
     * @return  bool                  True if valid false otherwise
     */
    public function isValid($value)
    {
        $this->reset();

        if (is_string($value)) {
            if ($value !== '') {
                return true;
            }
        } else if (is_array($value)) {
            foreach (array_values($value) as $val) {
                if (is_string($val) && $val !== '') {
                    return true;
                }
            }
        } else {
            $this->error(self::INVALID);
            return false;
        }

        $this->error(self::REQUIRED);
        return false;
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
            self::INVALID => __('Invalid type given, string or array expected',  'quform'),
            self::REQUIRED => __('This field is required',  'quform')
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
        $config = apply_filters('quform_default_config_validator_required', array(
            'messages' => array(
                self::INVALID => '',
                self::REQUIRED => ''
            )
        ));

        $config['type'] = 'required';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
