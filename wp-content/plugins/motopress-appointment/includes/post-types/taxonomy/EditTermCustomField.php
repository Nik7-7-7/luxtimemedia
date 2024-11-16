<?php

namespace MotoPress\Appointment\PostTypes\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
abstract class EditTermCustomField extends TermCustomField {

	/**
	 * @since 1.2
	 */
	protected function addActions() {
		parent::addActions();

		add_action( "{$this->taxonomy}_edit_form_fields", array( $this, 'display' ) );
		add_action( "edited_{$this->taxonomy}", array( $this, 'save' ) );
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
		<tr class="form-field <?php echo esc_attr( $customClass ); ?>">
			<th scope="row" valign="top">
				<?php if ( ! empty( $this->label ) ) { ?>
					<label for="<?php echo esc_attr( $htmlId ); ?>">
						<?php echo $this->label; // Already escaped text ?>
					</label>
				<?php } ?>
			</th>

			<td>
				<?php $this->displayInput( $term, 'edit' ); ?>

				<?php if ( ! empty( $this->description ) ) { ?>
					<span class="description">
						<?php echo $this->description; // Already escaped text ?>
					</span>
				<?php } ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param int $termId
	 * @return bool
	 *
	 * @since 1.2
	 */
	protected function verifyNonce( $termId ) {
		// wp_nonce_field("update-tag_{$tag_ID}");
		return wp_verify_nonce( $_POST['_wpnonce'], "update-tag_{$termId}" );
	}
}
