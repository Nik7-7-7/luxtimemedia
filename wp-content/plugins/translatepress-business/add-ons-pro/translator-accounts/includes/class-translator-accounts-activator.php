<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    TranslatePress - Translator Accounts Add-on
 * @subpackage TranslatePress - Translator Accounts Add-on/includes
 * @author     Cristian Antohe
 */
if( !class_exists('TRP_IN_Translator_Accounts_Activator') ) {
    class TRP_IN_Translator_Accounts_Activator {
        /**
         * Create the translator user role.
         *
         * The translator user role is similar to a subscriber, with the extra capabilities of translate_strings and upload_files.
         *
         * @since    1.0.0
         */
        public static function activate() {
            $role = get_role( 'translator' );

            if ( $role ) {
                $role->add_cap( 'translate_strings' );
                $role->add_cap( 'upload_files' );
            } else {
                add_role(
                    'translator',
                    __( 'Translator', 'translatepress-multilingual' ),
                    array(
                        'read'              => true,
                        'translate_strings' => true,
                        'upload_files'      => true,
                    )
                );
            }
        }

    }
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
if ( !function_exists('trp_in_activate_translator_accounts') ) {
    function trp_in_activate_translator_accounts($addon) {
        if ( $addon === 'tp-add-on-translator-accounts/index.php' ) {
            TRP_IN_Translator_Accounts_Activator::activate();
        }
    }

    add_action( 'trp_add_ons_activate', 'trp_in_activate_translator_accounts', 10, 1 );
}