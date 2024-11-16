<?php
if (!defined('ABSPATH')) {
	die('No direct access allowed');
}

if (!class_exists('WPO_Elementor')) :

/**
 * Class to handle used images in Elementor plugin
 */
class WPO_Elementor {

	/**
	 * Constructor
	 */
	private function __construct() {
		if (!defined('ELEMENTOR_VERSION')) return;
		add_filter('wpo_get_posts_content_images_from_plugins', array($this, 'get_posts_content_images'), 10, 2);
	}

	/**
	 * Returns singleton instance
	 *
	 * @return WPO_Elementor
	 */
	public static function instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Appends images array with images found in Elementor plugin content
	 *
	 * @param array $images
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_posts_content_images($images, $post_id) {
		$data_string = get_post_meta($post_id, '_elementor_data', true);
		if (empty($data_string)) return $images;
		
		$data = json_decode($data_string, true);
		if (null === $data && JSON_ERROR_NONE !== json_last_error()) return $images;

		$elements = $this->get_elements($data);
		if (empty($elements)) return $images;

		$elementor_images = $this->extract_elementor_images($elements);
		return array_merge($images, $elementor_images);
	}
	
	/**
	 * Separates an array of elementor data into an array of separate elements
	 *
	 * @param array $data An array of elementor data
	 *
	 * @return array An array of elements
	 */
	private function get_elements($data, $elements = array()) {
		if (empty($data) || !is_array($data)) {
			return $elements;
		}
		
		foreach ($data as $element_data) {
			$elements[] = $element_data;
			
			if (!empty($element_data['elements'])) {
				$elements = $this->get_elements($element_data['elements'], $elements);
			}
		}
		
		return $elements;
	}
	
	/**
	 * Extracts image IDs from elements array
	 *
	 * @param array $elements An array of elements
	 *
	 * @return array An array of image IDs
	 */
	private function extract_elementor_images($elements) {
		$elementor_images = array();
		foreach ($elements as $element) {
			
			$elementor_images = array_merge($elementor_images, $this->extract_background_images($element));
			
			if ($this->element_has_image($element)) {
				$elementor_images[] = $this->extract_image($element);
			}
			
			if ($this->element_has_background_slideshow_gallery($element)) {
				$elementor_images = array_merge($elementor_images, $this->extract_background_slideshow_gallery($element));
			}
			
			if ($this->element_has_gallery($element)) {
				$elementor_images = array_merge($elementor_images, $this->extract_gallery_images($element));
			}
			
			if ($this->element_has_slides($element)) {
				$elementor_images = array_merge($elementor_images, $this->extract_slides_images($element));
			}
			
			if ($this->element_has_selected_icon($element)) {
				$elementor_images[] = $this->extract_selected_icon($element);
			}

			if ($this->element_has_video_overlay($element)) {
				$elementor_images[] = $this->extract_video_overlay($element);
			}
			
			if ($this->element_has_post_featured_fallback_image($element)) {
				$fallback = $this->extract_post_featured_fallback_image($element);
				if (!empty($fallback)) {
					$elementor_images[] = $fallback;
				}
			}
			
		}
		return $elementor_images;
	}
	
	/**
	 * Determines whether the given element has image or not
	 *
	 * @param array $element An array of element data
	 *
	 * @return bool
	 */
	private function element_has_image($element) {
		return !empty($element['settings']['image']['id']);
	}
	
	/**
	 * Determines whether the given element has background slideshow gallery or not
	 *
	 * @param array $element An array of element data
	 *
	 * @return bool
	 */
	private function element_has_background_slideshow_gallery($element) {
		return !empty($element['settings']['background_slideshow_gallery']) && is_array($element['settings']['background_slideshow_gallery']);
	}
	
	/**
	 * Determines whether the given element has gallery or not
	 *
	 * @param array $element An array of element data
	 *
	 * @return bool
	 */
	private function element_has_gallery($element) {
		return !empty($element['settings']['gallery']) && is_array($element['settings']['gallery']);
	}
	
	/**
	 * Determines whether the given element has slides widget or not
	 *
	 * @param array $element An array of element data
	 *
	 * @return bool
	 */
	private function element_has_slides($element) {
		return !empty($element['settings']['slides']) && is_array($element['settings']['slides']);
	}
	
	/**
	 * Determines whether the given element has selected icon widget or not
	 *
	 * @param array $element An array of element data
	 *
	 * @return bool
	 */
	private function element_has_selected_icon($element) {
		return !empty($element['settings']['selected_icon']['value']['id']);
	}
	
	/**
	 * Determines whether the given element has post featured fallback image or not
	 *
	 * @param array $element An array of element data
	 *
	 * @return bool
	 */
	private function element_has_post_featured_fallback_image($element) {
		return !empty($element['settings']['__dynamic__']['image']);
	}

	/**
	 * Determines whether the given element has video widget overlay image or not
	 *
	 * @param array $element An array of element data
	 *
	 * @return bool
	 */
	private function element_has_video_overlay($element) {
		return !empty($element['settings']['show_image_overlay']) && 'yes' === $element['settings']['show_image_overlay'] && !empty($element['settings']['image_overlay']['id']);
	}
	
	/**
	 * Extracts background images from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return array An array of image IDs
	 */
	private function extract_background_images($element) {
		$background_images = array();
		
		$image_keys = array('bg_image', 'background_a_image', 'background_b_image', '_background_image', 'background_image', 'background_overlay_image', 'background_overlay_hover_image', 'background_hover_image');
		
		foreach ($image_keys as $key) {
			if (!empty($element['settings'][$key]['id'])) {
				$background_images[] = $element['settings'][$key]['id'];
			}
		}
		
		return $background_images;
	}
	
	/**
	 * Extracts image ID from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return int Image ID
	 */
	private function extract_image($element) {
		return $element['settings']['image']['id'];
	}
	
	/**
	 * Extracts background slideshow gallery images from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return array An array of image IDs
	 */
	private function extract_background_slideshow_gallery($element) {
		$background_slideshow_gallery_images = array();
		foreach ($element['settings']['background_slideshow_gallery'] as $image) {
			if (isset($image['id'])) {
				$background_slideshow_gallery_images[] = $image['id'];
			}
		}
		return $background_slideshow_gallery_images;
	}
	
	/**
	 * Extracts slides images from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return array An array of image IDs
	 */
	private function extract_slides_images($element) {
		$slides_images = array();
		foreach ($element['settings']['slides'] as $slide) {
			if (isset($slide['image']['id'])) {
				$slides_images[] = $slide['image']['id'];
			}
			
			if (isset($slide['background_image']['id'])) {
				$slides_images[] = $slide['background_image']['id'];
			}
		}

		return $slides_images;
	}
	
	/**
	 * Extracts gallery images from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return array An array of image IDs
	 */
	private function extract_gallery_images($element) {
		$gallery_images = array();
		foreach ($element['settings']['gallery'] as $image) {
			if (isset($image['id'])) {
				$gallery_images[] = $image['id'];
			}
		}
		return $gallery_images;
	}
	
	/**
	 * Extracts selected icon image ID from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return int Image ID
	 */
	private function extract_selected_icon($element) {
		return $element['settings']['selected_icon']['value']['id'];
	}

	/**
	 * Extracts video widget overlay image from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return int Image ID
	 */
	private function extract_video_overlay($element) {
		return (int) $element['settings']['image_overlay']['id'];
	}
	
	/**
	 * Extracts post featured fallback image ID from element data
	 *
	 * @param array $element An array of element data
	 *
	 * @return int|null Image ID
	 */
	private function extract_post_featured_fallback_image($element) {
		$string = $element['settings']['__dynamic__']['image'];
		// Example $string is `[elementor-tag id="" name="post-featured-image" settings="%7B%22fallback%22%3A%7B%22url%22%3A%22http%3A%2F%2Flocalhost%2Fwpo%2Fwp-content%2Fuploads%2F2023%2F07%2F10.jpg%22%2C%22id%22%3A88%2C%22size%22%3A%22%22%2C%22alt%22%3A%22%22%2C%22source%22%3A%22library%22%7D%7D"]`
		$pattern = '/settings="(.*?)"/';
		preg_match($pattern, $string, $matches);
		if (isset($matches[1])) {
			$settings_string = $matches[1];
			$settings_string = urldecode($settings_string);
			$settings = json_decode($settings_string, true);
			if (!empty($settings['fallback'])) {
				return $settings['fallback']['id'];
			}
		}
		return null;
	}
}

endif;
