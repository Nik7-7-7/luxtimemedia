<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Recaptcha extends Quform_Validator_Abstract
{
    const MISSING_INPUT_SECRET = 'recaptchaMissingInputSecret';
    const INVALID_INPUT_SECRET = 'recaptchaInvalidInputSecret';
    const MISSING_INPUT_RESPONSE = 'recaptchaMissingInputResponse';
    const INVALID_INPUT_RESPONSE = 'recaptchaInvalidInputResponse';
    const ERROR = 'recaptchaError';
    const SCORE_TOO_LOW = 'recaptchaScoreTooLow';

    /**
     * Mapping of reCAPTCHA error codes to message template keys
     *
     * @var array
     */
    protected $errorCodes = array(
        'missing-input-secret' => self::MISSING_INPUT_SECRET,
        'invalid-input-secret' => self::INVALID_INPUT_SECRET,
        'missing-input-response' => self::MISSING_INPUT_RESPONSE,
        'invalid-input-response' => self::INVALID_INPUT_RESPONSE
    );

    /**
     * @param   string   $value  The reCAPTCHA response
     * @return  boolean          True if valid false otherwise
     */
    public function isValid($value)
    {
        $this->reset();

        $provider = $this->config('provider');

        if ($provider == 'hcaptcha') {
            $url = 'https://hcaptcha.com/siteverify';
        } elseif ($provider == 'turnstile') {
            $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        } else {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
        }

        $params = array(
            'secret' => $this->config('secretKey'),
            'response' => $value,
            'remoteip' => Quform::getClientIp()
        );

        $response = wp_remote_post($url, array('body' => $params));
        $response = wp_remote_retrieve_body($response);
        $response = json_decode($response, true);

        if ( ! is_array($response) || ! isset($response['success'])) {
            $this->error(self::ERROR);
            return false;
        }

        if ( ! $response['success']) {
            if (isset($response['error-codes']) && is_array($response['error-codes']) && count($response['error-codes'])) {
                foreach ($response['error-codes'] as $error) {
                    if (array_key_exists($error, $this->errorCodes)) {
                        $this->error($this->errorCodes[$error]);
                    } else {
                        $this->error(self::ERROR);
                    }

                    return false;
                }
            } else {
                $this->error(self::ERROR);
                return false;
            }
        }

        if ( ! in_array($provider, array('hcaptcha', 'turnstile'), true) && $this->config('version') == 'v3') {
            if (isset($response['score'], $response['action']) && $response['action'] == 'quform' && is_numeric($response['score'])) {
                $threshold = (float) $this->config('threshold');

                if ($response['score'] < $threshold) {
                    $this->error(self::SCORE_TOO_LOW);
                    return false;
                }
            } else {
                $this->error(self::ERROR);
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
            self::MISSING_INPUT_SECRET => __('The secret parameter is missing',  'quform'),
            self::INVALID_INPUT_SECRET => __('The secret parameter is invalid or malformed',  'quform'),
            self::MISSING_INPUT_RESPONSE => __('The response parameter is missing',  'quform'),
            self::INVALID_INPUT_RESPONSE => __('The response parameter is invalid or malformed',  'quform'),
            self::ERROR => __('An error occurred, please try again',  'quform'),
            self::SCORE_TOO_LOW => __('Sorry, your submission failed our automated spam checks',  'quform'),
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
        $config = apply_filters('quform_default_config_validator_recaptcha', array(
            'secretKey' => '',
            'version' => 'v2',
            'threshold' => '0.5',
            'messages' => array(
                self::MISSING_INPUT_SECRET => '',
                self::INVALID_INPUT_SECRET => '',
                self::MISSING_INPUT_RESPONSE => '',
                self::INVALID_INPUT_RESPONSE => '',
                self::ERROR => '',
                self::SCORE_TOO_LOW => ''
            )
        ));

        $config['type'] = 'recaptcha';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
