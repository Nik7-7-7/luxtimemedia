<?php
$pages = uap_get_all_pages();
//getting pages
?>
<div class="uap-wrapper">
			<form  method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Redirects Setup', 'uap');?></h3>
					<div class="inside">
						<div class="uap-form-line">
							<h2><?php esc_html_e('Default Redirects', 'uap');?></h2>
							<p><?php esc_html_e('Customize where affiliates are redirected in different scenarios by selecting specific pages for various actions such as login, registration, or password reset', 'uap');?></p>
						</div>
						<div class="uap-form-line">
							<h4><?php esc_html_e('After LogOut', 'uap');?></h4>
							<p><?php esc_html_e('When Affiliates logs out from their Account may be redirected to a specific page, such Login Page', 'uap');?></p>
							<select name="uap_general_logout_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_logout_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_logout_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_logout_redirect']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('After Registration', 'uap');?></h4>
							<p><?php esc_html_e('If no other redirect is triggered after registration, new affiliates may be redirected to a specific page', 'uap');?></p>
							<select name="uap_general_register_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_register_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_register_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_register_redirect']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('After Login', 'uap');?></h4>
							<p><?php esc_html_e('It may redirect logged affiliates to Affiliate Portal', 'uap');?></p>
							<select name="uap_general_login_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_login_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_login_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_login_redirect']);?></div>
						</div>

            <div class="uap-form-line">
							<h4><?php esc_html_e('After Reset Password', 'uap');?></h4>
							<p><?php esc_html_e('A custom page where unlogged affiliates may be redirected after the Reset Password submission', 'uap');?></p>
							<select name="uap_general_after_reset_password_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_lost_pass_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_after_reset_password_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_after_reset_password_redirect']);?></div>
						</div>

					<div class="uap-form-line">
					<h2><?php esc_html_e('Extra Redirects', 'uap');?></h2>
					</div>
						<div class="uap-form-line">
							<h4><?php esc_html_e('Affiliate Portal - Restricted', 'uap');?></h4>
							<p><?php esc_html_e('If the affiliate is not logged in and tries to access the Affiliate Portal, the redirect should apply', 'uap');?></p>
							<select name="uap_general_account_page_no_logged_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_account_page_no_logged_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_account_page_no_logged_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_account_page_no_logged_redirect']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('Login Page - Restricted', 'uap');?></h4>
							<p><?php esc_html_e('If the affiliate is logged in and tries to access the Login Page, the redirect should apply', 'uap');?></p>
							<select name="uap_general_login_page_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_login_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_login_page_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_login_page_logged_users_redirect']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('Registration Page - Restricted', 'uap');?></h4>
							<p><?php esc_html_e('If the affiliate is logged in and tries to access the Registration Page, the redirect should apply', 'uap');?></p>
							<select name="uap_general_register_page_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_register_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_register_page_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_register_page_logged_users_redirect']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('LogOut Page - Restricted', 'uap');?></h4>
							<p><?php esc_html_e('If the affiliate is not logged in and tries to access the LogOut Page, the redirect should apply', 'uap');?></p>
							<select name="uap_general_logout_page_non_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_logout_page_non_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_logout_page_non_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_logout_page_non_logged_users_redirect']);?></div>
						</div>

						<div class="uap-form-line">
							<h4><?php esc_html_e('Lost Password Page - Restricted', 'uap');?></h4>
							<p><?php esc_html_e('If the affiliate is logged in and tries to access the Lost Password Page, the redirect should apply', 'uap');?></p>
							<select name="uap_general_lost_pass_page_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_lost_pass_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_general_lost_pass_page_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<div class="uap-general-options-link-pages"><?php echo uap_general_options_print_page_links($data['metas']['uap_general_lost_pass_page_logged_users_redirect']);?></div>
						</div>



						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>

			</form>
</div>
