<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Date extends Quform_Validator_Abstract
{
    const INVALID = 'dateInvalid';
    const INVALID_DATE = 'dateInvalidDate';
    const TOO_EARLY = 'dateTooEarly';
    const TOO_LATE = 'dateTooLate';

    /**
     * Returns true if the value is a valid date, false otherwise
     *
     * @param   string   $value  The value to check
     * @return  boolean          True if valid false otherwise
     */
    public function isValid($value)
    {
        $this->reset();

        if ( ! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $value, $matches) && checkdate($matches[2], $matches[3], $matches[1])) {
            try {
                $date = new DateTime($value, new DateTimeZone('UTC'));

                if (Quform::isNonEmptyString($this->config('min'))) {
                    try {
                        $min = new DateTime($this->config('min'), new DateTimeZone('UTC'));
                    } catch (Exception $e) {
                        $min = null;
                    }

                    if ($min instanceof DateTime && $date < $min) {
                        $this->error(self::TOO_EARLY, array(
                            'min' => $min->format($this->config('format')),
                            'value' => $date->format($this->config('format'))
                        ));

                        return false;
                    }
                }

                if (Quform::isNonEmptyString($this->config('max'))) {
                    try {
                        $max = new DateTime($this->config('max'), new DateTimeZone('UTC'));
                    } catch (Exception $e) {
                        $max = null;
                    }

                    if ($max instanceof DateTime && $date > $max) {
                        $this->error(self::TOO_LATE, array(
                            'max' => $max->format($this->config('format')),
                            'value' => $date->format($this->config('format'))
                        ));

                        return false;
                    }
                }

                return true;
            } catch (Exception $e) {
                // If there was an error creating the DateTime object, just fall through to the error below
            }
        }

        $this->error(self::INVALID_DATE);
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
            self::INVALID => __('Invalid data type, string expected',  'quform'),
            self::INVALID_DATE => __('Please enter a valid date',  'quform'),
            self::TOO_EARLY => __('The date must not be earlier than %min%', 'quform'),
            self::TOO_LATE => __('The date must not be later than %max%', 'quform')
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
        $config = apply_filters('quform_default_config_validator_date', array(
            'min' => '',
            'max' => '',
            'format' => 'n/j/Y',
            'messages' => array(
                self::INVALID => '',
                self::INVALID_DATE => '',
                self::TOO_EARLY => '',
                self::TOO_LATE => ''
            )
        ));

        $config['type'] = 'date';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
