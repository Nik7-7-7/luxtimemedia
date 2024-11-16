<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Admin_Page_Tools extends Quform_Admin_Page
{
    /**
     * Get the admin sub navigation HTML
     *
     * @return string
     */
    public function getSubNavHtml()
    {
        $links = array(
            array(
                'cap' => 'quform_view_tools',
                'href' => admin_url('admin.php?page=quform.tools'),
                'class' => 'home',
                'text' => __('Home', 'quform')
            ),
            array(
                'cap' => 'quform_export_entries',
                'href' => admin_url('admin.php?page=quform.tools&sp=export.entries'),
                'class' => 'export-entries',
                'text' => __('Export Entries', 'quform')
            ),
            array(
                'cap' => 'quform_export_forms',
                'href' => admin_url('admin.php?page=quform.tools&sp=export.form'),
                'class' => 'export-form',
                'text' => __('Export Form', 'quform')
            ),
            array(
                'cap' => 'quform_import_forms',
                'href' => admin_url('admin.php?page=quform.tools&sp=import.form'),
                'class' => 'import-form',
                'text' => __('Import Form', 'quform')
            ),
            array(
                'cap' => 'quform_full_access',
                'href' => admin_url('admin.php?page=quform.tools&sp=migrate'),
                'class' => 'migrate',
                'text' => __('Migrate', 'quform')
            ),
            array(
                'cap' => 'activate_plugins',
                'href' => admin_url('admin.php?page=quform.tools&sp=uninstall'),
                'class' => 'uninstall',
                'text' => __('Uninstall', 'quform')
            )
        );

        $visible = array();
        foreach ($links as $link) {
            if (current_user_can($link['cap'])) {
                $visible[] = $link;
            }
        }

        if ( ! count($visible)) {
            return '';
        }

        ob_start();
        ?>
        <div class="qfb-sub-nav qfb-cf">
            <ul class="qfb-sub-nav-ul">
                <?php
                foreach ($visible as $item) {
                    echo '<li class="qfb-sub-nav-tools-' . $item['class'] . '"><a href="' . esc_url($item['href']) . '"><span>' . esc_html($item['text']) . '</span></a></li>';
                }
                ?>
            </ul>
        </div>
        <?php

        return ob_get_clean();
    }
}
