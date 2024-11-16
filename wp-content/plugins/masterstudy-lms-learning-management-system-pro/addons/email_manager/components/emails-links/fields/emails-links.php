<?php
/**
 * @var $field
 * @var $field_name
 * @var $section_name
 *
 */

$field_key = "data['{$section_name}']['fields']['{$field_name}']";

wp_enqueue_style( 'emails-links-css', STM_LMS_PRO_URL . '/addons/email_manager/components/emails-links/css/emails-links.css', null, get_bloginfo( 'version' ), 'all' );
?>

<div class="wpcfto_generic_field wpcfto_generic_field__select" field_data="[object Object]">
	<div class="meet-link-buttons">
		<div class="wpcfto-field-aside">
			<label class="wpcfto-field-aside__label" for="">
				<?php echo esc_html__( 'Email Reports', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</label>
		</div>
		<a href="<?php echo esc_url( admin_url() . 'admin.php?page=email_manager_settings#reports' ); ?>" class="button">
			<?php echo esc_html__( 'Manage Emails', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</a>
	</div>
</div>
