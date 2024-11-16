<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_ClassLoader
{
    /**
     * Register this class loader with the spl autoload queue
     */
    public static function register()
    {
        spl_autoload_register(array('Quform_ClassLoader', 'loadClass'));
    }

    /**
     * Attempt to load the given class
     *
     * @param   string  $class  The class name
     */
    public static function loadClass($class)
    {
        // Don't interfere with other autoloaders
        if (strpos($class, 'Quform') === 0) {
            $directory = dirname(dirname(__FILE__));
            $class = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
            $path = $directory . DIRECTORY_SEPARATOR . $class;

            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
}
