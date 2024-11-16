<?php
/**
 * @var int $post_id
 * @var int $item_id
 * @var array $data
 */

wp_enqueue_style( 'masterstudy-course-player-lesson-google' );

if ( empty( $data['theme_fonts'] ) ) {
	wp_enqueue_style( 'masterstudy-course-player-lesson-google-fonts' );
}

$google_meet = apply_filters( 'masterstudy_course_player_lesson_google_data', $item_id, $post_id );
?>
<div class="masterstudy-course-player-lesson-google">
	<div class="masterstudy-course-player-lesson-google__wrapper">
		<?php if ( ! $google_meet['meet_started'] && class_exists( 'STM_LMS_Templates' ) ) { ?>
			<div class="masterstudy-course-player-lesson-google__countdown">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/countdown',
					array(
						'id'         => 'countdown_' . $item_id,
						'start_time' => intval( $google_meet['start_time'] ),
						'dark_mode'  => $data['dark_mode'],
						'style'      => 'default',
					)
				);
				?>
			</div>
		<?php } ?>
		<div class="masterstudy-course-player-lesson-google__info">
			<div class="masterstudy-course-player-lesson-google__info-item">
				<span><?php echo esc_html__( 'Starts:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
				<span><?php echo esc_html( $google_meet['start_date'] ); ?></span>
			</div>
			<div class="masterstudy-course-player-lesson-google__info-item">
				<span><?php echo esc_html__( 'Ends:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
				<span><?php echo esc_html( $google_meet['end_date'] ); ?></span>
			</div>
			<div class="masterstudy-course-player-lesson-google__info-item">
				<span><?php echo esc_html__( 'Host e-mail:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
				<span class="masterstudy-course-player-lesson-google__email"><?php echo esc_html( $google_meet['author_email'] ); ?></span>
			</div>
		</div>
		<a href="<?php echo esc_url( $google_meet['meet_url'] ); ?>" target="_blank" class="masterstudy-course-player-lesson-google__button">
			<?php echo esc_html__( 'Join meeting', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</a>
	</div>
	<?php if ( ! empty( $google_meet['description'] ) ) { ?>
		<div class="masterstudy-course-player-lesson-google__description">
			<?php echo esc_html( $google_meet['description'] ); ?>
		</div>
	<?php } ?>
</div>
