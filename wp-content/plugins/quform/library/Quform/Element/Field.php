<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Element_Field extends Quform_Element
{
    /**
     * The default value
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Element value
     * @var mixed
     */
    protected $value;

    /**
     * Element filters
     * @var array
     */
    protected $filters = array();

    /**
     * Element validators
     * @var array
     */
    protected $validators = array();

    /**
     * The current validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Is the element multiple input e.g. multiple select
     * @var boolean
     */
    protected $isMultiple = false;

    /**
     * The name of the element that this belongs to
     * @var Quform_Element
     */
    protected $belongsTo;

    /**
     * Get the unique element ID
     *
     * @return string
     */
    public function getUniqueId()
    {
        return sprintf('quform_%s_%s', $this->getIdentifier(), $this->form->getUniqueId());
    }

    /**
     * Get the name of the element
     *
     * @return string
     */
    public function getName()
    {
        if ($this->getBelongsTo() instanceof Quform_Element) {
            return $this->getId();
        }

        return parent::getName();
    }

    /**
     * Get the element identifier (e.g. 1_1)
     *
     * @return string
     */
    public function getIdentifier()
    {
        if ($this->getBelongsTo() instanceof Quform_Element) {
            return sprintf('%d_%d_%d', $this->form->getId(), $this->getBelongsTo()->getId(), $this->getId());
        }

        return parent::getIdentifier();
    }

    /**
     * Get the fully qualified name of the element
     *
     * @return string
     */
    public function getFullyQualifiedName()
    {
        $name = $this->getName();

        if ($this->getBelongsTo() instanceof Quform_Element) {
            $name = sprintf('%s[%s]', $this->getBelongsTo()->getName(), $name);
        }

        if ($this->isMultiple()) {
            $name .= '[]';
        }

        return $name;
    }

    /**
     * Get the HTML for the element
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $context = $this->prepareContext($context);
        $output = '';

        if ($this->isVisible()) {
            $output .= sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getElementClasses($context))));
            $output .= '<div class="quform-spacer">';
            $output .= $this->getLabelHtml($context);
            $output .= sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getInnerClasses($context))));
            $output .= $this->getDescriptionHtml('above') . $this->getSubLabelHtml('above');
            $output .= $this->getInputHtml($context);
            $output .= $this->getErrorHtml() . $this->getSubLabelHtml() . $this->getDescriptionHtml();
            $output .= '</div></div></div>';
        } else if ($this->shouldConvertToHidden()) {
            $output .= Quform::getHtmlTag('input', array(
                'type' => 'hidden',
                'name' => $this->getFullyQualifiedName(),
                'value' => $this->getValue()
            ));
        }

        return $output;
    }

    /**
     * Get the classes for the outermost element wrapper
     *
     * @param   array  $context
     * @return  array
     */
    protected function getElementClasses(array $context = array())
    {
        $classes = array(
            'quform-element',
            sprintf('quform-element-%s', $this->config('type')),
            sprintf('quform-element-%s', $this->getIdentifier()),
            'quform-cf'
        );

        if (Quform::isNonEmptyString($context['labelPosition'])) {
            $classes[] = sprintf('quform-labels-%s', $context['labelPosition']);
        }

        if ($this->isRequired()) {
            $classes[] = 'quform-element-required';
        } else {
            $classes[] = 'quform-element-optional';
        }

        if (Quform::isNonEmptyString($this->config('customElementClass'))) {
            $classes[] = $this->config('customElementClass');
        }

        $classes = apply_filters('quform_element_classes', $classes, $this, $context);
        $classes = apply_filters('quform_element_classes_' . $this->getIdentifier(), $classes, $this, $context);

        return $classes;
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
        $label = $this->getLabel($context);

        if ( ! Quform::isNonEmptyString($label)) {
            return '';
        }

        if ($forAttribute === true) {
            $forValue = $this->getUniqueId();
        } elseif (Quform::isNonEmptyString($forAttribute)) {
            $forValue = $forAttribute;
        }

        $output = sprintf('<div class="quform-label quform-label-%s">', $this->getIdentifier());

        if (Quform::isNonEmptyString($this->config('labelIcon'))) {
            $output .= sprintf('<span class="quform-label-icon"><i class="%s"></i></span>', $this->config('labelIcon'));
        }

        $output .= sprintf(
            '<label class="quform-label-text"%s%s>%s%s</label>',
            ($id ? ' id="' . Quform::escape($this->getUniqueId() . '_label') . '"' : ''),
            (isset($forValue) ? ' for="' . Quform::escape($forValue) . '"' : ''),
            $label,
            $this->isRequired() && Quform::isNonEmptyString($requiredText = $this->form->config('requiredText')) ? '<span class="quform-required">' . esc_html($requiredText) . '</span>' : ''
        );

        if ($this->form->config('tooltipsEnabled') && Quform::isNonEmptyString($this->form->config('tooltipIcon')) && Quform::isNonEmptyString($tooltip = $this->getTooltip()) && $context['tooltipType'] == 'icon') {
            $output .= sprintf('<div class="quform-tooltip-icon quform-tooltip-icon-%s">', esc_attr($context['tooltipEvent']));
            $output .= sprintf('<i class="%s"></i>', $this->form->config('tooltipIcon'));
            $output .= sprintf('<div class="quform-tooltip-icon-content">%s</div>', $tooltip);
            $output .= '</div>';
        }

        $output .= '</div>';

        $output = apply_filters('quform_field_label_html_' . $this->getIdentifier(), $output, $this, $this->getForm(), $context);

        return $output;
    }

    /**
     * Get the field label text
     *
     * @param   array   $context
     * @return  string
     */
    public function getLabel(array $context = array())
    {
        return apply_filters('quform_field_label_' . $this->getIdentifier(), $this->config('label'), $this, $this->getForm(), $context);
    }

    /**
     * Get the classes for the element inner wrapper
     *
     * @param   array  $context
     * @return  array
     */
    protected function getInnerClasses(array $context = array())
    {
        $classes = array(
            'quform-inner',
            sprintf('quform-inner-%s', $this->config('type')),
            sprintf('quform-inner-%s', $this->getIdentifier())
        );

        if (Quform::isNonEmptyString($context['fieldSize'])) {
            $classes[] = sprintf('quform-field-size-%s', $context['fieldSize']);
        }

        if (Quform::isNonEmptyString($context['fieldWidth']) && $context['fieldWidth'] != 'custom') {
            $classes[] = sprintf('quform-field-width-%s', $context['fieldWidth']);
        }

        return $classes;
    }

    /**
     * Get the HTML for the element description
     *
     * @param   string  $which  Which description to get ('above' or 'below')
     * @return  string
     */
    protected function getDescriptionHtml($which = 'below')
    {
        $output = '';
        $description = $which == 'above' ? $this->config('descriptionAbove') : $this->config('description');

        if (Quform::isNonEmptyString($description)) {
            $output = sprintf(
                '<p class="quform-description quform-description-%s">%s</p>',
                esc_attr($which),
                do_shortcode($description)
            );
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
        $output .= $this->getFieldHtml($context);
        $output .= $this->getFieldIconsHtml();

        if ($this->form->config('tooltipsEnabled') && Quform::isNonEmptyString($tooltip = $this->getTooltip()) && Quform::get($context, 'tooltipType') == 'field') {
            $output .= sprintf('<div class="quform-tooltip-content">%s</div>', $tooltip);
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the field tooltip
     *
     * @return  string
     */
    protected function getTooltip() {
        return apply_filters('quform_field_tooltip_' . $this->getIdentifier(), $this->config('tooltip'), $this, $this->getForm());
    }

    /**
     * Get the classes for the element input wrapper
     *
     * @param   array  $context
     * @return  array
     */
    protected function getInputClasses(array $context = array())
    {
        $classes = array(
            'quform-input',
            sprintf('quform-input-%s', $this->config('type')),
            sprintf('quform-input-%s', $this->getIdentifier()),
            'quform-cf'
        );

        if (Quform::isNonEmptyString($this->config('fieldIconLeft'))) {
            $classes[] = 'quform-has-field-icon-left';
        }

        if (Quform::isNonEmptyString($this->config('fieldIconRight'))) {
            $classes[] = 'quform-has-field-icon-right';
        }

        return $classes;
    }

    /**
     * Get the HTML for the field
     *
     * @param   array  $context
     * @return  string
     */
    abstract protected function getFieldHtml(array $context = array());

    /**
     * Get the HTML for the field icons
     *
     * @return string
     */
    protected function getFieldIconsHtml()
    {
        $output = '';

        if (Quform::isNonEmptyString($this->config('fieldIconLeft'))) {
            $output .= '<span class="quform-field-icon quform-field-icon-left">';
            $output .= sprintf('<i class="%s"></i>', $this->config('fieldIconLeft'));
            $output .= '</span>';
        }

        if (Quform::isNonEmptyString($this->config('fieldIconRight'))) {
            $output .= '<span class="quform-field-icon quform-field-icon-right">';
            $output .= sprintf('<i class="%s"></i>', $this->config('fieldIconRight'));
            $output .= '</span>';
        }

        return $output;
    }

    /**
     * Get the admin label
     *
     * @return string
     */
    public function getAdminLabel()
    {
        $adminLabel = apply_filters('quform_field_admin_label_' . $this->getIdentifier(), $this->config('adminLabel'), $this, $this->getForm());

        if ( ! Quform::isNonEmptyString($adminLabel)) {
            $adminLabel = $this->getLabel();
        }

        return $adminLabel;
    }

    /**
     * @return string
     */
    public function getEditLabelHtml()
    {
        $output = Quform::escape($this->getAdminLabel());

        if ($this->isRequired()) {
            $output .= '<span class="qfb-required">*</span>';
        }

        return $output;
    }

    /**
     * Set the flag that the element can have multiple values.
     *
     * @param boolean $flag
     */
    public function setIsMultiple($flag = true)
    {
        $this->isMultiple = (bool) $flag;
    }

    /**
     * Does this element have multiple values?
     *
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->isMultiple;
    }

    /**
     * Set the parent element to which this one belongs to
     *
     * @param   Quform_Element  $belongsTo
     * @return  $this
     */
    public function setBelongsTo($belongsTo)
    {
        $this->belongsTo = $belongsTo;

        return $this;
    }

    /**
     * Get the name of the parent element to which this one belongs
     *
     * @return Quform_Element
     */
    public function getBelongsTo()
    {
        return $this->belongsTo;
    }

    /**
     * Add a filter
     *
     * @param Quform_Filter_Interface $filter The instance of the filter
     */
    public function addFilter(Quform_Filter_Interface $filter)
    {
        $name = get_class($filter);
        $this->filters[$name] = $filter;
    }

    /**
     * Remove all filters
     */
    public function clearFilters()
    {
        $this->filters = array();
    }

    /**
     * Does this element have filters?
     *
     * @return bool
     */
    public function hasFilters()
    {
        return count($this->getFilters()) > 0;
    }

    /**
     * Get the filters
     *
     * @return array The array of filters
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Does the element have the given filter?
     *
     * @param   string|Quform_Filter_Interface  $filter  The name or instance of the filter
     * @return  bool
     */
    public function hasFilter($filter)
    {
        $result = false;

        if ($filter instanceof Quform_Filter_Interface) {
            $filter = get_class($filter);
        }

        if (is_string($filter)) {
            if (strpos($filter, 'Quform_Filter_') === false) {
                $filter = 'Quform_Filter_' . ucfirst($filter);
            }

            $result = array_key_exists($filter, $this->getFilters());
        }

        return $result;
    }

    /**
     * Get the filter with the given name
     *
     * @param string $filter The name of the filter
     * @return Quform_Filter_Interface|null The filter or null if the element does not have the filter
     */
    public function getFilter($filter)
    {
        $instance = null;

        if (strpos($filter, 'Quform_Filter_') === false) {
            $filter = 'Quform_Filter_' . ucfirst($filter);
        }

        $filters = $this->getFilters();
        if (array_key_exists($filter, $filters)) {
            $instance = $filters[$filter];
        }

        return $instance;
    }

    /**
     * Remove a filter with the given name
     *
     * @param  string  $filter  The name of the filter
     */
    public function removeFilter($filter)
    {
        if (strpos($filter, 'Quform_Filter_') === false) {
            $filter = 'Quform_Filter_' . ucfirst($filter);
        }

        if (array_key_exists($filter, $this->filters)) {
            unset($this->filters[$filter]);
        }
    }

    /**
     * Add a validator
     *
     * @param   Quform_Validator_Interface  $validator  The validator instance to add
     */
    public function addValidator(Quform_Validator_Interface $validator)
    {
        $name = get_class($validator);
        $this->validators[$name] = $validator;
    }

    /**
     * Remove all validators
     */
    public function clearValidators()
    {
        $this->validators = array();
    }

    /**
     * Does the element have any validators?
     *
     * @return bool
     */
    public function hasValidators()
    {
        return count($this->getValidators()) > 0;
    }

    /**
     * Get the validators
     *
     * @return Quform_Validator_Abstract[] The validators
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Does the element have the given validator?
     *
     * @param   string|Quform_Validator_Abstract  $validator  The name or instance of the validator
     * @return  boolean
     */
    public function hasValidator($validator)
    {
        $result = false;

        if ($validator instanceof Quform_Validator_Interface) {
            $validator = get_class($validator);
        }

        if (is_string($validator)) {
            if (strpos($validator, 'Quform_Validator_') === false) {
                $validator = 'Quform_Validator_' . ucfirst($validator);
            }

            $result = array_key_exists($validator, $this->getValidators());
        }

        return $result;
    }

    /**
     * Get the validator with the given name
     *
     * @param   string $validator               The name of the validator
     * @return  Quform_Validator_Abstract|null  The validator or null if the element does not have the validator
     */
    public function getValidator($validator)
    {
        $instance = null;

        if (strpos($validator, 'Quform_Validator_') === false) {
            $validator = 'Quform_Validator_' . ucfirst($validator);
        }

        $validators = $this->getValidators();
        if (array_key_exists($validator, $validators)) {
            $instance = $validators[$validator];
        }

        return $instance;
    }

    /**
     * Remove a validator with the given name
     *
     * @param string $validator The name of the validator
     */
    public function removeValidator($validator)
    {
        if (strpos($validator, 'Quform_Validator_') === false) {
            $validator = 'Quform_Validator_' . ucfirst($validator);
        }

        if (array_key_exists($validator, $this->validators)) {
            unset($this->validators[$validator]);
        }
    }

    /**
     * Gets whether the element is required or not
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->hasValidator('required');
    }

    /**
     * @param mixed $value
     */
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return apply_filters('quform_field_default_value_' . $this->getIdentifier(), $this->defaultValue, $this, $this->getForm());
    }

    /**
     * @return bool
     */
    public function hasDefaultValue()
    {
        return $this->getDefaultValue() !== $this->getEmptyValue();
    }

    /**
     * Set the value
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $this->isValidValue($value) ? $value : $this->getEmptyValue();
    }

    /**
     * Set the value from the database storage
     *
     * @param   string  $value
     * @return  $this
     */
    public function setValueFromStorage($value)
    {
        $value = apply_filters('quform_set_value_from_storage', $value, $this, $this->form);
        $value = apply_filters('quform_set_value_from_storage_' . $this->form->getId(), $value, $this, $this->form);
        $value = apply_filters('quform_set_value_from_storage_' . $this->getIdentifier(), $value, $this, $this->form);

        $value = $this->convertValueFromStorage($value);

        $this->setValue($value);

        return $this;
    }

    /**
     * Convert the value from storage format to element format
     *
     * @param   string  $value
     * @return  string
     */
    protected function convertValueFromStorage($value)
    {
        return $value;
    }

    /**
     * Is the given value valid for this element type
     *
     * @param   string  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        return is_string($value);
    }

    /**
     * Get the unfiltered (raw) value
     *
     * @return string|array
     */
    public function getValueRaw()
    {
        $value = apply_filters('quform_get_value_raw_' . $this->getIdentifier(), $this->value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the filtered value
     *
     * @return string
     */
    public function getValue()
    {
        $value = (string) $this->value;

        $this->filterValue($value);

        $value = apply_filters('quform_get_value_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value formatted in HTML
     *
     * @return string
     */
    public function getValueHtml()
    {
        $value = Quform::escape($this->getValue());

        $value = apply_filters('quform_get_value_html_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator  The separator for array types (used by child classes)
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        $value = $this->getValue();

        $value = apply_filters('quform_get_value_text_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value for storage in the database
     *
     * @return string
     */
    public function getValueForStorage()
    {
        $value = $this->getConvertedValueForStorage();

        $value = apply_filters('quform_get_value_for_storage', $value, $this, $this->form);
        $value = apply_filters('quform_get_value_for_storage_' . $this->form->getId(), $value, $this, $this->form);
        $value = apply_filters('quform_get_value_for_storage_' . $this->getIdentifier(), $value, $this, $this->form);

        return $value;
    }

    /**
     * Convert the value from element format to storage format
     *
     * @return string
     */
    protected function getConvertedValueForStorage()
    {
        return $this->getValue();
    }

    /**
     * Should this element be converted to a hidden field?
     *
     * Currently, this only applies when non-visible fields have a dynamic default value
     *
     * @return bool
     */
    protected function shouldConvertToHidden()
    {
        return ! $this->isVisible() && $this->config('dynamicDefaultValue') && Quform::isNonEmptyString($this->config('dynamicKey'));
    }

    /**
     * Is the data given for this element valid?
     *
     * @return boolean True if valid, false otherwise
     */
    public function isValid()
    {
        $this->clearErrors();
        $skipValidation = false;
        $valid = true;

        // Skip validation if the value is empty and the element is not required
        if ( ! $this->hasValidator('required') && $this->getValueText() === '') {
            $skipValidation = true;
        }

        // Skip validation if the element is conditionally hidden, or the element is not visible (e.g. admin only)
        if ($this->isConditionallyHidden() || ! $this->isVisible()) {
            $skipValidation = true;
        }

        if ( ! $skipValidation) {
            $value = $this->getValue();

            foreach ($this->getValidators() as $validator) {
                if ($validator->isValid($value)) {
                    continue;
                }

                $this->addError($validator->getMessage());
                $valid = false;
                break;
            }

            $valid = apply_filters('quform_element_valid', $valid, $value, $this);
            $valid = apply_filters('quform_element_valid_' . $this->getIdentifier(), $valid, $value, $this);
        }

        return $valid;
    }

    /**
     * Does the element have a validation error?
     *
     * @return boolean
     */
    public function hasError()
    {
        return count($this->errors) > 0;
    }

    /**
     * Set the validation error message
     *
     * @deprecated 2.2.0 Use addError
     * @param string $message
     */
    public function setError($message)
    {
        _deprecated_function(__METHOD__, '2.2.0', 'addError');

        $this->addError($message);
    }

    /**
     * Add a validation error message
     *
     * @param string $message
     */
    public function addError($message)
    {
        $this->errors[] = $message;
    }

    /**
     * @deprecated 2.2.0 Use clearErrors
     */
    public function clearError()
    {
        _deprecated_function(__METHOD__, '2.2.0', 'clearErrors');

        $this->clearErrors();
    }

    /**
     * Clear the validation error messages
     */
    public function clearErrors()
    {
        $this->errors = array();
    }

    /**
     * Get the first validation error message
     *
     * @return string
     */
    public function getError()
    {
        $errors = $this->getErrors();

        return count($errors) > 0 ? $errors[0] : '';
    }

    /**
     * Get the validation error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the validation error message for the front JS to process
     */
    public function getErrorArray()
    {
        return array($this->getIdentifier() => $this->getError());
    }

    /**
     * Reset the value to default
     */
    public function reset()
    {
        $this->setValue($this->getDefaultValue());
    }

    /**
     * Sets the default value dynamically
     *
     * @param string $key
     */
    public function setDynamicDefaultValue($key)
    {
        $value = '';

        $dynamicValues = $this->form->getDynamicValues();

        if (isset($dynamicValues[$key])) {
            $value = $dynamicValues[$key];
        }

        if (isset($_GET[$key])) {
            $value = $_GET[$key];
        }

        $value = $this->prepareDynamicValue($value);

        $value = apply_filters('quform_element_value', $value, $key);
        $value = apply_filters('quform_element_value_' . $key, $value, $key);

        if ($this->isValidValue($value) && $value !== $this->getEmptyValue()) {
            $this->setDefaultValue($value, false);
            $this->setValue($this->getDefaultValue());
        }
    }

    /**
     * Subclasses can alter the dynamic default value to suit
     *
     * @param   string  $value
     * @return  string  $value
     */
    public function prepareDynamicValue($value)
    {
        return $value;
    }

    /**
     * Filter the given value by reference
     *
     * @param string $value
     */
    protected function filterValue(&$value)
    {
        foreach ($this->getFilters() as $filter) {
            $value = $filter->filter($value);
        }
    }

    /**
     * Recursively filter the given value by reference
     *
     * @param array $value
     */
    protected function filterValueRecursive(&$value)
    {
        if (is_array($value)) {
            array_walk($value, array($this, 'filterValueRecursive'));
        } else {
            $this->filterValue($value);
        }
    }

    /**
     * Does this element have the given value?
     *
     * @param mixed $value
     * @return boolean
     */
    public function hasValue($value)
    {
        return $this->getValue() === $value;
    }

    /**
     * @return string
     */
    public function getEmptyValue()
    {
        return '';
    }

    /**
     * Does this element have an empty value?
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->getValue() === $this->getEmptyValue();
    }

    /**
     * Get the HTML for the sub label
     *
     * @param string $which
     * @return string
     */
    protected function getSubLabelHtml($which = 'below')
    {
        $output = '';
        $subLabel = $which == 'above' ? $this->config('subLabelAbove') : $this->config('subLabel');

        if (Quform::isNonEmptyString($subLabel)) {
            $output = sprintf(
                '<label id="%1$s_sub_label_%2$s" class="quform-sub-label quform-sub-label-%2$s">%3$s</label>',
                esc_attr($this->getUniqueId()),
                esc_attr($which),
                do_shortcode($subLabel)
            );
        }

        return $output;
    }

    /**
     * Get the HTML for this element's error (only shown when useAjax is false)
     *
     * @return string
     */
    protected function getErrorHtml()
    {
        if ( ! $this->hasError()) {
            return '';
        }

        $output = '<div class="quform-error quform-cf"><div class="quform-error-inner">';

        if (Quform::isNonEmptyString($this->form->config('errorsIcon'))) {
            $output .= sprintf('<span class="quform-error-icon"><i class="%s"></i></span>', Quform::escape($this->form->config('errorsIcon')));
        }

        $output .= sprintf('<span class="quform-error-text">%s</span>', $this->getError());

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Render the CSS for this field
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = '';

        if ($context['labelPosition'] == 'left' && $context['labelWidth'] != '150px') {
            $css .= sprintf('.quform-labels-left.quform-element-%1$s > .quform-spacer > .quform-label, .quform-rtl .quform-labels-left.quform-element-%1$s > .quform-spacer > .quform-inner { width: %2$s; }', $this->getIdentifier(), Quform::addCssUnit($context['labelWidth']));
            $css .= sprintf('.quform-labels-left.quform-element-%1$s > .quform-spacer > .quform-inner { margin-left: %2$s; }', $this->getIdentifier(), Quform::addCssUnit($context['labelWidth']));
            $css .= sprintf('.quform-rtl .quform-labels-left.quform-element-%1$s > .quform-spacer > .quform-inner { margin-right: %2$s; margin-left: 0; }', $this->getIdentifier(), Quform::addCssUnit($context['labelWidth']));
        }

        $css .= parent::renderCss($context);

        return $css;
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return array(
            'element' => '%s .quform-element-%s',
            'elementSpacer' => '%s .quform-element-%s > .quform-spacer',
            'elementLabel' => '%s .quform-label-%s',
            'elementLabelText' => '%s .quform-label-%s > label',
            'elementRequiredText' => '%s .quform-label-%s > label > .quform-required',
            'elementInner' => '%s .quform-inner-%s',
            'elementInput' => '%s .quform-input-%s',
            'elementText' => '%s .quform-field-%s',
            'elementTextHover' => '%s .quform-field-%s:hover',
            'elementTextFocus' => '%1$s .quform-field-%2$s:focus, %1$s .quform-field-%2$s:active',
            'elementTextarea' => '%s .quform-field-%s',
            'elementTextareaHover' => '%s .quform-field-%s:hover',
            'elementTextareaFocus' => '%1$s .quform-field-%2$s:focus, %1$s .quform-field-%2$s:active',
            'elementSelect' => '%s .quform-field-%s',
            'elementSelectHover' => '%s .quform-field-%s:hover',
            'elementSelectFocus' => '%1$s .quform-field-%2$s:focus, %1$s .quform-field-%2$s:active',
            'elementIcon' => '%s .quform-field-icon',
            'elementIconHover' => '%s .quform-field-icon:hover',
            'elementSubLabel' => '%s .quform-element-%s .quform-sub-label',
            'elementDescription' => '%s .quform-element-%s .quform-description'
        );
    }

    /**
     * Does the given logic rule match the current value?
     *
     * This is overridden in child classes
     *
     * @param   array  $rule
     * @return  bool
     */
    public function isLogicRuleMatch(array $rule)
    {
        return $this->isLogicValueMatch($this->getValue(), $rule);
    }

    /**
     * Does the given logic rule match the given value?
     *
     * @param   mixed  $value
     * @param   array  $rule
     * @return  bool
     */
    protected function isLogicValueMatch($value, array $rule)
    {
        switch ($rule['operator']) {
            case 'eq':
                return $value === $rule['value'];
            case 'neq':
                return $value !== $rule['value'];
            case 'empty':
                return $value === $this->getEmptyValue();
            case 'not_empty':
                return $value !== $this->getEmptyValue();
            case 'gt':
                return is_numeric($value) && is_numeric($rule['value']) && (float) $value > (float) $rule['value'];
            case 'lt':
                return is_numeric($value) && is_numeric($rule['value']) && (float) $value < (float) $rule['value'];
            case 'contains':
                return preg_match('/' . preg_quote($rule['value'], '/') . '/', $value);
            case 'starts_with':
                return preg_match('/^' . preg_quote($rule['value'], '/') . '/', $value);
            case 'ends_with':
                return preg_match('/' . preg_quote($rule['value'], '/') . '$/', $value);
        }

        return false;
    }
}
