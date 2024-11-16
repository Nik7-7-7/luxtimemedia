<?php
/**
 * @var $data
 */

wp_enqueue_script( 'masterstudy-select2' );
wp_enqueue_style( 'masterstudy-select2' );
wp_enqueue_style( 'masterstudy-form-builder-fields' );
wp_enqueue_script( 'masterstudy-form-builder-fields' );

$data['value'] = $data['value'] ?? '';
?>

<div class="masterstudy-form-builder__select-container">
	<?php if ( ! empty( $data['label'] ) ) { ?>
		<span class="masterstudy-form-builder__select-label">
			<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['label'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_label' ) ); ?>
		</span>
	<?php } ?>
	<div class="masterstudy-form-builder__select-wrapper">
		<select name="<?php echo esc_attr( $data['slug'] ?? '' ); ?>" class="masterstudy-form-builder__select">
			<?php
			if ( ! empty( $data['choices'] ) ) {
				foreach ( $data['choices'] as $index => $choice ) {
					?>
					<option value="<?php echo esc_attr( $choice ); ?>" <?php selected( $data['value'], $choice ); ?>><?php echo esc_html( apply_filters( 'wpml_translate_single_string', $choice, 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_choice_' . $index ) ); ?></option>
					<?php
				}
			}
			?>
		</select>
	</div>
</div>

<?php if ( ! empty( $data['description'] ) ) { ?>
	<span class="masterstudy-form-builder__select-description">
		<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['description'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_description' ) ); ?>
	</span>
	<?php
}
