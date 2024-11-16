<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Filter_Abstract implements Quform_Filter_Interface
{
    /**
     * The filter settings
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
}
