<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('MainPublicUlp')){
	 return;
}
class MainPublicUlp{
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}
	/**
	 * @param none
	 * @return none
	 */
	public static function run(){
		self::loadDependencies();
		add_action('init', array('MainPublicUlp', 'init'));
		add_action('wp_enqueue_scripts', array('MainPublicUlp', 'assets'));
	}
	/**
	 * @param none
	 * @return none
	 */
	public static function assets(){
		global $wp_version;
		wp_enqueue_style('ulp_main_public_style', ULP_URL . 'assets/css/public.min.css', array(), '3.9' );
		wp_enqueue_script('jquery');

		wp_register_script('ulp_main_easytimer', ULP_URL . 'assets/js/easytimer.js', array('jquery'), '3.9' );
		wp_register_script('ulp_main_public', ULP_URL . 'assets/js/public.min.js', array('jquery'), '3.9' );

		if ( version_compare ( $wp_version , '5.7', '>=' ) ){
				wp_localize_script('ulp_main_public', 'ulp_messages', self::messages_for_javascript( false ) );
				wp_add_inline_script('ulp_main_public', "var ulp_site_url='" . get_site_url() . "';" );
		} else {
				wp_localize_script('ulp_main_public', 'ulp_messages', self::messages_for_javascript() );
				wp_localize_script('ulp_main_public', 'ulp_site_url', get_site_url());
		}

		wp_enqueue_script( 'ulp_main_public' );
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_register_script( 'ulp_printThis', ULP_URL . 'assets/js/printThis.js', array('jquery'), '3.9' );

		if (is_rtl()){
				wp_enqueue_style('ulp_main_public_rtl', ULP_URL . 'assets/css/public_rtl.css', array(), '3.9' );
		}
	}
	/**
	 * @param none
	 * @return none
	 */
	public static function loadDependencies(){

	}
	/**
	 * @param none
	 * @return none
	 */
	public static function init(){
		require_once ULP_PATH . 'classes/public/PublicInitUlp.class.php';
		$PublicInitUlp = new PublicInitUlp();
	}
	public static function messages_for_javascript( $asJson=true ){
			$data = [
					'error' 								=> esc_html__('Error', 'ulp'),
					'general_error' 				=> esc_html__("An error have occued, please try again later!", 'ulp'),
					'payment_type_error' 		=> esc_html__('Please select a payment type!', 'ulp'),
					'delete_post' 					=> esc_html__('Are you sure you want to delete this post', 'ulp'),
					'delete_it' 						=> esc_html__("Yes, delete it!", 'ulp'),
					'cannot_delete'					=> esc_html__('You cannot delete this post!', 'ulp'),
					'toggle_section' 				=> esc_html__('Toggle Section', 'ulp'),
			];
			if ( $asJson ){
					return json_encode( $data );
			} else {
					return $data;
			}

	}
}
