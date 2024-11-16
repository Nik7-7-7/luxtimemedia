<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Element_Multi extends Quform_Element_Field
{
    /**
     * Element options
     * @var array
     */
    protected $options = array();

    /**
     * Add an element option
     *
     * @param array $option The option data
     */
    public function addOption(array $option)
    {
        $this->options[] = $option;
    }

    /**
     * Add multiple element options
     *
     * @param array $options
     */
    public function addOptions(array $options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * Set multiple element options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->clearOptions();
        $this->addOptions($options);
    }

    /**
     * Clear multiple element options
     */
    public function clearOptions()
    {
        $this->options = array();
    }

    /**
     * Get the options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return parent::getCssSelectors() + array(
            'options' => '%s .quform-input-%s .quform-options',
            'option' => '%s .quform-input-%s .quform-option',
            'optionRadioButton' => '%s .quform-input-%s .quform-option .quform-field-radio',
            'optionCheckbox' => '%s .quform-input-%s .quform-option .quform-field-checkbox',
            'optionLabel' => '%s .quform-input-%s .quform-option .quform-option-label',
            'optionLabelHover' => '%s .quform-input-%s .quform-option .quform-option-label:hover',
            'optionLabelSelected' => '%s .quform-input-%s .quform-option .quform-field:checked + .quform-option-label',
            'optionIcon' => '%s .quform-input-%s .quform-option .quform-option-icon',
            'optionIconSelected' => '%s .quform-input-%s .quform-option .quform-option-icon-selected',
            'optionText' => '%s .quform-input-%s .quform-option .quform-option-text',
            'optionTextSelected' => '%s .quform-input-%s .quform-option .quform-field:checked + .quform-option-label .quform-option-text'
        );
    }

    /**
     * Does the given logic rule match the current value?
     *
     * @param   array  $rule
     * @return  bool
     */
    public function isLogicRuleMatch(array $rule)
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $match = false;

            if (count($value)) {
                if ($rule['operator'] == 'not_empty') {
                    $match = true;
                } elseif ($rule['operator'] == 'neq') {
                    $match = true;

                    foreach ($value as $val) {
                        if ($val === $rule['value']) {
                            $match = false;
                            break;
                        }
                    }
                } else {
                    foreach ($value as $val) {
                        if ($this->isLogicValueMatch($val, $rule)) {
                            $match = true;
                            break;
                        }
                    }
                }
            } elseif ($rule['operator'] == 'neq' || $rule['operator'] == 'empty') {
                $match = true;
            }

            return $match;
        }

        return $this->isLogicValueMatch($value, $rule);
    }

    /**
     * Inherit settings from this element into the context
     *
     * @param   array  $context
     * @return  array
     */
    protected function prepareContext(array $context = array())
    {
        $context = parent::prepareContext($context);

        // Inside labels are not possible so set it above
        if ( ! in_array($context['labelPosition'], array('', 'left'), true)) {
            $context['labelPosition'] = '';
        }

        // Icon is the only possible tooltip type for this element
        $context['tooltipType'] = 'icon';

        if (is_string($this->config('optionsStyle')) && $this->config('optionsStyle') != 'inherit') {
            $context['optionsStyle'] = $this->config('optionsStyle');

            if ($this->config('optionsStyle') == 'button') {
                if (is_string($this->config('optionsButtonStyle')) && $this->config('optionsButtonStyle') != 'inherit') {
                    $context['optionsButtonStyle'] = $this->config('optionsButtonStyle');
                }

                if (is_string($this->config('optionsButtonSize')) && $this->config('optionsButtonSize') != 'inherit') {
                    $context['optionsButtonSize'] = $this->config('optionsButtonSize');
                }

                if (is_string($this->config('optionsButtonWidth')) && $this->config('optionsButtonWidth') != 'inherit') {
                    $context['optionsButtonWidth'] = $this->config('optionsButtonWidth');

                    if ($this->config('optionsButtonWidth') == 'custom' && Quform::isNonEmptyString($this->config('optionsButtonWidthCustom'))) {
                        $context['optionsButtonWidthCustom'] = $this->config('optionsButtonWidthCustom');
                    }
                }

                if (is_string($this->config('optionsButtonIconPosition')) && $this->config('optionsButtonIconPosition') != 'inherit') {
                    $context['optionsButtonIconPosition'] = $this->config('optionsButtonIconPosition');
                }
            }
        }

        return $context;
    }

    /**
     * Get the value of the given $key from the given $option or return the default if it does not exist
     *
     * @param   array   $option
     * @param   string  $key
     * @return  string
     */
    public function getOptionValue(array $option, $key)
    {
        $value = Quform::get($option, $key);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($this), 'getDefaultOptionConfig')), $key);
        }

        return $value;
    }

    /**
     * Get the default option config
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultOptionConfig($key = null)
    {
        $config = array(
            'label' => '',
            'value' => ''
        );

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
