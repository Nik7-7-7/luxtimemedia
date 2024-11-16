<?php

/**
 * @param string       $template_name        Recommended. Shortcode or other template name.
 * @param array|string $attributes           Recommended. Array of [label, content, link, class] (all
 *                                           optional, except for content), array of contents of field name.
 * @param string       $attributes_separator Optional. ':' by default.
 * @param string       $title                Optional. '' by default.
 * @param string       $class                Optional. '' by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'template_name'        => mpa_prefix( 'post' ),
		'attributes'           => 'attributes', // By default, try to pull attributes from $entity->attributes
		'attributes_separator' => ':',
		'title'                => '',
		'class'                => '',
	),
	EXTR_SKIP
);

/**
 * @param string
 *
 * @since 1.2
 */
$attributes_separator = apply_filters( "{$template_name}_item_attributes_separator", $attributes_separator );

// Check attributes
if ( is_string( $attributes ) ) {

	$entity = mpa_get_current_entity();

	if ( ! is_null( $entity ) && isset( $entity->$attributes ) ) {

		$attributes = $entity->$attributes;

	} elseif ( ! is_null( $entity ) && method_exists( $entity, 'get' . ucfirst( $attributes ) ) ) {

		$attributesGetter = 'get' . ucfirst( $attributes );
		$attributes       = call_user_func( array( $entity, $attributesGetter ) );

	} else {
		$attributes = array();
	}
}

if ( empty( $attributes ) ) {
	return;
}

// Display template
$postType = get_post_type();

$postClass  = mpa_tmpl_id( "{$postType}-attributes" ); // "mpa-service-attributes"
$postClass .= rtrim( " {$class}" );

?>
<?php if ( ! empty( $title ) ) { ?>
	<h2 class="mpa-attributes-title">
		<?php echo esc_html( $title ); ?>
	</h2>
<?php } ?>

<ul class="mpa-attributes <?php echo esc_attr( $postClass ); ?>">
	<?php
	foreach ( $attributes as $attribute ) {
		$label = $content = $link = $class = '';

		if ( is_array( $attribute ) ) {
			extract( $attribute );
		} else {
			$content = $attribute;
		}

		$attributeClass  = sanitize_title( "mpa-attribute-{$label}" ); // "mpa-attribute-price"
		$attributeClass .= rtrim( " {$class}" );

		$titleClass = sanitize_title( "mpa-{$label}-title" ); // "mpa-price-title"

		?>
		<li class="<?php echo esc_attr( $attributeClass ); ?>">
			<?php if ( '' !== $label ) { ?>
				<span class="mpa-attribute-title <?php echo esc_attr( $titleClass ); ?>">
					<?php echo esc_html( $label ), $attributes_separator; ?>
				</span>
			<?php } ?>

			<span class="mpa-attribute-value">
				<?php if ( '' !== $link ) { ?>
					<a href="<?php echo esc_url( $link ); ?>">
						<?php echo wp_kses_post( $content ); // Allow some HTML ?>
					</a>
				<?php } else { ?>
					<?php echo wp_kses_post( $content ); // Allow some HTML ?>
				<?php } ?>
			</span>
		</li>
	<?php } // For each attribute ?>
</ul>
