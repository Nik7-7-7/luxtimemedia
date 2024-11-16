<?php
$subtab = 'defaults';
if (isset($_REQUEST['subtab'])){
    $subtab = sanitize_text_field($_REQUEST['subtab']);
}
?>
<?php
echo ihc_inside_dashboard_error_license();
$pages = ihc_get_all_pages();//getting pages
?>
<?php
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-wrapper">
<div class="iump-page-headline"><?php esc_html_e('General Settings', 'ihc');?></div>

<div class="iump-settings-box-wrapper">
<!--div class="ihc-subtab-menu"-->
<div class="iump-settings-tab-wrapper">
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='defaults') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=defaults');?>"><?php esc_html_e('Pages Setup', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='menus') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=menus');?>"><?php esc_html_e('Restrict WordPress Menu', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='pay_settings') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=pay_settings');?>"><?php esc_html_e('Payment Settings', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='notifications') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=notifications');?>"><?php esc_html_e('Notifications Settings', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='double_email_verification') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=double_email_verification');?>"><?php esc_html_e('Email Verification', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='admin_workflow') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=admin_workflow');?>"><?php esc_html_e('Admin Workflow', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='public_workflow') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=public_workflow');?>"><?php esc_html_e('Public Workflow', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='access') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=access');?>"><?php esc_html_e('WP Dashboard Access', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='msg') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=msg');?>"><?php esc_html_e('Custom Messages', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='extra_settings') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=extra_settings');?>"><?php esc_html_e('Uploads Settings', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='captcha') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=captcha');?>"><?php esc_html_e('reCaptcha Setup', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='security') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=security');?>"><?php esc_html_e('Security', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='debug') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=debug');?>"><?php esc_html_e('Debug', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>

<div class="iump-settings-container">
<?php
switch ($subtab){
	case 'defaults':
		//ihc_save_update_metas('general-defaults');//save update metas
		if ( isset( $_POST['ihc_general_register_view_user'] ) && $_POST['ihc_general_register_view_user'] != get_option('ihc_general_register_view_user') ){
			$page_slug = Ihc_Db::get_page_slug(sanitize_text_field($_POST['ihc_general_register_view_user']));
			if ( $page_slug !== '' ){
					add_rewrite_rule($page_slug . "/([^/]+)/?",'index.php?pagename=' . $page_slug . '&ihc_name=$matches[1]', 'top');
					flush_rewrite_rules();
			}
		}
		if (!empty($_POST['ihc_save']) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				//save
				ihc_save_update_metas_general_defaults(indeed_sanitize_array($_POST));
				\Ihc_Db::create_extra_redirects();
		}
		$meta_arr = ihc_return_meta_arr('general-defaults');//getting metas





		?>
			<form  method="post">

				<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Pages Setup', 'ihc');?></h3>
					<div class="inside">
          <div class="row">
  						<div class="col-xs-12">
						<div class="iump-form-line">
							<h2><?php esc_html_e('Default Ultimate Membership Pro Pages', 'ihc');?></h2>
							<p><?php esc_html_e('Front-end Ultimate Membership Pro Pages are required to be properly setup with the right shortcode and content inside in order to complete the entire Membership process', 'ihc');?></p>
						</div>
          </div>
        </div>
        <div class="row">
            <div class="col-xs-8">
						<div class="iump-form-line">
							<h4><?php esc_html_e('Register Page', 'ihc');?></h4>
							<p><?php esc_html_e('Visitors are able to register into your website and manage their Memberships', 'ihc');?></p>
							<select name="ihc_general_register_default_page" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_register_default_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_register_default_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_register_default_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Subscription Plan Page', 'ihc');?></h4>
							<p><?php esc_html_e('Members may pick one of available Memberships and sign up on it', 'ihc');?></p>
							<select name="ihc_subscription_plan_page"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_subscription_plan_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_subscription_plan_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_subscription_plan_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Login Page', 'ihc');?></h4>
							<p><?php esc_html_e('Members may be able to login into their Account', 'ihc');?></p>
							<select name="ihc_general_login_default_page" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_login_default_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_login_default_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_login_default_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Logout Page', 'ihc');?></h4>
              <p><?php esc_html_e('Members may be able to logout from their Account', 'ihc');?></p>
							<select name="ihc_general_logout_page" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_logout_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_logout_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_logout_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Member Portal', 'ihc');?></h4>
							<p><?php esc_html_e('Members can change their Profile details and manage their Memberships or check their Payments', 'ihc');?></p>
							<select name="ihc_general_user_page" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_user_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_user_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_user_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('TOS Page', 'ihc');?></h4>
							<p><?php esc_html_e('Terms of Service Page accessible from Register form when user must accept website terms', 'ihc');?></p>
							<select name="ihc_general_tos_page" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_tos_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_tos_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_tos_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Lost Password Page', 'ihc');?></h4>
							<p><?php esc_html_e('Available for non-logged users in order to reset their Lost Password', 'ihc');?></p>
							<select name="ihc_general_lost_pass_page" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_lost_pass_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_lost_pass_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_lost_pass_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Public Individual user Page', 'ihc');?></h4>
							<p><?php esc_html_e('Dedicated for Members Directory Showcase with accessible public Individual Page', 'ihc');?></p>
							<select name="ihc_general_register_view_user" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_register_view_user']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_register_view_user']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_register_view_user']);?>
						</div>
						<div class="iump-form-line">
							<h4><?php esc_html_e('Checkout Page', 'ihc');?></h4>
							<p><?php esc_html_e('Members may be able to complete the Payment for selected Membership from Member Portal or Register Page', 'ihc');?></p>
							<select name="ihc_checkout_page"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_checkout_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_checkout_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_checkout_page']);?>
						</div>
						<div class="iump-form-line">
							<h4><?php esc_html_e('Thank You Page', 'ihc');?></h4>
							<p><?php esc_html_e('Confirmation step after a Payment have been completed', 'ihc');?></p>
							<select name="ihc_thank_you_page"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_thank_you_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_thank_you_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_thank_you_page']);?>
						</div>
          </div>
        </div>
					</div>
				</div>

<?php $pages_arr = $pages + ihc_get_redirect_links_as_arr_for_select();?>
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Redirects Setup', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
							<h4><?php esc_html_e('Default Redirect Page', 'ihc');?></h4>
							<p><?php esc_html_e('When Access is Restricted and no specific Redirect option have been set the default one will be used', 'ihc');?></p>
							<select name="ihc_general_redirect_default_page" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if ($meta_arr['ihc_general_redirect_default_page']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_redirect_default_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_redirect_default_page']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('After LogOut', 'ihc');?></h4>
							<p><?php esc_html_e('When Members logs out from their Account may be redirected to a specific page, such Login Page', 'ihc');?></p>
							<select name="ihc_general_logout_redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_logout_redirect']==-1){echo esc_attr('selected');}?> ><?php esc_html_e('Do Not Redirect', 'ihc');?></option>
								<?php
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_logout_redirect']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_logout_redirect']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('After Registration', 'ihc');?></h4>
							<p><?php esc_html_e('If no other redirect is triggered after registration, new members may be redirected to a specific page', 'ihc');?></p>
							<select name="ihc_general_register_redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_register_redirect']==-1){echo esc_attr('selected');}?> ><?php esc_html_e('Do Not Redirect', 'ihc');?></option>
								<?php
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_register_redirect']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_register_redirect']);?>
							<div><?php esc_html_e("Except if Bank Transfer Payment it's used", 'ihc');?></div>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('After Login', 'ihc');?></h4>
							<p><?php esc_html_e('It may redirect logged members to they Member Portal', 'ihc');?></p>
							<select name="ihc_general_login_redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_login_redirect']==-1){echo esc_attr('selected');}?> ><?php esc_html_e('Do Not Redirect', 'ihc');?></option>
								<?php
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_login_redirect']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_login_redirect']);?>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('After Password Reset', 'ihc');?></h4>
							<p><?php esc_html_e('A custom page where unlogged members may be redirected after the Reset Password submission', 'ihc');?></p>
							<select name="ihc_general_password_redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" <?php if($meta_arr['ihc_general_password_redirect']==-1){echo esc_attr('selected');}?> ><?php esc_html_e('-', 'ihc');?></option>
								<?php
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_general_password_redirect']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo ihc_general_options_print_page_links($meta_arr['ihc_general_password_redirect']);?>
						</div>

					</div>
				</div>
        <div class="ihc-stuffbox">
          <div class="inside">
						<div class="ihc-wrapp-submit-bttn">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>
		<?php
	break;
	case 'captcha':
		if (!empty($_POST['ihc_save']) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('general-captcha');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('general-captcha');//getting metas




		if ( empty( $meta_arr['ihc_recaptcha_version'] ) ){
				$meta_arr['ihc_recaptcha_version'] = 'v2';
		}
		?>
					<form  method="post">

						<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

						<div class="ihc-stuffbox">
							<h3><?php esc_html_e('ReCaptcha Setup', 'ihc');?></h3>
							<div class="inside">
								<div class="iump-form-line">
									<h4><?php esc_html_e('ReCaptcha Integration', 'ihc');?></h4>
                  <div class="row">
          						<div class="col-xs-8">
                  <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select js-ihc-change-recaptcha-version" name="ihc_recaptcha_version" >
												<option value="v2" <?php if ( $meta_arr['ihc_recaptcha_version'] == 'v2' ){ echo esc_attr('selected');}?> ><?php esc_html_e( 'reCAPTCHA v2', 'ihc');?></option>
												<option value="v3" <?php if ( $meta_arr['ihc_recaptcha_version'] == 'v3' ){ echo esc_attr('selected');}?> ><?php esc_html_e( 'reCAPTCHA v3', 'ihc');?></option>
									</select>
                </div>
              </div>
								</div>

								<div class="js-ihc-recaptcha-v2-wrapp <?php if ( $meta_arr['ihc_recaptcha_version'] == 'v3' ){ echo esc_attr('ihc-display-none');}?>" >
                                	<div class="iump-form-line">
										<h4><?php esc_html_e( 'reCAPTCHA v2', 'ihc');?></h4>
                    <div class="row">
            						<div class="col-xs-8">
										<div class="input-group">
											<span class="input-group-addon"><?php esc_html_e('SITE KEY:', 'ihc');?></span>
                                            <input type="text" name="ihc_recaptcha_public" value="<?php echo esc_attr($meta_arr['ihc_recaptcha_public']);?>" class="form-control ihc-deashboard-middle-text-input" />
										</div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-xs-8">
										<div class="input-group">
											<span class="input-group-addon"><?php esc_html_e('SECRET KEY:', 'ihc');?></span>
                                            <input type="text" name="ihc_recaptcha_private" value="<?php echo esc_attr($meta_arr['ihc_recaptcha_private']);?>" class="form-control ihc-deashboard-middle-text-input" />
										</div>
                  </div>
                </div>
										<div>
											<p><strong><?php esc_html_e('How to setup:', 'ihc');?></strong></p>
                                            <p>	<?php esc_html_e('1. Get Public and Private Keys from', 'ihc');?> <a href="https://www.google.com/recaptcha/admin#list" target="_blank"><?php esc_html_e('here', 'ihc');?></a>.</p>
                                            <p>	<?php esc_html_e('2. Click on "Create" button', 'ihc');?></p>
                                            <p>	<?php esc_html_e('3. Choose "reCAPTCHA v2" with "Im not a robot" Checkbox', 'ihc');?></p>
                                            <p>	<?php esc_html_e('4. Add curent WP website main domain', 'ihc');?></p>
                                            <p> <?php esc_html_e('5. Accept terms and conditions and Submit', 'ihc');?></p>
										</div>
                                     </div>
								</div>

								<div class="js-ihc-recaptcha-v3-wrapp <?php if ( $meta_arr['ihc_recaptcha_version'] == 'v2' ){ echo esc_attr('ihc-display-none');}?> ">
                                <div class="iump-form-line">
										<h4><?php esc_html_e( 'reCAPTCHA v3', 'ihc');?></h4>
                    <div class="row">
            						<div class="col-xs-8">
      										<div class="input-group">
      											<span class="input-group-addon"><?php esc_html_e('SITE KEY:', 'ihc');?></span>
                                                  <input type="text" name="ihc_recaptcha_public_v3" value="<?php echo esc_attr($meta_arr['ihc_recaptcha_public_v3']);?>" class="form-control ihc-deashboard-middle-text-input"/>
      										</div>
                      </div>
                    </div>
                    <div class="row">
            						<div class="col-xs-8">
      										<div class="input-group">
      											<span class="input-group-addon"><?php esc_html_e('SECRET KEY:', 'ihc');?></span>
                                                  <input type="text" name="ihc_recaptcha_private_v3" value="<?php echo esc_attr($meta_arr['ihc_recaptcha_private_v3']);?>" class="form-control ihc-deashboard-middle-text-input" />
      										</div>
                        </div>
                      </div>
										<div>
                                        	<p><strong><?php esc_html_e('How to setup:', 'ihc');?></strong></p>
											<p> <?php esc_html_e('1. Get Public and Private Keys from', 'ihc');?> <a href="https://www.google.com/recaptcha/admin#list" target="_blank"><?php esc_html_e('here', 'ihc');?></a>.</p>
                                            <p>	<?php esc_html_e('2. Click on "Create" button', 'ihc');?></p>
                                            <p>	<?php esc_html_e('3. Choose "reCAPTCHA v3"', 'ihc');?></p>
                                            <p>	<?php esc_html_e('4. Add curent WP website main domain', 'ihc');?></p>
                                            <p> <?php esc_html_e('5. Accept terms and conditions and Submit', 'ihc');?></p>
										</div>
                                  </div>
								</div>

    					</div>
    				</div>
            <div class="ihc-stuffbox">
              <div class="inside">
								<div class="ihc-wrapp-submit-bttn">
									<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" onClick="" class="button button-primary button-large" />
								</div>
							</div>
						</div>
					</form>
				<?php
	break;
	case 'msg':
		if (!empty($_POST['ihc_save']) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('general-msg');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('general-msg');//getting metas




		?>
					<form  method="post">
						<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />
						<div class="ihc-stuffbox">
							<h3><?php esc_html_e('Custom Messages', 'ihc');?></h3>
							<div class="inside">

								<div class="iump-form-line">
									<h4><?php esc_html_e('Update Successfully Message', 'ihc');?></h4>
									<textarea name="ihc_general_update_msg" class="ihc-dashboard-textarea"><?php echo ihc_correct_text($meta_arr['ihc_general_update_msg']);?></textarea>
								</div>
    					</div>
    				</div>
            <div class="ihc-stuffbox">
              <div class="inside">
								<div class="ihc-wrapp-submit-bttn">
									<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
								</div>
							</div>
						</div>
					</form>
				<?php
	break;
	case 'menus':
		$nav_menus = wp_get_nav_menus();
		?>
		<form  method="post">

			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Restrict WordPress Menu links', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line iump-no-border">
						<!--h2><?php esc_html_e('Restrict WordPress Menu', 'ihc');?></h2-->
						<p><?php esc_html_e('You may restrict WordPress Menu Links directly from Appearance->Menus section but if your Menu section have been customized you have this section available as an alternative solution', 'ihc');?></p>
					</div>
					<div class="iump-form-line">
						<h4><?php esc_html_e('Choose Menu to Restrict', 'ihc');?></h4>
            <div class="row">
  							<div class="col-xs-8">
        					<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-gen-menu-select ihc-js-admin-restrict-wp-menu-links" name="menu_id" data-url="<?php echo esc_url($url .'&tab='.$tab.'&subtab=menus&menu_id=' );?>" >
        						<option value="0"><?php esc_html_e('Select a Menu', 'ihc');?></option>
        						<?php foreach ( $nav_menus as $menu ){ ?>
        							<?php $selected = (!empty($_GET['menu_id']) && $_GET['menu_id']==$menu->term_id) ? 'selected' : ''; ?>
        							<option <?php echo esc_attr($selected); ?> value="<?php echo esc_attr($menu->term_id); ?>">
        								<?php echo wp_html_excerpt( $menu->name, 40, '&hellip;' ); ?>
        							</option>
        						<?php } ?>
        					</select>
                </div>
              </div>
					</div>
					<div class="iump-form-line iump-no-border">
          <div class="row">
							<div class="col-xs-6">
						<?php
							if (!empty($_GET['menu_id'])){
								///update
								if (!empty($_POST['ihc_save']) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
									foreach ($_POST['db_menu_id'] as $v){
										if (isset($_POST['ihc_mb_who_menu_type-'.$v]) && isset($_POST['ihc_menu_mb_type-'.$v])){
											update_post_meta( $v, 'ihc_mb_who_menu_type', sanitize_text_field($_POST['ihc_mb_who_menu_type-'.$v]) );
											update_post_meta( $v, 'ihc_menu_mb_type', sanitize_text_field($_POST['ihc_menu_mb_type-'.$v]) );
										}
									}
								}

								///list
								$menu_items = wp_get_nav_menu_items( sanitize_text_field($_GET['menu_id']), array( 'post_status' => 'any' ) );
								foreach ($menu_items as $obj){
									?>
									<div class="ihc-gen-menu-wrap">
										<div class="ihc-menu-page ihc-padding">
											<?php echo esc_html($obj->title);?> <span><?php esc_html_e('Menu Item', 'ihc');?></span>
										</div>
										<input type="hidden" name="db_menu_id[]" value="<?php echo esc_attr($obj->ID);?>" />
										<div class="ihc-class ihc-padding">
											<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" name="ihc_menu_mb_type-<?php echo esc_attr($obj->ID); ?>">
												<option value="show" <?php if ($obj->ihc_menu_mb_type=='show'){echo esc_attr('selected');}?> ><?php esc_html_e('Show Menu Item Only', 'ihc');?></option>
												<option value="block" <?php if ($obj->ihc_menu_mb_type=='block'){echo esc_attr('selected');}?> ><?php esc_html_e('Block Menu Item Only', 'ihc');?></option>
											</select>
										</div>
										<div  class="ihc-padding">
											<label class="ihc-bold ihc-display-block"><?php esc_html_e('for:', 'ihc');?></label>
											<?php
												$posible_values = array('all'=>esc_html__('All', 'ihc'), 'reg'=>esc_html__('Registered Users', 'ihc'), 'unreg'=>esc_html__('Unregistered Users', 'ihc') );

												$levels = \Indeed\Ihc\Db\Memberships::getAll();
												if ($levels){
													foreach ($levels as $id => $level){
														$posible_values[$id] = $level['name'];
													}
												}
												?>
												<select onChange="ihcWriteTagValue(this, '#ihc_mb_who_hidden-<?php echo esc_attr($obj->ID);?>', '#ihc_tags_field-<?php echo esc_attr($obj->ID);?>', '<?php echo esc_attr($obj->ID);?>_ihc_select_tag_' );" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
													<option value="-1" selected>...</option>
													<?php
														foreach($posible_values as $k=>$v){
															?>
															<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
															<?php
														}
													?>
												</select>
										</div>
										<div id="ihc_tags_field-<?php echo esc_attr($obj->ID);?>" class="ihc-padding">
							            	<?php

							                    if ($obj->ihc_mb_who_menu_type){
							                    	if (!empty($values)) unset($values);
							                    	if (strpos($obj->ihc_mb_who_menu_type, ',')!==FALSE){
							                    		$values = explode(',', $obj->ihc_mb_who_menu_type);
							                    	} else {
							                        	$values[] = $obj->ihc_mb_who_menu_type;
							                        }
							                        foreach ($values as $value) { ?>
							                        	<div id="<?php echo esc_attr($obj->ID);?>_ihc_select_tag_<?php echo esc_attr($value);?>" class="ihc-tag-item">
							                        		<?php echo esc_html($posible_values[$value]);?>
							                        		<div class="ihc-remove-tag" onclick="ihcremoveTag('<?php echo esc_attr($value);?>', '#<?php echo esc_attr($obj->ID);?>_ihc_select_tag_', '#ihc_mb_who_hidden-<?php echo esc_attr($obj->ID);?>');" title="<?php esc_html_e('Removing tag', 'ihc');?>">x</div>
							                        	</div>
							                            <?php
							                        }//end of foreach ?>
							                    <div class="ihc-clear"></div>
							                    <?php }//end of if ?>

										</div>
										<div class="ihc-clear"></div>
										<input type="hidden" id="ihc_mb_who_hidden-<?php echo esc_attr($obj->ID);?>" name="ihc_mb_who_menu_type-<?php echo esc_attr($obj->ID); ?>" value="<?php echo esc_attr($obj->ihc_mb_who_menu_type);?>" />
										<div class="clear"></div>
									</div>

									<?php
								}
							}
						?>
          </div>
        </div>
					</div>
        </div>
      </div>
      <div class="ihc-stuffbox">
        <div class="inside">
					<div  class="ihc-wrapp-submit-bttn ihc-submit-button-wrapper">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
				</div>
			</div>
		</form>
		<?php
	break;

	case 'extra_settings':
		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('extra_settings');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('extra_settings');//getting metas




		?>
			<form  method="post">

				<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3> <?php esc_html_e("Uploads Settings", 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
						<h2> <?php esc_html_e("Accepted Files Extensions", 'ihc');?></h2>
						<P> <?php esc_html_e("When Upload File field is required into Register/Profile form you may restrict the extensions of files that may be uploaded", 'ihc');?></P>
            <div class="row">
  							<div class="col-xs-6">
                  <textarea name="ihc_upload_extensions" class="ihc-gen-full-width"><?php echo esc_html($meta_arr['ihc_upload_extensions']);?></textarea>
                </div>
              </div>
            <div><?php  esc_html_e("Write the extensions with comma between values! ex: pdf,jpg,mp3");?></div>

					</div>
					<div class="iump-form-line">
            <div class="row">
  							<div class="col-xs-6">
					             <h2> <?php esc_html_e("Upload File Maximum Size", 'ihc');?></h2>
                      <div class="input-group">
            					       <input type="number" class="form-control" value="<?php echo esc_attr($meta_arr['ihc_upload_max_size']);?>" name="ihc_upload_max_size" min="0.1" step="0.1" />
                             <div class="input-group-addon">MB</div>
                      </div>
            </div>
          </div>
					</div>
					<div class="iump-form-line">
            <div class="row">
  							<div class="col-xs-6">
    						<h2> <?php esc_html_e("Avatar Maximum Size", 'ihc');?></h2>
                <div class="input-group">
    						        <input type="number" class="form-control" value="<?php echo esc_attr($meta_arr['ihc_avatar_max_size']);?>" name="ihc_avatar_max_size" min="0.1" step="0.1" />
                  <div class="input-group-addon">MB</div>
               </div>
             </div>
           </div>
          </div>

        </div>
      </div>
      <div class="ihc-stuffbox">
        <div class="inside">
				<div  class="ihc-wrapp-submit-bttn">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
				</div>
				</div>


			</form>
		<?php
	break;
	case 'notifications':
		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('notifications');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('notifications');//getting metas




		?>
		<form  method="post">

			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Email Notifications Settings', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Additional Options', 'ihc');?></h2>
						<p><?php esc_html_e('Customize the Sender details and also additional Email Addresses for Admin Notifications', 'ihc');?></p>
					</div>
					<div class="iump-form-line  iump-no-border">
						<h4><?php esc_html_e('Sender Details', 'ihc');?></h4>
					</div>
					<div class="iump-form-line">
						<div class="row">
					 		<div class="col-xs-8">
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Email Address", 'ihc');?></span>
									<input type="text" name="ihc_notification_email_from" value="<?php echo esc_attr($meta_arr['ihc_notification_email_from']);?>"  class="form-control" />
								</div>
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Name", 'ihc');?></span>
									<input type="text" name="ihc_notification_name" value="<?php echo esc_attr($meta_arr['ihc_notification_name']);?>"  class="form-control" />
								</div>
							</div>
						</div>
					</div>
          <div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Automatic Notifications Intervals', 'ihc');?></h2>
					</div>
					<div class="iump-form-line iump-no-border">
						<h4><?php esc_html_e('Before Membeships Expire Interval', 'ihc');?></h4>
						<p><?php esc_html_e("Before a member's membership is about to expire, up to three notifications can be sent out. You can select how many days before the expiration each notification should be sent.", 'ihc');?></p>
					</div>
					<div class="iump-form-line">
						<div class="row">
					 		<div class="col-xs-8">
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("First Interval", 'ihc');?></span>
									<input type="number" min="1" name="ihc_notification_before_time" value="<?php echo esc_attr($meta_arr['ihc_notification_before_time']);?>"  class="form-control" />
									<div class="input-group-addon"><?php esc_html_e("Days", 'ihc');?></div>
								</div>
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Second Interval", 'ihc');?></span>
									<input type="number" min="1" name="ihc_notification_before_time_second" value="<?php echo esc_attr($meta_arr['ihc_notification_before_time_second']);?>"  class="form-control" />
									<div class="input-group-addon"><?php esc_html_e("Days", 'ihc');?></div>
								</div>
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Third Interval", 'ihc');?></span>
									<input type="number" min="1" name="ihc_notification_before_time_third" value="<?php echo esc_attr($meta_arr['ihc_notification_before_time_third']);?>"  class="form-control" />
									<div class="input-group-addon"><?php esc_html_e("Days", 'ihc');?></div>
								</div>
							</div>
						</div>
					</div>

					<div class="iump-form-line">
						<h4><?php esc_html_e("Payment Due - Time Interval", 'ihc');?></h4>
						<div class="row">
					 		<div class="col-xs-8">
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Before", 'ihc');?></span>
									<input type="number" min="1" name="ihc_notification_payment_due_time_interval" value="<?php echo esc_attr($meta_arr['ihc_notification_payment_due_time_interval']);?>"  class="form-control" />
									<div class="input-group-addon"><?php esc_html_e("Days", 'ihc');?></div>
								</div>
							</div>
						</div>
					</div>
					<div class="iump-form-line">
						<h4><?php esc_html_e("Card Expiry Reminder - day of the Month prior the Expiry Month", 'ihc');?></h4>
						<div class="row">
					 		<div class="col-xs-8">
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Before", 'ihc');?></span>
									<input type="number" min="1" max="28" name="ihc_notification_card_expiry_time_interval" value="<?php echo esc_attr($meta_arr['ihc_notification_card_expiry_time_interval']);?>"  class="form-control" />
									<div class="input-group-addon"><?php esc_html_e("Months", 'ihc');?></div>
								</div>
							</div>
						</div>
					</div>
					<div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Admin Notifications Email Address(es)', 'ihc');?></h2>
					</div>
					<div class="iump-form-line">
						<div class="row">
					 		<div class="col-xs-8">
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Admin Email Address", 'ihc');?></span>
									<input type="text" name="ihc_notification_email_addresses" value="<?php echo esc_attr($meta_arr['ihc_notification_email_addresses']);?>"  class="form-control" />
								</div>
								<p><?php esc_html_e("Set multiple Email addresses separated by commas", 'ihc');?></p>
							</div>
						</div>
					</div>
        </div>
      </div>
      <div class="ihc-stuffbox">
        <div class="inside">
					<div class="ihc-wrapp-submit-bttn">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
				</div>
			</div>
		</form>

		<?php
			if ( !empty( $_GET['do_cleanup_logs'] ) && !empty( $_GET['older_then'] ) ){
				$olderThen = sanitize_text_field($_GET['older_then']) * 24 * 60 * 60;
				$olderThen = indeed_get_unixtimestamp_with_timezone() - $olderThen;
				$olderThen = date( 'Y-m-d', $olderThen );
				\Indeed\Ihc\Db\NotificationLogs::deleteMany( $olderThen );
			}
		?>
		<div class="ihc-stuffbox">

			<div class="inside">
				<?php $we_have_logs = \Indeed\Ihc\Db\NotificationLogs::countAll();?>
				<?php if ($we_have_logs):?>
					<div class="iump-form-line  iump-no-border">
            <h2><?php esc_html_e('Notification Logs', 'ihc');?></h2>
						<?php esc_html_e('Clean Up Notifcations logs older then:', 'ihc');?>
						<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " id="older_then_select">
							<option value="">...</option>
							<option value="1"><?php esc_html_e('One Day', 'ihc');?></option>
							<option value="7"><?php esc_html_e('One Week', 'ihc');?></option>
							<option value="30"><?php esc_html_e('One Month', 'ihc');?></option>
						</select>
          </div>
          <div class="iump-form-line  iump-no-border">
						<div class="button button-primary button-large iump-first-button ihc-text-center" onClick="ihcDoCleanUpLogs('<?php echo admin_url('admin.php?page=ihc_manage&tab=general&subtab=notifications');?>');"><?php esc_html_e('Clean Up', 'ihc');?></div>
					</div>

				<?php else :?>
          <div class="iump-form-line">
					       <?php esc_html_e('No notification logs yet', 'ihc');?>
          </div>
				<?php endif;?>
			</div>
		</div>

		<?php
	break;
	case 'pay_settings':
		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('payment');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('payment');//getting metas




		?>
		<form  method="post">

			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Payment Settings', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line">
						<h2><?php esc_html_e('Default System Currency', 'ihc');?></h2>
						<p><?php esc_html_e('Check which payment gateway supports the next currency and deactivate the other payment gateways', 'ihc');?></p>
            <div class="row">
					 		<div class="col-xs-6">
                  <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_currency">
      							<?php
      								$currency_arr = ihc_get_currencies_list('all');
      								$custom_currencies = ihc_get_currencies_list('custom');
      								foreach ($currency_arr as $k=>$v){
      									?>
      									<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_currency']){ echo esc_attr('selected');}?> >
      										<?php echo esc_html($v);?>
      										<?php if (is_array($custom_currencies) && in_array($v, $custom_currencies)){ esc_html_e(" (Custom Currency)");}?>
      									</option>
      									<?php
      								}
      							?>
      						</select>
      						<p><?php esc_html_e('You can add new currencies from', 'ihc');?> <a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=custom_currencies');?>"><?php esc_html_e('here', 'ihc');?></a></p>
                </div>
              </div>
          </div>
					<div class="iump-form-line">
						<h2><?php esc_html_e('Currency Custom Symbol', 'ihc');?></h2>
						<p><?php esc_html_e('You may customize how default Currency will show up into Ultimate Membership Pro Showcases', 'ihc');?></p>
						<div class="row">
					 		<div class="col-xs-6">
								<div class="input-group">
									<span class="input-group-addon" ><?php esc_html_e("Symbol", 'ihc');?></span>
									<input type="text" name="ihc_custom_currency_code" value="<?php echo esc_attr($meta_arr['ihc_custom_currency_code']);?>"   class="form-control" />
								</div>
							</div>
						</div>
					</div>
					<div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Currency Position', 'ihc');?></h2>
            <div class="row">
					 		<div class="col-xs-6">
    						<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_currency_position">
    							<?php
    								$position = array('left' => esc_html__('Left', 'ihc'), 'right' => esc_html__('Right', 'ihc'));
    								foreach ($position as $k=>$v){
    									?>
    									<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_currency_position']){ echo esc_attr('selected');}?> >
    										<?php echo esc_html($v);?>
    									</option>
    									<?php
    								}
    							?>
    						</select>
    						<p ><?php esc_html_e('Currency will be placed before (left) price number or after (right)', 'ihc');?></p>
              </div>
            </div>
        	</div>
          <div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Price Separators', 'ihc');?></h2>
								<p><?php esc_html_e('The following options affect how prices are displayed on the frontend', 'ihc');?></p>
							</div>
							<div class="iump-form-line iump-no-border">
								<div class="row">
							 		<div class="col-xs-6">
										<div class="input-group">
											<span class="input-group-addon" ><?php esc_html_e("Thousands Separator", 'ihc');?></span>
											<input type="text" name="ihc_thousands_separator" value="<?php echo esc_attr($meta_arr['ihc_thousands_separator']);?>"   class="form-control" />
										</div>
									</div>
								</div>
								<div class="row">
							 		<div class="col-xs-6">
										<div class="input-group">
											<span class="input-group-addon" ><?php esc_html_e("Decimals Separator", 'ihc');?></span>
											<input type="text" name="ihc_decimals_separator" value="<?php echo esc_attr($meta_arr['ihc_decimals_separator']);?>"   class="form-control" />
										</div>
									</div>
								</div>
								<div class="row">
							 		<div class="col-xs-6">
										<div class="input-group">
											<span class="input-group-addon" ><?php esc_html_e("Number of Decimals", 'ihc');?></span>
											<input type="text" name="ihc_num_of_decimals" value="<?php echo esc_attr($meta_arr['ihc_num_of_decimals']);?>"   class="form-control" />
										</div>
									</div>
								</div>
					</div>

					<div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Default Payment Gateway', 'ihc');?></h2>
						<p><?php esc_html_e('If no option to select a specific payment gateway by the customer or none of them is selected this Payment Service will be triggered. Be sure that is Enabled and Properly setup inside Payment Gateways section', 'ihc');?></p>
					</div>
					<div class="iump-form-line iump-no-border">
            <div class="row">
  							<div class="col-xs-8">
						<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_payment_selected">
							<?php
								$payment_arr = ihc_list_all_payments();
								foreach($payment_arr as $k=>$v){
									$active = (ihc_check_payment_available($k)) ? esc_html__('Active', 'ihc') : esc_html__('Inactive', 'ihc');
									?>
									<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_payment_selected']){ echo esc_attr('selected');}?> >
										<?php echo esc_html($v) . ' - ' . esc_html($active);?>
									</option>
									<?php
								}
							?>
						</select>
  						<div><?php esc_html_e('When no multi-payment activated or no payment selected or required.');?></div>
            </div>
          </div>
          </div>
          <div class="iump-form-line iump-no-border">
            <div class="ihc-alert-warning-wrapper">
              <div class="ihc-alert-warning"><?php esc_html_e("Be sure that your selected Payment Gateway it's activated and properly set!");?></div>
            </div>
          </div>

			<?php
				if (!empty($_GET['do_cleanup_logs']) && !empty($_GET['older_then'])){
					$older_then = sanitize_text_field($_GET['older_then']) * 24 * 60 * 60;
					$older_then = indeed_get_unixtimestamp_with_timezone() - $older_then;
					Ihc_User_Logs::delete_logs('payments', $older_then);
				}
			?>

					<div class="iump-form-line iump-no-border">
            <div class="iump-grid iump-grid--gap-big">
              <div class="iump-option-wrapper">
                <h4 class="iump-option-title"><?php esc_html_e('Save Payments Logs into Database', 'ihc');?></h4>
                <p><?php esc_html_e('Choose to store the Payments Logs in DataBase for further Debugging process and detailed Tracking.', 'ihc');?></p>
              </div>
              <div class="iump-flex iump-flex--align-center">
    						<?php $checked = ($meta_arr['ihc_payment_logs_on']) ? 'checked' : '';?>
    						<label class="iump_label_shiwtch ihc-switch-button-margin">
    							<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_payment_logs_on');" <?php echo esc_attr($checked);?> />
    							<div class="switch ihc-display-inline"></div>
    						</label>
    						<input type="hidden" name="ihc_payment_logs_on" id="ihc_payment_logs_on" value="<?php echo esc_attr($meta_arr['ihc_payment_logs_on']);?>" />
            </div>
          </div>
					</div>
					<?php $we_have_logs = Ihc_User_Logs::get_count_logs('payments');?>
					<?php if ($we_have_logs):?>
						<div class="iump-form-line iump-no-border">
							<?php esc_html_e('Clean Up Payment logs older then:', 'ihc');?>
							<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " id="older_then_select">
								<option value="">...</option>
								<option value="1"><?php esc_html_e('One Day', 'ihc');?></option>
								<option value="7"><?php esc_html_e('One Week', 'ihc');?></option>
								<option value="30"><?php esc_html_e('One Month', 'ihc');?></option>
							</select>
            </div>
            <div class="iump-form-line iump-no-border">
							<div class="button button-primary button-large iump-first-button ihc-text-center" onClick="ihcDoCleanUpLogs('<?php echo admin_url('admin.php?page=ihc_manage&tab=general&subtab=pay_settings');?>');"><?php esc_html_e('Clean Up', 'ihc');?></div>
						</div>
						<div class="iump-form-line iump-no-border">
							<a class="iump-second-button" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=view_user_logs&type=payments');?>" target="_blank"><?php esc_html_e('View Logs', 'ihc');?></a>
						</div>
					<?php endif;?>



			<div class="ihc-display-none">
						<?php
								if ( !isset( $meta_arr['ihc_payment_workflow'] ) || $meta_arr['ihc_payment_workflow'] == '' ){
										$meta_arr['ihc_payment_workflow'] = 'new';
								}
						?>
						<div class="iump-form-line">
							<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_payment_workflow" >
								<option value="standard" <?php if ( $meta_arr['ihc_payment_workflow'] == 'standard' ){ echo esc_attr('selected');}?> ><?php esc_html_e('Standard', 'ihc');?></option>
								<option value="new" <?php if ( $meta_arr['ihc_payment_workflow'] == 'new' ){ echo esc_attr('selected');}?> ><?php esc_html_e('New Integration', 'ihc');?></option>
							</select>
						</div>
			</div>

						<div class="row">
						<div class="col-xs-8">
              <div class="iump-form-line">
              <h2><?php esc_html_e('Merchant Business Address', 'ihc');?></h2>
              <p><?php esc_html_e('This is where your business is located', 'ihc');?></p>
							<div class="iump-form-line iump-no-border input-group">
									<span class="input-group-addon"><?php esc_html_e('Business Name:', 'ihc');?></span>
									<input type="text" class="form-control" name="ihc_payment_merchant_business_name" value="<?php echo esc_attr($meta_arr['ihc_payment_merchant_business_name']);?>" />
							</div>
							<div class="iump-form-line iump-no-border input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Address Line 1:', 'ihc' );?></span>
									<input type="text" class="form-control" name="ihc_payment_merchant_business_address_1" value="<?php echo esc_attr($meta_arr['ihc_payment_merchant_business_address_1']);?>" />
							</div>
							<div class="iump-form-line iump-no-border input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Address Line 2:', 'ihc' );?></span>
									<input type="text" class="form-control" name="ihc_payment_merchant_business_address_2" value="<?php echo esc_attr($meta_arr['ihc_payment_merchant_business_address_2']);?>" />
							</div>

							<div class="iump-form-line iump-no-border">
									<?php $countries = ihc_get_countries();?>
									<select name="ihc_payment_merchant_business_country" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-gen-default-country">
									<?php foreach ( $countries as $key => $value ):?>
											<option value="<?php echo esc_attr($key);?>" <?php echo ( $key == $meta_arr['ihc_payment_merchant_business_country'] ) ? 'selected' : '';?> ><?php echo esc_html($value);?></option>
									<?php endforeach;?>
									</select>
							</div>

							<div class="iump-form-line iump-no-border input-group">
									<span class="input-group-addon"><?php esc_html_e( 'State:', 'ihc' );?></span>
									<input type="text" class="form-control" name="ihc_payment_merchant_business_state" value="<?php echo esc_attr($meta_arr['ihc_payment_merchant_business_state']);?>" />
							</div>

							<div class="iump-form-line iump-no-border input-group">
									<span class="input-group-addon"><?php esc_html_e( 'City:', 'ihc' );?></span>
									<input type="text" class="form-control" name="ihc_payment_merchant_business_city" value="<?php echo esc_attr($meta_arr['ihc_payment_merchant_business_city']);?>" />
							</div>

							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon">	<?php esc_html_e( 'Postcode:', 'ihc' );?></span>
									<input type="text" class="form-control" name="ihc_payment_merchant_business_postcode" value="<?php echo esc_attr($meta_arr['ihc_payment_merchant_business_postcode']);?>" />
							</div>

							<div class="iump-form-line iump-no-border input-group">
									<span class="input-group-addon"><?php esc_html_e( 'VAT No.:', 'ihc' );?></span>
									<input type="text" class="form-control" name="ihc_payment_merchant_business_vat" value="<?php echo esc_attr($meta_arr['ihc_payment_merchant_business_vat']);?>" />
							</div>
            </div>
					 </div>
				 </div>

 					<h2><?php esc_html_e('Invoice Settings', 'ihc');?></h2>
 						<div class="iump-form-line">
 							<h4><?php esc_html_e('Invoice Prefix Code', 'ihc');?></h4>
               <p><?php esc_html_e('The value given in this field will be used as a reference element in all invoices', 'ihc');?></p>
 						</div>
 						<div class="iump-form-line">
 						<div class="row">
 							<div class="col-xs-6">
 								<div class="input-group">
 									<span class="input-group-addon">	<?php esc_html_e( 'Code', 'ihc' );?></span>
 										<input type="text" class="form-control" name="ihc_order_prefix_code" value="<?php echo (isset($meta_arr['ihc_order_prefix_code'])) ? $meta_arr['ihc_order_prefix_code'] : '';?>" />
 								</div>
 							</div>
 						</div>
 						</div>

                <h2><?php esc_html_e('Taxes Settings', 'ihc');?></h2>
                <div class="iump-form-line">
                  <div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
                      <h2  class="iump-option-title"><?php esc_html_e('Taxes included in Membership price', 'ihc');?></h2>
                      <p><?php esc_html_e('The price of a Membership will be the final price (taxes will be included)', 'ihc');?></p>
                    </div>
                  <div class="iump-flex iump-flex--align-center">
                      <div class="row">
                      <div class="col-xs-6">
                      <?php $checked = ($meta_arr['ihc_payment_taxes_included_in_amount']) ? 'checked' : '';?>
          						<label class="iump_label_shiwtch ihc-switch-button-margin">
          							<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_payment_taxes_included_in_amount');" <?php echo esc_attr($checked);?> />
          							<div class="switch ihc-display-inline"></div>
          						</label>
          						<input type="hidden" name="ihc_payment_taxes_included_in_amount" id="ihc_payment_taxes_included_in_amount" value="<?php echo esc_attr($meta_arr['ihc_payment_taxes_included_in_amount']);?>" />
                      </div>
                      </div>
                    </div>
                  </div>
             </div>
           </div>
         </div>
         <div class="ihc-stuffbox">
           <div class="inside">
          <div class="ihc-wrapp-submit-bttn">
            <input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
          </div>
        </div>
      </div>

		</form>
		<?php
	break;

		case 'double_email_verification':
			if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
					ihc_save_update_metas('double_email_verification');//save update metas
			}

			$meta_arr = ihc_return_meta_arr('double_email_verification');//getting metas




			$pages = $pages + ihc_get_redirect_links_as_arr_for_select();
			?>
			<form  method="post">

				<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Double Email Verification Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line iump-no-border">
							<p><?php esc_html_e('When Double Email Verification process is enabled inside Register Form settings, new registered members needs to confirm their email addresses a special link set via Email. Link have limited live time and if email is not confirmed on certain time member account may be removed', 'ihc');?></p>
						</div>
						<div class="iump-form-line iump-no-border">
							<h4><?php esc_html_e('Verification Link Life Time', 'ihc');?></h4>
              <p><?php esc_html_e('Determines the time required for new members to confirmed the email address', 'ihc');?></p>
              <div class="row">
  						<div class="col-xs-8">
              <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_double_email_expire_time">
								<?php
									$arr = array(
															'-1' => 'Never',
															'900' => '15 Minutes',
															'3600' => '1 Hour',
															'43200' => '12 Hours',
															'86400' => '1 Day',
															);
									foreach ($arr as $k=>$v){
										?>
										<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_double_email_expire_time']){ echo esc_attr('selected');}?> >
											<?php echo esc_html($v);?>
										</option>
										<?php
									}
								?>
							</select>
            </div>
          </div>
						</div>

						<div class="iump-form-line iump-no-border">
							<h4><?php esc_html_e('Success Redirect after Email Verification', 'ihc');?></h4>
              <p><?php esc_html_e('Members will be redirected to the selected page once their email address has been validated', 'ihc');?></p>
              <div class="row">
  						<div class="col-xs-8">
              <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_double_email_redirect_success">
								<option value="-1" <?php if($meta_arr['ihc_double_email_redirect_success']==-1){echo esc_attr('selected');}?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_double_email_redirect_success']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
            </div>
          </div>
						</div>

						<div class="iump-form-line iump-no-border">
							<h4><?php esc_html_e('Error Redirect', 'ihc');?></h4>
              	<p><?php esc_html_e('When the email expires or in other instances, members will be redirected to the designated page', 'ihc');?></p>
                <div class="row">
    						<div class="col-xs-8">
                    <select  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_double_email_redirect_error">
      								<option value="-1" <?php if($meta_arr['ihc_double_email_redirect_error']==-1){echo esc_attr('selected');}?> >...</option>
      								<?php
      									if ($pages){
      										foreach ($pages as $k=>$v){
      											?>
      												<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_double_email_redirect_error']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
      											<?php
      										}
      									}
      								?>
      							</select>
                  </div>
                </div>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Delete Member Account if is not Verified', 'ihc');?></h4>
							<p><?php esc_html_e('Caution: This option will completely delete the WordPress User from your website if email is not confirmed in certain time', 'ihc');?></p>
              <div class="row">
  						<div class="col-xs-8">
              <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_double_email_delete_user_not_verified">
								<?php
									$arr = array(
															'-1' => 'Never',
															'1' => 'After 1 day',
															'7' => 'After 7 days',
															'14' => 'After 14 days',
															'30' => 'After 30 days',
															);
									foreach ($arr as $k=>$v){
										?>
										<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_double_email_delete_user_not_verified']){ echo esc_attr('selected');}?> >
											<?php echo esc_html($v);?>
										</option>
										<?php
									}
								?>
							</select>
            </div>
          </div>
						</div>

					</div>
				</div>
        <div class="ihc-stuffbox">
          <div class="inside">
						<div class="ihc-wrapp-submit-bttn">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>
			<?php
			break;
	case 'access':
		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				update_option('ihc_dashboard_allowed_roles', sanitize_text_field($_POST['ihc_dashboard_allowed_roles']) );
		}

		$meta_value = get_option('ihc_dashboard_allowed_roles');
		$meta_values = (empty($meta_value)) ? array() : explode(',', $meta_value);
		?>
		<form  method="post">

			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('WordPress Roles Management', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Enable access for specific WordPress Roles', 'ihc');?></h2>
						<p><?php esc_html_e('By Default Only Administrator WP Role will have access into WordPress Dashboard. All other logged users will be kept on FrontEnd Side. You can enable access to certain Users by on their WP Role using below On/Off buttons', 'ihc');?></p>
						<p><?php esc_html_e('Warning: This option will manage access to WordPress Dashboard only. Ultimate Membership Pro Dashboard will continue to be accessible ONLY by users with Administrator WP Role', 'ihc');?></p>
					</div>
					<div class="ihc-gen-wprole-sec">
						<div class="iump-form-line ihc-gen-opacity  iump-no-border">
							<span class="ihc-gen-span-st"><?php esc_html_e('Administrator', 'ihc');?></span>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<input type="checkbox" class="iump-switch" checked disabled/>
								<div class="switch ihc-display-inline"></div>
							</label>
						</div>
						<?php
							$roles = get_editable_roles();
							if (!empty($roles['administrator'])){
								unset($roles['administrator']);
							}
							if (!empty($roles['pending_user'])){
								unset($roles['pending_user']);
							}
							$count = count($roles) + 1;
							$break = ceil($count/2);
							$i = 1;
							foreach ($roles as $role=>$arr){
								?>
									<div class="iump-form-line iump-no-border">
										<span class="ihc-gen-span-st"><?php echo esc_html($arr['name']);?></span>
										<label class="iump_label_shiwtch ihc-switch-button-margin">
											<?php $checked = (in_array($role, $meta_values)) ? 'checked' : '';?>
											<input type="checkbox" class="iump-switch" onClick="ihcMakeInputhString(this, '<?php echo esc_attr($role);?>', '#ihc_dashboard_allowed_roles');" <?php echo esc_attr($checked);?>/>
											<div class="switch ihc-display-inline"></div>
										</label>
									</div>
								<?php
								$i++;
								if ($count>7 && $i==$break){
									?>
										</div>
										<div  class="ihc-gen-wprole-sec">
									<?php
								}
							}
						?>
					</div>
					<input type="hidden" name="ihc_dashboard_allowed_roles" id="ihc_dashboard_allowed_roles" value="<?php echo esc_attr($meta_value);?>" />

        </div>
      </div>
      <div class="ihc-stuffbox">
        <div class="inside">
          <div class="ihc-wrapp-submit-bttn">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
				</div>
			</div>
		</form>
		<?php
		break;
	case 'admin_workflow':
		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('admin_workflow');//save update metas
		}
		$meta_arr = ihc_return_meta_arr('admin_workflow');//getting metas




		?>
		<form  method="post">

			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Admin Workflow Settings', 'ihc');?></h3>
				<div class="inside">
						<div class="iump-form-line iump-no-border">
              <h2><?php esc_html_e('WordPress Admin Bar', 'ihc');?></h2>
              <div class="iump-grid iump-grid--gap-big">
              <div class="iump-option-wrapper">
    							<h2 class="iump-option-title"><?php esc_html_e('Show WordPress Admin bar Notifications', 'ihc');?></h2>
    							<p><?php esc_html_e('Display New Users & New Orders Counts into WP Admin bar', 'ihc');?></p>
              </div>
              <div class="iump-flex iump-flex--align-center">
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_admin_workflow_dashboard_notifications']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_admin_workflow_dashboard_notifications');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_admin_workflow_dashboard_notifications" value="<?php echo esc_attr($meta_arr['ihc_admin_workflow_dashboard_notifications']);?>" id="ihc_admin_workflow_dashboard_notifications" />
            </div>
          </div>
          </div>
				</div>
			</div>

				<div class="ihc-stuffbox">

						<div class="inside">
								<div class="iump-form-line iump-no-border">
                <h2><?php esc_html_e('Uninstall Settings', 'ihc');?></h2>
                  <div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
										<h4 class="iump-option-title"><?php esc_html_e('Choose to Keep Data after Uninstall', 'ihc');?></h4>
                  </div>
                  <div class="iump-flex iump-flex--align-center">
										<label class="iump_label_shiwtch ihc-switch-button-margin">
											<?php $checked = ($meta_arr['ihc_keep_data_after_delete']) ? 'checked' : '';?>
											<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_keep_data_after_delete');" <?php echo esc_attr($checked);?> />
											<div class="switch ihc-display-inline"></div>
										</label>
										<input type="hidden" name="ihc_keep_data_after_delete" value="<?php echo esc_attr($meta_arr['ihc_keep_data_after_delete']);?>" id="ihc_keep_data_after_delete" />
                  </div>
                </div>
                </div>
						</div>
						<div class="inside">
							<div class="iump-form-line iump-no-border">
                <h2><?php esc_html_e('Custom WordPress Login Page', 'ihc');?></h2>
                <div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
								<h2 class="iump-option-title"><?php esc_html_e('WordPress Login  - Ultimate Membership Pro Style', 'ihc');?></h2>
								<p><?php esc_html_e('You may switch back to default WordPress style for WordPress login page', 'ihc');?></p>
              </div>
                <div class="iump-flex iump-flex--align-center">
                  <?php $checked = ($meta_arr['ihc_wp_login_custom_css']) ? 'checked' : '';?>
									<label class="iump_label_shiwtch ihc-switch-button-margin">
										<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_wp_login_custom_css');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" name="ihc_wp_login_custom_css" id="ihc_wp_login_custom_css" value="<?php echo esc_attr($meta_arr['ihc_wp_login_custom_css']);?>" />
                </div>
              </div>
              </div>

							<div class="iump-form-line">
									<h4><?php esc_html_e('Replace Default Logo Image', 'ihc');?></h4>
                  	<p><?php esc_html_e('The selected picture will replace the plugin\'s default logo in the login form', 'ihc');?></p>
									<div class="row">
										<div class="col-xs-10">
											<input type="text" class="form-control ihc-gent-login-logo" onclick="openMediaUp( this, '#ihc_wp_login_logo_image' );" value="<?php echo esc_attr($meta_arr['ihc_wp_login_logo_image']);?>" name="ihc_wp_login_logo_image" id="ihc_wp_login_logo_image">
											<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-delete-logo-image" title="<?php esc_html_e( 'Remove logo', 'ihc' );?>"></i>
										</div>
									</div>
							</div>
            </div>
          </div>

          <div class="ihc-stuffbox">
            <div class="inside">
							<div class="ihc-wrapp-submit-bttn">
								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
							</div>
						</div>
				</div>

		</form>
		<?php
		break;
	case 'public_workflow':
		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('public_workflow');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('public_workflow');//getting metas




		?>
		<form  method="post">

			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Public Workflow Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line iump-no-border">
              <h2><?php esc_html_e('Listing Pages/Posts', 'ihc');?></h2>
              <div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
							<h4 class="iump-option-title"><?php esc_html_e('Show hidden Pages/Posts Titles in listing', 'ihc');?></h4>
							<p><?php esc_html_e('Choose if you wish to keep restricted posts into listing Posts showcase even if access is restricted for current logged user', 'ihc');?></p>
            </div>
                <div class="iump-flex iump-flex--align-center">
              <label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_listing_show_hidden_post_pages']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_show_hidden_post_pages');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_listing_show_hidden_post_pages" value="<?php echo esc_attr($meta_arr['ihc_listing_show_hidden_post_pages']);?>" id="ihc_listing_show_hidden_post_pages" />
            </div>
          </div>
            </div>
					</div>


				<div id="ihc_general_grace_period">

					<div class="inside">
						<div class="iump-form-line">
              <h2> <?php esc_html_e("Grace Subscription Period", 'ihc');?></h2>
            </div>
            <div class="iump-form-line">
							<h4><?php esc_html_e('Default Grace Period for all Memberships', 'ihc');?></h4>
							<p><?php esc_html_e('If no specific Grace Period have been setup for each Membership this default value will be used instead', 'ihc');?></p>
						</div>
						<div class="iump-form-line">
              <div class="row">
                <div class="col-xs-8">
						<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_grace_period"><?php
							for ($i=0;$i<365;$i++){
								$selected = ($meta_arr['ihc_grace_period']==$i) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($i);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($i) . ' ' . esc_html__('Days', 'ihc');?></option>
								<?php
							}
						?></select>
              </div>
            </div>
					</div>
					</div>
				</div>



					<div class="inside">
						<div class="iump-form-line iump-no-border">
              <h2> <?php esc_html_e("Avatar Settings", 'ihc');?></h2>
              <div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
							<h4 class="iump-option-title"><?php esc_html_e('Use Gravatar Image', 'ihc');?></h4>
							<p><?php esc_html_e('If Avatar Image is missing, Gravatar image for that WordPress User will be displayed instead', 'ihc');?></p>
            </div>
                <div class="iump-flex iump-flex--align-center">
              <label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_use_gravatar']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_use_gravatar');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_use_gravatar" value="<?php echo esc_attr($meta_arr['ihc_use_gravatar']);?>" id="ihc_use_gravatar" />
            </div>
                </div>
            </div>

						<?php $display = (function_exists('bp_core_fetch_avatar')) ? 'ihc-display-block' : 'ihc-display-none';?>
						<div class="iump-form-line  iump-no-border <?php echo esc_attr($display);?>">
              <div class="iump-grid iump-grid--gap-big">
              <div class="iump-option-wrapper">
							<h2 class="iump-option-title"><?php esc_html_e('Use BuddyPress Image', 'ihc');?></h2>
            </div>
            <div class="iump-flex iump-flex--align-center">
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_use_buddypress_avatar']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_use_buddypress_avatar');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_use_buddypress_avatar" value="<?php echo esc_attr($meta_arr['ihc_use_buddypress_avatar']);?>" id="ihc_use_buddypress_avatar" />
            </div>
                </div>
            </div>
					</div>

					<div class="inside">
            <div class="iump-form-line">
								<h2><?php esc_html_e('Default Country', 'ihc');?></h2>
								<p><?php esc_html_e('Choose a default country for Members Registration form. If none is chosen default WordPress Locale will be used instead', 'ihc');?></p>
								<?php
										wp_enqueue_style( 'ihc_select2_style' );
										wp_enqueue_script( 'ihc-select2' );
								?>
                <div class="row">
                  <div class="col-xs-8">
								<select name="ihc_default_country" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " >
										<?php $countries = ihc_get_countries();?>
										<?php foreach ( $countries as $key => $value ):?>
												<option value="<?php echo esc_attr($key);?>" <?php if ( $meta_arr['ihc_default_country'] == $key ){ echo esc_attr('selected');}?> ><?php echo esc_html($value);?></option>
										<?php endforeach;?>
								</select>
								<ul id="ihc_countries_list_ul ihc-display-none"></ul>
              </div>
            </div>
						</div>
					</div>

					<div class="inside">
						<div class="iump-form-line iump-no-border">
              <h2><?php esc_html_e('Pretty Links', 'ihc');?></h2>
              <div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
							<h2 class="iump-option-title"><?php esc_html_e('Replace standard Links with args into more Friendly Links', 'ihc');?></h2>
              <p><?php esc_html_e('This option will replace the links into Subscription Plan, Register Page, Checkout Page and Account Page', 'ihc');?></p>
            </div>
            <div class="iump-flex iump-flex--align-center">
              <label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_pretty_links']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_pretty_links');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_pretty_links" value="<?php echo esc_attr($meta_arr['ihc_pretty_links']);?>" id="ihc_pretty_links" />
            </div>
                </div>
						</div>
          </div>
        </div>

        <div class="ihc-stuffbox">
          <div class="inside">
						<div class="ihc-wrapp-submit-bttn">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
					</div>
				</div>

		</form>
		<?php
		break;
	case 'security':
		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
				ihc_save_update_metas('security');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('security');//getting metas




		?>
		<form  method="post">

			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Security Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line iump-no-border">
            <div class="iump-grid iump-grid--gap-big">
              <div class="iump-option-wrapper">
                <h2><?php esc_html_e('Authorize Search Engines', 'ihc');?></h2>
							<h4 class="iump-option-title"><?php esc_html_e('Turn On access to Search engines for Restricted pages', 'ihc');?></h4>
							<p><?php esc_html_e('Once a WordPress Page is restricted, Search Engines (such Google) may not be able to index the page content. When this option is enabled Search Engines are allowed to view protected content on your site. This can help search engines index your content. ', 'ihc');?></p>
            </div>
            <div class="iump-flex iump-flex--align-center">
              <label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_security_allow_search_engines']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_security_allow_search_engines');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_security_allow_search_engines" value="<?php echo esc_attr($meta_arr['ihc_security_allow_search_engines']);?>" id="ihc_security_allow_search_engines" />
            </div>
            </div>
					</div>
				</div>
       </div>

				<div class="ihc-stuffbox">

					<div class="inside">
						<div class="iump-form-line iump-no-border">
              <h2><?php esc_html_e('Block Username on SignUp', 'ihc');?></h2>
							<p><?php esc_html_e('Usernames which are setup on this section will be blocked on Registration process. Any keywords stored will not be allowed to be part of username submission. Ex: "admin" will block usernames as "admin123", "Badmin". Place each keyword separated by comma', 'ihc');?></p>
              <div class="iump-padding-bottom">
                <textarea name="ihc_security_username" class="ihc_email_blacklist"><?php echo esc_html(stripslashes($meta_arr['ihc_security_username']));?></textarea>
              </div>
              <div class="row">
								<div class="col-xs-6">
									<div class="input-group">
											<span class="input-group-addon"><?php esc_html_e('Warning Message', 'ihc');?></span>
												<input type="text" class="form-control" name="ihc_security_block_username_message" value="<?php echo esc_attr(stripslashes($meta_arr['ihc_security_block_username_message']));?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="ihc-stuffbox">

						<div class="inside">
              <div class="iump-form-line iump-no-border">
              <h2> <?php esc_html_e("Email Address Black List", 'ihc');?></h2>
	                    	<p><?php esc_html_e("Will prevent visitors to Register with specified email address. All items must be separated by comma", 'ihc');?></p>
              <div class="iump-padding-bottom">
                <textarea name="ihc_email_blacklist" class="ihc_email_blacklist"><?php echo stripslashes($meta_arr['ihc_email_blacklist']);?></textarea>
              </div>
            </div>
            </div>
				</div>
				<div class="ihc-stuffbox">

					<div class="inside">
						<div class="iump-form-line iump-no-border">
              <h2><?php esc_html_e('Restrict entire website without Login', 'ihc');?></h2>
              <div class="iump-grid iump-grid--gap-big iump-padding-bottom">
                <div class="iump-option-wrapper">
  							         <h4 class="iump-option-title"><?php esc_html_e('Restrict access over entire Website', 'ihc');?></h4>
  							         <p><?php esc_html_e('All pages, except WP Homepage, UMP default pages (Login,Register, etc) will be restricted. Visitors are automtically redirected to UMP Login page. ', 'ihc'); ?><b>Important:</b><?php esc_html_e(' Be sure that you already have UMP Login page properly setup and available for visitors', 'ihc');?></p>
                </div>
                <div class="iump-flex iump-flex--align-center">
                    <label class="iump_label_shiwtch ihc-switch-button-margin">
    								<?php $checked = (!empty($meta_arr['ihc_security_restrict_everything'])) ? 'checked' : '';?>
    								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_security_restrict_everything');" <?php echo esc_attr($checked);?> />
    								<div class="switch ihc-display-inline"></div>
    							</label>
    							<input type="hidden" name="ihc_security_restrict_everything" value="<?php echo esc_attr($meta_arr['ihc_security_restrict_everything']);?>" id="ihc_security_restrict_everything" />
                </div>
          </div>
							<div class="row">
								<div class="col-xs-8">
									<p><?php esc_html_e('Exclude other pages from current Restriction', 'ihc');?></p>
									<input type="text" class="form-control" name="ihc_security_restrict_everything_except" value="<?php echo esc_attr($meta_arr['ihc_security_restrict_everything_except']);?>" />

								</div>
							</div>
              <p><?php esc_html_e('The value entered will be the page or post ', 'ihc');?> <b>ID</b>.<?php echo esc_html_e(' If multiple pages will be excluded, separate the IDs with a comma', 'ihc');?></p>

						</div>
					</div>
				</div>

				<div class="ihc-stuffbox ihc-display-none">
					<h3><?php esc_html_e('Rename wp-admin folder', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
							<span class="iump-labels-special"><?php esc_html_e('Hide Default "wp-admin" from Link. ', 'ihc');?></span>
							<p><?php esc_html_e('For backup, be sure that you have FTP access. In case you are not able to login anymore, just deactivate the plugin by changing the plugin folder name with something else', 'ihc');?></p>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = (!empty($meta_arr['ihc_security_rename_wpadmin'])) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_security_rename_wpadmin');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_security_rename_wpadmin" value="<?php echo esc_attr($meta_arr['ihc_security_rename_wpadmin']);?>" id="ihc_security_rename_wpadmin" />

							<div class="row">
								<div class="col-xs-6">
									<div class="iump-form-line iump-no-border input-group">
											<span class="input-group-addon"><?php esc_html_e('New wp-admin name', 'ihc');?></span>
												<input type="text" class="form-control" name="ihc_security_rename_wpadmin_name" value="<?php echo esc_attr($meta_arr['ihc_security_rename_wpadmin_name']);?>" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
        <div class="ihc-stuffbox">
          <div class="inside">
            <div class="ihc-wrapp-submit-bttn">
              <input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
            </div>
          </div>
      </div>

		</form>

		<?php
		break;
    case 'debug':
  		if ( isset( $_POST['ihc_save'] ) && !empty($_POST['ihc_admin_general_options_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
          ihc_save_update_metas('debug');//save update metas
  		}

  		$meta_arr = ihc_return_meta_arr('debug');//getting metas




  		?>
  		<form  method="post">

  			<input type="hidden" name="ihc_admin_general_options_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_general_options_nonce' );?>" />

  				<div class="ihc-stuffbox">
  					<h3><?php esc_html_e('Debug', 'ihc');?></h3>
  					<div class="inside">
    						<div class="iump-form-line iump-no-border">
                    <div class="iump-grid iump-grid--gap-big">
                        <div class="iump-option-wrapper">
            							  <h4><?php esc_html_e('Output your content without any content filtering by using the do_shortcode() function', 'ihc'); //esc_html_e('Restrict do_shortcode on content', 'ihc');?></h4>
            							  <p><?php esc_html_e('By activating this option, the content in each post or page will be unfiltered and the output will not be return in a do_shortcode() WP native function', 'ihc');?></p>
                            <p><?php esc_html_e('This option is for developers, be sure you know what you do', 'ihc');?></p>
                        </div>
                        <div class="iump-flex iump-flex--align-center">
                            <label class="iump_label_shiwtch ihc-switch-button-margin">
                								<?php $checked = ($meta_arr['ihc_prevent_use_of_do_shortcode_on_content']) ? 'checked' : '';?>
                								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_prevent_use_of_do_shortcode_on_content');" <?php echo esc_attr($checked);?> />
                								<div class="switch ihc-display-inline"></div>
              							</label>
              							<input type="hidden" name="ihc_prevent_use_of_do_shortcode_on_content" value="<?php echo esc_attr($meta_arr['ihc_prevent_use_of_do_shortcode_on_content']);?>" id="ihc_prevent_use_of_do_shortcode_on_content" />
                        </div>
                    </div>
                </div>

                <div class="iump-form-line iump-no-border">
                    <div class="iump-grid iump-grid--gap-big">
                        <div class="iump-option-wrapper">
                            <h4><?php esc_html_e('Enable Tracking', 'ihc');?></h4>
                            <p><?php esc_html_e( 'By choosing this option, you give us permission to gather some technical information about your website in order to improve the plugin. A full list of the data to be collected can be found ', 'ihc');?><a href="https://ultimatemembershippro.com/usage-tracking/" target="_blank"><?php esc_html_e('here', 'ihc');?></a></p>
                        </div>
                        <div class="iump-flex iump-flex--align-center">
                            <label class="iump_label_shiwtch ihc-switch-button-margin">
                                  <?php $checked = ($meta_arr['ihc_allow_tracking']) ? 'checked' : '';?>
                                  <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_allow_tracking');" <?php echo esc_attr($checked);?> />
                                  <div class="switch ihc-display-inline"></div>
                            </label>
                            <input type="hidden" name="ihc_allow_tracking" value="<?php echo esc_attr($meta_arr['ihc_allow_tracking']);?>" id="ihc_allow_tracking" />
                        </div>
                    </div>
    					  </div>
            </div>
  				</div>

            <div class="ihc-stuffbox">
                <div class="inside">
        						<div class="ihc-wrapp-submit-bttn">
        							  <input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
        						</div>
      					</div>
    				</div>


  		</form>

  		<?php
  		break;
}
?>
</div>
<div class="ihc-clear"></div>
</div>
</div>
<?php
