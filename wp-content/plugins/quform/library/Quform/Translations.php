<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Translations
{
    /**
     * Load the plugin translated strings
     */
    public function load()
    {
        load_plugin_textdomain('quform', false, basename(QUFORM_PATH). '/languages/');
    }
}
