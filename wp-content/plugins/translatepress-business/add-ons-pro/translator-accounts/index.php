<?php
/*
TranslatePress - Translator Accounts Add-on

License: GPL2

== Copyright ==
Copyright 2017 Cozmoslabs (www.cozmoslabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}




/**
 * The code that instantiates our plugin
 * This action is documented in includes/class-plugin-name-activator.php
 */
function trp_in_run_translator_accounts(){

    require_once plugin_dir_path( __FILE__ ) . 'includes/class-translator-accounts.php';
    if ( class_exists( 'TRP_Translate_Press' ) ) {
        new TRP_IN_Translator_Accounts();
    }
}
add_action( 'plugins_loaded', 'trp_in_run_translator_accounts', 0 );

/**
 * Allow users with the translate_strings to translate the website, besides those with manage_options.
 *
 * Needs to be outside everything because of early call of get_lang_from_url_string when determining language.
 * Determining language happens when instantiating TRP_Translate_Press, this is why this function
 * needs to be before plugins_loaded.
 *
 * @since    1.0.5
 */
add_filter( 'trp_translating_capability', 'trp_in_ta_translator_account_permissions' );
function trp_in_ta_translator_account_permissions(){
    // Return manage_options for admins
    if (current_user_can('manage_options'))
        return 'manage_options';

    return 'translate_strings';
}