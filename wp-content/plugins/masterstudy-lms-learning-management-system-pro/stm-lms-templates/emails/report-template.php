<?php // phpcs:ignoreFile
/**
 * Email template
 *
 * @var $email_manager
 * @var $role
 * @var $user_id
 */

use MasterStudy\Lms\Pro\RestApi\Context\InstructorContext;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Instructor\InstructorDataSerializer;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Student\StudentDataSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\Instructor\EnrollmentRepository;
use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;

$header_bg            = ! empty( $email_manager['stm_lms_email_template_hf_header_bg'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_hf_header_bg'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_header.png';
$logo                 = ! empty( $email_manager['stm_lms_email_template_hf_logo'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_hf_logo'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_logo.png';
$footer_bg            = ! empty( $email_manager['stm_lms_email_template_hf_footer_bg'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_hf_footer_bg'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_footer.png';
$reply_icon           = ! empty( $email_manager['stm_lms_email_template_reply_icon'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_reply_icon'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_reply.png';
$footer_copyrights    = $email_manager['stm_lms_email_template_reply_textarea'] ?? '';
$footer_reply         = $email_manager['stm_lms_email_template_reply_text'] ?? '';
$outside_bg           = $email_manager['stm_lms_email_template_hf_entire_bg'] ?? '';
$status_header_footer = $email_manager['stm_lms_email_template_hf'] ?? '';
$status_reply         = $email_manager['stm_lms_email_template_reply'] ?? '';
$email_message        = get_message_by_role( $role, $email_manager );

$date_range = get_date_range( $role, get_option( 'stm_lms_email_manager_settings', array() ) );
$date_from  = $date_range['date_from'];
$date_to    = $date_range['date_to'];

$analytics_helper              = new MsLmsEmailsAnalyticsHelper( $date_from, $date_to, $user_id );
$enrollment_repository         = new EnrollmentRepository( $user_id, $date_from, $date_to );
$student_enrollment_repository = new MasterStudy\Lms\Pro\RestApi\Repositories\Student\EnrollmentRepository( $user_id, $date_from, $date_to );
$analytics_controller          = new Controller();
$orders_provider               = $analytics_controller->get_checkout_provider()->get_provider( 'orders' );
$revenue_repository            = new $orders_provider( $date_from, $date_to );

if ( 'administrator' === $role || 'stm_lms_instructor' === $role ) {
	//only admin
	$instructor_registered = $analytics_helper->get_registrations_count_by_role( 'stm_lms_instructor' );
	$course_added          = $analytics_helper->get_courses_published_count( 'stm-courses' );

	//only admin and instructor
	if ( 'stm_lms_instructor' === $role ) {
		$reviews_count = $analytics_helper->get_reviews_for_instructor_courses( $user_id, $date_from, $date_to );
	} else {
		$reviews_count       = $analytics_helper->get_courses_published_count( 'stm-reviews' );
	}
	$revenue_enrollments = @( new InstructorDataSerializer() )->toArray(
		array_merge(
			$revenue_repository->get_instructor_revenue(
				$enrollment_repository->get_instructor_course_ids()
			),
			$enrollment_repository->get_enrollments_data()
		)
	);

	if ( 'stm_lms_instructor' === $role ) {
		InstructorContext::get_instance()->set_instructor_id( $user_id );

		$students_joined      = $revenue_enrollments['total_enrollments'];
		$students_joined_text = esc_html__( 'Students enrolled', 'masterstudy-lms-learning-management-system-pro' );
	} else {
		$students_joined      = $analytics_helper->get_registrations_count_by_role( 'subscriber' );
		$students_joined_text = esc_html__( 'Students joined', 'masterstudy-lms-learning-management-system-pro' );
	}
	$revenue = @( new $orders_provider( $date_from, $date_to ) )->get_revenue();
}

$points      = $analytics_helper->get_user_points_sum();
$assignments = $analytics_helper->get_user_assignments_count();

$result_learning = @( new StudentDataSerializer() )->toArray(
	array_merge(
		$revenue_repository->get_student_revenue( $user_id ),
		$student_enrollment_repository->get_enrollments_data()
	)
);

$see_reports_url = '/wp-admin/admin.php?page=revenue';
if ( 'stm_lms_instructor' === $role ) {
	$see_reports_url = '/user-account/analytics/';
}

?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta property="og:title" content="<?php esc_html__( 'Masterstudy LMS Email Template', 'masterstudy-lms-learning-management-system-pro' ); ?>">
<title><?php esc_html__( 'Masterstudy LMS Email Template', 'masterstudy-lms-learning-management-system-pro' ); ?></title>
<center
	style="margin: 0;padding: 0;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; font-style: normal;font-weight: 500;font-size: 15px;line-height: 26px;color: #808C98;background-color: <?php echo ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ? esc_html( $outside_bg ) : 'white'; ?>;">
	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable"
		style="margin: 0;padding: 0;height: 100% !important;width: 100% !important;">
		<tbody>
		<tr>
			<td align="center" valign="top">
				<table border="0" cellpadding="0" cellspacing="0" id="templateContainer"
					style="border: 1px solid <?php echo ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ? '#DDDDDD' : 'white'; ?>; margin-top: 40px;background-color: #FFFFFF;">
					<tbody>
					<?php
					STM_LMS_Templates::show_lms_template(
						'emails/report-partials/header',
						array(
							'email_manager'        => $email_manager,
							'status_header_footer' => $status_header_footer,
							'header_bg'            => $header_bg,
							'logo'                 => $logo,
						)
					);
					?>
					<tr class="columnOneContent courseBody">
						<td>
							<div class="courseContentBody"
								style="max-width: 620px;margin: 0 auto;text-align: center;margin-bottom: 30px !important;">
								<div class="email-analytics-reports">
									<!-- Photo Block -->
									<div class="email-analytics-reports-photo-block" style="text-align: center; padding: 20px 20px 0 20px; background-color: #ffffff;">
										<?php
										$avatar = get_user_meta( $user_id, 'stm_lms_user_avatar', true );
										if ( empty( $avatar ) ) {
											echo str_replace(
												'<img',
												'<img style="border-radius: 50%; width: 80px; height: 80px; object-fit: cover;"',
												get_avatar( $user_id, 80 )
											);
										} else {
											?>
											<img src="<?php echo $avatar; ?>"
												style="border-radius: 50%; width: 80px; height: 80px; object-fit: cover;"
												alt="">
											<?php
										}
										?>
									</div>
									<!-- Main Content -->
									<div class="email-analytics-reports-main-content" style="padding: 20px;">
										<p class="email-analytics-reports-section-beforetitle" style=" color: #4D5E6F; text-align: center; font-family: 'system-ui'; font-size: 12px; font-style: normal; font-weight: 700; line-height: normal; text-transform: uppercase; margin-bottom: 0;"><?php echo esc_html( $date_from ) . ' - ' . esc_html( $date_to ); ?></p>
										<h2 class="email-analytics-reports-section-title" style=" color: #001931; text-align: center; font-family: 'system-ui';font-size: 20px;font-style: normal;font-weight: 700;line-height: normal;margin-bottom: 30px;margin-top: 5px;">
										<?php
											printf(
												esc_html__( 'Your %s Report', 'masterstudy-lms-learning-management-system-pro' ),
												esc_html( ucfirst( $date_range['frequency'] ) )
											);
											?>
										</h2>

										<p class="email-analytics-reports-section-subtitle" style=" color: #808C98;margin-bottom: 30px; text-align: center; font-family: 'system-ui'; font-size: 15px; font-style: normal; padding: 0 80px; font-weight: 500; line-height: 26px; /* 173.333% */">
											<?php echo $email_message; // phpcs:ignore ?>
										</p>

										<!-- Stats Section -->
										<?php
										if ( 'administrator' === $role || 'stm_lms_instructor' === $role ) {
											?>
											<table border="0" cellpadding="10" cellspacing="0" width="100%" style="margin-bottom: 20px;">
												<tr>
													<!-- Column 1: Revenue -->
													<td style="text-align: center; width: 50%; padding: 10px;">
														<?php STM_LMS_Templates::show_lms_template(
															'emails/report-partials/common-items',
															array(
																'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/revenue.png',
																'value'      => $revenue['total_revenue'] . '$',
																'text'       => esc_html__( 'Earned', 'masterstudy-lms-learning-management-system-pro' ),
																'style_type' => 'common',
															)
														); ?>
													</td>
													<!-- Column 2: Orders -->
													<td style="text-align: center; width: 50%; padding: 10px;">
														<?php STM_LMS_Templates::show_lms_template(
															'emails/report-partials/common-items',
															array(
																'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/cart.png',
																'value'      => $revenue['orders_count'],
																'text'       => esc_html__( 'Orders', 'masterstudy-lms-learning-management-system-pro' ),
																'style_type' => 'common',
															)
														); ?>
													</td>
												</tr>
												<tr>
													<!-- Column 3: Students Joined -->
													<td style="text-align: center; width: 50%; padding: 10px;">
														<?php STM_LMS_Templates::show_lms_template(
															'emails/report-partials/common-items',
															array(
																'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/users.png',
																'value'      => $students_joined,
																'text'       => $students_joined_text,
																'style_type' => 'common',
															)
														); ?>
													</td>

													<!-- Column 4: Instructors Joined (for administrators only) -->
													<?php if ( 'administrator' === $role ) : ?>
														<td style="text-align: center; width: 50%; padding: 10px;">
															<?php STM_LMS_Templates::show_lms_template(
																'emails/report-partials/common-items',
																array(
																	'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/instructor.png',
																	'value'      => $instructor_registered,
																	'text'       => esc_html__( 'Instructors joined', 'masterstudy-lms-learning-management-system-pro' ),
																	'style_type' => 'common',
																)
															); ?>
														</td>
													<?php endif; ?>
													<?php if ( 'stm_lms_instructor' === $role ) : ?>
														<td style="text-align: center; width: 50%; padding: 10px;">
															<?php STM_LMS_Templates::show_lms_template(
																'emails/report-partials/common-items',
																array(
																	'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/star.png',
																	'value'      => $reviews_count,
																	'text'       => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
																	'style_type' => 'common',
																)
															); ?>
														</td>
													<?php endif; ?>
												</tr>
												<tr>
												<?php if ( 'administrator' === $role ) : ?>
														<!-- Column 5: Courses Added (for administrators only) -->
														<td style="text-align: center; width: 50%; padding: 10px;">
															<?php STM_LMS_Templates::show_lms_template(
																'emails/report-partials/common-items',
																array(
																	'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/book.png',
																	'value'      => $course_added,
																	'text'       => esc_html__( 'Courses added', 'masterstudy-lms-learning-management-system-pro' ),
																	'style_type' => 'common',
																)
															); ?>
														</td>
												<?php endif; ?>
													<?php if ( 'administrator' === $role ) : ?>
														<!-- Column 6: Reviews (common) -->
														<td style="text-align: center; width: 50%; padding: 10px;">
															<?php STM_LMS_Templates::show_lms_template(
																'emails/report-partials/common-items',
																array(
																	'icon'       => STM_LMS_PRO_URL . 'assets/icons/email-icons/star.png',
																	'value'      => $reviews_count,
																	'text'       => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
																	'style_type' => 'common',
																)
															); ?>
														</td>
													<?php endif; ?>
												</tr>
											</table>

											<!-- CTA Button -->
											<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 auto 30px auto; text-align: center;">
												<tr>
													<td align="center">
														<a href="<?php echo esc_url( site_url() . $see_reports_url ); ?>" target="_blank"
															style="background-color: #227AFF; color: #ffffff; text-decoration: none; padding: 16px 24px; display: inline-block; border-radius: 5px; font-family: 'Arial', sans-serif; font-size: 16px; line-height: 1.5; text-align: center;">
															<?php echo esc_html__( 'See reports', 'masterstudy-lms-learning-management-system-pro' ); ?>
														</a>
													</td>
												</tr>
											</table>

											<?php
										}
										?>
										<!-- Learning Progress Section -->
										<?php
										STM_LMS_Templates::show_lms_template(
											'emails/report-partials/learning-progress',
											array(
												'result_learning' => $result_learning,
												'assignments'     => $assignments,
												'points'          => $points,
											)
										);
										?>
									</div>
								</div>
							</div>
						</td>
					</tr>
					<?php
					STM_LMS_Templates::show_lms_template(
						'emails/report-partials/footer',
						array(
							'email_manager'        => $email_manager,
							'status_header_footer' => $status_header_footer,
							'footer_bg'            => $footer_bg,
							'status_reply'         => $status_reply,
							'reply_icon'           => $reply_icon,
							'footer_reply'         => $footer_reply,
							'footer_copyrights'    => $footer_copyrights,
						)
					);
					?>
					</tbody>
				</table>
				<!--End Template Body-->
			</td>
		</tr>
		</tbody>
	</table>
	<br>
</center>
