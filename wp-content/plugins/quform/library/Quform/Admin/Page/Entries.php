<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Admin_Page_Entries extends Quform_Admin_Page
{
    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        parent::enqueueScripts();

        wp_enqueue_script('quform-entries', Quform::adminUrl('js/entries.min.js'), array('jquery'), QUFORM_VERSION, true);

        wp_localize_script('quform-entries', 'quformEntriesL10n', array(
            'setEntryLabelsNonce' => wp_create_nonce('quform_set_entry_labels'),
            'errorSettingEntryLabel' => __('An error occurred setting the entry label', 'quform')
        ));
    }

    /**
     * Get the HTML for the entry label indicators
     *
     * @param   array   $labels  The selected labels data
     * @return  string
     */
    public function getEntryLabelsHtml(array $labels)
    {
        $output = '<div class="qfb-entry-label-indicators qfb-cf">';
        $hasVisibleLabel = false;

        foreach ($labels as $label) {
            if (is_array($label)) {
                $classes = array('qfb-entry-label-indicator');

                if ($this->isColorTransparent($label['color'])) {
                    $classes[] = 'qfb-entry-label-indicator-transparent';
                } else {
                    $hasVisibleLabel = true;
                }

                $output .= sprintf(
                    '<span class="%s" data-id="%d" style="background-color: %s;"%s></span>',
                    Quform::escape(join(' ', $classes)),
                    Quform::escape($label['id']),
                    Quform::escape($label['color']),
                    Quform::isNonEmptyString($label['name']) ? sprintf(' title="%s"', Quform::escape($label['name'])) : ''
                );
            }
        }

        $output .= sprintf('<i class="qfb-entry-no-labels qfb-icon qfb-icon-tags%s"></i>', $hasVisibleLabel ? ' qfb-hidden' : '');

        $output .= '</div>';

        return $output;
    }

    /**
     * Is the given CSS color transparent?
     *
     * @param string $color
     * @return bool
     */
    public function isColorTransparent($color)
    {
        if ($color == 'transparent') {
            return true;
        }

        if (preg_match('/rgba\((.+)\)/', $color, $matches)) {
            $parts = explode(',', $matches[1]);

            if (count($parts) == 4 && is_numeric($parts[3]) && floatval($parts[3]) === 0.0) {
                return true;
            }
        }

        return false;
    }
}
