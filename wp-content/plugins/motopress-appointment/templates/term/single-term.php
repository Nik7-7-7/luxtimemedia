<?php

/**
 * @param WP_Term $term             Required.
 * @param string  $template_name    Recommended. Shortcode or other template name.
 * @param bool    $show_image       Optional. False by default.
 * @param bool    $show_name        Optional. False by default.
 * @param bool    $show_count       Optional. False by default.
 * @param bool    $show_description Optional. False by default.
 * @param bool    $show_children    Optional. False by default.
 * @param int     $depth            Optional. Big number by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'template_name'    => mpa_prefix( 'term' ),
		'show_image'       => false,
		'show_name'        => false,
		'show_count'       => false,
		'show_description' => false,
		'show_children'    => false,
		'depth'            => PHP_INT_MAX,
	),
	EXTR_SKIP
);

if ( 0 == $depth ) {
	$show_children = false;
}

// Args for nested templates
$itemArgs = $template_args + array( 'view' => 'single' ); // Don't replace the view from loop-terms.php, if exists

/**
 * @param array Template args.
 *
 * @since 1.2
 */
do_action( "{$template_name}_before_term", $itemArgs );

?>
<div class="mpa-grid-column">
	<div class="mpa-loop-term-wrapper">
		<?php
			/**
			 * @param array Template args.
			 *
			 * @since 1.2
			 */
			do_action( "{$template_name}_after_term_start", $itemArgs );
		?>

		<?php
		if ( $show_image ) {
			/**
			 * @param array Template args.
			 *
			 * @since 1.2
			 */
			do_action( "{$template_name}_term_image", $itemArgs );
		}
		?>

		<?php
		if ( $show_name ) {
			/**
			 * @param array Template args.
			 *
			 * @since 1.2
			 */
			do_action( "{$template_name}_term_name", $itemArgs );
		}
		?>

		<?php
		if ( $show_description ) {
			/**
			 * @param array Template args.
			 *
			 * @since 1.2
			 */
			do_action( "{$template_name}_term_description", $itemArgs );
		}
		?>

		<?php
		if ( $show_children ) {
			/**
			 * @param array Template args.
			 *
			 * @since 1.2
			 */
			do_action( "{$template_name}_term_children", $itemArgs );
		}
		?>

		<?php
			/**
			 * @param array Template args.
			 *
			 * @since 1.2
			 */
			do_action( "{$template_name}_before_term_end", $itemArgs );
		?>
	</div>
</div>

<?php
	/**
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$template_name}_after_term", $itemArgs );
?>
