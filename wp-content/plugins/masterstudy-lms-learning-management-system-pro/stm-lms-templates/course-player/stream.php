<?php
/**
 * @var int $post_id
 * @var int $item_id
 * @var array $data
 */

wp_enqueue_style( 'masterstudy-course-player-lesson-stream' );

if ( empty( $data['theme_fonts'] ) ) {
	wp_enqueue_style( 'masterstudy-course-player-lesson-stream-fonts' );
}

$stream_data = apply_filters( 'masterstudy_course_player_lesson_stream_data', $item_id );
?>
<div class="masterstudy-course-player-lesson-stream">
	<?php if ( ! $stream_data['stream_started'] && class_exists( 'STM_LMS_Templates' ) ) { ?>
		<div class="masterstudy-course-player-lesson-stream__wrapper">
			<div class="masterstudy-course-player-lesson-stream__countdown">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/countdown',
					array(
						'id'         => 'countdown_' . $item_id,
						'start_time' => intval( $stream_data['start_time'] ),
						'dark_mode'  => $data['dark_mode'],
						'style'      => 'default',
					)
				);
				?>
			</div>
			<div class="masterstudy-course-player-lesson-stream__info">
				<div class="masterstudy-course-player-lesson-stream__info-item">
					<span><?php echo esc_html__( 'Starts:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
					<span><?php echo esc_html( $stream_data['start_date'] ); ?></span>
				</div>
				<div class="masterstudy-course-player-lesson-stream__info-item">
					<span><?php echo esc_html__( 'Ends:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
					<span><?php echo esc_html( $stream_data['end_date'] ); ?></span>
				</div>
			</div>
		</div>
	<?php } else { ?>
	<div class="masterstudy-course-player-lesson-stream__video">
		<iframe src="<?php echo esc_attr( $stream_data['youtube_url'] ); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		<?php if ( ! empty( $stream_data['youtube_chat_url'] ) ) { ?>
			<iframe src="<?php echo esc_attr( $stream_data['youtube_chat_url'] ); ?>" frameborder="0" class="masterstudy-course-player-lesson-stream__chat"></iframe>
		<?php } ?>
	</div>
		<?php
	}
	if ( ! empty( $stream_data['content'] ) ) {
		?>
		<div class="masterstudy-course-player-lesson-stream__content">
			<?php echo wp_kses( htmlspecialchars_decode( $stream_data['content'] ), stm_lms_allowed_html() ); ?>
		</div>
		<?php
	}
	?>
</div>
