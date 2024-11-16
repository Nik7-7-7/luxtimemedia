<form action="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=lifetime_commissions' );?>" method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('LifeTime Commissions - Add New Relation', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<label class="uap-label"><?php esc_html_e('Referral Username:', 'uap');?></label>
        <select name="referral_uid"><?php
					foreach ($data['users'] as $userObject ){
						?>
						<option value="<?php echo esc_attr($userObject->ID);?>"><?php echo esc_html($userObject->user_login);?></option>
						<?php
					}
				?></select>
			</div>
			<div class="uap-form-line">
				<label class="uap-label"><?php esc_html_e('Affiliate Username:', 'uap');?></label>
				<select name="affiliate"><?php
					foreach ($data['affiliates'] as $id=>$username){
						$selected = ( isset( $data['edit_data']['affiliate_id']) ) && $id==$data['edit_data']['affiliate_id'] ? 'selected' : '';
						?>
						<option <?php echo esc_attr($selected);?> value="<?php echo esc_attr($id);?>"><?php echo esc_html($username);?></option>
						<?php
					}
				?></select>
			</div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save-new-relation" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>
