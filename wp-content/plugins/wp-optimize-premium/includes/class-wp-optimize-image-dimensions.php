<?php
if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WP_Optimize_Image_Dimensions')) :

class WP_Optimize_Image_Dimensions {

	/**
	 * WP_Optimize options.
	 *
	 * @var WP_Optimize_Options
	 */
	private $options;

	/**
	 * WP_Optimize_Image_Dimensions constructor.
	 */
	public function __construct() {
		$this->options = WP_Optimize()->get_options();

		// Add callback function to process output buffer
		ob_start(array($this, 'add_missing_image_dimensions'));
	}

	/**
	 * Find images without width/height, make an effort to extract size information, put attributes inside img tag
	 *
	 * @param string $buffer The current output content being sent to the user
	 * @return string
	 */
	public function add_missing_image_dimensions($buffer) {
		// Bail out if not needed
		if (!WP_Optimize_Utils::is_valid_html($buffer)) return $buffer;

		// MAX_FILE_SIZE is defined in simple_html_dom.
		// For safety, we make sure it is defined before using
		defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 600000);
		if (strlen($buffer) > MAX_FILE_SIZE) {
			return  $buffer . "\n" . "<!-- Add image dimensions functionality was skipped because the HTML is too big to process! " .
				"(limit is set to " . MAX_FILE_SIZE . " bytes) -->";
		}

		// using the simple html dom library
		$html_dom = WP_Optimize_Utils::get_simple_html_dom_object($buffer);
		if (false === $html_dom) {
			return  $buffer . "\n" . "<!-- Add image dimensions functionality was skipped because the helper library refused to process the html -->";
		}

		$img_tags = $html_dom->getElementsByTagName('img');
		if (empty($img_tags)) return $buffer;

		// Run valid tags against checks and filter
		$filtered_tags = $this->filter_not_required_img_tags($img_tags);
		if (empty($filtered_tags)) return $buffer;

		$this->add_image_dimensions($filtered_tags);

		return $html_dom->save();
	}

	/**
	 * Checks if src/data-src attribute are present in tag
	 *
	 * @param simplehtmldom\HtmlNode $ele
	 * @return boolean
	 */
	private function is_src_attr_present($ele) {
		return $ele->hasAttribute('src') || $ele->hasAttribute('data-src');
	}

	/**
	 * Checks if tag already has width/height attribute
	 *
	 * @param simplehtmldom\HtmlNode $ele
	 * @return boolean
	 */
	private function is_width_or_height_present($ele) {
		return ($ele->hasAttribute('width') || $ele->hasAttribute('height'));
	}

	/**
	 * Checks if tag class name is in the ignore list
	 *
	 * @param simplehtmldom\HtmlNode $ele
	 * @return boolean
	 */
	private function is_class_in_ignore_list($ele) {
		if (!$ele->hasAttribute('class')) return false;

		$ignore_list = $this->get_class_name_ignore_list();
		$class_array = preg_split('/\s+/', $ele->getAttribute('class'));

		return !empty(array_intersect($class_array, $ignore_list));
	}

	/**
	 * Checks if tag has any of the special ignored attributes
	 *
	 * @param simplehtmldom\HtmlNode $ele
	 * @return boolean
	 */
	private function is_special_attribute_present($ele) {
		return ($ele->hasAttribute('data-no-image-dimensions')
			|| $ele->hasAttribute('data-height-percentage')
			|| $ele->hasAttribute('data-disable-image-dimensions'));
	}

	/**
	 * Filters img tags using some checks and assertions
	 *
	 * @param array $img_tags - array of img tags
	 * @return array
	 */
	private function filter_not_required_img_tags($img_tags) {
		return array_filter($img_tags, function($ele) {
			return $this->is_src_attr_present($ele)
			&& !$this->is_width_or_height_present($ele)
			&& !$this->is_class_in_ignore_list($ele)
			&& !$this->is_special_attribute_present($ele);
		});
	}

	/**
	 * Adds height and width attributes to tags when possible
	 *
	 * @param array $filtered_tags - array of img/picture tags
	 * @return void
	 */
	private function add_image_dimensions($filtered_tags) {
		foreach ($filtered_tags as $ele) {
			$src = $ele->hasAttribute('src') ? $ele->getAttribute('src') : $ele->getAttribute('data-src');
			$src = trim($src);
			if (empty($src)) continue;
			$img_size_array = array();

			// Check if src is an internal url
			if ($this->is_internal_url($src)) {
				// Get a possible file path
				$img_path = WP_Optimize_Utils::get_file_path($src);
				if (!is_file($img_path)) continue;

				// Get image size using WP builtin function if available
				if (function_exists('wp_getimagesize')) {
					$img_size_array = wp_getimagesize($img_path);
				} else {
					$img_size_array = @getimagesize($img_path); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- suppress the error when there is issue getting image info
				}
			} else {
				if (apply_filters('wpo_add_dimensions_for_external_images', false)) {
					// If hook/filter is `true` then try to process external images
					// Defaulting not to process external images because getimagesize for external urls takes time to run
					$img_size_array = @getimagesize($src); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- suppress the error when there is issue getting image info
				}
			}

			if (empty($img_size_array)) continue;

			$ele->setAttribute('width', $img_size_array[0]);
			$ele->setAttribute('height', $img_size_array[1]);
		}
	}

	/**
	 * Check if it's an internal url or not
	 *
	 * @param string $url
	 * @return boolean
	 */
	private function is_internal_url($url) {
		$pos = strpos($url, site_url());
		return 0 === $pos;
	}

	/**
	 * Retrieves a list of class names to ignore
	 *
	 * @return array
	 */
	private function get_class_name_ignore_list() {
		$ignore_list = $this->options->get_option('image_dimensions_ignore_classes');
		if (empty($ignore_list)) return array();

		// Remove potential whitespaces
		$ignore_list = preg_replace('/\s+/', '', $ignore_list);
		return explode(',', $ignore_list);
	}
}

endif;
