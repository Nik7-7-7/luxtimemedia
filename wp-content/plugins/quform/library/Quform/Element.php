<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Element
{
    /**
     * Element ID
     * @var int
     */
    protected $id;

    /**
     * The form this element belongs to
     * @var Quform_Form
     */
    protected $form;

    /**
     * Is the element conditionally hidden?
     * @var boolean
     */
    protected $isConditionallyHidden = false;

    /**
     * Is the element a child of a non-visible container?
     * @var boolean
     */
    protected $hasNonVisibleAncestor = false;

    /**
     * Element config storage
     * @var array
     */
    protected $config = array();

    /**
     * Element unique ID
     * @var string
     */
    protected $uniqueId = '';

    /**
     * @param  int          $id
     * @param  Quform_Form  $form
     */
    public function __construct($id, Quform_Form $form)
    {
        $this->id = $id;
        $this->form = $form;
    }

    /**
     * Render the element and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    abstract public function render(array $context = array());

    /**
     * Should this element be visible in the form?
     *
     * @return bool
     */
    public function isVisible()
    {
        if ($this->hasNonVisibleAncestor) {
            return false;
        }

        $visible = true;

        switch ($this->config('visibility')) {
            case 'admin-only':
                if ( ! in_array($this->form->config('environment'), array('viewEntry', 'editEntry', 'listEntry'))) {
                    $visible = false;
                }
                break;
            case 'logged-in-only':
                if ($this->form->config('environment') == 'frontend' && ! is_user_logged_in()) {
                    $visible = false;
                }
                break;
            case 'logged-out-only':
                if ($this->form->config('environment') == 'frontend' && is_user_logged_in()) {
                    $visible = false;
                }
                break;
        }

        $visible = apply_filters('quform_element_visible', $visible, $this, $this->form);
        $visible = apply_filters('quform_element_visible_' . $this->getIdentifier(), $visible, $this, $this->form);

        return $visible;
    }

    /**
     * Set the ID of the element
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the ID of the element
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the form the element belongs to
     *
     * @param Quform_Form $form
     */
    public function setForm(Quform_Form $form)
    {
        $this->form = $form;
    }

    /**
     * Get the form the element belongs to
     *
     * @return Quform_Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Returns the config value for the given $key
     *
     * If the value is null, the default will be returned
     *
     * @param   string|null  $key      The config key
     * @param   null|mixed   $default  The default value to return if the value key not exist
     * @return  mixed                  The config value or $default if not set
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

    /**
     * Get the name of the element
     *
     * @return string
     */
    public function getName()
    {
        return sprintf('quform_%s', $this->getIdentifier());
    }

    /**
     * Get the element identifier (e.g. 1_1)
     *
     * @return string
     */
    public function getIdentifier()
    {
        return sprintf('%d_%d', $this->form->getId(), $this->getId());
    }

    /**
     * @param string $uniqueId
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * Set whether the element is hidden by the conditional logic rules
     *
     * @param boolean $flag
     */
    public function setConditionallyHidden($flag)
    {
        $this->isConditionallyHidden = $flag;
    }

    /**
     * Get whether the element is hidden by the conditional logic rules
     *
     * @return boolean
     */
    public function isConditionallyHidden()
    {
        return $this->isConditionallyHidden;
    }

    /**
     * Get whether the element is hidden or not
     *
     * @return boolean
     */
    public function isHidden()
    {
        return $this->isConditionallyHidden();
    }

    /**
     * Set whether the element is a child of a non-visible container
     *
     * @param boolean $flag
     */
    public function setHasNonVisibleAncestor($flag)
    {
        $this->hasNonVisibleAncestor = $flag;
    }

    /**
     * Get whether the element is a child of a non-visible container
     *
     * @return boolean
     */
    public function hasNonVisibleAncestor()
    {
        return $this->hasNonVisibleAncestor;
    }

    /**
     * Get the CSS for this element
     *
     * @param   array   $context
     * @return  string
     */
    public function getCss(array $context = array())
    {
        $context = $this->prepareContext($context);

        return $this->renderCss($context);
    }

    /**
     * Render the CSS for this element
     *
     * Override in sub classes to add element-specific CSS
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = '';

        if (is_array($this->config('styles'))) {
            foreach ($this->config('styles') as $style) {
                $selector = $this->getCssSelector($style['type']);

                if (Quform::isNonEmptyString($selector) && Quform::isNonEmptyString($style['css'])) {
                    $css .= sprintf('%s { %s }', $selector, $style['css']);
                }
            }
        }

        return $css;
    }

    /**
     * Get the CSS selectors for this element
     *
     * Override in sub classes to add element-specific CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return array();
    }

    /**
     * Get the CSS selector for the given style type
     *
     * @param   string  $type
     * @return  string
     */
    protected function getCssSelector($type)
    {
        $selector = '';
        $selectors = $this->getCssSelectors();

        if (array_key_exists($type, $selectors)) {
            $prefix = sprintf('.quform-%d', $this->form->getId());
            $selector = sprintf($selectors[$type], $prefix, $this->getIdentifier());
        }

        return $selector;
    }

    /**
     * Inherit settings from this element into the context
     *
     * @param   array  $context
     * @return  array
     */
    protected function prepareContext(array $context = array())
    {
        if (is_string($this->config('fieldSize')) && $this->config('fieldSize') != 'inherit') {
            $context['fieldSize'] = $this->config('fieldSize');
        }

        if (is_string($this->config('fieldWidth')) && $this->config('fieldWidth') != 'inherit') {
            $context['fieldWidth'] = $this->config('fieldWidth');

            if ($this->config('fieldWidth') == 'custom' && Quform::isNonEmptyString($this->config('fieldWidthCustom'))) {
                $context['fieldWidthCustom'] = $this->config('fieldWidthCustom');
            }
        }

        if (is_string($this->config('buttonStyle')) && $this->config('buttonStyle') != 'inherit') {
            $context['buttonStyle'] = $this->config('buttonStyle');
        }

        if (is_string($this->config('buttonSize')) && $this->config('buttonSize') != 'inherit') {
            $context['buttonSize'] = $this->config('buttonSize');
        }

        if (is_string($this->config('buttonWidth')) && $this->config('buttonWidth') != 'inherit') {
            $context['buttonWidth'] = $this->config('buttonWidth');

            if ($this->config('buttonWidth') == 'custom' && Quform::isNonEmptyString($this->config('buttonWidthCustom'))) {
                $context['buttonWidthCustom'] = $this->config('buttonWidthCustom');
            }
        }

        if (is_string($this->config('labelPosition')) && $this->config('labelPosition') != 'inherit') {
            $context['labelPosition'] = $this->config('labelPosition');

            if ($this->config('labelPosition') == 'left' && Quform::isNonEmptyString($this->config('labelWidth'))) {
                $context['labelWidth'] = $this->config('labelWidth');
            }
        }

        if (Quform::isNonEmptyString($this->config('tooltipType')) && $this->config('tooltipType') != 'inherit') {
            $context['tooltipType'] = $this->config('tooltipType');
        }

        if (Quform::isNonEmptyString($this->config('tooltipEvent')) && $this->config('tooltipEvent') != 'inherit') {
            $context['tooltipEvent'] = $this->config('tooltipEvent');
        }

        return $context;
    }
}
