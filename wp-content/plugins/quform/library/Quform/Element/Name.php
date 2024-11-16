<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Name extends Quform_Element_Field implements Quform_Element_Editable
{
    /**
     * @var array
     */
    static $partKeys = array(
        1 => 'prefix',
        2 => 'first',
        3 => 'middle',
        4 => 'last',
        5 => 'suffix'
    );

    /**
     * @var Quform_Element_Field[]
     */
    protected $parts = array();

    /**
     * @var array
     */
    protected $value = array();

    /**
     * Set the value
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);

        $value = $this->getValue();

        foreach (self::$partKeys as $key => $slug) {
            if (array_key_exists($key, $this->parts) && array_key_exists($key, $value)) {
                $this->parts[$key]->setValue($value[$key]);
            }
        }
    }

    /**
     * Get the filtered value
     *
     * @return array The filtered value
     */
    public function getValue()
    {
        $value = $this->value;

        $this->filterValueRecursive($value);

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
        $value = Quform::escape($this->getValueText());

        $value = apply_filters('quform_get_value_html_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator  The separator for array types (not used here)
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        $nonEmptyParts = array();

        foreach ($this->getValue() as $value) {
            if (Quform::isNonEmptyString($value)) {
                $nonEmptyParts[] = $value;
            }
        }

        $value = join(' ', $nonEmptyParts);

        $value = apply_filters('quform_get_value_text_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Is the given value valid for this element type
     *
     * @param   array  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        if ( ! is_array($value)) {
            return false;
        }

        foreach ($value as $key => $val) {
            if ( ! array_key_exists($key, self::$partKeys) || ! parent::isValidValue($val)) {
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
        return $this->getValueText() === $value;
    }

    /**
     * @return array
     */
    public function getEmptyValue()
    {
        return array();
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
     * @return bool
     */
    public function isRequired()
    {
        $required = false;

        foreach ($this->parts as $part) {
            if ($part->isRequired()) {
                $required = true;
            }
        }

        return $required;
    }

    /**
     * If the value is not an array or is an empty array it's empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->hasValue('');
    }

    /**
     * Set a part by name
     *
     * @param  $name
     * @param  Quform_Element_Field  $element
     */
    public function setPart($name, Quform_Element_Field $element)
    {
        $this->parts[$name] = $element;
    }

    /**
     * Get a part by name
     *
     * @param   string  $name  The name of the part
     * @return  Quform_Element_Field|null
     */
    public function getPart($name)
    {
        return isset($this->parts[$name]) ? $this->parts[$name] : null;
    }

    /**
     * Is this element valid?
     *
     * @return bool
     */
    public function isValid()
    {
        $this->clearErrors();
        $skipValidation = false;
        $valid = true;

        // Skip validation if the element is conditionally hidden, or not visible (e.g. admin only)
        if ($this->isConditionallyHidden() || ! $this->isVisible()) {
            $skipValidation = true;
        }

        if ( ! $skipValidation) {
            $value = $this->getValue();

            foreach ($this->parts as $part) {
                if ( ! $part->isValid()) {
                    $valid = false;
                }
            }

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
     * @return bool
     */
    public function hasError()
    {
        $hasError = false;

        if (parent::hasError()) {
            $hasError = true;
        }

        foreach ($this->parts as $part) {
            if ($part->hasError()) {
                $hasError = true;
            }
        }

        return $hasError;
    }

    /**
     * @return array
     */
    public function getErrorArray()
    {
        $errors = array();

        if (parent::hasError()) {
            $errors[$this->getIdentifier()] = $this->getError();
        }

        foreach ($this->parts as $part) {
            if ($part->hasError()) {
                $errors[$part->getIdentifier()] = $part->getError();
            }
        }

        return $errors;
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
     * Get the HTML for the element input wrapper
     *
     * @param   array   $context
     * @return  string
     */
    protected function getInputHtml(array $context = array())
    {
        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getInputClasses($context))));

        $rowClasses = array(
            'quform-element-row',
            sprintf('quform-%d-columns', count($this->parts)),
            Quform::isNonEmptyString($context['fieldWidth']) ? 'quform-element-row-size-float' : 'quform-element-row-size-fixed', // For non-100% input widths use float class
        );

        if (Quform::isNonEmptyString($context['responsiveColumns']) && $context['responsiveColumns'] != 'custom') {
            $rowClasses[] = sprintf('quform-responsive-columns-%s', $context['responsiveColumns']);
        }

        $output .= sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($rowClasses)));

        $output .= $this->getFieldHtml($context);

        $output .= '</div>';

        $output .= '</div>';

        return $output;
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

        foreach (self::$partKeys as $key => $slug) {
            if ($this->config($slug . 'Enabled') && ($part = $this->getPart($key)) instanceof Quform_Element) {
                $ariaLabelledby = array();

                if (Quform::isNonEmptyString($this->getLabel($context))) {
                    $ariaLabelledby[] = $this->getUniqueId() . '_label';
                }

                if (Quform::isNonEmptyString($part->config('subLabelAbove'))) {
                    $ariaLabelledby[] = $part->getUniqueId() . '_sub_label_above';
                }

                if (Quform::isNonEmptyString($part->config('subLabel'))) {
                    $ariaLabelledby[] = $part->getUniqueId() . '_sub_label_below';
                }

                if (count($ariaLabelledby)) {
                    $part->setConfig('aria-labelledby', join(' ', $ariaLabelledby));
                }

                $output .= sprintf('<div class="quform-element-column">%s</div>', $part->render($context));
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
        $output = sprintf('<div class="qfb-edit-name-row qfb-edit-name-row-%d">', count($this->parts));

        foreach (self::$partKeys as $key => $slug) {
            if ($this->config($slug . 'Enabled') && $this->getPart($key) instanceof Quform_Element_Editable) {
                $part = $this->getPart($key);

                $output .= sprintf(
                    '<div class="qfb-edit-name-column"><div class="qfb-edit-element qfb-edit-element-%1$s"><div class="qfb-edit-input qfb-edit-input-%1$s">%2$s</div></div></div>',
                    $part->getIdentifier(),
                    $part->getEditFieldHtml()
                );
            }
        }

        $output .= '</div>';

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
        $css = parent::renderCss($context);

        if ($context['fieldWidth'] == 'custom' && Quform::isNonEmptyString($context['fieldWidthCustom'])) {
            $css .= sprintf('.quform-input-name.quform-input-%s { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-input-name.quform-input-%s .quform-inner > .quform-input { width: 100%% !important; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        if ($context['responsiveColumns'] == 'custom' && Quform::isNonEmptyString($context['responsiveColumnsCustom'])) {
            $css .= sprintf(
                '@media (max-width: %s) { .quform-input-%s > .quform-element-row > .quform-element-column { float: none; width: 100%% !important; } }',
                Quform::addCssUnit($context['responsiveColumnsCustom']),
                $this->getIdentifier()
            );
        }

        foreach ($this->parts as $part) {
            $css .= $part->getCss($context);
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

        // Inside labels are not possible so set it above
        if ( ! in_array($context['labelPosition'], array('', 'left'), true)) {
            $context['labelPosition'] = '';
        }

        // Icon is the only possible tooltip type for this element
        $context['tooltipType'] = 'icon';

        if (is_string($this->config('responsiveColumns'))) {
            if ($this->config('responsiveColumns') != 'inherit') {
                $context['responsiveColumns'] = $this->config('responsiveColumns');

                if ($this->config('responsiveColumns') == 'custom' && Quform::isNonEmptyString($this->config('responsiveColumnsCustom'))) {
                    $context['responsiveColumnsCustom'] = $this->config('responsiveColumnsCustom');
                }
            }
        }

        return $context;
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_name', array(
            // Basic
            'label' => __('Name', 'quform'),
            'description' => '',
            'descriptionAbove' => '',
            'prefixEnabled' => false,
            'prefixRequired' => false,
            'prefixOptions' => array(
                array('id' => 1, 'label' => __('Mr', 'quform'), 'value' => __('Mr', 'quform')),
                array('id' => 2, 'label' => __('Mrs', 'quform'), 'value' => __('Mrs', 'quform')),
                array('id' => 3, 'label' => __('Ms', 'quform'), 'value' => __('Ms', 'quform')),
                array('id' => 4, 'label' => __('Miss', 'quform'), 'value' => __('Miss', 'quform')),
                array('id' => 5, 'label' => __('Dr', 'quform'), 'value' => __('Dr', 'quform')),
            ),
            'prefixNextOptionId' => 6,
            'prefixDefaultValue' => '',
            'prefixCustomiseValues' => false,
            'prefixNoneOption' => true,
            'prefixSubLabel' => __('Prefix', 'quform'),
            'prefixSubLabelAbove' => '',
            'prefixCustomClass' => '',
            'prefixCustomElementClass' => '',
            'firstEnabled' => true,
            'firstRequired' => false,
            'firstPlaceholder' => '',
            'firstSubLabel' => __('First', 'quform'),
            'firstSubLabelAbove' => '',
            'firstDefaultValue' => '',
            'firstCustomClass' => '',
            'firstCustomElementClass' => '',
            'firstAutocomplete' => '',
            'middleEnabled' => false,
            'middleRequired' => false,
            'middlePlaceholder' => '',
            'middleSubLabel' => __('Middle', 'quform'),
            'middleSubLabelAbove' => '',
            'middleDefaultValue' => '',
            'middleCustomClass' => '',
            'middleCustomElementClass' => '',
            'middleAutocomplete' => '',
            'lastEnabled' => true,
            'lastRequired' => false,
            'lastPlaceholder' => '',
            'lastSubLabel' => __('Last', 'quform'),
            'lastSubLabelAbove' => '',
            'lastDefaultValue' => '',
            'lastCustomClass' => '',
            'lastCustomElementClass' => '',
            'lastAutocomplete' => '',
            'suffixEnabled' => false,
            'suffixRequired' => false,
            'suffixPlaceholder' => '',
            'suffixSubLabel' => __('Suffix', 'quform'),
            'suffixSubLabelAbove' => '',
            'suffixDefaultValue' => '',
            'suffixCustomClass' => '',
            'suffixCustomElementClass' => '',

            // Styles
            'labelIcon' => '',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'responsiveColumns' => 'inherit',
            'responsiveColumnsCustom' => '',
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
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'showInEmail' => true,
            'saveToDatabase' => true,

            // Advanced
            'visibility' => '',
            'validators' => array(),

            // Translations
            'prefixNoneOptionText' => '',
            'messageRequired' => ''
        ));

        $config['type'] = 'name';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
