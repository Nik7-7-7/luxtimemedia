<?php
wp_enqueue_style( 'masterstudy-button' );
wp_enqueue_style( 'masterstudy-tabs' );
wp_enqueue_style( 'masterstudy-pagination' );
?>

<span class="masterstudy-loader masterstudy-loader_global">
	<div class="masterstudy-loader__body"></div>
</span>
<div id="masterstudy-certificate-builder" class="masterstudy-certificate-wrapper">
	<?php STM_LMS_Templates::show_lms_template( 'certificate-builder/header' ); ?>
	<div class="masterstudy-certificate-content">
		<div v-show="currentTab === 'builder'" class="masterstudy-certificate-builder">
			<?php
			STM_LMS_Templates::show_lms_template( 'certificate-builder/certificates' );
			STM_LMS_Templates::show_lms_template( 'certificate-builder/canvas' );
			STM_LMS_Templates::show_lms_template( 'certificate-builder/controls' );
			STM_LMS_Templates::show_lms_template( 'certificate-builder/create-popup' );
			?>
		</div>
		<div v-show="currentTab === 'destination' && isAdmin" class="masterstudy-certificate-destination">
			<?php
			STM_LMS_Templates::show_lms_template( 'certificate-builder/destination-header' );
			STM_LMS_Templates::show_lms_template( 'certificate-builder/destination-content' );
			STM_LMS_Templates::show_lms_template( 'certificate-builder/select-popup' );
			?>
		</div>
		<?php STM_LMS_Templates::show_lms_template( 'certificate-builder/delete-popup' ); ?>
		<?php STM_LMS_Templates::show_lms_template( 'certificate-builder/image-popup' ); ?>
	</div>
</div>
