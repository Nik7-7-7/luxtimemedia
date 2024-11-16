<?php
/**
 * @var $data
 */

wp_enqueue_style( 'masterstudy-form-builder-fields' );
wp_enqueue_script( 'masterstudy-form-builder-fields' );
?>

<div class="masterstudy-form-builder__checkbox-group">
	<?php if ( ! empty( $data['label'] ) ) { ?>
		<span class="masterstudy-form-builder__checkbox-label">
			<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['label'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_label' ) ); ?>
		</span>
		<?php
	}
	foreach ( $data['choices'] as $index => $choice ) {
		$values     = isset( $data['value'] ) ? explode( ',', $data['value'] ) : array();
		$is_checked = in_array( $choice, $values, true );
		?>
		<div class="masterstudy-form-builder__checkbox-container">
			<div class="masterstudy-form-builder__checkbox">
				<input type="checkbox" name="<?php echo esc_attr( $data['slug'] ?? '' ); ?>" value="<?php echo esc_attr( $choice ); ?>"/>
				<span class="masterstudy-form-builder__checkbox-wrapper <?php echo esc_attr( $is_checked ? 'masterstudy-form-builder__checkbox-wrapper_checked' : '' ); ?>"></span>
			</div>
			<span class="masterstudy-form-builder__checkbox-title">
				<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $choice, 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_choice_' . $index ) ); ?>
			</span>
		</div>
		<?php
	}
	if ( ! empty( $data['description'] ) ) {
		?>
		<span class="masterstudy-form-builder__checkbox-description">
			<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['description'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_description' ) ); ?>
		</span>
	<?php } ?>
</div>
