<?php

namespace MotoPress\Appointment\Shortcodes\SingleEmployee;

use MotoPress\Appointment\Shortcodes\AbstractShortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
abstract class AbstractSingleEmployeeShortcode extends AbstractShortcode {

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		$singleEmployeeAtts = array(
			'id' => array(
				'type'          => 'integer',
				'description'   => esc_html__( "Post ID of an employee to display content from. Note: this parameter automatically uses the current post ID when a shortcode is inside the employee's post and is required otherwise.", 'motopress-appointment' ),
				'default'       => 0,
				'default_label' => esc_html__( 'empty string or current post ID', 'motopress-appointment' ),
			),
		);

		return $singleEmployeeAtts + parent::getAttributes();
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function renderContent( $args, $content, $shortcodeTag ) {
		if ( ! is_admin() ) {
			mpa_assets()->enqueueStyle( 'mpa-public' );
		}

		$output = '';

		$query = $this->queryPosts( $args );

		if ( ! is_null( $query ) && $query->have_posts() ) {
			$query->the_post();
			$output .= $this->renderTemplate( $args, $content, $shortcodeTag );
			wp_reset_postdata();
		} else {
			$output .= $this->renderTemplate( $args, $content, $shortcodeTag );
		}

		return $output;
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function renderTemplate( $args, $content, $shortcodeTag ) {
		// One or more templates
		$templates = (array) $this->getTemplate();

		$templateArgs = $this->filterTemplateArgs( $args );

		// Render templates
		$functionArgs   = $templates;
		$functionArgs[] = $templateArgs;

		return call_user_func_array( 'mpa_render_template', $functionArgs );
	}

	/**
	 * @return string|string[]
	 *
	 * @since 1.2
	 */
	abstract public function getTemplate();

	/**
	 * @param array $args
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function filterTemplateArgs( $args ) {
		return $args;
	}

	/**
	 * @param array $args
	 * @return \WP_Query|null
	 *
	 * @since 1.2
	 */
	protected function queryPosts( $args ) {
		if ( 0 == $args['id'] ) {
			return null;
		}

		$queryArgs = array(
			'post_type'   => mpa_employee()->getPostType(),
			'post_status' => 'publish',
			'post__in'    => (array) $args['id'],
		);

		return new \WP_Query( $queryArgs );
	}
}
