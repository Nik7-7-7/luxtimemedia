<?php
/**
 * @var $data
 */

wp_enqueue_style( 'masterstudy-form-builder-fields' );
wp_enqueue_script( 'masterstudy-form-builder-fields' );

if ( ! empty( $data['label'] ) ) { ?>
	<span class="masterstudy-form-builder__textarea-label">
		<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['label'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_label' ) ); ?>
	</span>
<?php } ?>

<textarea rows="5" name="<?php echo isset( $data['slug'] ) ? esc_attr( $data['slug'] ) : ''; ?>" class="masterstudy-form-builder__textarea" placeholder="<?php echo isset( $data['placeholder'] ) ? esc_attr( apply_filters( 'wpml_translate_single_string', $data['placeholder'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_placeholder' ) ) : ''; ?>"><?php echo isset( $data['value'] ) ? esc_attr( $data['value'] ) : ''; ?></textarea>

<?php if ( ! empty( $data['description'] ) ) { ?>
	<span class="masterstudy-form-builder__textarea-description">
		<?php echo esc_html( apply_filters( 'wpml_translate_single_string', $data['description'], 'masterstudy-lms-learning-management-system-pro', 'masterstudy_form_builder_' . $data['id'] . '_description' ) ); ?>
	</span>
	<?php
}
