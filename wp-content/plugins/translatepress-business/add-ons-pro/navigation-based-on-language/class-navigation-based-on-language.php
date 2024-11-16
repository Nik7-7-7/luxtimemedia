<?php

class TRP_IN_Navigation_Based_on_Language{

    protected $loader;
    protected $slug_manager;
    protected $settings;
    protected $trp_languages;

    public function __construct() {

        define( 'TRP_IN_NBL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'TRP_IN_NBL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

        $trp = TRP_Translate_Press::get_trp_instance();
        $this->loader = $trp->get_component( 'loader' );
        $trp_settings = $trp->get_component( 'settings' );
        $this->settings = $trp_settings->get_settings();
        $this->trp_languages = $trp->get_component( 'languages' );

        require_once(  TRP_IN_NBL_PLUGIN_DIR . 'includes/class-tp-nbl-walker-nav-menu.php' );


        //$this->loader->add_filter( 'trp_view_as_values', $this, 'trp_bor_view_as_values' );
        // switch the admin walker
        $this->loader->add_filter('wp_edit_nav_menu_walker', $this, 'change_nav_menu_walker' );
        // add extra fields in menu items on the hook we define in the walker class
        $this->loader->add_action('wp_nav_menu_item_custom_fields', $this, 'extra_fields', 10, 4);
        // save the extra fields
        $this->loader->add_action('wp_update_nav_menu_item', $this, 'update_menu', 10, 2);
        // exclude items from frontend
        if ( ! is_admin() ){
            $this->loader->add_filter( 'wp_get_nav_menu_items', $this, 'hide_menu_elements' );
        }

    }


    /*
     * Change the default walker class for the menu
     *
     * @param $walker the filtered walker class
     * @return string new walker
     *
     */
    function change_nav_menu_walker( $walker ){
        global $wp_version;
        if ( version_compare( $wp_version, "5.4", "<" ) ) {
            $walker = 'TRP_IN_NBL_Walker_Nav_Menu';
        }

        return $walker;
    }

    /*
     * Function that ads extra fields on the hook we added in the walker class
     *
     */
    function extra_fields( $item_id, $item, $depth, $args ) {
        $languages = get_post_meta( $item->ID, '_trp_menu_languages', true );
        if( empty( $languages ) )
            $languages = array();
        else
            $languages = explode( ',', $languages );
        ?>

        <input type="hidden" name="trp-menu-filtering" value="<?php echo esc_attr( wp_create_nonce('trp-menu-filtering') ); ?>"/>

        <?php
        $all_languages = $this->trp_languages->get_languages( 'english_name' );
        $our_languages = $this->settings['translation-languages'];
        if( !empty( $all_languages ) && !empty( $our_languages ) ){
            ?>
            <div class="trp-languages">
                <p class="description"><?php esc_html_e("Limit this menu item to the following languages", 'translatepress-multilingual'); ?></p>

                <label class="trp-language-checkbox-label">
                    <input type="checkbox"
                           value="trp_nbol_all_languages" <?php if (in_array('trp_nbol_all_languages', $languages) || empty($languages)) echo 'checked="checked"'; ?>
                           name="trp-languages_<?php echo esc_attr($item->ID); ?>[]"/>
                    <?php esc_html_e( 'All Languages', 'translatepress-multilingual' ); ?>
                </label>

                <?php
                $readonly = '';
                if (in_array('trp_nbol_all_languages', $languages) || empty($languages)) $readonly = 'readonly="readonly"';
                ?>

                <?php foreach( $all_languages as $language_code => $all_language ) {
                    if ( in_array($language_code, $our_languages) ) {
                        ?>
                        <label class="trp-language-checkbox-label">
                            <input type="checkbox"
                                   value="<?php echo esc_attr($language_code); ?>" <?php if (in_array($language_code, $languages) ) echo 'checked="checked"'; ?>
                                   name="trp-languages_<?php echo esc_attr($item->ID); ?>[]" <?php echo $readonly; //phpcs:ignore ?> class="trp-nbol-lang-input"/>
                            <?php echo esc_html($all_language); ?>
                        </label>
                    <?php }
                }
                ?>

            </div>
        <?php } ?>
        <?php
    }

    /**
     * Save the values on the menu
     */
    function update_menu($menu_id, $menu_item_db_id){

        // verify this came from our screen and with proper authorization.
        if (!isset($_POST['trp-menu-filtering']) || !wp_verify_nonce( sanitize_text_field( $_POST['trp-menu-filtering'] ), 'trp-menu-filtering'))
            return;

        if( !empty( $_REQUEST['trp-languages_'.$menu_item_db_id] ) && is_array( $_REQUEST['trp-languages_'.$menu_item_db_id] ) ) {
            $languages = array_map( 'sanitize_text_field', $_REQUEST['trp-languages_'.$menu_item_db_id] );
            update_post_meta( $menu_item_db_id, '_trp_menu_languages', implode( ',', $languages ) );
        } else
            delete_post_meta( $menu_item_db_id, '_trp_menu_languages' );
    }

    /**
     * Function that hides the elements on the frontend
     * @param $items the filtered item objects in the menu
     */
    function hide_menu_elements( $items ){
        $hide_children_of = array();

        // Iterate over the items to search and destroy
        foreach ( $items as $key => $item ) {

            $visible = true;

            // hide any item that is the child of a hidden item
            if( in_array( $item->menu_item_parent, $hide_children_of ) ){
                $visible = false;
                $hide_children_of[] = $item->ID; // for nested menus
            }

            // check any item that has NMR roles set
            if( $visible ){

                $languages = get_post_meta( $item->ID, '_trp_menu_languages', true );
                if( empty( $languages ) )
                    $languages = array();
                else
                    $languages = explode( ',', $languages );

                if( !empty( $languages ) && !in_array( 'trp_nbol_all_languages',$languages ) ){
                    global $TRP_LANGUAGE;
                    if( !in_array( $TRP_LANGUAGE, $languages ) )
                        $visible = false;
                }


            }

            // add filter to work with plugins that don't use traditional roles
            $visible = apply_filters( 'nav_menu_roles_item_visibility', $visible, $item );

            // unset non-visible item
            if ( ! $visible ) {
                $hide_children_of[] = $item->ID; // store ID of item
                unset( $items[$key] ) ;
            }

        }

        return $items;
    }

    public function enqueue_navigation_script( ){
        global $pagenow;
        if( $pagenow === 'nav-menus.php' ) {
            wp_enqueue_script('trp-sortable-languages', TRP_IN_NBL_PLUGIN_URL . 'assets/js/trp-navigation.js', array('jquery'), TRP_PLUGIN_VERSION);
        }
    }

    
}