<?php
/**
 * @var $bundle_id
 */

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;
use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleSettings;

if ( ! is_user_logged_in() ) {
	STM_LMS_User::js_redirect( STM_LMS_User::login_page_url() );
	die;
}

$args                 = array(
	'posts_per_page' => - 1,
	'post_status'    => array( 'publish' ),
);
$coming_soon_settings = get_option( 'masterstudy_lms_coming_soon_settings' );

if ( ! ( $coming_soon_settings['lms_coming_soon_course_bundle_status'] ?? true ) ) {
	$args['meta_query'][] = array(
		'relation' => 'OR',
		array(
			'key'     => 'coming_soon_status',
			'value'   => '', // Check for an empty value
			'compare' => '=',
		),
		array(
			'key'     => 'coming_soon_status',
			'compare' => 'NOT EXISTS',
		),
	);
}

stm_lms_register_style( 'user_info_top' );
stm_lms_register_style( 'bundles/my-bundle' );
stm_lms_register_script( 'bundles/my-bundle', array( 'vue.js', 'vue-resource.js' ) );
wp_localize_script(
	'stm-lms-bundles/my-bundle',
	'stm_lms_my_bundle_courses',
	array(
		'list'         => STM_LMS_Instructor::get_courses(
			$args,
			true
		),
		'bundle_id'    => $bundle_id,
		'bundle_limit' => ( new CourseBundleSettings() )->get_bundle_courses_limit(),
		'editor_id'    => "stm_lms_bundle_name_{$bundle_id}",
	)
);

if ( ! empty( $bundle_id ) ) {
	$bundle_data = ( new CourseBundleRepository() )->get_bundle_data( $bundle_id );

	if ( $bundle_data ) {
		wp_localize_script(
			'stm-lms-bundles/my-bundle',
			'stm_lms_my_bundle',
			array(
				'data' => $bundle_data,
			)
		);
	}
}

$bundle_content = $bundle_data->post_content ?? '';
?>

<div class="stm_lms_my_bundles">
	<h2><?php esc_html_e( 'Add new bundle', 'masterstudy-lms-learning-management-system-pro' ); ?></h2>
</div>

<div class="multiseparator"></div>

<div id="stm_lms_my_bundle">
	<?php STM_LMS_Templates::show_lms_template( 'bundles/my-bundle/select-course' ); ?>
	<?php STM_LMS_Templates::show_lms_template( 'bundles/my-bundle/title' ); ?>
	<?php STM_LMS_Templates::show_lms_template( 'bundles/my-bundle/image' ); ?>
	<?php STM_LMS_Templates::show_lms_template( 'bundles/my-bundle/description', compact( 'bundle_id', 'bundle_content' ) ); ?>
	<?php STM_LMS_Templates::show_lms_template( 'bundles/my-bundle/price' ); ?>

	<a href="#" @click.prevent="saveBundle()" class="btn btn-default" v-bind:class="{'loading' : loading}">
		<span><?php esc_html_e( 'Save Bundle', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
	</a>

	<transition name="slide-fade">
		<div class="stm-lms-message" v-bind:class="status" v-if="message" v-html="message"></div>
	</transition>
</div>
