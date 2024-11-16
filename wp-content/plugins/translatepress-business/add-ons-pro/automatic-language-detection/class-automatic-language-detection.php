<?php

class TRP_IN_Automatic_Language_Detection {

	protected $settings;
	protected $loader;
	/* @var TRP_Url_Converter */
	protected $url_converter;
	/* @var TRP_IN_ALD_Settings */
	protected $trp_ald_settings;
	/* @var TRP_Languages */
	protected $trp_languages;

	/**
	 * TRP_Automatic_Language_Detection constructor.
	 *
	 * Defines constants, adds hooks and deals with license page.
	 */
	public function __construct() {

		define( 'TRP_IN_ALD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'TRP_IN_ALD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		require_once( TRP_IN_ALD_PLUGIN_DIR . 'includes/class-ald-settings.php' );
		require_once( TRP_IN_ALD_PLUGIN_DIR . 'includes/class-determine-language.php' );

		$this->trp_ald_settings = new TRP_IN_ALD_Settings();
		$trp = TRP_Translate_Press::get_trp_instance();

		$this->loader = $trp->get_component( 'loader' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_cookie_adding' );

        $this->loader->add_action( 'trp_extra_settings', $this, 'addon_general_setting', 10, 1 );
		$this->loader->add_action( 'trp_output_advanced_settings_options', $this->trp_ald_settings, 'addon_settings_ui', 4, 1 );
		$this->loader->add_action( 'admin_init', $this->trp_ald_settings, 'register_setting' );

		$this->loader->add_action('wp_footer', $this, 'activate_popup');
        $this->loader->add_action('wp_footer', $this, 'activate_hello_bar');


        $this->loader->add_filter('trp_advanced_tab_add_element', $this, 'add_automatic_user_language_detection_to_tab');
        $this->loader->add_filter( 'trp_register_advanced_settings', $this, 'eliminate_no_border_from_setting_array', 10000, 1);
    }

    public function addon_general_setting($settings){
        require_once ( TRP_IN_ALD_PLUGIN_DIR . 'partials/general-settings.php' );
    }

    public function add_automatic_user_language_detection_to_tab($advanced_settings_array){
        $settings_array[] = array(
            'name'          => 'automatic_user_language_detection',
            'type'          => 'separator',
            'label'         => esc_html__( 'Automatic User Language Detection', 'translatepress-multilingual' ),
            'no-border'     => true,
            'id'            =>'ald_settings',
        );

        array_unshift($advanced_settings_array, $settings_array[0]);

        return $advanced_settings_array;
    }

    public function eliminate_no_border_from_setting_array($settings_array){
        foreach ($settings_array as $item => $value){
            unset($settings_array[$item]['no-border']);
        }

        return $settings_array;
    }

	/**
	 * Enqueue script on all front-end pages
	 */
	public function enqueue_cookie_adding() {
		$trp_language_cookie_data = $this->get_language_cookie_data();
        if ( apply_filters( 'trp_ald_enqueue_redirecting_script', true ) ) {
            wp_enqueue_script( 'trp-language-cookie', TRP_IN_ALD_PLUGIN_URL . 'assets/js/trp-language-cookie.js', array( 'jquery' ), TRP_IN_ALD_PLUGIN_VERSION );
            wp_localize_script( 'trp-language-cookie', 'trp_language_cookie_data', $trp_language_cookie_data );

            $ald_settings = $this->trp_ald_settings->get_ald_settings();
            if($ald_settings['popup_option'] == 'popup') {
                wp_register_style( 'trp-popup-style', TRP_IN_ALD_PLUGIN_URL . 'assets/css/trp-popup.css' );
                wp_enqueue_style( 'trp-popup-style' );
            }

        }
	}


	/**
	 * Returns site data useful for determining language from url
	 *
	 * @return array
	 */
	public function get_language_cookie_data() {
		$trp = TRP_Translate_Press::get_trp_instance();
		if ( ! $this->url_converter ) {
			$this->url_converter = $trp->get_component( 'url_converter' );
		}
		if ( ! $this->settings ) {
			$trp_settings   = $trp->get_component( 'settings' );
			$this->settings = $trp_settings->get_settings();
		}
		if ( ! $this->trp_languages ) {
			$this->trp_languages = $trp->get_component( 'languages' );
		}
		$ald_settings = $this->trp_ald_settings->get_ald_settings();

        $language_urls = array();
        foreach( $this->settings['publish-languages'] as $language ){
            $language_urls[ $language ] = esc_url( $this->url_converter->get_url_for_language( $language, null, '' ) );

        }

		$data = array(
			'abs_home'          => $this->url_converter->get_abs_home(),
			'url_slugs'         => $this->settings['url-slugs'],
			'cookie_name'       => 'trp_language',
			'cookie_age'        => '30',
			'cookie_path'       => COOKIEPATH,
			'default_language'  => $this->settings['default-language'],
			'publish_languages' => $this->settings['publish-languages'],
			'trp_ald_ajax_url'  => apply_filters( 'trp_ald_ajax_url', TRP_IN_ALD_PLUGIN_URL . 'includes/trp-ald-ajax.php' ),
			'detection_method'  => $ald_settings['detection-method'],
			'popup_option'      => $ald_settings['popup_option'],
            'popup_type'        => $ald_settings['popup_type'],
            'popup_textarea'    => $ald_settings['popup_textarea'],
            'popup_textarea_change_button' => $ald_settings['popup_textarea_button'],
            'popup_textarea_close_button' =>$ald_settings['popup_textarea_close_button'],
			'iso_codes'         => $this->trp_languages->get_iso_codes( $this->settings['publish-languages'] ),
            'language_urls'     => $language_urls,
            'english_name'      => $this->trp_languages->get_language_names($this->settings['publish-languages']),
            'is_iphone_user_check' =>apply_filters('trp_hide_popup_for_iphone_users', false)

		);

		return apply_filters( 'trp_language_cookie_data', $data );
	}

	public function activate_popup(){
	    require_once('partials/popup.php');
    }

    public function activate_hello_bar(){
        require_once('partials/no-text-popup.php');
    }
}
