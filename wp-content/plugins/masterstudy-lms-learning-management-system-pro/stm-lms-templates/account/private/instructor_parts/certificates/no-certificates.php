<div class="masterstudy-instructor-certificates__empty">
	<div class="masterstudy-instructor-certificates__empty-wrapper">
		<div class="masterstudy-instructor-certificates__empty-icon">
			<img
				src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/certificate.png' ); ?>"
				class="masterstudy-instructor-certificates__empty-image"
			>
		</div>
		<span class="masterstudy-instructor-certificates__empty-title">
			<?php echo esc_html__( 'You have no certificates yet', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
		<?php
		STM_LMS_Templates::show_lms_template(
			'components/button',
			array(
				'id'    => 'masterstudy-instructor-certificates-create',
				'title' => esc_html__( 'Create certificate', 'masterstudy-lms-learning-management-system-pro' ),
				'link'  => admin_url( 'admin.php?page=certificate_builder' ),
				'style' => 'primary',
				'size'  => 'sm',
			)
		);
		?>
	</div>
</div>
