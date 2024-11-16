<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Group extends Quform_Element_Container
{
    /**
     * Render this group and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $context = $this->prepareContext($context);
        $output = '';

        if ($this->isVisible()) {
            $output .= sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getContainerClasses())));
            $output .= '<div class="quform-spacer">';
            $output .= $this->getTitleDescriptionHtml();
            $output .= '<div class="quform-child-elements">';

            foreach ($this->elements as $key => $element) {
                $output .= $element->render($context);
            }

            $output .= '</div></div></div>';
        } else {
            foreach ($this->getRecursiveIterator() as $element) {
                if ( ! $element instanceof Quform_Element_Field) {
                    continue;
                }

                if ($element->config('dynamicDefaultValue') && Quform::isNonEmptyString($element->config('dynamicKey'))) {
                    $output .= Quform::getHtmlTag('input', array(
                        'type' => 'hidden',
                        'name' => $element->getFullyQualifiedName(),
                        'value' => $element->getValue()
                    ));
                }
            }
        }

        return $output;
    }

    /**
     * Get the classes for the outermost group wrapper
     *
     * @return array
     */
    protected function getContainerClasses()
    {
        $classes = array(
            'quform-element',
            'quform-element-group',
            sprintf('quform-element-%s', $this->getIdentifier()),
            'quform-cf',
            sprintf('quform-group-style-%s', $this->config('groupStyle'))
        );

        if (Quform::isNonEmptyString($this->config('customElementClass'))) {
            $classes[] = $this->config('customElementClass');
        }

        $classes = apply_filters('quform_container_classes', $classes, $this);
        $classes = apply_filters('quform_container_classes_' . $this->getIdentifier(), $classes, $this);

        return $classes;
    }

    /**
     * Get the group label text
     *
     * @return string
     */
    public function getLabel()
    {
        return apply_filters('quform_group_label_' . $this->getIdentifier(), $this->config('label'), $this, $this->getForm());
    }

    /**
     * Render the CSS for this group and its children
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = '';

        if ($this->config('groupStyle') == 'bordered' && ($this->config('borderColor') || $this->config('backgroundColor'))) {
            $css .= sprintf('.quform .quform-group-style-bordered.quform-element-%1$s > .quform-spacer > .quform-child-elements,
                 .quform .quform-group-style-bordered.quform-page-%1$s > .quform-child-elements {', $this->getIdentifier());

            if ($this->config('borderColor')) {
                $css .= 'border-color: ' . esc_attr($this->config('borderColor')) . '!important;';
            }
            if ($this->config('backgroundColor')) {
                $css .= 'background-color: ' . esc_attr($this->config('backgroundColor')) . '!important;';
            }

            $css .= '}';
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
            'group' => '%s .quform-element-%s',
            'groupSpacer' => '%s .quform-element-%s > .quform-spacer',
            'groupTitle' => '%s .quform-element-%s > .quform-spacer > .quform-group-title-description .quform-group-title',
            'groupDescription' => '%s .quform-element-%s > .quform-spacer > .quform-group-title-description p.quform-group-description',
            'groupElements' => '%s .quform-element-%s > .quform-spacer > .quform-child-elements'
        );
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_group', array(
            // Basic
            'label' => __('Untitled group', 'quform'),
            'title' => '',
            'titleTag' => 'h4',
            'description' => '',

            // Styles
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'groupStyle' => 'plain',
            'borderColor' => '',
            'backgroundColor' => '',
            'customElementClass' => '',
            'styles' => array(),

            // Labels
            'tooltipType' => 'inherit',
            'tooltipEvent' => 'inherit',
            'labelPosition' => 'inherit',
            'labelWidth' => '',
            'showLabelInEmail' => false,
            'showLabelInEntry' => false,

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Advanced
            'visibility' => '',

            // Elements
            'elements' => array()
        ));

        $config['type'] = 'group';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
