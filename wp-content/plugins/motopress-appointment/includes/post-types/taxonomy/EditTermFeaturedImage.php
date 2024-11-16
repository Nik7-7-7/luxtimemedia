<?php

namespace MotoPress\Appointment\PostTypes\Taxonomy;

use MotoPress\Appointment\Fields\Basic\ImageField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EditTermFeaturedImage extends EditTermCustomField {

	/**
	 * @since 1.2
	 */
	protected function addActions() {
		parent::addActions();

		add_action( "{$this->taxonomy}_pre_edit_form", array( $this, 'enqueueAssets' ) );
	}

	/**
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function enqueueAssets() {
		mpa_assets()->enqueueBundle( 'mpa-edit-category' );
	}

	/**
	 * @param \WP_Term $term
	 * @param 'edit' $view
	 *
	 * @since 1.2
	 */
	protected function displayInput( $term, $view ) {
		$imageField = new ImageField( $this->getInputName(), array(), $this->getValue( $term ) );

		echo $imageField->renderInput();
	}
}
