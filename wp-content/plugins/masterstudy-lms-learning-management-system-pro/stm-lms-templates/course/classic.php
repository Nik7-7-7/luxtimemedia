<?php
$course_data = apply_filters( 'masterstudy_course_page_header', 'classic' );

if ( ! is_array( $course_data ) ) {
	return;
}
?>

<div class="masterstudy-single-course-classic">
	<div class="masterstudy-single-course-classic__topbar">
		<div class="masterstudy-single-course-classic__topbar-status">
			<?php STM_LMS_Templates::show_lms_template( 'components/course/status', array( 'course' => $course_data['course'] ) ); ?>
		</div>
		<?php if ( ! $course_data['is_coming_soon'] || $course_data['course']->coming_soon_details ) { ?>
			<div class="masterstudy-single-course-classic__info">
				<?php if ( ! empty( $course_data['course']->category ) ) { ?>
					<div class="masterstudy-single-course-classic__info-block">
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/course/categories',
							array(
								'term_ids' => $course_data['course']->category,
								'only_one' => true,
							)
						);
						?>
					</div>
				<?php } ?>
				<div class="masterstudy-single-course-classic__info-block">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/course/instructor',
						array(
							'instructor' => $course_data['instructor'],
							'course'     => $course_data['course'],
						)
					);
					?>
				</div>
				<?php
				if ( ! empty( $course_data['course']->rate ) || ! empty( $course_data['course']->udemy_rate ) ) {
					?>
					<div class="masterstudy-single-course-classic__info-block">
						<?php STM_LMS_Templates::show_lms_template( 'components/course/rating', array( 'course' => $course_data['course'] ) ); ?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<div class="masterstudy-single-course-classic__heading">
			<?php STM_LMS_Templates::show_lms_template( 'components/course/title', array( 'title' => $course_data['course']->title ) ); ?>
		</div>
		<?php
		if ( ! empty( $course_data['course']->excerpt ) || ! empty( $course_data['course']->udemy_headline ) ) {
			?>
			<div class="masterstudy-single-course-classic__desc">
				<?php STM_LMS_Templates::show_lms_template( 'components/course/excerpt', array( 'course' => $course_data['course'] ) ); ?>
			</div>
		<?php } ?>
	</div>
	<div class="masterstudy-single-course-classic__sidebar">
		<?php
		STM_LMS_Templates::show_lms_template(
			'components/course/complete',
			array(
				'course_id'     => $course_data['course']->id,
				'user_id'       => $course_data['current_user_id'],
				'settings'      => $course_data['settings'],
				'block_enabled' => true,
			)
		);
		if ( ! $course_data['is_coming_soon'] || $course_data['course']->coming_soon_preorder ) {
			?>
			<div class="masterstudy-single-course-classic__cta">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/buy-button/buy-button',
					array(
						'post_id'              => $course_data['course']->id,
						'item_id'              => '',
						'user_id'              => $course_data['current_user_id'],
						'dark_mode'            => false,
						'prerequisite_preview' => false,
						'hide_group_course'    => false,
					)
				);
				?>
			</div>
			<?php
		}
		if ( $course_data['is_coming_soon'] && $course_data['course']->coming_soon_price && ! $course_data['course']->coming_soon_preorder ) {
			?>
			<div class="masterstudy-single-course-classic__cta">
				<?php STM_LMS_Templates::show_lms_template( 'components/course/coming-button' ); ?>
			</div>
			<?php
		}
		STM_LMS_Templates::show_lms_template(
			'components/course/expired',
			array(
				'course'         => $course_data['course'],
				'user_id'        => $course_data['current_user_id'],
				'is_coming_soon' => $course_data['is_coming_soon'],
			)
		);
		?>
		<div class="masterstudy-single-course-classic__buttons">
			<?php
			STM_LMS_Templates::show_lms_template( 'components/course/wishlist', array( 'course_id' => $course_data['course']->id ) );
			STM_LMS_Templates::show_lms_template( 'components/course/share-button', array( 'course' => $course_data['course'] ) );
			?>
		</div>
		<?php STM_LMS_Templates::show_lms_template( 'components/course/price-info', array( 'course' => $course_data['course'] ) ); ?>
		<div class="masterstudy-single-course-classic__details">
			<?php STM_LMS_Templates::show_lms_template( 'components/course/details', array( 'course' => $course_data['course'] ) ); ?>
		</div>
		<?php
		if ( $course_data['settings']['course_allow_basic_info'] && ! empty( $course_data['course']->basic_info ) ) {
			STM_LMS_Templates::show_lms_template(
				'components/course/info',
				array(
					'course_id' => $course_data['course']->id,
					'content'   => $course_data['course']->basic_info,
					'title'     => esc_html__( 'Basic info', 'masterstudy-lms-learning-management-system' ),
				),
			);
		}
		if ( $course_data['settings']['course_allow_requirements_info'] && ! empty( $course_data['course']->requirements ) ) {
			STM_LMS_Templates::show_lms_template(
				'components/course/info',
				array(
					'course_id' => $course_data['course']->id,
					'content'   => $course_data['course']->requirements,
					'title'     => esc_html__( 'Course requirements', 'masterstudy-lms-learning-management-system' ),
				),
			);
		}
		if ( $course_data['settings']['course_allow_intended_audience'] && ! empty( $course_data['course']->intended_audience ) ) {
			STM_LMS_Templates::show_lms_template(
				'components/course/info',
				array(
					'course_id' => $course_data['course']->id,
					'content'   => $course_data['course']->intended_audience,
					'title'     => esc_html__( 'Intended audience', 'masterstudy-lms-learning-management-system' ),
				),
			);
		}
		STM_LMS_Templates::show_lms_template( 'components/course/popular-courses', array( 'course' => $course_data['course'] ) );
		if ( is_active_sidebar( 'stm_lms_sidebar' ) ) {
			?>
			<div class="masterstudy-single-course-widgets">
				<?php dynamic_sidebar( 'stm_lms_sidebar' ); ?>
			</div>
			<?php
		}
		if ( $course_data['settings']['enable_sticky'] && $course_data['show_panel'] ) {
			STM_LMS_Templates::show_lms_template(
				'components/course/stickybar',
				array(
					'course'     => $course_data['course'],
					'instructor' => $course_data['instructor'],
					'settings'   => $course_data['settings'],
				)
			);
		}
		?>
	</div>
	<div class="masterstudy-single-course-classic__main">
		<?php
		STM_LMS_Templates::show_lms_template(
			'global/coming_soon',
			array(
				'course_id' => $course_data['course']->id,
				'mode'      => 'course',
			),
		);
		STM_LMS_Templates::show_lms_template(
			'components/course/thumbnail',
			array(
				'course'         => $course_data['course'],
				'course_preview' => $course_data['course_preview'] ?? '',
			)
		);
		STM_LMS_Templates::show_lms_template(
			'components/course/tabs',
			array(
				'course'         => $course_data['course'],
				'course_preview' => $course_data['course_preview'] ?? '',
				'user_id'        => $course_data['current_user_id'],
				'style'          => 'underline',
			)
		);
		if ( $course_data['settings']['enable_related_courses'] ) {
			STM_LMS_Templates::show_lms_template( 'components/course/related-courses', array( 'course' => $course_data['course'] ) );
		}
		?>
	</div>
</div>
