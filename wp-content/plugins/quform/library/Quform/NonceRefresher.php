<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_NonceRefresher
{
    /**
     * Refresh the plugin nonces via the Heartbeat API (e.g. if the user logged out in another tab)
     *
     * @param   array   $response  The Heartbeat response
     * @param   array   $data      The $_POST data sent
     * @param   string  $screenId  The ID of the screen
     * @return  array
     */
    public function refreshNonces($response, $data, $screenId)
    {
        switch ($screenId) {
            case 'forms_page_quformforms':
                $response['quformSaveFormNonce'] = wp_create_nonce('quform_save_form');
                $response['quformAddFormNonce'] = wp_create_nonce('quform_add_form');
                $response['quformSaveFormsTableSettingsNonce'] = wp_create_nonce('quform_save_forms_table_settings');
                break;
            case 'forms_page_quformentries':
                $response['quformSetEntryLabelsNonce'] = wp_create_nonce('quform_set_entry_labels');
                $response['quformSaveEntriesTableSettingsNonce'] = wp_create_nonce('quform_save_entries_table_settings');
                $response['quformResendNotificationsNonce'] = wp_create_nonce('quform_resend_notifications');
                break;
            case 'forms_page_quformsettings':
                $response['quformSaveSettingsNonce'] = wp_create_nonce('quform_save_settings');
                $response['quformUpdateCheckNonce'] = wp_create_nonce('quform_manual_update_check');
                $response['quformVerifyNonce'] = wp_create_nonce('quform_verify_purchase_code');
                $response['quformRebuildScriptCacheNonce'] = wp_create_nonce('quform_rebuild_script_cache');
                break;
            case 'forms_page_quformtools':
                $response['quformExportFormNonce'] = wp_create_nonce('quform_export_form');
                $response['quformImportFormNonce'] = wp_create_nonce('quform_import_form');
                $response['quformMigrateSettingsNonce'] = wp_create_nonce('quform_migrate_settings');
                $response['quformMigrateNonce'] = wp_create_nonce('quform_migrate_form');
                $response['quformMigrateImportFormNonce'] = wp_create_nonce('quform_migrate_import_form');
                break;
        }

        return $response;
    }
}
