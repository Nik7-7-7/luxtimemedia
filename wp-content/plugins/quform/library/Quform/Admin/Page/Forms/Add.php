<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Forms_Add extends Quform_Admin_Page
{
    /**
     * Process this page
     */
    public function process()
    {
        if ( ! current_user_can('quform_add_forms')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        wp_safe_redirect(admin_url('admin.php?page=quform.forms#add'));
        exit;
    }
}
