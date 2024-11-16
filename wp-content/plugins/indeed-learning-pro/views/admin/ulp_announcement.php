
	<div class="inside">
			<div class="wrap">
					<a href="<?php echo admin_url('post-new.php?post_type=ulp_announcement&course_id=' . sanitize_text_field($_GET['course_id']));?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e("Add new Announcement", 'ulp');?></a>
			</div>
			<form method="get" >
				<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
				<input type="hidden" name="page" value="ultimate_learning_pro" />
				<input type="hidden" name="tab" value="ulp_announcement" />
			<?php
			$object = new \Indeed\Ulp\Admin\Listing\Announcements();
			$object->finalOutput();
			?>
			</form>
	</div>
