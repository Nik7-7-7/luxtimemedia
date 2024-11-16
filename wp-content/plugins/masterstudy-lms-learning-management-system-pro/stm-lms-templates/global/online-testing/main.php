<?php
/**
 * @var $item_id
 */

$quiz_data = array();

if ( class_exists( '\\MasterStudy\\Lms\\Repositories\\CoursePlayerRepository' ) ) {
	$course_player = new \MasterStudy\Lms\Repositories\CoursePlayerRepository();
	$quiz_data     = $course_player->get_quiz_data( $item_id );
}
if ( ! is_user_logged_in() && isset( $_GET['show_answers'], $_COOKIE['quiz_user_answer_id'] ) && $_GET['show_answers'] === $_COOKIE['quiz_user_answer_id'] ) {
	$quiz_data['show_answers'] = true;
	$quiz_data['passed']       = true;
	$quiz_data['last_quiz']    = true;
	$quiz_data['progress']     = sanitize_text_field( $_GET['progress'] ?? 0 );
}

$source = STM_LMS_Helpers::current_screen();

do_action( 'masterstudy_lms_course_player_register_assets' );

stm_lms_register_style( 'online-testing' );
?>
<div class="masterstudy-online-testing">
	<div class="masterstudy-online-testing__wrapper">
		<?php
		if ( ! empty( $quiz_data['duration'] ) && $quiz_data['duration'] > 0 ) {
			STM_LMS_Templates::show_lms_template( 'course-player/content/quiz/timer' );
		}
		?>
		
		<h1><?php the_title(); ?></h1>

		<?php
		STM_LMS_Templates::show_lms_template(
			'course-player/content/quiz/main',
			array(
				'item_id'        => $item_id,
				'post_id'        => $source,
				'dark_mode'      => false,
				'lesson_type'    => 'quiz',
				'data'           => $quiz_data,
				'last_lesson'    => false,
				'is_single_quiz' => ! is_user_logged_in(),
			)
		);
		?>
		<div class="masterstudy-course-player-navigation__submit-quiz masterstudy-course-player-navigation__submit-quiz_hide">
			<?php
			STM_LMS_Templates::show_lms_template(
				'components/button',
				array(
					'title'         => __( 'Submit quiz', 'masterstudy-lms-learning-management-system' ),
					'type'          => '',
					'link'          => '#',
					'style'         => 'primary',
					'size'          => 'sm',
					'id'            => 'submit-quiz',
					'icon_position' => '',
					'icon_name'     => '',
				)
			);
			?>
		</div>
		<?php if ( 'default' === $quiz_data['quiz_style'] && ! empty( $quiz_data['questions_for_nav'] ) && $quiz_data['questions_for_nav'] > 1 ) { ?>
		<div class="masterstudy-online-testing-quiz__navigation-tab">
			<div class="masterstudy-course-player-quiz__navigation-tabs">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/tabs-pagination',
					array(
						'max_visible_tabs' => 10,
						'tabs_quantity'    => $quiz_data['questions_for_nav'],
						'vertical'         => true,
						'dark_mode'        => false,
					)
				);
				?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
