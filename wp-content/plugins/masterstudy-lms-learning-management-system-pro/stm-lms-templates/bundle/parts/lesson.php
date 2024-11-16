<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} //Exit if accessed directly ?>

<?php
/**
 * @var $post_id
 * @var $item_id
 * @var $is_previewed
 */

if ( ! empty( $item_id ) ) :
	stm_lms_register_script( 'lessons' );

	if ( function_exists( 'vc_asset_url' ) ) {
		wp_enqueue_style( 'stm_lms_wpb_front_css', vc_asset_url( 'css/js_composer.min.css' ), array(), STM_LMS_PRO_VERSION );
	}

	if ( class_exists( 'Ultimate_VC_Addons' ) ) {
		STM_LMS_Lesson::aio_front_scripts();
	}

	$lesson = get_post( $item_id );

	if ( ! empty( $lesson ) ) :
		?>
		<div class="stm-lms-course__lesson-content">
			<?php
			STM_LMS_Templates::show_lms_template( 'lesson/video', array( 'id' => $item_id ) );

			echo wp_kses_post( $lesson->post_content );
			?>
		</div>

		<?php
		wp_reset_postdata();
	endif;
endif;
