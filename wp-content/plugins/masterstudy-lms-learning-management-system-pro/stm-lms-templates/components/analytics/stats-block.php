<?php
/**
 * @var string $type
 * @var string $value
 * @var string $color
 *
 * available colors: success, warning, danger
 */

$titles = array(
	'revenue'              => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
	'courses'              => esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
	'bundles'              => esc_html__( 'Bundles', 'masterstudy-lms-learning-management-system-pro' ),
	'orders'               => esc_html__( 'Orders', 'masterstudy-lms-learning-management-system-pro' ),
	'groups'               => esc_html__( 'Groups', 'masterstudy-lms-learning-management-system-pro' ),
	'points'               => esc_html__( 'Points', 'masterstudy-lms-learning-management-system-pro' ),
	'all_lessons'          => esc_html__( 'Lessons', 'masterstudy-lms-learning-management-system-pro' ),
	'enrolled'             => esc_html__( 'Enrolled', 'masterstudy-lms-learning-management-system-pro' ),
	'completed'            => esc_html__( 'Completed', 'masterstudy-lms-learning-management-system-pro' ),
	'not_started'          => esc_html__( 'Not started', 'masterstudy-lms-learning-management-system-pro' ),
	'failed'               => esc_html__( 'Failed', 'masterstudy-lms-learning-management-system-pro' ),
	'passed'               => esc_html__( 'Passed', 'masterstudy-lms-learning-management-system-pro' ),
	'in_progress'          => esc_html__( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
	'memberships'          => esc_html__( 'Memberships', 'masterstudy-lms-learning-management-system-pro' ),
	'membership_plan'      => esc_html__( 'Membership plan', 'masterstudy-lms-learning-management-system-pro' ),
	'new_courses'          => esc_html__( 'New courses', 'masterstudy-lms-learning-management-system-pro' ),
	'enrollments'          => esc_html__( 'Enrollments', 'masterstudy-lms-learning-management-system-pro' ),
	'certificates'         => esc_html__( 'Certificates given', 'masterstudy-lms-learning-management-system-pro' ),
	'certificates_created' => esc_html__( 'Certificates created', 'masterstudy-lms-learning-management-system-pro' ),
	'new_assignments'      => esc_html__( 'New assignments', 'masterstudy-lms-learning-management-system-pro' ),
	'new_students'         => esc_html__( 'New students', 'masterstudy-lms-learning-management-system-pro' ),
	'new_lessons'          => esc_html__( 'New lessons', 'masterstudy-lms-learning-management-system-pro' ),
	'new_quizzes'          => esc_html__( 'New quizzes', 'masterstudy-lms-learning-management-system-pro' ),
	'new_groups_courses'   => esc_html__( 'New group courses', 'masterstudy-lms-learning-management-system-pro' ),
	'new_trial_courses'    => esc_html__( 'New trial courses', 'masterstudy-lms-learning-management-system-pro' ),
	'total'                => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
	'unregistred'          => esc_html__( 'Unregistred', 'masterstudy-lms-learning-management-system-pro' ),
	'registered_students'  => esc_html__( 'Registered students', 'masterstudy-lms-learning-management-system-pro' ),
	'students'             => esc_html__( 'Students', 'masterstudy-lms-learning-management-system-pro' ),
	'reviews'              => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
	'instructors'          => esc_html__( 'Registered instructors', 'masterstudy-lms-learning-management-system-pro' ),
);


$value = $value ?? '';
?>

<div class="masterstudy-stats-block masterstudy-stats-block_<?php echo esc_attr( $type ); ?> <?php echo ! empty( $color ) ? esc_attr( $color ) : ''; ?>">
	<span class="masterstudy-stats-block__icon"></span>
	<div class="masterstudy-stats-block__content">
		<div class="masterstudy-stats-block__title">
			<?php echo esc_html( $titles[ $type ] ); ?>
		</div>
		<div class="masterstudy-stats-block__value">
			<?php echo esc_html( $value ); ?>
		</div>
	</div>
</div>
