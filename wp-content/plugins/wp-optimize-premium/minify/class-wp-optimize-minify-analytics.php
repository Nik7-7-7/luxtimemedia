<?php
if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Localizes Google Analytics.
 */
if (!class_exists('WP_Optimize_Minify_Analytics')) :

class WP_Optimize_Minify_Analytics {
	
	/**
	 * Analytics ID
	 *
	 * @var string
	 */
	private $id;
	
	/**
	 * Analytics Method
	 *
	 * @var string
	 */
	private $method;
	
	/**
	 * Is hosting local analytics script enabled
	 *
	 * @var bool
	 */
	private $is_enabled;
	
	/**
	 * Constructor.
	 */
	private function __construct() {
		$config = wp_optimize_minify_config()->get();
		$this->id = $config['tracking_id'];
		$this->method = $config['analytics_method'];
		$this->is_enabled = $config['enable_analytics'];
		
		if (!$this->is_enabled || empty($this->id)) return;
		
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_print_footer_scripts', array($this, 'inject_analytics_js'));
		global $wp_version;
		if (version_compare($wp_version, '6.3', '<')) {
			add_filter( 'script_loader_tag', array($this, 'loading_strategy_fallback'), 10, 2);
		}
	}
	
	/**
	 * Singleton instance
	 *
	 * @return WP_Optimize_Minify_Analytics
	 */
	public static function get_instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Enqueue analytics script
	 */
	public function enqueue_scripts() {
		$enqueue_version = WP_Optimize()->get_enqueue_version();
		if ('gtagv4' === $this->method) {
			wp_enqueue_script($this->method, WPO_PLUGIN_URL . 'js/gtag/analytics.min.js', array(), $enqueue_version);
		} elseif ('minimal-analytics' === $this->method) {
			wp_enqueue_script($this->method, WPO_PLUGIN_URL . 'js/minimal-analytics/minimal-analytics.min.js', array(), $enqueue_version);
		}
		wp_script_add_data($this->method, 'strategy', 'defer');
	}
	
	/**
	 * Injects corresponding JS to footer.
	 */
	public function inject_analytics_js() {
		if ('gtagv4' === $this->method) {
			echo "<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', '".esc_attr($this->id)."');</script>";
		} elseif ('minimal-analytics' === $this->method) {
			echo "<script>window.minimalAnalytics = { trackingId: '".esc_attr($this->id)."', autoTrack: true, };</script>";
		}
	}
	
	/**
	 * Fallback to add script loading strategy attribute to enqueued scripts
	 *
	 * @param string $tag    The `<script>` tag for the enqueued script
	 * @param string $handle Handle of the enqueued script
	 *
	 * @return string URL of the enqueued script with defer loading strategy
	 */
	public function loading_strategy_fallback($tag, $handle) {
		if (!in_array($handle, array('gtagv4', 'minimal-analytics'))) return $tag;
		
		return str_replace(' src=', ' defer src=', $tag);
	}
}

endif;
