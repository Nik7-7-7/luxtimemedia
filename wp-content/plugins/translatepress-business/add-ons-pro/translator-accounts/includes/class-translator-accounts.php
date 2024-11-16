<?php
/**
 * Main Class For Translator Accounts
 *
 * @link       https://translatepress.com
 * @since      1.0.0
 *
 * @package    TranslatePress - Translator Accounts Add-on
 * @subpackage TranslatePress - Translator Accounts Add-on/includes
 */
/**
 * Main functionality for the translator accounts addon.
 *
 * This allows the administrator to create users with the translator role or assign the translator role to existing users, except those who can manage options.
 *
 * @since      1.0.0
 * @package    TranslatePress - Translator Accounts Add-on
 * @subpackage TranslatePress - Translator Accounts Add-on/includes
 * @author     Cristian Antohe
 */
class TRP_IN_Translator_Accounts{
    protected $loader;

    /**
     * Init all the hooks and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {

        define( 'TRP_IN_TA_PLUGIN_DIR', plugin_dir_path( __DIR__ ) );
        define( 'TRP_IN_TA_PLUGIN_URL', plugin_dir_url( __DIR__ ) );

        $trp = TRP_Translate_Press::get_trp_instance();
        $this->loader = $trp->get_component( 'loader' );
        $this->loader->add_action( 'show_admin_bar', $this, 'show_admin_bar', 80, 1 );
        $this->loader->add_action( 'admin_bar_menu', $this, 'remove_settings_link', 999, 1 );
        $this->loader->add_action( 'admin_menu', $this, 'output_translation_button' );

        $this->loader->add_action( 'show_user_profile', $this, 'translator_profile_field', 10, 1 );
        $this->loader->add_action( 'edit_user_profile', $this, 'translator_profile_field', 10, 1 );
        $this->loader->add_action( 'profile_update', $this, 'translator_save_profile_field', 20, 2 );

    }

    /**
     * Always show the admin bar if the user has the translate_strings capability.
     *
     * @since    1.0.0
     */
    public function show_admin_bar($show){
        if (current_user_can('translate_strings') && apply_filters('trp_force_show_admin_bar_for_translator_accounts', true ) )
            return true;

        return $show;
    }

    /**
     * Anyone who can't manage_options should not see the TranslatePress Settings page link.
     *
     * @since    1.0.0
     */
    public function remove_settings_link( $wp_admin_bar ){
        if ( !current_user_can('manage_options') )
            $wp_admin_bar->remove_node( 'trp_settings_page' );
    }


    /**
     * Add a checkbox to user profile page to add the translator role to it.
     *
     * If the user is an administrator the checkbox is always checked and disabled. We're also modifying the default WordPress role select to be named trp_role so WordPress does not overwrite our checkbox.
     *
     * @since    1.0.0
     */
    public function translator_profile_field( $user ){
        // users with translate_strings can translate the website
        $checked = "";
        if ( user_can( $user,'translate_strings' ) ){
            $checked = ' checked="checked" ';
        }

        // administrators can translate the website
        $disabled = '';
        if ( user_can( $user,'manage_options' ) ){
            $checked = ' checked="checked" ';
            $disabled = ' disabled="disabled" ';
        }

        // only show this setting to the administrator. Translators can not make themselves a translator.
        if (current_user_can('manage_options')) :
            ?>
            <h3><?php esc_html_e(" TranslatePress Settings", "translatepress-multilingual"); ?></h3>

            <table class="form-table">
                <tr class="">
                    <th scope="row"><?php esc_html_e('Translator', 'translatepress-multilingual');?></th>
                    <td><fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Translator', 'translatepress-multilingual');?></span></legend>
                            <label for="trp_translator">
                                <input name="trp_translator" id="trp_translator_hidden" value="0" type="hidden">
                                <input name="trp_translator" id="trp_translator" value="1" <?php echo $checked . $disabled; //phpcs:ignore ?> type="checkbox">
                                <?php esc_html_e('Allow this user to translate the website.', 'translatepress-multilingual');?></label><br>
                        </fieldset>
                    </td>
                </tr>
            </table>
        <?php endif;
    }

	/**
     * Returns id the user is translator, ie has the 'translator' role attached
	 * @param $user_data can be current user data object or old user data, that is received at load time of user settings.
	 *
	 * @return bool
	 */
	public function is_user_translator($user_data){
	    if (isset($user_data->roles) && in_array( 'translator', (array) $user_data->roles )){
		    return true;
	    } else {
		    return false;
	    }
    }

    /**
     * Save the user translator role if needed the case.
     *
     * @since    1.0.0
     */
    public function translator_save_profile_field( $user_id, $old_user_data ){
        // First Take into account the WordPress Role
        $user = new WP_User($user_id);

        // Next add the extra translator user role if needed.
        // only an administrator can make a translator and an administrator doesn't need to be a translator.
        if ( !current_user_can( 'edit_users' ) || user_can($user_id, 'manage_options') ) {
            return;
        }

        if( isset( $_POST['trp_translator'] ) && $_POST['trp_translator'] == '1' ) {
            $user ->add_role('translator');
        } elseif( isset( $_POST['trp_translator'] ) && $_POST['trp_translator'] == '0'  && $this->is_user_translator($old_user_data) ) {
            $user->remove_role( 'translator' );
        }
    }

    /**
     * TRP Translator button is added as a top-level menu page due to wordpress.com not showing the admin bar
     *
     * @return void
     */
    public function output_translation_button(){
        add_menu_page( 'TRP Translator button', __( 'Translate Site', 'translatepress-multilingual' ), 'translate_strings', add_query_arg( 'trp-edit-translation', 'true', trailingslashit( home_url() ) ), '', 'dashicons-translation' );
    }
}