<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Filter_StripTags extends Quform_Filter_Abstract
{
    /**
     * Strips all HTML tags from the given value
     *
     * If the value provided is not a string, the value will remain unfiltered
     *
     * @param   string  $value  The value to filter
     * @return  string          The filtered value
     */
    public function filter($value)
    {
        if ( ! is_string($value)) {
            return $value;
        }

        return strip_tags($value, $this->config('allowableTags'));
    }

    /**
     * Get the default config for this filter
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_filter_strip_tags', array(
            'allowableTags' => ''
        ));

        $config['type'] = 'stripTags';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
