<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HELPER COMMENT START
 * 
 * This class is used to bring your plugin to life. 
 * All the other registered classed bring features which are
 * controlled and managed by this class.
 * 
 * Within the add_hooks() function, you can register all of 
 * your WordPress related actions and filters as followed:
 * 
 * add_action( 'my_action_hook_to_call', array( $this, 'the_action_hook_callback', 10, 1 ) );
 * or
 * add_filter( 'my_filter_hook_to_call', array( $this, 'the_filter_hook_callback', 10, 1 ) );
 * or
 * add_shortcode( 'my_shortcode_tag', array( $this, 'the_shortcode_callback', 10 ) );
 * 
 * Once added, you can create the callback function, within this class, as followed: 
 * 
 * public function the_action_hook_callback( $some_variable ){}
 * or
 * public function the_filter_hook_callback( $some_variable ){}
 * or
 * public function the_shortcode_callback( $attributes = array(), $content = '' ){}
 * 
 * 
 * HELPER COMMENT END
 */

/**
 * Class Dayschedule_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		DAYSCHEDUL
 * @subpackage	Classes/Dayschedule_Run
 * @author		Dayschedule
 * @since		1.0.1
 */
class Dayschedule_Run{

	/**
	 * Our Dayschedule_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_shortcode( 'dayschedule', array( $this, 'add_shortcode_callback' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts_and_styles' ), 20 );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu_items' ), 100, 1 );
	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	 * Add the shortcode callback for [dayschedule]
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param	array	$attr		Additional attributes you have added within the shortcode tag.
	 * @param	string	$content	The content you added between an opening and closing shortcode tag.
	 *
	 * @return	string	The customized content by the shortcode.
	 */
	public function add_shortcode_callback( $attr = array(), $content = '' ) {
		// Convert attributes to array with default value
		$attributes = shortcode_atts([
			'type' => 'popup',
			'url' => 'https://demo.dayschedule.com',
			'text' => 'Book an appointment',
			'class' => 'wp-block-button__link',
			'style' => '',
			'color_primary' => '#0f0980',
			'color_secondary' => '#afeefe',
			'color_mode' => 'light',
			'hide_event' => '0',
			'hide_header' => '0',
		], $attr);

		// Escape attributes and sanitizing for security
		// https://developer.wordpress.org/apis/security/sanitizing/		
		$type = strtolower(sanitize_text_field(esc_attr($attributes['type'])));
		$url = sanitize_url(esc_url($attributes['url']));
		$text = sanitize_text_field(esc_attr($attributes['text']));
		$class = sanitize_html_class(esc_attr($attributes['class']));
		$style = sanitize_text_field(esc_attr($attributes['style']));
		$color_primary = sanitize_hex_color(esc_attr($attributes['color_primary']));
		$color_secondary = sanitize_hex_color(esc_attr($attributes['color_secondary']));
		$color_mode = sanitize_hex_color(esc_attr($attributes['color_mode']));
		$hide_event = sanitize_text_field(esc_attr($attributes['hide_event']));
		$hide_header = sanitize_text_field(esc_attr($attributes['hide_header']));

		if($type == 'popup'){
			return '<a 
				data-url="' . $url . '" 
				href="#" 
				class="dayschedule-widget ' . $class . '" 
				onclick="daySchedule.initPopupWidget({
					url: \'' . $url . '\',
					color: {
						primary: \'' . $color_primary . '\',
						secondary: \'' . $color_secondary . '\',
						mode: \'' . $color_mode . '\' 
					},
					hideEvent: ' . (int)$hide_event . ',
					hideHeader: ' . (int)$hide_header . '
				});				
				return false;">' . $text . '</a>';
		}else{
			return '<dayschedule-widget url="' . $url . '" 
				options=\'{ "color": {
					"primary": "' . $color_primary . '",
					"secondary": "' . $color_secondary . '",
					"mode": "' . $color_mode . '"
				},
				"hideEvent": ' . (int)$hide_event . ',
				"hideHeader": ' . (int)$hide_header . '
			}\'></dayschedule-widget>';		
		}
	}

	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function enqueue_frontend_scripts_and_styles() {
		wp_enqueue_style( 'dayschedule-popup-css', DAYSCHEDUL_PLUGIN_URL . 'core/includes/assets/css/dayschedule-popup.css', array(), DAYSCHEDUL_VERSION, 'all' );
		wp_enqueue_script( 'dayschedule-widget-js', DAYSCHEDUL_PLUGIN_URL . 'core/includes/assets/js/dayschedule-widget.js', array(), DAYSCHEDUL_VERSION, false );
		wp_enqueue_script( 'dayschedule-main', DAYSCHEDUL_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array(), DAYSCHEDUL_VERSION, false );
		// wp_localize_script( 'dayschedul-frontend-scripts', 'dayschedul', array(
		// 	'demo_var'   		=> __( 'This is some demo text coming from the backend through a variable within javascript.', 'dayschedule' ),
		// ));
	}

	/**
	 * Add a new menu item to the WordPress topbar
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param	object $admin_bar The WP_Admin_Bar object
	 *
	 * @return	void
	 */
	public function add_admin_bar_menu_items( $admin_bar ) {

		$admin_bar->add_menu( array(
			'id'		=> 'dayschedule-id', // The ID of the node.
			'title'		=> __( 'DaySchedule', 'dayschedule' ), // The text that will be visible in the Toolbar. Including html tags is allowed.
			'parent'	=> false, // The ID of the parent node.
			'href'		=> '#', // The ‘href’ attribute for the link. If ‘href’ is not set the node will be a text node.
			'group'		=> false, // This will make the node a group (node) if set to ‘true’. Group nodes are not visible in the Toolbar, but nodes added to it are.
			'meta'		=> array(
				'title'		=> __( 'DaySchedule', 'dayschedule' ), // The title attribute. Will be set to the link or to a div containing a text node.
				'target'	=> '_blank', // The target attribute for the link. This will only be set if the ‘href’ argument is present.
				'class'		=> 'dayschedule-class', // The class attribute for the list item containing the link or text node.
				'html'		=> false, // The html used for the node.
				'rel'		=> false, // The rel attribute.
				'onclick'	=> false, // The onclick attribute for the link. This will only be set if the ‘href’ argument is present.
				'tabindex'	=> false, // The tabindex attribute. Will be set to the link or to a div containing a text node.
			),
		));

		$admin_bar->add_menu( array(
			'id'		=> 'dayschedule-bookings',
			'title'		=> __( 'Bookings', 'dayschedule' ),
			'parent'	=> 'dayschedule-id',
			'href'		=> 'https://app.dayschedule.com/bookings',
			'group'		=> false,
			'meta'		=> array(
				'title'		=> __( 'Bookings', 'dayschedule' ),
				'target'	=> '_blank',
				'class'		=> 'dayschedule-sub-class',	
				'html'		=> false,    
				'rel'		=> false,
				'onclick'	=> false,
				'tabindex'	=> false,
			),
		));

		$admin_bar->add_menu( array(
			'id'		=> 'dayschedule-resources',
			'title'		=> __( 'Resources', 'dayschedule' ),
			'parent'	=> 'dayschedule-id',
			'href'		=> 'https://app.dayschedule.com/resources',
			'group'		=> false,
			'meta'		=> array(
				'title'		=> __( 'Resources', 'dayschedule' ),
				'target'	=> '_blank',
				'class'		=> 'dayschedule-sub-class',	
				'html'		=> false,    
				'rel'		=> false,
				'onclick'	=> false,
				'tabindex'	=> false,
			),
		));

	}

}
