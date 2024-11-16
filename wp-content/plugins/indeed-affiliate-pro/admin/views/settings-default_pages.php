<?php
$pages = uap_get_all_pages();
//getting pages
?>
<div class="uap-wrapper">
			<form  method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Pages Setup', 'uap');?></h3>
					<div class="inside">
						<div class="uap-form-line">
							<h2><?php esc_html_e('Default Ultimate Affiliate Pro Pages', 'uap');?></h2>
							<p><?php esc_html_e('Front-end Ultimate Affiliate Pro Pages are required to be properly setup with the right shortcode and content inside in order to complete the entire Affiliation process', 'uap');?></p>
						</div>
						<div class="uap-form-line">
							<h4><?php esc_html_e('Registration Page', 'uap');?></h4>
							<p><?php esc_html_e('Visitors are able to register into your website and to become Affiliates', 'uap');?></p>
							<select name="uap_general_register_default_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_register_default_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_register_default_page']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_register_default_page']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('Login Page', 'uap');?></h4>
							<p><?php esc_html_e('Affiliates may be able to login into their Account', 'uap');?></p>
							<select name="uap_general_login_default_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_login_default_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_login_default_page']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_login_default_page']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('Lost Password Page', 'uap');?></h4>
							<p><?php esc_html_e('Available for non-logged users in order to reset their Lost Password', 'uap');?></p>
							<select name="uap_general_lost_pass_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_lost_pass_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_lost_pass_page']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_lost_pass_page']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('Logout Page', 'uap');?></h4>
							<p><?php esc_html_e('Affiliates may be able to logout from their Account', 'uap');?></p>
							<select name="uap_general_logout_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_logout_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_logout_page']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_logout_page']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('Affiliate Portal', 'uap');?></h4>
							<p><?php esc_html_e('Affiliates can change their Profile details and manage their work or check their Rewards', 'uap');?></p>
							<select name="uap_general_user_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_user_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_user_page']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_user_page']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('TOS Page', 'uap');?></h4>
							<p><?php esc_html_e('Terms of Service Page accessible from Register form when user must accept website terms', 'uap');?></p>
							<select name="uap_general_tos_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_tos_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_tos_page']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_tos_page']);?></div>
						</div>

						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>
</div>
