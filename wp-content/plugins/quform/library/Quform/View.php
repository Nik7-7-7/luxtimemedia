<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_View
{
    /**
     * Path to the view template
     * @var string
     */
    protected $template;

    /**
     * Data to be extracted and available within the view template
     * @var array
     */
    protected $data = array();

    /**
     * @param  string  $template
     * @param  array   $data
     */
    public function __construct($template, array $data = array())
    {
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * Render the view and return the output
     *
     * @return string
     */
    public function render()
    {
        extract($this->data);

        ob_start();

        include $this->template;

        return ob_get_clean();
    }

    /**
     * Add a piece of data to the view.
     *
     * @param   string|array  $key
     * @param   mixed         $value
     * @return  Quform_View   $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }
}
