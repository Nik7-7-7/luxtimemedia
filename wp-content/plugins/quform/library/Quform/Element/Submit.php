<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Submit extends Quform_Element
{
    /**
     * Render this element and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $context = $this->prepareContext($context);

        if ( ! $this->isVisible()) {
            return '';
        }

        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getElementClasses($context))));

        if ($this->form->hasPages()) {
            if ( ! $this->isOnFirstPage() && $this->form->config('backLocation') != 'after') {
                $output .= $this->getButtonHtml('back', __('Back', 'quform'));
            }

            if ( ! $this->isOnLastPage()) {
                $output .= $this->getButtonHtml('next', __('Next', 'quform'));
            } else {
                $output .= $this->getButtonHtml('submit', __('Send', 'quform'));
            }

            if ( ! $this->isOnFirstPage() && $this->form->config('backLocation') == 'after') {
                $output .= $this->getButtonHtml('back', __('Back', 'quform'));
            }

        } else {
            $output .= $this->getButtonHtml('submit', __('Send', 'quform'));
        }

        $output .= $this->getLoadingHtml();

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the classes for the outermost element wrapper
     *
     * @param   array   $context
     * @return  array
     */
    protected function getElementClasses(array $context = array())
    {
        $classes = array(
            'quform-element',
            'quform-element-submit',
            sprintf('quform-element-%s', $this->getIdentifier()),
            'quform-cf'
        );

        if (Quform::isNonEmptyString($context['buttonStyle'])) {
            $classes[] = sprintf('quform-button-style-%s', $context['buttonStyle']);
        }

        if (Quform::isNonEmptyString($context['buttonSize'])) {
            $classes[] = sprintf('quform-button-size-%s', $context['buttonSize']);
        }

        if (Quform::isNonEmptyString($context['buttonWidth']) && $context['buttonWidth'] != 'custom') {
            $classes[] = sprintf('quform-button-width-%s', $context['buttonWidth']);
        }

        if (Quform::isNonEmptyString($this->config('customElementClass'))) {
            $classes[] = $this->config('customElementClass');
        }

        $classes = apply_filters('quform_element_classes', $classes, $this, $context);
        $classes = apply_filters('quform_element_classes_' . $this->getIdentifier(), $classes, $this, $context);

        return $classes;
    }

    /**
     * Get the HTML for the submit / next / back button
     *
     * @param   string  $which         Which button - 'submit', 'next' or 'back'
     * @param   string  $defaultText   The fallback text for the default button
     * @return  string
     */
    protected function getButtonHtml($which, $defaultText)
    {
        if ($this->config($which . 'Type') == 'inherit') {
            $config = array(
                'type' => $this->form->config($which . 'Type'),
                'text' => $this->form->config($which . 'Text'),
                'icon' => $this->form->config($which . 'Icon'),
                'iconPosition' => $this->form->config($which . 'IconPosition'),
                'image' => $this->form->config($which . 'Image'),
                'html' => $this->form->config($which . 'Html'),
            );
        } else {
            $config = array(
                'type' => $this->config($which . 'Type'),
                'text' => $this->config($which . 'Text'),
                'icon' => $this->config($which . 'Icon'),
                'iconPosition' => $this->config($which . 'IconPosition') == 'inherit' ? $this->form->config($which . 'IconPosition') : $this->config($which . 'IconPosition'),
                'image' => $this->config($which . 'Image'),
                'html' => $this->config($which . 'Html'),
            );
        }

        $classes = array(
            sprintf('quform-button-%s', $which),
            sprintf('quform-button-%s-%s', $which, $config['type']),
            sprintf('quform-button-%s-%s', $which, $this->getIdentifier())
        );

        if ($config['type'] == 'default' && Quform::isNonEmptyString($config['icon'])) {
            $classes[] = sprintf('quform-button-icon-%s', $config['iconPosition']);
        }

        $output = sprintf('<div class="%s"%s>',
            esc_attr(join(' ', $classes)),
            Quform::isNonEmptyString($this->form->config('buttonAnimation')) ? sprintf(' data-animation="%s"', esc_attr($this->form->config('buttonAnimation'))) : ''
        );

        $output .= '<button name="quform_submit" type="submit" class="quform-' . esc_attr($which) . '" value="' . ($which == 'back' ? 'back' : 'submit') . '">';

        if ($config['type'] == 'default') {
            $text = sprintf(
                '<span class="%s">%s</span>',
                esc_attr(sprintf('quform-button-text quform-button-%s-text', $which)),
                esc_html(Quform::isNonEmptyString($config['text']) ? $config['text'] : $defaultText)
            );

            if (Quform::isNonEmptyString($config['icon'])) {
                $icon = sprintf(
                    '<span class="%s"><i class="%s"></i></span>',
                    esc_attr(sprintf('quform-button-icon quform-button-%s-icon', $which)),
                    esc_attr($config['icon'])
                );

                if ($config['iconPosition'] == 'right') {
                    $output .= $text . $icon;
                } else {
                    $output .= $icon . $text;
                }
            } else {
                $output .= $text;
            }
        } elseif ($config['type'] == 'image') {
            $output .= '<img src="' . esc_url($config['image']) . '" alt="' . esc_attr($which) . '">';
        } elseif ($config['type'] == 'html') {
            $output .= do_shortcode($config['html']);
        }

        $output .= '</button></div>';

        return $output;
    }

    /**
     * Get the HTML for the loading indicator
     *
     * @return string
     */
    protected function getLoadingHtml()
    {
        if ( ! Quform::isNonEmptyString($this->form->config('loadingType')) || $this->form->config('loadingPosition') == 'over-form') {
            return '';
        }

        $classes = array(
            'quform-loading',
            sprintf('quform-loading-position-%s', $this->form->config('loadingPosition'))
        );

        if ($this->config('loadingType') != 'custom') {
            $classes[] = sprintf('quform-loading-type-%s', $this->form->config('loadingType'));
        }

        $output = sprintf('<div class="%s">', esc_attr(join(' ', $classes)));

        $output .= '<div class="quform-loading-inner">';

        if ($this->form->config('loadingType') == 'custom') {
            $output .= do_shortcode($this->form->config('loadingCustom'));
        } else {
            $output .= '<div class="quform-loading-spinner"><div class="quform-loading-spinner-inner"></div></div>';
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Is this button on the first page?
     *
     * @return bool
     */
    public function isOnFirstPage()
    {
        return $this->isOnPage($this->form->getFirstPage());
    }

    /**
     * Is this button on the last page?
     *
     * @return bool
     */
    public function isOnLastPage()
    {
        return $this->isOnPage($this->form->getLastPage());
    }

    /**
     * Is this button a child of the given page
     *
     * @param   Quform_Element_Page|null  $page
     * @return  bool
     */
    protected function isOnPage($page)
    {
        if ($page instanceof Quform_Element_Page) {
            foreach ($page->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $child) {
                if ($child->getName() == $this->getName()) {
                    return true;
                }
            }
        }

        return false;
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

        if ($context['buttonWidth'] == 'custom' && Quform::isNonEmptyString($context['buttonWidthCustom'])) {
            $css .= sprintf('.quform-element-submit.quform-element-%s button { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['buttonWidthCustom']));
        }

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
            'submit' => '%s .quform-element-submit.quform-element-%s',
            'submitInner' => '%s .quform-button-submit.quform-button-submit-%s',
            'submitButton' => '%1$s .quform-button-submit.quform-button-submit-%2$s button, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button',
            'submitButtonHover' => '%1$s .quform-button-submit.quform-button-submit-%2$s button:hover, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button:hover',
            'submitButtonActive' => '%1$s .quform-button-submit.quform-button-submit-%2$s button:active, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button:active',
            'submitButtonText' => '%1$s .quform-button-submit.quform-button-submit-%2$s button .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button .quform-button-text',
            'submitButtonTextHover' => '%1$s .quform-button-submit.quform-button-submit-%2$s button:hover .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button:hover .quform-button-text',
            'submitButtonTextActive' => '%1$s .quform-button-submit.quform-button-submit-%2$s button:active .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button:active .quform-button-text',
            'submitButtonIcon' => '%1$s .quform-button-submit.quform-button-submit-%2$s button .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button .quform-button-icon',
            'submitButtonIconHover' => '%1$s .quform-button-submit.quform-button-submit-%2$s button:hover .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button:hover .quform-button-icon',
            'submitButtonIconActive' => '%1$s .quform-button-submit.quform-button-submit-%2$s button:active .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit.quform-button-submit-%2$s button:active .quform-button-icon',
            'backInner' => '%s .quform-button-back.quform-button-back-%s',
            'backButton' => '%1$s .quform-button-back.quform-button-back-%2$s button, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button',
            'backButtonHover' => '%1$s .quform-button-back.quform-button-back-%2$s button:hover, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button:hover',
            'backButtonActive' => '%1$s .quform-button-back.quform-button-back-%2$s button:active, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button:active',
            'backButtonText' => '%1$s .quform-button-back.quform-button-back-%2$s button .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button .quform-button-text',
            'backButtonTextHover' => '%1$s .quform-button-back.quform-button-back-%2$s button:hover .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button:hover .quform-button-text',
            'backButtonTextActive' => '%1$s .quform-button-back.quform-button-back-%2$s button:active .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button:active .quform-button-text',
            'backButtonIcon' => '%1$s .quform-button-back.quform-button-back-%2$s button .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button .quform-button-icon',
            'backButtonIconHover' => '%1$s .quform-button-back.quform-button-back-%2$s button:hover .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button:hover .quform-button-icon',
            'backButtonIconActive' => '%1$s .quform-button-back.quform-button-back-%2$s button:active .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back.quform-button-back-%2$s button:active .quform-button-icon',
            'nextInner' => '%s .quform-button-next.quform-button-next-%s',
            'nextButton' => '%1$s .quform-button-next.quform-button-next-%2$s button, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button',
            'nextButtonHover' => '%1$s .quform-button-next.quform-button-next-%2$s button:hover, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button:hover',
            'nextButtonActive' => '%1$s .quform-button-next.quform-button-next-%2$s button:active, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button:active',
            'nextButtonText' => '%1$s .quform-button-next.quform-button-next-%2$s button .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button .quform-button-text',
            'nextButtonTextHover' => '%1$s .quform-button-next.quform-button-next-%2$s button:hover .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button:hover .quform-button-text',
            'nextButtonTextActive' => '%1$s .quform-button-next.quform-button-next-%2$s button:active .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button:active .quform-button-text',
            'nextButtonIcon' => '%1$s .quform-button-next.quform-button-next-%2$s button .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button .quform-button-icon',
            'nextButtonIconHover' => '%1$s .quform-button-next.quform-button-next-%2$s button:hover .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button:hover .quform-button-icon',
            'nextButtonIconActive' => '%1$s .quform-button-next.quform-button-next-%2$s button:active .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next.quform-button-next-%2$s button:active .quform-button-icon'
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
        $config = apply_filters('quform_default_config_submit', array(
            // Basic
            'label' => __('Submit', 'quform'),
            'submitType' => 'inherit',
            'submitText' => '',
            'submitIcon' => '',
            'submitIconPosition' => 'inherit',
            'submitImage' => '',
            'submitHtml' => '',
            'nextType' => 'inherit',
            'nextText' => '',
            'nextIcon' => '',
            'nextIconPosition' => 'inherit',
            'nextImage' => '',
            'nextHtml' => '',
            'backType' => 'inherit',
            'backText' => '',
            'backIcon' => '',
            'backIconPosition' => 'inherit',
            'backImage' => '',
            'backHtml' => '',

            // Styles
            'buttonStyle' => 'inherit',
            'buttonSize' => 'inherit',
            'buttonWidth' => 'inherit',
            'buttonWidthCustom' => '',
            'customElementClass' => '',
            'styles' => array(),

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Advanced
            'visibility' => ''
        ));

        $config['type'] = 'submit';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
