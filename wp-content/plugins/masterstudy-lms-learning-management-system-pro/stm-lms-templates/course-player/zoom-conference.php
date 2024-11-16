<?php
/**
 * @var int $post_id
 * @var int $item_id
 * @var array $data
 */

wp_enqueue_style( 'masterstudy-course-player-lesson-zoom' );

if ( empty( $data['theme_fonts'] ) ) {
	wp_enqueue_style( 'masterstudy-course-player-lesson-zoom-fonts' );
}

$meeting_id = get_post_meta( $item_id, 'meeting_created', true );
$content    = masterstudy_course_player_get_content( $item_id, true );
?>
<div class="masterstudy-course-player-lesson-zoom">
	<?php
	if ( ! empty( $meeting_id ) ) {
		echo do_shortcode( '[stm_zoom_conference post_id="' . $meeting_id . '"]' );
	}
	if ( ! empty( $content ) ) {
		?>
		<div class="masterstudy-course-player-lesson-zoom__content">
			<?php echo wp_kses( htmlspecialchars_decode( $content ), stm_lms_allowed_html() ); ?>
		</div>
	<?php } ?>
</div>
