<?php

namespace MotoPress\Appointment\PostTypes\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
abstract class AddTermCustomField extends TermCustomField {

	/**
	 * @since 1.2
	 */
	protected function addActions() {
		parent::addActions();

		add_action( "{$this->taxonomy}_add_form_fields", array( $this, 'display' ) );
		add_action( "create_{$this->taxonomy}", array( $this, 'save' ) );
	}

	/**
	 * @param \WP_Term $term
	 *
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function display( $term ) {
		$customClass = $htmlId = $this->getInputId();

		?>
		<div class="form-field term-meta-wrap <?php echo esc_attr( $customClass ); ?>">
			<?php if ( ! empty( $this->label ) ) { ?>
				<label for="<?php echo esc_attr( $htmlId ); ?>">
					<?php echo $this->label; // Already escaped text ?>
				</label>
			<?php } ?>

			<?php $this->displayInput( $term, 'add' ); ?>

			<?php if ( ! empty( $this->description ) ) { ?>
				<p class="description">
					<?php echo $this->description; // Already escaped text ?>
				</p>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * @param int $termId
	 * @return bool
	 *
	 * @since 1.2
	 */
	protected function verifyNonce( $termId ) {
		// wp_nonce_field('add-tag', '_wpnonce_add-tag')
		return wp_verify_nonce( $_POST['_wpnonce_add-tag'], 'add-tag' );
	}
}
