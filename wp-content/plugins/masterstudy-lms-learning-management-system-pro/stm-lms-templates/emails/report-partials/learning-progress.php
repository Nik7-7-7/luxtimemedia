<?php // phpcs:ignoreFile
/**
 * Email template
 *
 * @var $result_learning
 * @var $assignments
 * @var $points
 */
?>
<h3 class="email-analytics-reports-section-title" style="color: black">
	<?php echo esc_html__( 'Learning progress', 'masterstudy-lms-learning-management-system-pro' ); ?>
</h3>
<!-- Replace grid layout with table layout for better email client support -->
<table border="0" cellpadding="10" cellspacing="0" width="100%" style="margin-bottom: 20px;">
	<tr>
		<!-- Column 1: Courses enrolled -->
		<td style="text-align: center; width: 33%; padding: 10px;">
			<?php STM_LMS_Templates::show_lms_template(
				'emails/report-partials/common-items',
				array(
					'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/lp-book.png',
					'value'      => $result_learning['courses']['enrolled'],
					'text'       => esc_html__( 'Courses enrolled', 'masterstudy-lms-learning-management-system-pro' ),
					'style_type' => 'learning',
				)
			); ?>
		</td>
		<!-- Column 2: Courses completed -->
		<td style="text-align: center; width: 33%; padding: 10px;">
			<?php STM_LMS_Templates::show_lms_template(
				'emails/report-partials/common-items',
				array(
					'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/lp-completed.png',
					'value'      => $result_learning['courses']['completed'],
					'text'       => esc_html__( 'Courses completed', 'masterstudy-lms-learning-management-system-pro' ),
					'style_type' => 'learning',
				)
			); ?>
		</td>
		<!-- Column 4: Quizzes passed -->
		<td style="text-align: center; width: 33%; padding: 10px;">
			<?php STM_LMS_Templates::show_lms_template(
				'emails/report-partials/common-items',
				array(
					'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/lp-quizzes.png',
					'value'      => $result_learning['quizzes']['passed'],
					'text'       => esc_html__( 'Quizzes passed', 'masterstudy-lms-learning-management-system-pro' ),
					'style_type' => 'learning',
				)
			); ?>
		</td>
	</tr>
	<tr>
		<?php
		if ( is_ms_lms_addon_enabled( 'certificate_builder' ) ) {
			?>
			<!-- Column 3: Certificates earned -->
			<td style="text-align: center; width: 33%; padding: 10px;">
				<?php STM_LMS_Templates::show_lms_template(
					'emails/report-partials/common-items',
					array(
						'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/lp-certificate.png',
						'value'      => $result_learning['certificates'],
						'text'       => esc_html__( 'Certificates earned', 'masterstudy-lms-learning-management-system-pro' ),
						'style_type' => 'learning',
					)
				); ?>
			</td>
			<?php
		}
		?>
		<?php
		if ( is_ms_lms_addon_enabled( 'assignments' ) ) {
			?>
			<!-- Column 5: Assignments passed -->
			<td style="text-align: center; width: 33%; padding: 10px;">
				<?php STM_LMS_Templates::show_lms_template(
					'emails/report-partials/common-items',
					array(
						'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/lp-assignments.png',
						'value'      => $assignments,
						'text'       => esc_html__( 'Assignments passed', 'masterstudy-lms-learning-management-system-pro' ),
						'style_type' => 'learning',
					)
				); ?>
			</td>
			<?php
		}
		?>
		<?php
		if ( is_ms_lms_addon_enabled( 'point_system' ) ) {
			?>
			<!-- Column 6: Points earned -->
			<td style="text-align: center; width: 33%; padding: 10px;">
				<?php STM_LMS_Templates::show_lms_template(
					'emails/report-partials/common-items',
					array(
						'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/lp-points.png',
						'value'      => $points,
						'text'       => esc_html__( 'Points earned', 'masterstudy-lms-learning-management-system-pro' ),
						'style_type' => 'learning',
					)
				); ?>
			</td>
			<?php
		}
		?>
	</tr>
</table>
<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 auto 30px auto; text-align: center;">
	<tr>
		<td align="center">
			<a href="<?php echo esc_url( site_url() . '/user-account/enrolled-courses/' ); ?>"
			   style="background-color: #227AFF; color: #ffffff; text-decoration: none; padding: 16px 24px; display: inline-block; border-radius: 5px; font-family: 'Arial', sans-serif; font-size: 16px; line-height: 1.5; text-align: center;">
				<?php echo esc_html__( 'Continue learning', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</a>
		</td>
	</tr>
</table>
