<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Html extends Quform_Element
{
    /**
     * Render this element and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $output = '';

        if ($this->isVisible()) {
            $output .= sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getElementClasses($context))));
            $output .= sprintf('<div class="quform-spacer">%s</div>', $this->getContent());
            $output .= '</div>';
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

        if (Quform::isNonEmptyString($this->config('customElementClass'))) {
            $classes[] = $this->config('customElementClass');
        }

        $classes = apply_filters('quform_element_classes', $classes, $this, $context);
        $classes = apply_filters('quform_element_classes_' . $this->getIdentifier(), $classes, $this, $context);

        return $classes;
    }

    /**
     * Get the HTML content
     *
     * @param   string  $format  The format, 'html' (default) or 'text' for plain text
     * @return  string
     */
    public function getContent($format = 'html')
    {
        if ($format == 'text' && Quform::isNonEmptyString($this->config('plainTextContent'))) {
            $content = $this->config('plainTextContent');
        } else {
            $content = $this->config('content');

            if ($this->config('autoFormat')) {
                $content = nl2br($content);
            }
        }

        $content = $this->form->replaceVariablesPreProcess($content);

        $content = do_shortcode($content);

        if (is_admin()) {
            // Filter JS in the WP admin (e.g. form preview, view entry) to avoid XSS
            $content = wp_kses_post($content);
        }

        $content = apply_filters('quform_html_content', $content, $format, $this, $this->form);
        $content = apply_filters('quform_html_content_' . $this->getIdentifier(), $content, $format, $this, $this->form);

        return $content;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getContent() === '';
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
            'elementSpacer' => '%s .quform-element-%s > .quform-spacer'
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
        $config = apply_filters('quform_default_config_html', array(
            // Basic
            'label' => __('HTML', 'quform'),
            'content' => '',
            'autoFormat' => false,

            // Styles
            'customElementClass' => '',
            'styles' => array(),

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Data
            'showInEmail' => false,
            'plainTextContent' => '',
            'showInEntry' => false,

            // Advanced
            'visibility' => ''
        ));

        $config['type'] = 'html';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
