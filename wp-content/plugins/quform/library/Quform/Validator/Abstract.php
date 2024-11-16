<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Validator_Abstract implements Quform_Validator_Interface
{
    /**
     * Last failure error message
     *
     * @var string
     */
    protected $message = '';

    /**
     * The validator settings
     *
     * @var array
     */
    protected $config = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setConfig($options);
    }

    /**
     * Set the error message
     *
     * @param string $message
     */
    protected function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get the last failure error message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Clears the error message
     *
     * @return $this
     */
    public function reset()
    {
        $this->message = '';

        return $this;
    }

    /**
     * Create and set the error message from a template
     *
     * @param  string  $key         The key of the message template
     * @param  array   $variables   Variables that can be replaced in the error message
     */
    protected function error($key, array $variables = array())
    {
        $this->message = $this->createMessage($key, $variables);
    }

    /**
     * Constructs and returns a validation failure message with the given message key
     *
     * If a translation exists for $key, the translation will be used
     *
     * @param   string  $key        The message template key
     * @param   array   $variables  An array of variable keys and values for replacement
     * @return  string
     */
    public function createMessage($key, array $variables = array())
    {
        $message = call_user_func(array(get_class($this), 'getMessageTemplates'), $key);

        if ($message === null) {
            return '';
        }

        $message = $this->getTranslation('messages.' . $key, $message);

        foreach ($variables as $variable => $value) {
            $message = str_replace("%$variable%", $value, $message);
        }

        return $message;
    }

    /**
     * Returns the config value for the given $key
     *
     * @param   string|null  $key
     * @param   null|mixed   $default
     * @return  mixed        The config value or $default if not set
     */
    public function config($key = null, $default = null)
    {
        $value = Quform::get($this->config, $key, $default);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($this), 'getDefaultConfig')), $key, $default);
        }

        return $value;
    }

    /**
     * Set the config value for the given $key or multiple values using an array
     *
     * @param   string|array  $key    Key or array of key/values
     * @param   mixed         $value  Value or null if $key is array
     * @return  $this
     */
    public function setConfig($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->config[$k] = $v;
            }
        } else {
            $this->config[$key] = $value;
        }

        return $this;
    }

    /**
     * @param   string  $key
     * @param   string  $default
     * @return  string
     */
    public function getTranslation($key, $default = '')
    {
        $string = $this->config($key);

        if (Quform::isNonEmptyString($string)) {
            return $string;
        }

        return $default;
    }
}
