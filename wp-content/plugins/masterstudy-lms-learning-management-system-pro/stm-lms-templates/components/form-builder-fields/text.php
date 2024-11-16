<?php
/**
 * @var $data
 */

wp_enqueue_style( 'masterstudy-form-builder-fields' );
wp_enqueue_script( 'masterstudy-form-builder-fields' );

if ( ! empty( $data['label'] ) ) { ?>
	<span class="masterstudy-form-builder__text-label">
		<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['label'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_label' ) ); ?>
	</span>
<?php } ?>

<input type="text" name="<?php echo isset( $data['slug'] ) ? esc_attr( $data['slug'] ) : ''; ?>" class="masterstudy-form-builder__text" placeholder="<?php echo isset( $data['placeholder'] ) ? esc_attr( apply_filters( 'wpml_translate_single_string', $data['placeholder'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_placeholder' ) ) : ''; ?>" value="<?php echo isset( $data['value'] ) ? esc_attr( $data['value'] ) : ''; ?>">

<?php if ( ! empty( $data['description'] ) ) { ?>
	<span class="masterstudy-form-builder__text-description">
		<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['description'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_description' ) ); ?>
	</span>
	<?php
}
