<?php
/**
 * Automatic.css Builder Input Validation class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Fix_Bricks_Template_Ids;

use Automatic_CSS\Features\Base;

/**
 * Builder Input Validation class.
 */
class Fix_Bricks_Template_Ids extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		 add_filter( 'bricks/get_remote_templates_data', array( $this, 'fix_template_ids' ) );
	}

	/**
	 * Fix template ids.
	 *
	 * @param array $template_data The template data.
	 * @return array The fixed template data.
	 */
	public function fix_template_ids( $template_data ) {

		if ( ! isset( $template_data['templates'] ) ) {
			return $template_data;
		}

		// new approach - replace every id everywhere by adding a random sequence to the front?
		$random_prefix = self::generate_random_string();

		foreach ( $template_data['templates'] as &$template ) {
			foreach ( $template['global_classes'] as &$template_class ) {
				// check if the class name is already somewhere in the database.
				$template_class['id'] = $random_prefix . $template_class['id'];
			}
			// the template can either have a "content" or "header" or "footer" key that contains the content.
			if ( isset( $template['content'] ) && $template['content'] ) {
				foreach ( $template['content'] as &$element ) {
					if (
						isset( $element['settings'] ) &&
						isset( $element['settings']['_cssGlobalClasses'] )
					) {
						for (
							$i = 0;
							$i < count( $element['settings']['_cssGlobalClasses'] ); // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found
							$i++
						) {

							$element['settings']['_cssGlobalClasses'][ $i ] = $random_prefix . $element['settings']['_cssGlobalClasses'][ $i ];
						}
					}
				}
			}
			if ( isset( $template['footer'] ) && $template['footer'] ) {
				foreach ( $template['footer'] as &$element ) {
					if (
						isset( $element['settings'] ) &&
						isset( $element['settings']['_cssGlobalClasses'] )
					) {
						for (
							$i = 0;
							$i < count( $element['settings']['_cssGlobalClasses'] ); // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found
							$i++
						) {

							$element['settings']['_cssGlobalClasses'][ $i ] = $random_prefix . $element['settings']['_cssGlobalClasses'][ $i ];
						}
					}
				}
			}
			if ( isset( $template['header'] ) && $template['header'] ) {
				foreach ( $template['header'] as &$element ) {
					if (
						isset( $element['settings'] ) &&
						isset( $element['settings']['_cssGlobalClasses'] )
					) {
						for (
							$i = 0;
							$i < count( $element['settings']['_cssGlobalClasses'] ); // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found
							$i++
						) {

							$element['settings']['_cssGlobalClasses'][ $i ] = $random_prefix . $element['settings']['_cssGlobalClasses'][ $i ];
						}
					}
				}
			}
		}

		return $template_data;
	}

	/**
	 * Generate a random string.
	 *
	 * @param integer $length The length of the string.
	 * @return string The random string.
	 */
	private static function generate_random_string( $length = 5 ) {
		$characters =
			'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$randomString = '';

		for ( $i = 0; $i < $length; $i++ ) {
			$random = rand( 0, strlen( $characters ) - 1 );
			$randomString .= $characters[ $random ];
		}

		return $randomString;
	}
}
