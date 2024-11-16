<div class="uap-wrapper">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Add/Update Affiliate Member', 'uap');?></h3>
		<div class="inside uap-admin-edit">
        	<div class="uap-edit-profile-section-title">

        		<h2><?php echo esc_html__('Affiliate Profile details', 'uap'); ?></h2>
            	<p><?php echo esc_html__('Control which fields are available in the "Showcases->Register Form->Custom Fields" section', 'uap'); ?></p>

					</div>
			<?php echo esc_uap_content($data['output']);?>
		</div>
	</div>
</div>
