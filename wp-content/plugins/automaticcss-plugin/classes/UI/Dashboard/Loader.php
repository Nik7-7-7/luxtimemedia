<?php
/**
 * Dashboard_Loader
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Dashboard;

/**
 * Class Loader
 */
class Loader {

	/**
	 * Context info about each builder.
	 *
	 * @var array
	 */
	private $context_info;

	/**
	 * Loader constructor.
	 *
	 * @param array $context_info Array of context info about each builder.
	 */
	public function __construct( array $context_info ) {
		$this->context_info = $context_info;
	}

	/**
	 * Check if a builder is enabled and in the frontend context.
	 *
	 * @return array<{builder: string, context: string, loading: string, action: string}>
	 */
	public function get_loading_context() {
		$builder = ''; // Name of the builder.
		$version = ''; // Version of the builder.
		$context = ''; // Context of the builder.
		$action = ''; // Action to hook into.
		$function = ''; // Function to call.

		// STEP: Check if a builder is active and in any context.
		foreach ( $this->context_info as $builder_name => $builder_context ) {
			$is_active = $builder_context['is_active'];
			$is_builder = $builder_context['is_builder'];
			$is_frontend = $builder_context['is_frontend'];
			$is_preview = $builder_context['is_preview'];
			$is_selected = $is_active && ( $is_builder || $is_frontend || $is_preview );
			if ( $is_selected ) {
				$builder = $builder_name;
				$version = $builder_context['version'];
				switch ( true ) {
					case $is_builder:
						$context = 'builder';
						if ( 'breakdance' === $builder && version_compare( $version, '2.0.0', '>=' ) ) {
							// Breakdance v2 added the 'unofficial_i_am_kevin_geary_master_of_all_things_css_and_html' hook,
							// which allows us to load in the builder context, but we have to output the script directly.
							$function = 'output_builder_scripts';
							$action = 'unofficial_i_am_kevin_geary_master_of_all_things_css_and_html';
						}
						break;
					case $is_frontend:
						$context = 'frontend';
						break;
					case $is_preview:
						$context = 'preview';
						break;
					default:
						$context = 'unknown';
						break;
				}
				$action = '' === $action ? 'acss/' . $builder . '/in_' . $context . '_context' : $action;
				$function = '' === $function ? 'enqueue_' . $context . '_scripts' : $function;
				break;
			}
		}

		// STEP: Return the context info.
		return array(
			'builder' => $builder,
			'version' => $version,
			'context' => $context,
			'loading' => $builder . '/' . $context,
			'action'  => $action,
			'function' => $function,
		);
	}

}
