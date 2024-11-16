<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
abstract class AbstractPostShortcode extends AbstractShortcode {

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		$postAtts = array(
			'post' => array(
				'type'        => 'string',
				'description' => esc_html__( 'Linked Post. The slug or the post ID with shortcode settings. Manage settings in Appointments > Shortcodes.', 'motopress-appointment' ),
				'default'     => '',
			),
		);

		return parent::getAttributes() + $postAtts;
	}

	/**
	 * @param array $args
	 * @param array $defaults
	 * @param string $content
	 * @param string $shortcodeTag
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function parseArgs( $args, $defaults, $content, $shortcodeTag ) {
		// Load args from Appointments > Shortcodes first, so inline parameters
		// will replace the values from the post
		if ( ! empty( $args['post'] ) ) {
			$postArgs = $this->getPostArgs( $args['post'] );
			$defaults = array_merge( $defaults, $postArgs );
		}

		// Parse actual/inline args
		$shortcodeArgs = parent::parseArgs( $args, $defaults, $content, $shortcodeTag );

		// "template_name" is recommended parameter for post templates
		$shortcodeArgs['template_name'] = $shortcodeArgs['shortcode_name'];

		return $shortcodeArgs;
	}

	/**
	 * @param int|string $post Post ID or slug.
	 * @return array Arguments from the shortcode post type.
	 *
	 * @since 1.2
	 */
	public function getPostArgs( $post ) {
		if ( is_numeric( $post ) ) {
			$postId = (int) $post;
		} else {
			$postId = mpa_get_post_id_by_name( $post, mpa_shortcode()->getPostType() );
		}

		// Get all post metas
		$postArgs = mpa_get_post_meta( $postId, '', true );

		// Check shortcode name
		if ( ! isset( $postArgs['name'] ) || $postArgs['name'] !== $this->name ) {
			return array(); // The post is not valid
		}

		// Remove keys that does not belong to the shortcode
		$validArgs = array_intersect_key( $postArgs, $this->attributes );
		$validArgs = $this->validateArgs( $validArgs );

		$filteredArgs = $this->filterPostArgs( $validArgs, $postArgs );

		return $filteredArgs;
	}

	/**
	 * @param array $validArgs
	 * @param array $postArgs Source values.
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function filterPostArgs( $validArgs, $postArgs ) {
		// Remove default values
		$filteredArgs = array_filter(
			$validArgs,
			function ( $value, $name ) {
				return $value !== $this->attributes[ $name ]['default'];
			},
			ARRAY_FILTER_USE_BOTH
		);

		return $filteredArgs;
	}
}
