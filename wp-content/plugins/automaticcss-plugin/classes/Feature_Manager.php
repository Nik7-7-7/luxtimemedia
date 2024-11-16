<?php
/**
 * Automatic.css Feature Manager class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS;

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Traits\Singleton;

/**
 * Feature Manager class.
 */
final class Feature_Manager {


	use Singleton;

	/**
	 * Array of features.
	 *
	 * @var array
	 */
	private $features = array(
		/**
		 * 'feature-name' => array(
		 *   'option' => 'option-name',
		 *   'class' => 'Feature Class'
		 * )
		 */
		'builder-input-validation' => array(
			'option' => 'option-builder-input-validation',
			'class' => 'Automatic_CSS\Features\Builder_Input_Validation\Builder_Input_Validation',
		),

		'contextual-menus-enable' => array(
			'option' => 'option-contextual-menus-enable',
			'class' => 'Automatic_CSS\Features\Contextual_Menus\Contextual_Menus',
		),

		'keyboard-nav-hover-preview-enable' => array(
			'option' => 'option-keyboard-nav-hover-preview-enable',
			'class' => 'Automatic_CSS\Features\Keyboard_Nav_Hover_Preview\Keyboard_Nav_Hover_Preview',
		),

		'hide-deactivated-classes' => array(
			'option' => 'option-hide-deactivated-classes',
			'class' => 'Automatic_CSS\Features\Hide_Deactivated_Classes\Hide_Deactivated_Classes',
		),

		'bricks-color-swatches-checkerboard' => array(
			'option' => 'option-bricks-color-swatches-checkerboard-enable',
			'class' => 'Automatic_CSS\Features\Bricks_Color_Swatches_Checkerboard\Bricks_Color_Swatches_Checkerboard',
		),

		'move-gutenberg-css-input' => array(
			'option' => 'option-move-gutenberg-css-input',
			'class' => 'Automatic_CSS\Features\Move_Gutenberg_CSS_Input\Move_Gutenberg_CSS_Input',
		),

		'bem-class-generator' => array(
			'option' => 'option-bem-class-generator-enable',
			'class' => 'Automatic_CSS\Features\Bem_Class_Generator\Bem_Class_Generator',
		),

		'fix-bricks-template-ids' => array(
			'option' => 'option-fix-bricks-template-ids-enable',
			'class' => 'Automatic_CSS\Features\Fix_Bricks_Template_Ids\Fix_Bricks_Template_Ids',
		),

		'color-scheme-switcher' => array(
			'option' => 'option-color-scheme-switcher-enable',
			'class' => 'Automatic_CSS\Features\Color_Scheme_Switcher\Color_Scheme_Switcher',
		),
	);

	/**
	 * Initialize the Features.
	 *
	 * @return Feature_Manager
	 */
	public function init() {
		Logger::log( sprintf( '%s: Initializing features', __METHOD__ ) );
		if ( empty( $this->features ) || ! is_array( $this->features ) ) {
			Logger::log( sprintf( '%s: No features to initialize', __METHOD__ ) );
			return;
		}
		$acss_database = Database_Settings::get_instance();
		foreach ( $this->features as $feature_name => $feature_options ) {
			$option = $feature_options['option'];
			$setting = $acss_database->get_var( $option );
			if ( null !== $setting && 'on' === $setting ) {
				$class = $feature_options['class'];
				if ( class_exists( $class ) ) {
					Logger::log( sprintf( '%s: Initializing feature: %s', __METHOD__, $feature_name ) );
					$feature = new $class();
				}
			}
		}
		return $this;
	}
}
