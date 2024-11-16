<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Multiselect extends Quform_Element_Select
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
            $ulStyle = apply_filters('quform_value_list_multiselect_ul_style', 'margin:0;padding:0;list-style:disc inside;', $this, $this->getForm());
            $ulStyle = apply_filters('quform_value_list_multiselect_ul_style_' . $this->getIdentifier(), $ulStyle, $this, $this->getForm());

            $liStyle = apply_filters('quform_value_list_multiselect_li_style', '', $this, $this->getForm());
            $liStyle = apply_filters('quform_value_list_multiselect_li_style_' . $this->getIdentifier(), $liStyle, $this, $this->getForm());

            $value = sprintf(
                '<ul class="quform-value-list quform-value-list-multiselect"%s>',
                Quform::isNonEmptyString($ulStyle) ? ' style="' . esc_attr($ulStyle) . '"' : ''
            );

            foreach ($this->getValue() as $option) {
                $value .= sprintf(
                    '<li class="quform-value-list-item quform-value-list-item-multiselect"%s>',
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
     * Get the HTML attributes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldAttributes(array $context = array())
    {
        $attributes = array(
            'id' => $this->getUniqueId(),
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context)),
            'multiple' => true
        );

        if ($this->config('enhancedSelectEnabled')) {
            $attributes['data-options'] = wp_json_encode(array(
                'rtl' => $this->form->isRtl(),
                'placeholder' => $this->getTranslation('enhancedSelectPlaceholder', __('Please select', 'quform')),
                'noResultsFound' => $this->getTranslation('enhancedSelectNoResultsFound', __('No results found.', 'quform')),
            ));

            $attributes['style'] = 'width: 100%;';
        }

        if (Quform::isNonEmptyString($this->config('aria-labelledby')))  {
            $attributes['aria-labelledby'] = $this->config('aria-labelledby');
        }

        if (Quform::isNonEmptyString($this->config('sizeAttribute'))) {
            if ($this->config('sizeAttribute') == 'auto') {
                $attributes['size'] = $this->getOptionsCount();
            } else {
                $attributes['size'] = $this->config('sizeAttribute');
            }
        }

        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context);

        return $attributes;
    }

    /**
     * Get the total number of options, including optgroup options
     *
     * @return int
     */
    protected function getOptionsCount()
    {
        $count = count($this->getOptions());

        foreach ($this->getOptions() as $option) {
            if (isset($option['options'])) {
                $count += count($option['options']);
            }
        }

        return $count;
    }

    /**
     * Get the classes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldClasses(array $context = array())
    {
        $classes = array(
            'quform-field',
            'quform-field-multiselect',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->config('enhancedSelectEnabled')) {
            $classes[] = 'quform-field-multiselect-enhanced';
        }

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
        }

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
        return Quform::getHtmlTag('select', $this->getFieldAttributes($context), $this->getOptionsHtml());
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
        $config = apply_filters('quform_default_config_multi_select', array(
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
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'enhancedSelectEnabled' => false,
            'enhancedSelectPlaceholder' => '',
            'customClass' => '',
            'customElementClass' => '',
            'sizeAttribute' => '',
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
            'messageRequired' => '',
            'enhancedSelectNoResultsFound' => ''
        ));

        $config['type'] = 'multiselect';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
