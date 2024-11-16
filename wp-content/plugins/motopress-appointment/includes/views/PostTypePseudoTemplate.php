<?php

namespace MotoPress\Appointment\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class PostTypePseudoTemplate {

	/**
	 * @var string
	 *
	 * @since 1.2
	 */
	protected $postType;

	/**
	 * @param string $postType
	 *
	 * @since 1.2
	 */
	public function __construct( $postType ) {
		$this->postType = $postType;

		$this->addActions();
	}

	/**
	 * @since 1.2
	 */
	protected function addActions() {
		add_filter( 'single_template', array( $this, 'filterSingleTemplate' ) );
	}

	/**
	 * @param string $template
	 * @return string
	 *
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function filterSingleTemplate( $template ) {
		if ( get_post_type() == $this->postType ) {
			add_action( 'loop_start', array( $this, 'setupPseudoTemplate' ) );
		}

		return $template;
	}

	/**
	 * @param \WP_Query $query
	 *
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function setupPseudoTemplate( $query ) {
		if ( ! $query->is_main_query() ) {
			return;
		}

		remove_action( 'loop_start', array( $this, 'setupPseudoTemplate' ) );

		add_filter( 'the_content', array( $this, 'appendContent' ) );
		add_action( 'loop_end', array( $this, 'endTemplate' ) );
	}

	/**
	 * @param string $content
	 * @return string
	 *
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function appendContent( $content ) {
		if ( is_main_query() && get_post_type() == $this->postType ) {
			remove_filter( 'the_content', array( $this, 'appendContent' ) );

			ob_start();
			$this->displayTemplate();
			$content .= ob_get_clean();
		}

		return $content;
	}

	/**
	 * @since 1.2
	 */
	protected function displayTemplate() {

		$postType = $this->postType; // Just to make the action text similar in all parts of the plugin

		/**
		 * @hooked PostTypesView::employeeSinglePost - 10
		 * @hooked PostTypesView::serviceSinglePost  - 10
		 *
		 * @since 1.2
		 */
		do_action( "{$postType}_single_post" );
	}

	/**
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function endTemplate() {
		remove_filter( 'the_content', array( $this, 'appendContent' ) );
		remove_action( 'loop_end', array( $this, 'endTemplate' ) );
	}
}
