<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_ViewFactory
{
    public function create($template, array $data = array())
    {
        return new Quform_View($template, $data);
    }
}
