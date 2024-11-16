<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Email extends Quform_Element_Field implements Quform_Element_Editable
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
     * Get the value formatted in HTML
     *
     * @return string
     */
    public function getValueHtml()
    {
        $value = '';

        if ( ! $this->isEmpty()) {
            $value = sprintf('<a href="mailto:%1$s">%1$s</a>', Quform::escape($this->getValue()));
        }

        $value = apply_filters('quform_get_value_html_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
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
            'type' => 'email',
            'id' => $this->getUniqueId(),
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context))
        );

        if ( ! $this->isEmpty()) {
            $attributes['value'] = $this->getValue();
        }

        $placeholder = $this->form->replaceVariablesPreProcess($this->config('placeholder'));
        if (Quform::isNonEmptyString($placeholder)) {
            $attributes['placeholder'] = $placeholder;
        }

        if (Quform::isNonEmptyString($this->config('autocomplete'))) {
            $attributes['autocomplete'] = $this->config('autocomplete');
        }

        if (Quform::isNonEmptyString($this->config('maxLength'))) {
            $attributes['maxlength'] = $this->config('maxLength');
        }

        if ($this->config('readOnly')) {
            $attributes['readonly'] = true;
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
            'quform-field-email',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->form->config('tooltipsEnabled') && Quform::isNonEmptyString($this->config('tooltip')) && Quform::get($context, 'tooltipType') == 'field') {
            $classes[] = sprintf('quform-tooltip-%s', Quform::get($context, 'tooltipEvent'));
        }

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
            $css .= sprintf('.quform-input-email.quform-input-%s { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        return $css;
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_email', array(
            // Basic
            'label' => __('Email address', 'quform'),
            'description' => '',
            'descriptionAbove' => '',
            'required' => true,

            // Styles
            'labelIcon' => '',
            'fieldIconLeft' => '',
            'fieldIconRight' => '',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'customClass' => '',
            'customElementClass' => '',
            'styles' => array(),

            // Labels
            'placeholder' => '',
            'subLabel' => '',
            'subLabelAbove' => '',
            'adminLabel' => '',
            'tooltip' => '',
            'tooltipType' => 'inherit',
            'tooltipEvent' => 'inherit',
            'labelPosition' => 'inherit',
            'labelWidth' => '',

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Data
            'defaultValue' => '',
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'autocomplete' => '',
            'maxLength' => '',
            'readOnly' => false,
            'showInEmail' => true,
            'saveToDatabase' => true,

            // Advanced
            'visibility' => '',
            'filters' => array(array('type' => 'trim')),
            'validators' => array(),

            // Translations
            'messageRequired' => '',
            'messageEmailAddressInvalidFormat' => '',
            'messageLengthTooLong' => ''
        ));

        $config['type'] = 'email';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
