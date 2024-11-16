<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Help extends Quform_Admin_Page
{

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/help.php';
    }

    /**
     * Get the HTML for the admin navigation menu
     *
     * @param   array|null  $currentForm  The data for the current form (if any)
     * @param   array       $extra        Extra HTML to add to the nav, the array key is the hook position
     * @return  string
     */
    public function getNavHtml(array $currentForm = null, array $extra = array())
    {
        $extra[40] = sprintf(
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-help_outline"></i><span class="qfb-nav-page-title">%s</span></div>',
            esc_html__('Help', 'quform')
        );

        return parent::getNavHtml($currentForm, $extra);
    }
}
