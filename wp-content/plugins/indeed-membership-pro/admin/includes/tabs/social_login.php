<?php
$subtab = isset( $_GET['subtab'] ) ? sanitize_text_field($_GET['subtab']) : 'design';
$item = isset( $_GET['item']) ? sanitize_text_field($_GET['item']) : '';
$subtab_settings_selected = "ihc-subtab-selected";

if(isset($item) && $item !== '' ) {
	$subtab_settings_selected = "";
}
wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
?>
<div class="iump-wrapper">
	<div class="iump-page-headline"><?php esc_html_e('Social Connect', 'ihc');?>
	<div class="ihc-top-message"><?php esc_html_e('Allow users to sign up/login with their social accounts', 'ihc');?></div></div>
<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
 ?>
<div class="iump-settings-box-wrapper">

	<!--div class="ihc-subtab-menu"-->
	<div class="iump-settings-tab-wrapper">
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='design') ?  "ihc-subtab-selected" : '';?>" href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=design');?>"><?php esc_html_e('Design', 'ihc');?></a>
	<?php
	$arr = array(
			"fb" => "Facebook",
			"tw" => "Twitter",
			"goo" => "Google",
			"in" => "LinkedIn",
			"vk" => "Vkontakte",
			"ig" => "Instagram",
			"tbr" => "Tumblr"
	);


	foreach ($arr as $k=>$v){
		?>
		<a class="ihc-subtab-menu-item <?php echo ($item == $k ) ? "ihc-subtab-selected" : ''; ?>" href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=settings&item=' . $k);?>"><?php echo esc_html($v);?></a>
		<?php
	}

	?>
	<div class="ihc-clear"></div>
</div>
<div class="iump-settings-container">
<?php

	if (!empty($_GET['item'])){
		$callbackURL = IHC_URL . 'public/social_handler.php'; // old was site_url()
		switch ($_GET['item']){
			case 'fb':
				if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
						ihc_save_update_metas('fb');
				}

				$meta_arr = ihc_return_meta_arr('fb');
				?>

				<form method="post" class="ihc-social-login-settings-wrapper">

					<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Facebook Settings', 'ihc');?></h3>
						<div class="inside">
						<div class="row ihc-row-no-margin">
							<div class="col-xs-12">
							<div class="iump-form-line iump-no-border">
								<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
									<h2 class="iump-option-title"><?php esc_html_e('Activate Facebook Service', 'ihc');?> </h2>
									<p><?php esc_html_e("Once everything is set up, activate Facebook login to use it.", "ihc");?></p>
								</div>
                  <div class="iump-flex iump-flex--align-center">
								<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_fb_status']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_fb_status');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_fb_status']);?>" name="ihc_fb_status" id="ihc_fb_status" />

						</div>
								</div>
							</div>
						</div>
					</div>
					</div>
				</div>

					<div class="ihc-stuffbox">
						<h4><?php esc_html_e('Facebook Keys', 'ihc');?></h4>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-10">
							<div class="iump-form-line iump-no-border">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Application ID', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_fb_app_id']);?>" name="ihc_fb_app_id" class="form-control" /></div>
							</div>
							<div class="iump-form-line">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Application Secret', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_fb_app_secret']);?>" name="ihc_fb_app_secret" class="form-control" /></div>
							</div>
						</div>
						<div class="col-xs-10">
							<div class="iump-form-line">
								<div><h4><?php esc_html_e("How to create a Facebook App");?></h4></div>

								<ul class="ihc-payment-capabilities-list">
								<li><?php esc_html_e("Go to ", "ihc");?><a href="https://developers.facebook.com/apps" target="_blank">https://developers.facebook.com/apps</a></li>
								<li><?php esc_html_e('Look after \'My Apps\' and \'Add a New App\'.', 'ihc');?></li>
								<li><?php esc_html_e('After complete the name of the app (make sure not to put facebook or fb in app name) click \'Create App ID\'.', 'ihc');?></li>
								<li><?php esc_html_e('In left side area look after \'Settings > Basic\' and fill \'App Domains\' with your site domain. (mywebsite.com,  www.mywebsite.com).', 'ihc');?></li>
								<li><?php esc_html_e('Go back to your website and create 2 pages with Privacy Policy and Terms of Service. Put their URL\'s in Privacy Policy URL and Terms of Service URL in your Facebook app.', 'ihc');?></li>
								<li><?php esc_html_e('Choose a category of the app form \'Category\' list. ');?></li>
								<li><?php esc_html_e('Click on \'+\' from PRODUCTS and set up Facebook Login product. Choose Web platform and set your Site URL with '. site_url() . '.', 'ihc');?></li>
								<li><?php esc_html_e('In Facebook Login product from the left side of the menu click on Settings and make sure that Client OAuth Login and Web OAuth Login are \'Yes\'.', 'ihc');?></li>
								<li><?php esc_html_e('In \' Valid OAuth Redirect URIs\' put the ', 'ihc');?><b><?php  echo esc_url($callbackURL); ?></b> <?php esc_html_e(' url. ', 'ihc');?></li>
								<li><?php esc_html_e('In top of the page your app is in development mode. Switch to Live Mode.', 'ihc');?></li>
								<li><?php esc_html_e('In Facebook app go to  Settings > Basic and copy \'App ID\' and \'App Secret\' and paste it to \'Facebook Settings\' from your website.', 'ihc');?></li>
								<li><?php esc_html_e('In order to activate social login field, go to UMP Dashboard > SHOWCASES > Register Form > Custom Fields page. Make sure that you have checked \'ihc_social_media\' on register page.', 'ihc');?></li>
								<li><?php esc_html_e('Go to UMP Dashboard -> Showcases -> Login Form page. Activate the \'Show Social Media Login Buttons\' option.', 'ihc');?></li>

								</ul>

							</div>
							<div class="iump-form-line">
								<p><b><?php esc_html_e("Notice:", "ihc");?></b></p>
								<p><?php esc_html_e("Ultimate Membership Pro members may synchronized their Facebook account with WP user account from the registration process.", "ihc");?></p>
								<p><?php esc_html_e("Even after the register step, a user can sync multiple social accounts by going to their profile page, under the 'Social Plus' tab.", "ihc");?></p>
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

			case 'tw':
				if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
						ihc_save_update_metas('tw');
				}

				$meta_arr = ihc_return_meta_arr('tw');
				?>

								<form method="post" class="ihc-social-login-settings-wrapper">

									<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

									<div class="ihc-stuffbox">
										<h3><?php esc_html_e('Twitter Settings', 'ihc');?></h3>
										<div class="inside">
										<div class="row ihc-row-no-margin">
										<div class="col-xs-12">
											<div class="iump-form-line iump-no-border">
												<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
													<h2 class="iump-option-title"><?php esc_html_e('Activate Twitter Service', 'ihc');?> </h2>
													<p><?php esc_html_e("Once everything is set up, activate Twitter login to use it.", "ihc");?></p>
												</div>
	                  <div class="iump-flex iump-flex--align-center">
												<label class="iump_label_shiwtch ihc-switch-button-margin">
												<?php $checked = ($meta_arr['ihc_tw_status']) ? 'checked' : '';?>
												<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_tw_status');" <?php echo esc_attr($checked);?> />
												<div class="switch ihc-display-inline"></div>
											</label>
											<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_tw_status']);?>" name="ihc_tw_status" id="ihc_tw_status" />

											</div>
										</div>
							</div>
										</div>
									</div>
							</div>
								</div>
									<div class="ihc-stuffbox">
										<h4><?php esc_html_e('Twitter Keys', 'ihc');?></h4>
										<div class="inside">
											<div class="row ihc-row-no-margin">
											<div class="col-xs-10">
											<div class="iump-form-line iump-no-border">
												<div class="input-group"><span class="input-group-addon"><?php esc_html_e('API key', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_tw_app_key']);?>" name="ihc_tw_app_key" class="form-control" /></div>
											</div>
											<div class="iump-form-line">
												<div class="input-group"><span class="input-group-addon"><?php esc_html_e('API secret key', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_tw_app_secret']);?>" name="ihc_tw_app_secret" class="form-control"/></div>
											</div>
											<div class="iump-form-line">
												<div><h4><?php esc_html_e("How to create a Twitter App");?></h4></div>
												<ul class="ihc-payment-capabilities-list">
												<li><?php esc_html_e("Go to ", "ihc");?><a href="https://developer.twitter.com/" target="_blank">https://developer.twitter.com/</a></li>
												<li><?php esc_html_e('Apply for a developer account by clicking on Developer Portal and complete the fields.', 'ihc');?></li>
												<li><?php esc_html_e('Once your Twitter Developer Account is approved go to Developer Portal and create a project.', 'ihc');?></li>
												<li><?php esc_html_e('Choose the app environment and pick a new name. ', 'ihc'); ?></li>
												<li><?php esc_html_e('In Keys & Tokens app area you will find API Key and API Key secret.', 'ihc');?></li>
												<li><?php esc_html_e('To make API requests, you’ll need to generate API keys from your Twitter Developer App. Go to Consumer Keys section and click on the "Generate/Regenerate".', 'ihc');?></li>
												<li><?php esc_html_e('Generate Access Tokens and Secret. It is recommended to save them.', 'ihc');?></li>
												<li><?php esc_html_e('Go back to your app and click on Settings. Edit "User authentication settings".', 'ihc');?></li>
												<li><?php esc_html_e('In App permissions select "Read" and check "Request email from users". In Type of App select "Web App, Automated App or Bot". In App info add ', 'ihc');?> <?php echo esc_ump_content('<b>'.$callbackURL.'</b>'); ?></li>
												</ul>
											</div>
											<div class="iump-form-line">
												<p><b><?php esc_html_e("Notice:", "ihc");?></b></p>
												<p><?php esc_html_e("Ultimate Membership Pro members may synchronized their Twitter account with WP user account from the registration process.", "ihc");?></p>
												<p><?php esc_html_e("Even after the register step, a user can sync multiple social accounts by going to their profile page, under the 'Social Plus' tab.", "ihc");?></p>
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

			case 'in':
				if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
						ihc_save_update_metas('in');
				}

				$meta_arr = ihc_return_meta_arr('in');
				?>

							<form method="post" class="ihc-social-login-settings-wrapper">

								<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

								<div class="ihc-stuffbox">
									<h3><?php esc_html_e('LinkedIn Settings', 'ihc');?></h3>
									<div class="inside">
										<div class="row ihc-row-no-margin">
										<div class="col-xs-12">
										<div class="iump-form-line iump-no-border">
											<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
												<h2 class="iump-option-title"><?php esc_html_e('Activate LinkedIn Service', 'ihc');?> </h2>
												<p><?php esc_html_e("Once everything is set up, activate LinkedIn login to use it.", "ihc");?></p>
											</div>
                  <div class="iump-flex iump-flex--align-center">
											<label class="iump_label_shiwtch ihc-switch-button-margin">
												<?php $checked = ($meta_arr['ihc_in_status']) ? 'checked' : '';?>
												<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_in_status');" <?php echo esc_attr($checked);?> />
												<div class="switch ihc-display-inline"></div>
											</label>
											<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_in_status']);?>" name="ihc_in_status" id="ihc_in_status" />
										</div>
									</div>
							</div>
									</div>
								</div>
							</div>
						</div>
								<div class="ihc-stuffbox">
									<h4><?php esc_html_e('LinkedIn Keys', 'ihc');?></h4>
									<div class="inside">
										<div class="row ihc-row-no-margin">
										<div class="col-xs-10">
										<div class="iump-form-line iump-no-border">
											<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Client ID', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_in_app_key']);?>" name="ihc_in_app_key" class="form-control" /></div>
										</div>
										<div class="iump-form-line">
											<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Client Secret', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_in_app_secret']);?>" name="ihc_in_app_secret" class="form-control" /></div>
										</div>

										<div class="iump-form-line">
											<div><h4><?php esc_html_e("How to create a LinkedIn App");?></h4></div>
											<ul class="ihc-payment-capabilities-list">
											<li><?php esc_html_e("Go to ", "ihc");?><a href="https://www.linkedin.com/secure/developer" target="_blank">https://www.linkedin.com/secure/developer</a></li>
											<li><?php esc_html_e('Click "Create app".', 'ihc');?></li>
											<li><?php esc_html_e('In \'LinkedIn Page*\' you have to add an existent LinkedIn company name or create a new company page. Complete the field with company name.', 'ihc');?></li>
                      <li><?php esc_html_e('Once the app has been created in \'Settings\' tab you must verify the company. Clik on ', 'ihc');?> <b><?php esc_html_e(' Verify ', 'ihc');?></b><?php esc_html_e(' and \'Generate URL\'. Open the URL in a browser and click on \'Verify\'.', 'ihc');?></li>
											<li><?php esc_html_e( 'In \'OAuth 2.0 settings\' add ' , 'ihc' );?> <b><?php echo esc_url($callbackURL);?></b> <?php esc_html_e(' redirect URL.' , 'ihc' );?></li>
											<li><?php esc_html_e('In \'Products\' select \'Sign In with LinkedIn\' product.', 'ihc');?></li>
											<li><?php esc_html_e('In order to activate social login field, go to UMP Dashboard > SHOWCASES > Register Form > Custom Fields page. Make sure that you have checked ihc_social_media on register page.', 'ihc');?></li>
											<li><?php esc_html_e('Go to UMP Dashboard -> Showcases -> Login Form page. Activate the Show Social Media Login Buttons option.', 'ihc');?></li>
											</ul>
										</div>
										<div class="iump-form-line">
											<p><b><?php esc_html_e("Notice:", "ihc");?></b></p>
											<p><?php esc_html_e("Ultimate Membership Pro members may synchronized their LinkedIn accounts with WP user account from the registration process.", "ihc");?></p>
											<p><?php esc_html_e("Even after the register step, a user can sync multiple social accounts by going to their profile page, under the 'Social Plus' tab.", "ihc");?></p>
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

			case 'tbr':
					if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
							ihc_save_update_metas('tbr');
					}

					$meta_arr = ihc_return_meta_arr('tbr');
					?>

					<form method="post" class="ihc-social-login-settings-wrapper">

						<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

						<div class="ihc-stuffbox">
							<h3><?php esc_html_e('Tumblr Settings', 'ihc');?></h3>
							<div class="inside">
								<div class="row ihc-row-no-margin">
								<div class="col-xs-12">
								<div class="iump-form-line iump-no-border">
									<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
									<h2 class="iump-option-title"><?php esc_html_e('Activate Tumblr Service', 'ihc');?> </h2>
										<p><?php esc_html_e("Once everything is set up, activate Tumblr login to use it.", "ihc");?></p>
									</div>
								<div class="iump-flex iump-flex--align-center">
									<label class="iump_label_shiwtch ihc-switch-button-margin">
										<?php $checked = ($meta_arr['ihc_tbr_status']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_tbr_status');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_tbr_status']);?>" name="ihc_tbr_status" id="ihc_tbr_status" />

								</div>
							</div>
						</div>
							</div>
						</div>
				</div>
						</div>

						<div class="ihc-stuffbox">
							<h4><?php esc_html_e('Tumblr Keys', 'ihc');?></h4>
							<div class="inside">
								<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
								<div class="iump-form-line iump-no-border">
									<div class="input-group"><span class="input-group-addon"><?php esc_html_e('OAuth consumer key', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_tbr_app_key']);?>" name="ihc_tbr_app_key" class="form-control" /></div>
								</div>
								<div class="iump-form-line">
									<div class="input-group"><span class="input-group-addon"><?php esc_html_e('OAuth consumer secret', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_tbr_app_secret']);?>" name="ihc_tbr_app_secret" class="form-control" /></div>
								</div>

								<div class="iump-form-line">
									<div><h4><?php esc_html_e("How to create a Tumblr App");?></h4></div>
									<ul class="ihc-payment-capabilities-list">
									<li><?php esc_html_e("Go to ", "ihc");?><a href="http://www.tumblr.com/oauth/apps" target="_blank">http://www.tumblr.com/oauth/apps</a></li>
									<li><?php esc_html_e('Register a new application.', 'ihc');?>
									<li><?php esc_html_e("Fill out the required fields and submit.", 'ihc');?></li>
									<li><?php esc_html_e('Set the "Default callback URL:" as: ', 'ihc'); echo esc_ump_content('<b>' . $callbackURL. '</b>');?></li>
									<li><?php esc_html_e('After submitting you will find "OAuth consumer key" and "OAuth consumer secret" in the right side of the screen.', 'ihc');?></li>
									<li><?php esc_html_e('In order to activate social login field, go to UMP Dashboard > SHOWCASES > Register Form > Custom Fields page. Make sure that you have checked ihc_social_media on register page.', 'ihc');?></li>
									<li><?php esc_html_e('Go to UMP Dashboard -> Showcases -> Login Form page. Activate the Show Social Media Login Buttons option.', 'ihc');?></li>
									</ul>
								</div>
								<div class="iump-form-line">
									<p><b><?php esc_html_e("Notice:", "ihc");?></b></p>
									<p><?php esc_html_e("Ultimate Membership Pro members may synchronized their Tumblr accounts with WP user account from the registration process.", "ihc");?></p>
									<p><?php esc_html_e("Even after the register step, a user can sync multiple social accounts by going to their profile page, under the 'Social Plus' tab.", "ihc");?></p>
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
			case 'ig':
				if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
						ihc_save_update_metas('ig');
				}

				$meta_arr = ihc_return_meta_arr('ig');
				?>

				<form method="post" class="ihc-social-login-settings-wrapper">

					<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Instagram Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-12">
							<div class="iump-form-line iump-no-border">
								<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
									<h2 class="iump-option-title"><?php esc_html_e('Activate Instagram Service', 'ihc');?> </h2>
									<p><?php esc_html_e("Once everything is set up, activate Instagram login to use it.", "ihc");?></p>
								</div>
                  <div class="iump-flex iump-flex--align-center">
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<?php $checked = ($meta_arr['ihc_ig_status']) ? 'checked' : '';?>
									<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_ig_status');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_ig_status']);?>" name="ihc_ig_status" id="ihc_ig_status" />

								</div>
                </div>
							</div>
						</div>
					</div>
			</div>
			</div>
					<div class="ihc-stuffbox">
						<h4><?php esc_html_e('Instagram Keys', 'ihc');?></h4>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-10">
							<div class="iump-form-line iump-no-border">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Client ID', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_ig_app_id']);?>" name="ihc_ig_app_id" class="form-control" /></div>
							</div>
							<div class="iump-form-line">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Client Secret', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_ig_app_secret']);?>" name="ihc_ig_app_secret" class="form-control" /></div>
							</div>
							<div class="iump-form-line">
								<div><h4><?php esc_html_e("How to create a Instagram App");?></h4></div>
								<ul class="ihc-payment-capabilities-list">
								<li><?php esc_html_e("Go to ", "ihc");?><a href="https://developers.facebook.com/apps" target="_blank">https://developers.facebook.com/apps</a></li>
								<li><?php esc_html_e('Create a new App.', 'ihc');?></li>
								<li><?php esc_html_e('In Dashboard click on Settings > Basic and fill \'App Domains\' with your site domain (mywebsite.com,  www.mywebsite.com).', 'ihc');?></li>
								<li><?php esc_html_e('Go back to your website and create 2 pages with Privacy Policy and Terms of Service. Put their URL\'s in \'Privacy Policy URL\' and \'Terms of Service URL\' in your Facebook app.', 'ihc');?></li>
								<li><?php esc_html_e('Choose a category of the app from \'Category\' list.', 'ihc');?></li>
								<li><?php esc_html_e('Click on \'+ Add Platform\', choose \'Website\' and add ', 'ihc'); echo esc_ump_content('<b>' . site_url() . '</b>'); ?></li>
								<li><?php esc_html_e('Click on \'+\' from \'PRODUCTS\' and set up an \'Instagram Basic Display\' product. Click on \'Create New App\', name the app and create it.', 'ihc');?></li>
								<li><?php esc_html_e('Go back to the left side of the dashboard and In \'Instagram Basic Display\' > Basic Display you will find \'Instagram App ID\' and \'Instagram App Secret\'.', 'ihc');?></li>
								<li><?php esc_html_e('In \'Client OAuth Settings\' put the ', 'ihc');?><b><?php echo IHC_URL . 'public/social_handler.php';?></b> </li>
								<li><?php esc_html_e(' In \'Deauthorize\' and \'Data Deletion Requests\' put the ', 'ihc'); echo esc_ump_content('<b>' . $callbackURL . '</b>');?></li>
								<li><?php esc_html_e('Add to Submission \'instagram_graph_user_profile\' and \'instagram_graph_user_media\' from \'App Review for Instagram Basic Display\'.', 'ihc');?></li>

								<li><?php esc_html_e('In order to test your app go to \'Roles\' and in \'Instagram Testers\' add your instagram username.', 'ihc');?></li>
								<li><?php esc_html_e('Log in in Instagram, navigate to (Profile Icon) > Edit Profile > \'Apps and Websites\' > Tester Invites and accept the invitation.', 'ihc');?></li>
								<li><?php esc_html_e('Your Instagram account is now eligible to be accessed by your Facebook app while it is in \'Development Mode\'.', 'ihc');?></li>
								<li><?php esc_html_e('To get the full access of your app go to Dashboard click on settings > Basic and verify your Business on Facebook.', 'ihc');?></li>
								<li><?php esc_html_e('In order to activate social login field, go to UMP Dashboard > SHOWCASES > Register Form > Custom Fields page. Make sure that you have checked \'ihc_social_media\' on register page.', 'ihc');?></li>
								<li><?php esc_html_e('Go to UMP Dashboard -> Showcases -> Login Form page. Activate the \'Show Social Media Login Buttons\' option.', 'ihc');?></li>
								</ul>
							</div>
							<div class="iump-form-line">
								<p><b><?php esc_html_e("Notice:", "ihc");?></b></p>
								<p><?php esc_html_e("Ultimate Membership Pro members may synchronized their Instagram accounts with WP user account from the registration process.", "ihc");?></p>
								<p><?php esc_html_e("Even after the register step, a user can sync multiple social accounts by going to their profile page, under the 'Social Plus' tab.", "ihc");?></p>
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
			case 'vk':
				if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
						ihc_save_update_metas('vk');
				}

				$meta_arr = ihc_return_meta_arr('vk');
				?>

				<form method="post" class="ihc-social-login-settings-wrapper">

					<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Vkontakte Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-12">
							<div class="iump-form-line iump-no-border">
								<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
									<h2 class="iump-option-title"><?php esc_html_e('Activate Vkontakte Service', 'ihc');?> </h2>
									<p><?php esc_html_e("Once everything is set up, activate Vkontakte login to use it.", "ihc");?></p>
								</div>
								<div class="iump-flex iump-flex--align-center">
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<?php $checked = ($meta_arr['ihc_vk_status']) ? 'checked' : '';?>
									<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_vk_status');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_vk_status']);?>" name="ihc_vk_status" id="ihc_vk_status" />
							</div>
							</div>
							</div>
						</div>
					</div>
			</div>
					</div>
					<div class="ihc-stuffbox">
						<h4><?php esc_html_e('Vkontakte Keys', 'ihc');?></h4>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-10">
							<div class="iump-form-line iump-no-border">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Application ID', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_vk_app_id']);?>" name="ihc_vk_app_id" class="form-control" /></div>
							</div>
							<div class="iump-form-line">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Application Secret', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_vk_app_secret']);?>" name="ihc_vk_app_secret" class="form-control" /></div>
							</div>

							<div class="iump-form-line">
								<div><h4><?php esc_html_e("How to create a VK App", 'ihc');?></h4></div>
								<ul class="ihc-payment-capabilities-list">
								<li><?php esc_html_e("Go to ", "ihc");?><a href="http://vk.com/developers.php" target="_blank">http://vk.com/developers.php</a></li>
								<li><?php esc_html_e('In top of the page click on ' , 'ihc');?> <b> <?php esc_html_e(' My Apps ', 'ihc');?></b> <?php esc_html_e(" and ", 'ihc');?> <b> <?php esc_html_e("Create app", "ihc");?></b></li>
								<li><?php esc_html_e('In \'Platform\' section you must select Website.', 'ihc');?></li>
								<li><?php esc_html_e('Connect website.', 'ihc');?></li>
								<li><?php esc_html_e('In Contact info section add ', 'ihc');?> <b><?php esc_html_e('Terms and Conditions', 'ihc'); ?></b> <?php esc_html_e('and', 'ihc');?> <b><?php esc_html_e('Privacy Policy ', 'ihc');?></b><?php esc_html_e('pages.', 'ihc');?></li>
								<li><?php esc_html_e(' Click on Settings menu tab and make sure that ', 'ihc');?>  <b><?php esc_html_e('App status', 'ihc');?></b> <?php esc_html_e(' is \'Application on and visible to all\'', 'ihc');?></li>
								<li><?php esc_html_e('In Authorized redirect URI add ', 'ihc'); echo esc_ump_content('<b>'.$callbackURL.'</b>');?></li>
								<li><?php esc_html_e('In order to activate social login field, go to UMP Dashboard > SHOWCASES > Register Form > Custom Fields page. Make sure that you have checked ihc_social_media on register page.', 'ihc');?></li>
								<li><?php esc_html_e('Go to UMP Dashboard -> Showcases -> Login Form page. Activate the','ihc');?> <b><?php esc_html_e('Show Social Media Login Buttons ', 'ihc');?></b><?php esc_html_e('option', 'ihc'); ?></li>
								</ul>
							</div>
							<div class="iump-form-line">
								<p><b><?php esc_html_e('Notice', 'ihc');?></b></p>
								<p><?php esc_html_e("Ultimate Membership Pro members may synchronized their Vkontakte accounts with WP user account from the registration process.", "ihc");?></p>
								<p><?php esc_html_e('Even after the register step, a user can sync multiple social accounts by going to their profile page, under the \'Social Plus\' ', 'ihc');?></p>
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

			case 'goo':
				if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
						ihc_save_update_metas('goo');
				}

				$meta_arr = ihc_return_meta_arr('goo');
				?>

				<form method="post" class="ihc-social-login-settings-wrapper">

					<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Google Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-12">
							<div class="iump-form-line iump-no-border">
								<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
									<h2 class="iump-option-title"><?php esc_html_e('Activate Google Service', 'ihc');?> </h2>
									<p><?php esc_html_e("Once everything is set up, activate Google login to use it.", "ihc");?></p>
								</div>
								<div class="iump-flex iump-flex--align-center">
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<?php $checked = ($meta_arr['ihc_goo_status']) ? 'checked' : '';?>
									<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_goo_status');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_goo_status']);?>" name="ihc_goo_status" id="ihc_goo_status" />
							</div>
						</div>
							</div>
						</div>
					</div>
			</div>
					</div>
					<div class="ihc-stuffbox">
						<h4><?php esc_html_e('Google Keys', 'ihc');?></h4>
							<div class="inside">
								<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
								<div class="iump-form-line iump-no-border">
									<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Application ID', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_goo_app_id']);?>" name="ihc_goo_app_id" class="form-control" /></div>
								</div>
								<div class="iump-form-line">
									<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Application Secret', 'ihc');?></span> <input type="text" value="<?php echo esc_attr($meta_arr['ihc_goo_app_secret']);?>" name="ihc_goo_app_secret" class="form-control" /></div>
								</div>

							<div class="iump-form-line">
								<div><h4><?php esc_html_e("How to create a Google App");?></h4></div>
								<?php
										$siteUrl = site_url();
						        //$siteUrl = trailingslashit($siteUrl);
								?>
								<ul class="ihc-payment-capabilities-list">
								<li><?php esc_html_e("Go to ", "ihc");?><a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a></li>
								<li><?php esc_html_e("Create new project.", 'ihc');?></li>
								<li><?php esc_html_e('Click on \'OAuth consent screen\'.', 'ihc');?></li>
								<li><?php esc_html_e('Choose \'External\' in User Type section.', 'ihc');?></li>
								<li><?php esc_html_e('Fill all the reqired fields.', 'ihc');?></li>
								<li><?php esc_html_e('In \'Authorized domains\' you may add your website domain (mywebsite.com).', 'ihc');?></li>
								<li><?php esc_html_e('Return to Credentials from left sidebar menu, and create an OAuth client ID in \' CREATE CREDENTIALS\'.', 'ihc');?></li>
								<li><?php esc_html_e('In \'Create OAuth client ID\' select Web application. Add callback URL ', 'ihc');?><b><?php echo esc_url($siteUrl . "?ihc_action=social_login"); ?></b><?php esc_html_e(' in \'Authorized redirect URIs\' ', 'ihc'); ?></li>
								<li><?php esc_html_e('After submitting a popup will appear with \'Your Client ID\' and \'Your Client Secret\'.', 'ihc');?></li>
								<li><?php esc_html_e("In 'Domain verification' add a domain to configure webhook notifications.", 'ihc');?></li>
								<li><?php esc_html_e("In order to activate social login field, go to UMP Dashboard > SHOWCASES > Register Form > Custom Fields page. Make sure that you have checked ", "ihc");?> <b>ihc_social_media</b><?php esc_html_e(' on register page.', 'ihc');?></li>
								<li><?php esc_html_e('Go to UMP Dashboard -> Showcases -> Login Form page. Activate the \'Show Social Media Login Buttons\' option.', 'ihc');?></li>
								</ul>
							</div>
							<div class="iump-form-line">
								<p><b><?php esc_html_e("Notice:", "ihc");?></b></p>
								<p><?php esc_html_e("Ultimate Membership Pro members may synchronized their Google accounts with WP user account from the registration process.", "ihc");?></p>
								<p><?php esc_html_e("Even after the register step, a user can sync multiple social accounts by going to their profile page, under the 'Social Plus' tab.", "ihc");?></p>
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
	} else {
	//===================== DESIGN
	if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_social_login_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_social_login_nonce']), 'ihc_admin_social_login_nonce' ) ){
			ihc_save_update_metas('social_media');//save update metas
	}

	$meta_arr = ihc_return_meta_arr('social_media');//getting metas
	?>

		<form method="post" class="ihc-social-login-settings-wrapper">

			<input type="hidden" name="ihc_admin_social_login_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_social_login_nonce' );?>" />

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e("Display Settings", "ihc");?></h3>
				<div class="inside">
					<div class="row ihc-row-no-margin">
					<div class="col-xs-12">
					<div class="iump-form-line">
						<h2><?php esc_html_e("Social Buttons Template", 'ihc');?></h2>
						<p><?php esc_html_e("Choose one of predefined Templates for Social Network buttons that will show up into Front-end Showcases", 'ihc');?></p>
							<select name="ihc_sm_template"><?php
								$templates = array("ihc-sm-template-1" => "Awesome Template One","ihc-sm-template-2" => "Split Box Template","ihc-sm-template-3" => "Shutter Color Template","ihc-sm-template-4" => "Margarita Template","ihc-sm-template-5" => "Picaso Template");
								foreach ($templates as $k=>$v){
									$selected = ($meta_arr['ihc_sm_template']==$k) ? "selected" : '';
									?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
									<?php
								}
							?></select>
					</div>
					<div class="iump-form-line">
						<div class="iump-grid iump-grid--gap-big">
                    <div class="iump-option-wrapper">
						<h4><?php esc_html_e("Show Label on Buttons", 'ihc');?></h4>
						<p><?php esc_html_e("You may display only the Social Icons or Social Network Name also", 'ihc');?></p>
					</div>
								<div class="iump-flex iump-flex--align-center">
						<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = (!empty($meta_arr['ihc_sm_show_label'])) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_sm_show_label');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
						</label>
						<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_sm_show_label']);?>" name="ihc_sm_show_label" id="ihc_sm_show_label" />
					</div>
							</div>
					</div>
				</div>
			</div>
	</div>
	</div>
			<div class="ihc-stuffbox">
				<h2><?php esc_html_e("Above Content", 'ihc');?></h2>
				<div class="inside">
					<div class="row ihc-row-no-margin">
					<div class="col-xs-10">
					<div class="iump-form-line">
					<p><?php esc_html_e("Above Social Section text that will show up into Login Form", 'ihc');?></p>
						<?php
							$settings = array(
												'media_buttons' => true,
												'textarea_name'=>'ihc_sm_top_content',
												'textarea_rows' => 5,
												'tinymce' => true,
												'quicktags' => true,
												'teeny' => true,
											);
							wp_editor(ihc_correct_text($meta_arr['ihc_sm_top_content']), 'tag-description', $settings);
						?>
					</div>

				</div>
			</div>
	</div>
			</div>
			<div class="ihc-stuffbox">
				<h2><?php esc_html_e("Below Content", 'ihc');?></h2>
				<div class="inside">
					<div class="row ihc-row-no-margin">
					<div class="col-xs-10">
					<div class="iump-form-line">
					<p><?php esc_html_e("Below Social Section text that will show up into Login Form", 'ihc');?></p>
						<?php
							$settings = array(
												'media_buttons' => true,
												'textarea_name'=>'ihc_sm_bottom_content',
												'textarea_rows' => 5,
												'tinymce' => true,
												'quicktags' => true,
												'teeny' => true,
											);
							wp_editor( ihc_correct_text($meta_arr['ihc_sm_bottom_content']), 'ihc_sm_bottom_content', $settings );
						?>
					</div>
				</div>
			</div>
	</div>
			</div>
			<div class="ihc-stuffbox iump-custom-css-box-wrapper">
				<h3><?php esc_html_e("Custom CSS", 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line">
						<textarea name="ihc_sm_custom_css"  id="ihc_sm_custom_css" class="ihc-dashboard-textarea-full"><?php echo esc_html($meta_arr['ihc_sm_custom_css']);?></textarea>
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
}
?>
</div>
<div class="ihc-clear"></div>
</div>
</div>
<?php
