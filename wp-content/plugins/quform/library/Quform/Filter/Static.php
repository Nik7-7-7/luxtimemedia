<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Filter_Static
{
    /**
     * Returns the filtered value
     *
     * @param   string  $filter     Lowercase name of the filter
     * @param   mixed   $value      The value to filter
     * @param   array   $options    Options to pass to the filter
     * @return  bool
     */
    public static function filter($filter, $value, array $options = array())
    {
        $instance = null;

        if ( ! empty($filter)) {
            $className = 'Quform_Filter_' . ucfirst($filter);
            if (class_exists($className)) {
                $instance = new $className($options);
            }
        }

        if ( ! $instance instanceof Quform_Filter_Abstract) {
            throw new InvalidArgumentException("Filter '$filter' does not exist");
        }

        return $instance->filter($value);
    }
}
