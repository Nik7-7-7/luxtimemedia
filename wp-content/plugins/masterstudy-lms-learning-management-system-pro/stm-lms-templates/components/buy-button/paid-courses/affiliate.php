<?php
/**
 * @var int $course_id
 * @var string $text
 * @var string $link
 */

wp_enqueue_style( 'masterstudy-buy-button-affiliate' );

$price             = get_post_meta( $course_id, 'price', true );
$sale_price        = get_post_meta( $course_id, 'sale_price', true );
$sale_price_active = STM_LMS_Helpers::is_sale_price_active( $course_id );
$settings          = get_option( 'stm_lms_settings' );
$theme_fonts       = $settings['course_player_theme_fonts'] ?? false;
if ( empty( $theme_fonts ) ) {
	wp_enqueue_style( 'masterstudy-buy-button-affiliate-fonts' );
}
?>

<div class="masterstudy-button-affiliate">
	<a class="masterstudy-button-affiliate__link" href="<?php echo esc_url( $link ); ?>" target="_blank">
		<span class="masterstudy-button-affiliate__title"><?php echo esc_html( sanitize_text_field( $text ) ); ?></span>
		<?php if ( ! empty( $price ) || ! empty( $sale_price ) ) : ?>
			<span class="masterstudy-button-affiliate__price<?php echo ! empty( $sale_price ) && ! empty( $sale_price_active ) ? ' has_sale' : ''; ?>">
			<?php if ( ! empty( $sale_price ) && ! empty( $sale_price_active ) ) : ?>
				<span class="masterstudy-button-affiliate__price_sale"><?php echo esc_html( STM_LMS_Helpers::display_price( $sale_price ) ); ?></span>
				<?php
			endif;
			if ( ! empty( $price ) ) :
				?>
				<span class="masterstudy-button-affiliate__price_regular"><?php echo esc_html( STM_LMS_Helpers::display_price( $price ) ); ?></span>
				<?php
			endif;
			?>
		</span>
		<?php endif; ?>
	</a>
</div>
