<?php
/**
 * @var $user_id
 */

$forms        = get_option( 'stm_lms_form_builder_forms', array() );
$profile_form = array();

if ( class_exists( 'STM_LMS_Form_Builder' ) && ! empty( $forms ) && is_array( $forms ) ) {
	foreach ( $forms as $form ) {
		if ( 'profile_form' === $form['slug'] ) {
			$profile_form = $form['fields'];
		}
	}
}

if ( empty( $profile_form ) || empty( $user_id ) ) {
	return;
}

wp_enqueue_style( 'masterstudy-form-builder-public-fields' );
?>

<div class="masterstudy-form-builder-public-fields">
	<?php
	$user_meta = get_user_meta( $user_id );
	foreach ( $profile_form as $field ) {
		$field['value'] = ! empty( $user_meta[ $field['id'] ][0] ) ? $user_meta[ $field['id'] ][0] : '';
		if ( ! empty( $field['value'] ) ) {
			?>
			<div class="masterstudy-form-builder-public-fields__field">
				<?php
				if ( 'file' === $field['type'] ) {
					$field['extensions'] = ! empty( $field['extensions'] ) ? $field['extensions'] : '.png, .jpg, .jpeg, .mp4, .pdf';
					$attachment_id       = attachment_url_to_postid( $field['value'] );
					$attachment          = ! empty( $attachment_id ) ? get_post( $attachment_id ) : '';
					if ( ! empty( $attachment ) ) {
						STM_LMS_Templates::show_lms_template(
							'components/form-builder-fields/file',
							array(
								'data'                   => $field,
								'attachments'            => array( $attachment ),
								'allowed_extensions'     => explode( ', ', $field['extensions'] ),
								'files_limit'            => '',
								'allowed_filesize'       => '',
								'allowed_filesize_label' => '',
								'readonly'               => true,
								'desc_off'               => true,
								'label_off'              => true,
								'multiple'               => false,
								'dark_mode'              => false,
							)
						);
					}
				} else {
					if ( ! empty( $field['label'] ) && in_array( $field['type'], array( 'tel', 'email' ), true ) ) {
						?>
						<div class="masterstudy-form-builder-public-fields__field-label">
							<?php echo esc_html( $field['label'] ); ?>:
						</div>
						<?php
					}
					$field['value'] = 'checkbox' === $field['type'] ? str_replace( ',', ', ', $field['value'] ) : $field['value'];
					?>
					<div class="masterstudy-form-builder-public-fields__field-value">
						<?php if ( 'tel' === $field['type'] ) { ?>
							<a href="tel:<?php echo esc_attr( $field['value'] ); ?>" class="masterstudy-form-builder-public-fields__field-link">
								<?php echo esc_html( $field['value'] ); ?>
							</a>
							<?php
						} elseif ( 'email' === $field['type'] ) {
							?>
							<a href="mailto:<?php echo esc_attr( $field['value'] ); ?>" class="masterstudy-form-builder-public-fields__field-link">
								<?php echo esc_html( $field['value'] ); ?>
							</a>
							<?php
						} else {
							echo esc_html( $field['value'] );
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
	}
	?>
</div>
