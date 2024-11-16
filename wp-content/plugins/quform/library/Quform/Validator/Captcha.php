<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Captcha extends Quform_Validator_Abstract
{
    const INVALID = 'captchaInvalid';
    const NOT_MATCH = 'captchaNotMatch';

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * Required options:
     * 'session' - Quform_Session instance
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if ( ! array_key_exists('session', $options) || ! ($options['session'] instanceof Quform_Session)) {
            throw new InvalidArgumentException('Session instance is required for the Captcha validator');
        }

        $this->session = $options['session'];
        unset($options['session']);

        parent::__construct($options);
    }

    /**
     * Compares the given value with the captcha value saved in session. Also sets the error message.
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

        $code = $this->session->get($this->config('sessionKey'));

        if (is_string($code) && strtolower($code) == strtolower($value)) {
            return true;
        }

        $this->error(self::NOT_MATCH);
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
            self::NOT_MATCH => __('The value does not match',  'quform')
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
        $config = apply_filters('quform_default_config_validator_captcha', array(
            'sessionKey' => '',
            'messages' => array(
                self::INVALID => '',
                self::NOT_MATCH => ''
            )
        ));

        $config['type'] = 'captcha';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
