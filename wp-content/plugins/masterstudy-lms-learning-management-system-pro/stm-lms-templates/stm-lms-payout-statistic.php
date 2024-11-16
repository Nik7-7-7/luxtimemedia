<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();

wp_enqueue_script( 'vue-resource.js' );
wp_enqueue_script( 'vue2-datepicker' );

do_action( 'stm_lms_template_main' );
?>

<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>

	<div class="stm-lms-wrapper stm-lms-wrapper--assignments user-account-page">

		<div class="container">

			<div id="stm_lms_instructor_assignments">
				<?php STM_LMS_Templates::show_lms_template( 'account/private/instructor_parts/statistic/main' ); ?>
			</div>


		</div>

	</div>

<?php get_footer(); ?>
