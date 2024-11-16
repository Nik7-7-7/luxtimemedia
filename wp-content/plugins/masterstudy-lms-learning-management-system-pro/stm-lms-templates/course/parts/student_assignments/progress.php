<?php
/**
 * @var $post_id
 * @var $item_id
 * @var $draft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$editor_id = "stm_lms_assignment__{$draft['id']}";

$q = new WP_Query(
	array(
		'posts_per_page' => 1,
		'post_type'      => 'stm-assignments',
		'post__in'       => array( $item_id ),
	)
);

$actual_link = STM_LMS_Assignments::get_current_url();

$attachments = STM_LMS_Assignments::uploaded_attachments( $draft['id'] );

stm_lms_register_script(
	'assignment_edit',
	array(),
	false,
	"stm_lms_editor_id = '{$editor_id}'; 
    stm_lms_draft_id = {$draft['id']}; 
    stm_lms_course_id = {$post_id}; 
    stm_lms_assignment_translations = {'delete' : '" . esc_html__( 'Delete File?', 'masterstudy-lms-learning-management-system-pro' ) . "'}
    stm_lms_assignment_files = " . wp_json_encode( $attachments ) . ''
);

if ( $q->have_posts() ) :
	?>
	<div class="stm-lms-course__assignment stm-lms-course__assignment-draft">

		<?php
		$unpassed_assignment = STM_LMS_Assignments::get_student_assignment_by_status( $item_id, 'unpassed' );

		if ( ! empty( $unpassed_assignment ) ) {
			$editor_comment = get_post_meta( $unpassed_assignment->ID, 'editor_comment', true );
			$assignment_id  = get_post_meta( $unpassed_assignment->ID, 'assignment_id', true );

			if ( ! empty( $editor_comment ) ) {
				STM_LMS_Templates::show_lms_template(
					'course/parts/assignment_parts/comment',
					array(
						'comment'   => $editor_comment,
						'editor_id' => get_post_field( 'post_author', $assignment_id ),
					)
				);
			}
		}

		while ( $q->have_posts() ) :
			$q->the_post();

			STM_LMS_Templates::show_lms_template(
				'course/parts/assignment_parts/task',
				array(
					'item_id' => $item_id,
					'content' => get_the_content(),
				)
			);
			?>

			<div class="stm_lms_assignment__edit">

				<?php wp_editor( $draft['content'], $editor_id, array( 'quicktags' => false ) ); ?>

				<div class="autosaving">
					<span><?php esc_html_e( 'Autosaving', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
				</div>

				<?php STM_LMS_Templates::show_lms_template( 'course/parts/assignment_parts/file_loader' ); ?>

			</div>

			<div class="text-center">
				<a href="#" type="submit" class="btn btn-default btn-accept-assignment">
					<?php esc_attr_e( 'Send Assignment', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</a>
			</div>

			<?php do_action( 'stm_lms_after_assignment' ); ?>

		<?php endwhile; ?>
	</div>

	<?php
endif;
