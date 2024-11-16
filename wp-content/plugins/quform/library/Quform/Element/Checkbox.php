<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Checkbox extends Quform_Element_Multi implements Quform_Element_Editable
{
    /**
     * @var array
     */
    protected $value = array();

    /**
     * @var bool
     */
    protected $isMultiple = true;

    /**
     * Prepare the dynamic default value
     *
     * @param   string  $value
     * @return  array
     */
    public function prepareDynamicValue($value)
    {
        return Quform::isNonEmptyString($value) ? explode(',', $value) : $this->getEmptyValue();
    }

    /**
     * Set the value
     *
     * @param   array  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        if ( ! is_array($value)) {
            return false;
        }

        foreach ($value as $val) {
            if ( ! parent::isValidValue($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Does this element have the given value?
     *
     * @param   mixed    $value
     * @return  boolean
     */
    public function hasValue($value)
    {
        return in_array($value, $this->getValue(), true);
    }

    /**
     * Get the empty value for this element
     *
     * @return array
     */
    public function getEmptyValue()
    {
        return array();
    }

    /**
     * Get the filtered value
     *
     * @return array
     */
    public function getValue()
    {
        $value = $this->value;

        $this->filterValueRecursive($value);

        $value = apply_filters('quform_get_value_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value formatted for HTML
     *
     * @return string
     */
    public function getValueHtml()
    {
        $value = '';

        if ( ! $this->isEmpty()) {
            $ulStyle = apply_filters('quform_value_list_checkbox_ul_style', 'margin:0;padding:0;list-style:disc inside;', $this, $this->getForm());
            $ulStyle = apply_filters('quform_value_list_checkbox_ul_style_' . $this->getIdentifier(), $ulStyle, $this, $this->getForm());

            $liStyle = apply_filters('quform_value_list_checkbox_li_style', '', $this, $this->getForm());
            $liStyle = apply_filters('quform_value_list_checkbox_li_style_' . $this->getIdentifier(), $liStyle, $this, $this->getForm());

            $value = sprintf(
                '<ul class="quform-value-list quform-value-list-checkbox"%s>',
                Quform::isNonEmptyString($ulStyle) ? ' style="' . esc_attr($ulStyle) . '"' : ''
            );

            foreach ($this->getValue() as $option) {
                $value .= sprintf(
                    '<li class="quform-value-list-item quform-value-list-item-checkbox"%s>',
                    Quform::isNonEmptyString($liStyle) ? ' style="' . esc_attr($liStyle) . '"' : ''
                );

                $value .= Quform::escape($option);

                $value .= '</li>';
            }

            $value .= '</ul>';
        }

        $value = apply_filters('quform_get_value_html_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator  The separator
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        $value = join($separator, $this->getValue());

        $value = apply_filters('quform_get_value_text_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value in storage format
     *
     * @return string
     */
    protected function getConvertedValueForStorage()
    {
        return serialize($this->getValue());
    }

    /**
     * Convert given the value from storage format
     *
     * @param   string  $value
     * @return  array
     */
    protected function convertValueFromStorage($value)
    {
        return is_serialized($value) ? unserialize($value) : $this->getEmptyValue();
    }

    /**
     * If the value is not an array or is an empty array it's empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return ! count($this->getValue());
    }

    /**
     * Get the HTML for the element label
     *
     * @param   array        $context
     * @param   string|bool  $forAttribute  Set the "for" attribute to the element unique ID
     * @param   bool         $id            Add a unique ID to the label
     * @return  string
     */
    protected function getLabelHtml(array $context = array(), $forAttribute = true, $id = false)
    {
        return parent::getLabelHtml($context, false, true);
    }

    /**
     * Get the HTML attributes for the field
     *
     * @param   array  $option  The current option within the loop
     * @param   array  $context
     * @return  array
     */
    protected function getFieldAttributes(array $option, array $context = array())
    {
        $attributes = array(
            'type' => 'checkbox',
            'name' => $this->getFullyQualifiedName(),
            'id' => sprintf('%s_%d', $this->getUniqueId(), $this->getOptionValue($option, 'id')),
            'class' => Quform::sanitizeClass($this->getFieldClasses($option)),
            'value' => $this->getOptionValue($option, 'value')
        );

        if ($this->hasValue($attributes['value'])) {
            $attributes['checked'] = true;
        }

        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context, $option);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context, $option);

        return $attributes;
    }

    /**
     * Get the classes for the field
     *
     * @param   array  $option  The current option within the loop
     * @param   array  $context
     * @return  array
     */
    protected function getFieldClasses(array $option, array $context = array())
    {
        $classes = array(
            'quform-field',
            'quform-field-checkbox',
            sprintf('quform-field-%s', $this->getIdentifier()),
            sprintf('quform-field-%s_%d', $this->getIdentifier(), $this->getOptionValue($option, 'id'))
        );

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
        }

        $classes = apply_filters('quform_field_classes', $classes, $option, $this, $this->form, $context);
        $classes = apply_filters('quform_field_classes_' . $this->getIdentifier(), $classes, $option, $this, $this->form, $context);

        return $classes;
    }

    /**
     * Get the HTML for the field
     *
     * @param   array   $context
     * @return  string
     */
    protected function getFieldHtml(array $context = array())
    {
        $output = '';

        foreach ($this->getOptions() as $option) {
            $attributes = $this->getFieldAttributes($option, $context);

            $output .= sprintf(
                '<div class="quform-option%s">',
                Quform::isNonEmptyString($this->getOptionValue($option, 'label')) ? '' : ' quform-option-label-empty'
            );

            $output .= Quform::getHtmlTag('input', $attributes);

            $output .= sprintf(
                '<label for="%s" class="quform-option-label quform-option-label-%s_%d">',
                esc_attr($attributes['id']),
                esc_attr($this->getIdentifier()),
                $this->getOptionValue($option, 'id')
            );

            if (Quform::isNonEmptyString($this->getOptionValue($option, 'icon'))) {
                $output .= sprintf('<span class="quform-option-icon"><i class="%s"></i></span>', Quform::escape($this->getOptionValue($option, 'icon')));
            }

            if (Quform::isNonEmptyString($this->getOptionValue($option, 'iconSelected'))) {
                $output .= sprintf('<span class="quform-option-icon-selected"><i class="%s"></i></span>', Quform::escape($this->getOptionValue($option, 'iconSelected')));
            }

            if (Quform::isNonEmptyString($this->getOptionValue($option, 'label'))) {
                $output .= sprintf('<span class="quform-option-text">%s</span>', do_shortcode($this->getOptionValue($option, 'label')));
            }

            $output .= '</label>';

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the HTML for the element input wrapper
     *
     * @param   array   $context
     * @return  string
     */
    protected function getInputHtml(array $context = array())
    {
        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getInputClasses($context))));

        $optionsClasses = array('quform-options', 'quform-cf');

        if (is_numeric($this->config('optionsLayout'))) {
            $optionsClasses[] = 'quform-options-columns';
            $optionsClasses[] = sprintf('quform-%d-columns', $this->config('optionsLayout'));

            if (Quform::isNonEmptyString($this->config('optionsLayoutResponsiveColumns')) && $this->config('optionsLayoutResponsiveColumns') != 'custom') {
                $optionsClasses[] = sprintf('quform-responsive-columns-%s', $this->config('optionsLayoutResponsiveColumns'));
            }
        } else {
            $optionsClasses[] = sprintf('quform-options-%s', $this->config('optionsLayout'));
        }

        if (Quform::isNonEmptyString($context['optionsStyle'])) {
            $optionsClasses[] = sprintf('quform-options-style-%s', $context['optionsStyle']);

            if ($context['optionsStyle'] == 'button') {
                if (Quform::isNonEmptyString($context['optionsButtonStyle'])) {
                    $optionsClasses[] = sprintf('quform-button-style-%s', $context['optionsButtonStyle']);
                }

                if (Quform::isNonEmptyString($context['optionsButtonSize'])) {
                    $optionsClasses[] = sprintf('quform-button-size-%s', $context['optionsButtonSize']);
                }

                if (Quform::isNonEmptyString($context['optionsButtonWidth']) && $context['optionsButtonWidth'] != 'custom') {
                    $optionsClasses[] = sprintf('quform-button-width-%s', $context['optionsButtonWidth']);
                }

                if (Quform::isNonEmptyString($context['optionsButtonIconPosition'])) {
                    $optionsClasses[] = sprintf('quform-button-icon-%s', $context['optionsButtonIconPosition']);
                }
            }
        }

        if ($this->hasOnlySimpleOptions($context)) {
            $optionsClasses[] = 'quform-options-simple';
        }

        $output .= sprintf(
            '<div class="%s"%s>',
            Quform::escape(Quform::sanitizeClass($optionsClasses)),
            Quform::isNonEmptyString($this->config('label')) ? ' role="group" aria-labelledby="' . Quform::escape($this->getUniqueId() . '_label') . '"' : ''
        );

        $output .= $this->getFieldHtml();

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Returns true if the options are simple text options, false otherwise
     *
     * @param   array  $context
     * @return  bool
     */
    protected function hasOnlySimpleOptions(array $context = array())
    {
        if (Quform::isNonEmptyString($context['optionsStyle'])) {
            return false;
        }

        $keys = array('image', 'imageSelected', 'width', 'height', 'icon', 'iconSelected');

        foreach ($this->getOptions() as $option) {
            foreach ($keys as $key) {
                if (Quform::isNonEmptyString($this->getOptionValue($option, $key))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the field HTML when editing
     *
     * @return string
     */
    public function getEditFieldHtml()
    {
        return $this->getFieldHtml();
    }

    /**
     * Render the CSS for this element
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = parent::renderCss($context);

        if ($this->config('optionsLayoutResponsiveColumns') == 'custom' && Quform::isNonEmptyString($this->config('optionsLayoutResponsiveColumnsCustom'))) {
            $css .= sprintf(
                '@media (max-width: %s) { .quform-input-%s > .quform-options-columns > .quform-option { float: none; width: 100%% !important; } }',
                Quform::addCssUnit($this->config('optionsLayoutResponsiveColumnsCustom')),
                $this->getIdentifier()
            );
        }

        if ($context['optionsButtonWidth'] == 'custom' && Quform::isNonEmptyString($context['optionsButtonWidthCustom'])) {
            $css .= sprintf(
                '.quform-input-%s .quform-option .quform-option-label { width: %s;}',
                $this->getIdentifier(),
                Quform::addCssUnit($context['optionsButtonWidthCustom'])
            );
        }

        foreach ($this->getOptions() as $option) {
            if (Quform::isNonEmptyString($this->getOptionValue($option, 'image'))) {
                $css .= sprintf(
                    '.quform-option-label-%s_%d { background-image: url(%s); }',
                    $this->getIdentifier(),
                    $this->getOptionValue($option, 'id'),
                    esc_url($this->getOptionValue($option, 'image'))
                );
            }

            if (Quform::isNonEmptyString($this->getOptionValue($option, 'imageSelected'))) {
                $css .= sprintf(
                    '.quform-field-checkbox:checked + .quform-option-label-%s_%d { background-image: url(%s); }',
                    $this->getIdentifier(),
                    $this->getOptionValue($option, 'id'),
                    esc_url($this->getOptionValue($option, 'imageSelected'))
                );
            }

            if (Quform::isNonEmptyString($this->getOptionValue($option, 'width'))) {
                $css .= sprintf(
                    '.quform-option .quform-option-label-%s_%d { width: %s; }',
                    $this->getIdentifier(),
                    $this->getOptionValue($option, 'id'),
                    Quform::addCssUnit($this->getOptionValue($option, 'width'))
                );
            }

            if (Quform::isNonEmptyString($this->getOptionValue($option, 'height'))) {
                $css .= sprintf(
                    '.quform-option .quform-option-label-%s_%d { height: %s; }',
                    $this->getIdentifier(),
                    $this->getOptionValue($option, 'id'),
                    Quform::addCssUnit($this->getOptionValue($option, 'height'))
                );
            }
        }

        return $css;
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
            'value' => '',
            'image' => '',
            'imageSelected' => '',
            'width' => '',
            'height' => '',
            'icon' => '',
            'iconSelected' => ''
        );

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }

    /**
     * Get the default options for this element
     *
     * @return array
     */
    protected static function getDefaultOptions()
    {
        $options = array();
        $defaults = array(__('Option 1', 'quform'), __('Option 2', 'quform'), __('Option 3', 'quform'));

        foreach ($defaults as $key => $value) {
            $option = self::getDefaultOptionConfig();
            $option['id'] = $key + 1;
            $option['label'] = $option['value'] = $value;
            $options[] = $option;
        }

        return $options;
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_checkbox', array(
            // Basic
            'label' => __('Untitled', 'quform'),
            'options' => self::getDefaultOptions(),
            'nextOptionId' => 4,
            'defaultValue' => array(),
            'customiseValues' => false,
            'description' => '',
            'descriptionAbove' => '',
            'required' => false,

            // Styles
            'labelIcon' => '',
            'optionsLayout' => 'block',
            'optionsLayoutResponsiveColumns' => 'phone-landscape',
            'optionsLayoutResponsiveColumnsCustom' => '',
            'optionsStyle' => 'inherit',
            'optionsButtonStyle' => 'inherit',
            'optionsButtonSize' => 'inherit',
            'optionsButtonWidth' => 'inherit',
            'optionsButtonWidthCustom' => '',
            'optionsButtonIconPosition' => 'inherit',
            'customClass' => '',
            'customElementClass' => '',
            'styles' => array(),

            // Labels
            'subLabel' => '',
            'subLabelAbove' => '',
            'adminLabel' => '',
            'tooltip' => '',
            'tooltipType' => 'icon',
            'tooltipEvent' => 'inherit',
            'labelPosition' => 'inherit',
            'labelWidth' => '',

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Data
            'inArrayValidator' => true,
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'showInEmail' => true,
            'saveToDatabase' => true,

            // Advanced
            'visibility' => '',
            'validators' => array(),

            // Translations
            'messageRequired' => ''
        ));

        $config['type'] = 'checkbox';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
