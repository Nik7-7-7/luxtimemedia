<?php

namespace MotoPress\Appointment\DirectLinkActions\Pages;

use MotoPress\Appointment\DirectLinkActions\AbstractAction;
use MotoPress\Appointment\Entities\InterfaceUniqueEntity;

/**
 * @since 1.15.0
 */
abstract class AbstractVirtualPage extends AbstractAction {

	/**
	 * Negative ID to avoid clash with a valid post
	 * We need to use 0 instead of negative integer because otherwise in Edme theme
	 * we will get post with positive integer id and show its thumbnail on virtual pages.
	 */
	const POST_ID = 0;


	public function __construct() {
		parent::__construct();

		add_filter( 'body_class', array( $this, 'addPageClasses' ), 10 );
	}

	/**
	 * @return string
	 */
	abstract protected function getPageSlug();

	/**
	 * @return string
	 */
	abstract protected function getTitle();

	/**
	 * @param $classes
	 *
	 * @return string[]
	 */
	public function addPageClasses( $classes ) {
		if ( $this->isValidToken() ) {
			$classes[] = 'mpa-direct-link-action-page';
			$classes[] = 'mpa-direct-link-action-page--' . $this->getPageSlug();
		}

		return $classes;
	}


	public function removeEditButtonFromAdminBar() {
		/**
		 * @param $wp_admin_bar
		 */
		add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_node( 'edit' );
		}, 100 );
	}

	/**
	 * @return string
	 */
	protected function getActionName() {
		return $this->getPageSlug() . '-page';
	}

	/**
	 * @return string
	 */
	abstract protected function defaultContent();

	/**
	 * @return void
	 */
	protected function action( InterfaceUniqueEntity $entity ) {
		$wpPost = $this->createPage();

		/**
		 * @param \WP_Post $wpPost
		 * @param \MotoPress\Appointment\Entities\InterfaceUniqueEntity $entity
		 */
		$wpPost = apply_filters( "mpa_direct_link_action_page_pre_render_{$this->getPageSlug()}", $wpPost, $entity );

		$this->render( $wpPost );
	}

	/**
	 * @return \WP_Post
	 */
	protected function createPage() {
		$this->removeEditButtonFromAdminBar();

		$title   = $this->getTitle();
		$content = $this->defaultContent();

		return $this->createWpPost( $title, $content );
	}

	final protected function createWpPost( $title, $content ) {
		$post                 = new \stdClass();
		$post->ID             = static::POST_ID;
		$post->post_author    = 1;
		$post->post_date      = current_time( 'mysql' );
		$post->post_date_gmt  = current_time( 'mysql', 1 );
		$post->post_title     = $title;
		$post->post_content   = $content;
		$post->post_status    = 'publish';
		$post->comment_status = 'closed';
		$post->ping_status    = 'closed';
		$post->post_name      = $this->getActionName() . '-' . rand( 1, 99999 ); // append random number to avoid clash
		$post->post_type      = 'page';
		$post->filter         = 'raw'; // important!

		return new \WP_Post( $post );
	}

	/**
	 * Caching our page to prevent any (error-producing) calls to the database
	 *
	 * @param \WP_Post $wpPost
	 *
	 * @return void
	 */
	private function preventDbCalls( \WP_Post $wpPost ) {
		wp_cache_add( static::POST_ID, $wpPost, 'posts' );
	}

	/**
	 * Adding our page to the main wp_query
	 *
	 * @param \WP_Post $wp_post
	 *
	 * @return void
	 */
	private function updateMainWpQuery( \WP_Post $wp_post ) {
		global $wp, $wp_query;

		$wp_query->post                 = $wp_post;
		$wp_query->posts                = array( $wp_post );
		$wp_query->queried_object       = $wp_post;
		$wp_query->queried_object_id    = static::POST_ID;
		$wp_query->found_posts          = 1;
		$wp_query->post_count           = 1;
		$wp_query->max_num_pages        = 1;
		$wp_query->is_page              = true;
		$wp_query->is_singular          = true;
		$wp_query->is_single            = false;
		$wp_query->is_attachment        = false;
		$wp_query->is_archive           = false;
		$wp_query->is_category          = false;
		$wp_query->is_tag               = false;
		$wp_query->is_tax               = false;
		$wp_query->is_author            = false;
		$wp_query->is_date              = false;
		$wp_query->is_year              = false;
		$wp_query->is_month             = false;
		$wp_query->is_day               = false;
		$wp_query->is_time              = false;
		$wp_query->is_search            = false;
		$wp_query->is_feed              = false;
		$wp_query->is_comment_feed      = false;
		$wp_query->is_trackback         = false;
		$wp_query->is_home              = false;
		$wp_query->is_embed             = false;
		$wp_query->is_404               = false;
		$wp_query->is_paged             = false;
		$wp_query->is_admin             = false;
		$wp_query->is_preview           = false;
		$wp_query->is_robots            = false;
		$wp_query->is_posts_page        = false;
		$wp_query->is_post_type_archive = false;

		// Update global vars
		$GLOBALS['wp_query'] = $wp_query;
		$wp->register_globals();
	}

	final protected function render( $wpPost ) {
		add_action(
			'template_redirect',
			function () use ( $wpPost ) {
				$this->preventDbCalls( $wpPost );
				$this->updateMainWpQuery( $wpPost );
			}
		);
	}
}
