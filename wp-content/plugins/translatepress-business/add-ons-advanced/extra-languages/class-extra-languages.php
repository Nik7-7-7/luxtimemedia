<?php

class TRP_IN_Extra_Languages{

    protected $url_converter;
    protected $trp_languages;
    protected $settings;
    protected $loader;

    public function __construct() {

        define( 'TRP_IN_EL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'TRP_IN_EL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

        $trp = TRP_Translate_Press::get_trp_instance();
        $this->loader = $trp->get_component( 'loader' );
        $this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_sortable_language_script' );
        $this->loader->remove_hook( 'trp_language_selector' );
        $this->loader->add_action( 'trp_language_selector', $this, 'languages_selector', 10, 1 );

    }

    public function languages_selector( $languages ){
        if ( ! $this->url_converter ){
            $trp = TRP_Translate_Press::get_trp_instance();
            $this->url_converter = $trp->get_component( 'url_converter' );
        }
        if ( ! $this->settings ){
            $trp = TRP_Translate_Press::get_trp_instance();
            $trp_settings = $trp->get_component( 'settings' );
            $this->settings = $trp_settings->get_settings();
        }
        require_once( TRP_IN_EL_PLUGIN_DIR . 'partials/language-selector-pro.php' );
    }

    public function enqueue_sortable_language_script( ){
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'translate-press' ){
            wp_enqueue_script( 'trp-sortable-languages', TRP_IN_EL_PLUGIN_URL . 'assets/js/trp-sortable-languages.js', array( 'jquery-ui-sortable' ), TRP_PLUGIN_VERSION );
        }
    }

}