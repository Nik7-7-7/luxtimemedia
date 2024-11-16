<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Select extends Quform_Element_Multi implements Quform_Element_Editable
{
    /**
     * Get the the HTML for an option tag
     *
     * @param   array   $option    The option data
     * @return  string             The resulting HTML
     */
    protected function getOptionHtml(array $option)
    {
        $output = sprintf(
            '<option value="%s"%s>%s</option>',
            Quform::escape($this->getOptionValue($option, 'value')),
            $this->hasValue($this->getOptionValue($option, 'value')) ? ' selected="selected"' : '',
            Quform::escape($this->getOptionValue($option, 'label'))
        );

        return $output;
    }

    /**
     * Get the HTML for an optgroup tag
     *
     * @param   array   $optgroup  The option data
     * @return  string             The resulting HTML
     */
    protected function getOptgroupHtml(array $optgroup)
    {
        $output = sprintf('<optgroup label="%s">', Quform::escape($this->getOptgroupValue($optgroup, 'label')));

        foreach ($this->getOptgroupValue($optgroup, 'options') as $option) {
            $output .= $this->getOptionHtml($option);
        }

        $output .= '</optgroup>';

        return $output;
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
            'class' => Quform::sanitizeClass($this->getFieldClasses($context))
        );

        if ($this->config('enhancedSelectEnabled')) {
            $attributes['data-options'] = wp_json_encode(array(
                'rtl' => $this->form->isRtl(),
                'search' => $this->config('enhancedSelectSearch'),
                'noResultsFound' => $this->getTranslation('enhancedSelectNoResultsFound', __('No results found.', 'quform'))
            ));

            $attributes['style'] = 'width: 100%;';
        }

        if (Quform::isNonEmptyString($this->config('autocomplete'))) {
            $attributes['autocomplete'] = $this->config('autocomplete');
        }

        if (Quform::isNonEmptyString($this->config('aria-labelledby')))  {
            $attributes['aria-labelledby'] = $this->config('aria-labelledby');
        }

        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context);

        return $attributes;
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
            'quform-field-select',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->config('enhancedSelectEnabled')) {
            $classes[] = 'quform-field-select-enhanced';
        }

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
        }

        if ($this->config('submitOnChoice')) {
            $classes[] = 'quform-submit-on-choice';
        }

        $classes = apply_filters('quform_field_classes', $classes, $this, $this->form, $context);
        $classes = apply_filters('quform_field_classes_' . $this->getIdentifier(), $classes, $this, $this->form, $context);

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
     * Get the HTML for the select options
     *
     * @return string
     */
    protected function getOptionsHtml()
    {
        $output = '';

        if ($this->config('noneOption')) {
            $output .= $this->getOptionHtml(array(
                'label' => $this->getTranslation('noneOptionText', __('Please select', 'quform')),
                'value' => ''
            ));
        }

        foreach ($this->getOptions() as $option) {
            if (isset($option['options'])) {
                $output .= $this->getOptgroupHtml($option);
            } else {
                $output .= $this->getOptionHtml($option);
            }
        }

        return $output;
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

        if ($context['fieldWidth'] == 'custom' && Quform::isNonEmptyString($context['fieldWidthCustom'])) {
            $css .= sprintf('.quform-input-select.quform-input-%1$s, .quform-input-multiselect.quform-input-%1$s { width: %2$s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        return $css;
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

        // Icon is the only possible tooltip type for this element
        $context['tooltipType'] = 'icon';

        return $context;
    }

    /**
     * Get the default optgroup config
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultOptgroupConfig($key = null)
    {
        $config = array(
            'label' => __('Untitled', 'quform'),
            'options' => array()
        );

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }

    /**
     * Get the value of the given $key from the given $optgroup or return the default if it does not exist
     *
     * @param   array         $optgroup
     * @param   string        $key
     * @return  string|array
     */
    protected function getOptgroupValue(array $optgroup, $key)
    {
        $value = Quform::get($optgroup, $key);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($this), 'getDefaultOptgroupConfig')), $key);
        }

        return $value;
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
        $config = apply_filters('quform_default_config_select', array(
            // Basic
            'label' => __('Untitled', 'quform'),
            'options' => self::getDefaultOptions(),
            'nextOptionId' => 4,
            'defaultValue' => '',
            'customiseValues' => false,
            'noneOption' => true,
            'description' => '',
            'descriptionAbove' => '',
            'required' => false,

            // Styles
            'labelIcon' => '',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'enhancedSelectEnabled' => false,
            'enhancedSelectSearch' => true,
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
            'autocomplete' => '',
            'submitOnChoice' => false,
            'showInEmail' => true,
            'saveToDatabase' => true,

            // Advanced
            'visibility' => '',
            'validators' => array(),

            // Translations
            'messageRequired' => '',
            'noneOptionText' => '',
            'enhancedSelectNoResultsFound' => ''
        ));

        $config['type'] = 'select';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
