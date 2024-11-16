<?php
if (!defined('ABSPATH')) {
	die('No direct access allowed');
}

/**
 * Adds compatibility for "YITH Point of Sale for WooCommerce" plugin.
 */
class WPO_YITH_POS_Compatibility {

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Return if YITH POS or WooCommerce is not active
		if (!defined('YITH_POS') || !function_exists('WC')) {
			return;
		}

		add_filter('wpo_minify_run_on_page', array($this, 'disable_minify_on_pos_page'));
	}

	/**
	 * Returns singleton instance.
	 */
	public static function instance() {
		static $instance = null;
		if (null == $instance) {
			$instance = new static;
		}

		return $instance;
	}
	
	/**
	 * Disables the minify feature on the POS page.
	 *
	 * @param bool $minify_enabled The current state of the minify feature.
	 * @return bool
	 */
	public function disable_minify_on_pos_page($minify_enabled) {
		if ($minify_enabled) {
			$template = get_page_template_slug();

			// If the current page is the YITH POS page, disable minify and add a footer comment
			if ('yith-pos-page.php' === $template) {
				add_action('yith_pos_footer', array($this, 'display_minify_disabled_footer_comment'));
				return false;
			}
		}

		return $minify_enabled;
	}

	/**
	 * Displays a footer comment indicating that minify is disabled on the POS page.
	 */
	public function display_minify_disabled_footer_comment() {
		echo '<!-- WP-Optimize: Minify feature is disabled on this page due to incompatibility with the "YITH Point of Sale for WooCommerce" plugin. -->' . "\n";
	}
}
