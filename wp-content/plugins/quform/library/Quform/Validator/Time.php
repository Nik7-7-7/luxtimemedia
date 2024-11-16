<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Time extends Quform_Validator_Abstract
{
    const INVALID = 'timeInvalid';
    const INVALID_TIME = 'timeInvalidTime';
    const TOO_EARLY = 'timeTooEarly';
    const TOO_LATE = 'timeTooLate';
    const BAD_INTERVAL = 'timeBadInterval';

    /**
     * Returns true if the value is a valid time, false otherwise
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

        if (preg_match('/^(2[0-3]|[01][0-9]):[0-5][0-9]$/', $value)) {
            try {
                $time = new DateTime($value, new DateTimeZone('UTC'));

                if (Quform::isNonEmptyString($this->config('min'))) {
                    try {
                        $min = new DateTime($this->config('min'), new DateTimeZone('UTC'));
                    } catch (Exception $e) {
                        $min = null;
                    }

                    if ($min instanceof DateTime && $time < $min) {
                        $this->error(self::TOO_EARLY, array(
                            'min' => $min->format($this->config('format')),
                            'value' => $time->format($this->config('format'))
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

                    if ($max instanceof DateTime && $time > $max) {
                        $this->error(self::TOO_LATE, array(
                            'max' => $max->format($this->config('format')),
                            'value' => $time->format($this->config('format'))
                        ));

                        return false;
                    }
                }

                if (Quform::isNonEmptyString($this->config('interval'))) {
                    $interval = (int) $this->config('interval');
                    $parts = explode(':', $value);
                    $minutes = (int) $parts[1];

                    if ($minutes % $interval !== 0) {
                        $this->error(self::BAD_INTERVAL, array(
                            'interval' => $interval,
                            'value' => $time->format($this->config('format'))
                        ));

                        return false;
                    }
                }

                return true;
            } catch (Exception $e) {
                // If there was an error creating the DateTime object, just fall through to the error below
            }
        }

        $this->error(self::INVALID_TIME);
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
            self::INVALID_TIME => __('Please enter a valid time',  'quform'),
            self::TOO_EARLY => __('The time must not be earlier than %min%', 'quform'),
            self::TOO_LATE => __('The time must not be later than %max%', 'quform'),
            self::BAD_INTERVAL => __('The minutes must be a multiple of %interval%', 'quform')
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
        $config = apply_filters('quform_default_config_validator_time', array(
            'format' => 'g:i A',
            'min' => '',
            'max' => '',
            'interval' => '',
            'messages' => array(
                self::INVALID => '',
                self::INVALID_TIME => '',
                self::TOO_EARLY => '',
                self::TOO_LATE => ''
            )
        ));

        $config['type'] = 'time';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
