<?php

namespace MotoPress\Appointment\DirectLinkActions\Pages;

use MotoPress\Appointment\Entities\InterfaceUniqueEntity;

/**
 * @since 1.15.0
 */
abstract class AbstractRealPage extends AbstractVirtualPage {

	public function __construct() {
		parent::__construct();

		add_filter( "update_option_{$this->optionNameWithPageId()}", array( $this, 'togglePageStatus' ), 10, 2 );
		add_filter( 'get_pages', array( $this, 'addPageToPageList' ), 10, 2 );
		add_filter( 'display_post_states', array( $this, 'displayPostStates' ), 10, 2 );
	}

	/**
	 * @return string
	 */
	abstract protected function optionNameWithPageId();

	/**
	 * @return int
	 */
	abstract protected function getPageId();

	/**
	 * @return \WP_Post|null
	 */
	protected function getPage() {
		$wpPostId = (int) $this->getPageId();

		return get_post( $wpPostId );
	}

	public function togglePageStatus( $oldValue, $value ) {

		$oldWpPost = get_post( $oldValue );
		$wpPost    = get_post( $value );

		if ( null !== $oldWpPost && 'publish' != $oldWpPost->post_status ) {
			$oldWpPost->post_status = 'publish';
			wp_update_post( $oldWpPost );
		}

		if ( null !== $wpPost && 'private' != $wpPost->post_status ) {
			$wpPost->post_status = 'private';
			wp_update_post( $wpPost );
		}
	}

	public function addPageToPageList( $pages, $parsed_args ) {

		if ( isset( $parsed_args['name'] ) && $parsed_args['name'] === $this->optionNameWithPageId() ) {

			$page = $this->getPage();

			if ( $page ) {
				$pages[] = $page;
				$pages   = wp_list_sort( $pages, array( 'post_title' => 'ASC' ) );
			}
		}

		return $pages;
	}

	public function displayPostStates( $postStates, $post ) {

		if ( $post->ID === $this->getPageId() ) {
			$optionName                = $this->optionNameWithPageId();
			$postStates[ $optionName ] = mpapp()->getName() . ': ' . $this->getTitle();
		}

		return $postStates;
	}

	/**
	 * @return string
	 */
	protected function defaultContent() {
		return '';
	}

	/**
	 * @return string
	 */
	protected function mustContent() {
		return '';
	}

	private function hasMustContent( $content ) {

		$mustContent = $this->mustContent();

		if ( ! $mustContent ) {
			return true;
		}

		if ( false === strpos( $content, $mustContent ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param \MotoPress\Appointment\Entities\AbstractUniqueEntity $entity
	 *
	 * @return void
	 */
	protected function action( InterfaceUniqueEntity $entity ) {
		$wpPost = $this->getPage();

		if ( ! $wpPost ) {
			$wpPost = $this->createPage();
		}

		if ( ! $this->hasMustContent( $wpPost->post_content ) ) {
			$wpPost->post_content .= $this->mustContent();
		}

		// If page isn't virtual, for correct it displaying need make page status of publish,
		// because it pages was made private by hook in __construct().
		$wpPost->post_status = 'publish';

		/**
		 * @param \WP_Post $wpPost
		 * @param \MotoPress\Appointment\Entities\AbstractUniqueEntity $entity
		 */
		$wpPost = apply_filters( "mpa_direct_link_action_page_pre_render_{$this->getPageSlug()}", $wpPost, $entity );

		$this->render( $wpPost );
	}
}
