<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Row extends Quform_Element_Container
{
    /**
     * Render the row and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $context = $this->prepareContext($context);

        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getContainerClasses($context))));

        foreach ($this->elements as $key => $element) {
            $output .= $element->render($context);
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the classes for the outermost row wrapper
     *
     * @param   array  $context
     * @return  array
     */
    protected function getContainerClasses(array $context = array())
    {
        $classes = array(
            'quform-element',
            'quform-element-row',
            sprintf('quform-element-row-%s', $this->getIdentifier()),
            sprintf('quform-%d-columns', count($this->elements)),
            sprintf('quform-element-row-size-%s', $this->config('columnSize'))
        );

        if (Quform::isNonEmptyString($context['responsiveColumns']) && $context['responsiveColumns'] != 'custom') {
            $classes[] = sprintf('quform-responsive-columns-%s', $context['responsiveColumns']);
        }

        $classes = apply_filters('quform_container_classes', $classes, $this);
        $classes = apply_filters('quform_container_classes_' . $this->getIdentifier(), $classes, $this);

        return $classes;
    }

    /**
     * Render the CSS for this element and its children
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = '';

        if ($context['responsiveColumns'] == 'custom' && Quform::isNonEmptyString($context['responsiveColumnsCustom'])) {
            $css .= sprintf(
                '@media (max-width: %s) { .quform-element-row-%s > .quform-element-column { float: none; width: 100%% !important; } }',
                Quform::addCssUnit($context['responsiveColumnsCustom']),
                $this->getIdentifier()
            );
        }

        $css .= parent::renderCss($context);

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
        $config = apply_filters('quform_default_config_row', array(
            // Settings
            'columnSize' => 'fixed',
            'responsiveColumns' => 'inherit',
            'responsiveColumnsCustom' => '',

            // Elements
            'elements' => array()
        ));

        $config['type'] = 'row';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }

    /**
     * Inherit settings from this row into the context
     *
     * @param   array  $context
     * @return  array
     */
    protected function prepareContext(array $context = array())
    {
        $context = parent::prepareContext($context);

        $context['columnSize'] = $this->config('columnSize');

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
}
