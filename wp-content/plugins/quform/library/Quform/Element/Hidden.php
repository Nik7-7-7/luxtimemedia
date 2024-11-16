<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Hidden extends Quform_Element_Field implements Quform_Element_Editable
{
    /**
     * Set the default value
     *
     * @param  string   $value
     * @param  boolean  $replacePlaceholders  Whether or not to replace variables
     */
    public function setDefaultValue($value, $replacePlaceholders = true)
    {
        $this->defaultValue = $replacePlaceholders ? $this->getForm()->replaceVariablesPreProcess($value) : $value;
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
            'type' => 'hidden',
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context)),
            'data-default' => $this->getValue()
        );

        if ( ! $this->isEmpty()) {
            $attributes['value'] = $this->getValue();
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
            'quform-field-hidden',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
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
        return Quform::getHtmlTag('input', $this->getFieldAttributes($context));
    }

    /**
     * Render this field and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        return $this->getFieldHtml();
    }

    /**
     * Get the field HTML when editing
     *
     * @return string
     */
    public function getEditFieldHtml()
    {
        $attributes = $this->getFieldAttributes();
        $attributes['type'] = 'text';

        return Quform::getHtmlTag('input', $attributes);
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_hidden', array(
            // Basic
            'label' => __('Hidden', 'quform'),

            // Styles
            'customClass' => '',

            // Data
            'defaultValue' => '',
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'showInEmail' => true,
            'saveToDatabase' => true,

            // Advanced
            'visibility' => ''
        ));

        $config['type'] = 'hidden';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
