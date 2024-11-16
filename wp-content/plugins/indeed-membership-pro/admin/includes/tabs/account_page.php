<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
if (isset($_POST['ihc_save']) && !empty( $_POST['ihc_admin_account_page_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_account_page_nonce']), 'ihc_admin_account_page_nonce' ) ){
	//update/save
	ihc_save_update_metas('account_page');
	Ihc_Db::account_page_save_tabs_details( indeed_sanitize_textarea_array( $_POST ) );
}

$meta_arr = ihc_return_meta_arr('account_page');
$temp = Ihc_Db::account_page_get_tabs_details();
if ($temp){
	$meta_arr = array_merge($meta_arr, $temp);
}
$font_awesome = Ihc_Db::get_font_awesome_codes();

$custom_tabs = Ihc_Db::account_page_menu_get_custom_items();
$available_tabs = Ihc_Db::account_page_get_menu();
$custom_css = '';

 foreach ($font_awesome as $base_class => $code):
	$custom_css .= "." . $base_class . ":before{".
		"content: '\\".$code."';".
	"}";
endforeach;
if ($available_tabs):
foreach ($available_tabs as $slug => $array):

	$custom_css .=  ".fa-" . $slug . "-account-ihc:before{".
		"content: '\\". $array['icon']."';".
	"}";

endforeach;
endif;

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );

wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
 ?>

<div class="iump-page-headline"><?php esc_html_e('Member Portal Showcase', 'ihc');?></div>
			<div class="impu-shortcode-display-wrapper">
				<div class="impu-shortcode-display">
					[ihc-user-page]
				</div>
			</div>
<div class="metabox-holder indeed ihc-admin-account-page">
<form  method="post">
	<input type="hidden" name="ihc_admin_account_page_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_account_page_nonce' );?>" />

	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Top Section', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-register-select-template">
				<?php esc_html_e('Select Template', 'ihc');?>
				<select name="ihc_ap_top_template">
					<?php
					$themes = array(
											'ihc-ap-top-theme-1' => '(#1) '.esc_html__('Basic Full Background Theme', 'ihc'),
											'ihc-ap-top-theme-2' => '(#2) '.esc_html__('Square Top Image Theme', 'ihc'),
											'ihc-ap-top-theme-3' => '(#3) '.esc_html__('Rounded Big Image Theme', 'ihc'),
											'ihc-ap-top-theme-4' => '(#4) '.esc_html__('Modern OverImage Theme', 'ihc'),
					);
					foreach ($themes as $k=>$v){
						?>
						<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_ap_top_template']==$k){
							 echo esc_attr('selected');
						}?> ><?php echo esc_html($v);?></option>
						<?php
					}
				?></select>
			</div>

			<div class="inside">

					<div class="iump-form-line iump-no-border">
						<h4><?php esc_html_e('Member Banner Image', 'ihc');?></h4>
						<p><?php esc_html_e('The cover or background image, based on what Template you have chosen. This section is achievable also with ','ihc');?> <strong>[ihc-user-banner]</strong><?php esc_html_e(' shortcode.', 'ihc');?></p>
						<label class="iump_label_shiwtch ihc-switch-button-margin">
							<?php if (!isset($meta_arr['ihc_ap_edit_background'])){
								$meta_arr['ihc_ap_edit_background'] = 1;
							} ?>
							<?php $checked = ($meta_arr['ihc_ap_edit_background']==1) ? 'checked' : '';?>
							<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_ap_edit_background');" <?php echo esc_attr($checked);?> />
							<div class="switch ihc-display-inline"></div>
						</label>
						<input type="hidden" name="ihc_ap_edit_background" value="<?php echo esc_attr($meta_arr['ihc_ap_edit_background']);?>" id="ihc_ap_edit_background"/>

						<div class="row">
						<div class="col-xs-4">
							<p><?php esc_html_e('Upload a custom Banner image to replace the default one.', 'ihc');?></p>
							<div class="input-group" >
								<input type="text" class="form-control ihc-background-image" onClick="openMediaUp(this);" value="<?php  echo esc_attr($meta_arr['ihc_ap_top_background_image']);?>" name="ihc_ap_top_background_image" id="ihc_ap_top_background_image" />
								<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-top-bacgrkound-image-delete" title="<?php esc_html_e('Remove Background Image', 'ihc');?>"></i>
							</div>
					</div>
					</div>
				</div>

				<div class="iump-form-line iump-no-border">
					<h4><?php esc_html_e('Member Avatar Image', 'ihc');?></h4>
					<p><?php esc_html_e('If Members have the option to upload their own Avatar, this one can show on Member Portal. You can display the Avatar Image anywhere else by using ', 'ihc');?> <strong>	[ihc-user field="ihc_avatar"]</strong><?php esc_html_e(' shortcode.', 'ihc');?></p>
					<label class="iump_label_shiwtch iump-onbutton ihc-switch-button-margin">
						<?php $checked = ($meta_arr['ihc_ap_edit_show_avatar']) ? 'checked' : '';?>
						<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_ap_edit_show_avatar');" <?php echo esc_attr($checked);?> />
						<div class="switch ihc-display-inline"></div>
					</label>
					<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_ap_edit_show_avatar']);?>" name="ihc_ap_edit_show_avatar" id="ihc_ap_edit_show_avatar" />
				</div>

				<div class="iump-form-line iump-no-border">
					<h4><?php esc_html_e('Display Member Memberships', 'ihc');?></h4>
					<p><?php esc_html_e('Members may see their signed Memberships directly the top of Member Portal', 'ihc');?></p>
					<label class="iump_label_shiwtch iump-onbutton ihc-switch-button-margin">
						<?php $checked = ($meta_arr['ihc_ap_edit_show_level']) ? 'checked' : '';?>
						<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_ap_edit_show_level');" <?php echo esc_attr($checked);?> />
						<div class="switch ihc-display-inline"></div>
					</label>
					<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_ap_edit_show_level']);?>" name="ihc_ap_edit_show_level" id="ihc_ap_edit_show_level" />

				</div>

				<div class="iump-form-line iump-no-border">
				<h2><?php esc_html_e('Welcome Message', 'ihc');?></h2>
				<p><?php esc_html_e('Customize the Top Message with Member personal information', 'ihc');?></p>
				<div class="iump-wp_editor">
				<?php wp_editor(stripslashes($meta_arr['ihc_ap_welcome_msg']), 'ihc_ap_welcome_msg', array('textarea_name'=>'ihc_ap_welcome_msg', 'editor_height'=>300));?>
				</div>
				<div class="iump-wp_editor-constants">
				<h4><?php esc_html_e('Template Tags', 'ihc');?></h4>
					<?php
						$constants = array( '{username}'=>'',
											'{user_email}'=>'',
											'{user_id}'		=> '',
											'{first_name}'=>'',
											'{last_name}'=>'',
											'{account_page}'=>'',
											'{login_page}'=>'',
											'{level_list}'=>'',
											'{blogname}'=>'',
											'{blogurl}'=>'',
											'{ihc_avatar}' => '',
											'{current_date}' => '',
											'{user_registered}' => '',
											'{flag}' => '',
						);
						$extra_constants = ihc_get_custom_constant_fields();
						?>
						<div class="ump-js-list-constants">
						<?php foreach ($constants as $k=>$v){ ?>
							<div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_ap_welcome_msg" ><?php echo esc_html($k);?></span></div>
							<?php	}?>
						</div>
						</div>
						<div class="iump-wp_editor-constants-coltwo">
							<h4><?php esc_html_e('Custom Fields Tags', 'ihc');?></h4>
							<div class="iump-wp_editor-constants-colthree">
							<div class="ump-js-list-constants">
						<?php
						$i = 1;
						$half = round(count($extra_constants)/2);
						foreach ($extra_constants as $k=>$v){
							?>
							<div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_ap_welcome_msg"><?php echo esc_html($k);?></span></div>
							<?php
							$i++;
							if($i == $half){
								?>
								</div>
								</div>
								<div class="iump-wp_editor-constants-colfour">
									<div class="ump-js-list-constants">
								<?php
							}
							}
							?>
							</div>
						</div>
					</div>
				<div class="ihc-clear"></div>
			</div>


				<div class="ihc-wrapp-submit-bttn">
					<input type="submit" id="ihc_submit_bttn" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>

			</div>
		</div>
	</div>

	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Content Section', 'ihc');?></h3>
			  <div class="inside">

				<div class="iump-register-select-template">
					<?php esc_html_e('Select Template', 'ihc');?>
					<select name="ihc_ap_theme" ><?php
						$themes = array(
												'ihc-ap-theme-1' => '(#1) '.esc_html__('Blue New Theme', 'ihc'),
												'ihc-ap-theme-2' => '(#2) '.esc_html__('Dark Theme', 'ihc'),
												'ihc-ap-theme-3' => '(#3) '.esc_html__('Mega Icons', 'ihc'),
												'ihc-ap-theme-4' => '(#4) '.esc_html__('Ultimate Member', 'ihc'),
						);
						foreach ($themes as $k=>$v){
							?>
							<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_ap_theme']==$k){
								 echo esc_attr('selected');
							}
							?> ><?php echo esc_html($v);?></option>
							<?php
						}
					?></select>
				</div>
				<div class="iump-form-line iump-no-border">
					<h2 class="ihc-myaccount-title"><?php esc_html_e('Member Portal Menu', 'ihc');?></h2>
					<p><?php esc_html_e('Customize the content of each predefined or custom Tab from Member Portal. If you want to add extra custom Tabas or reorder them check the ', 'ihc');?> <strong>Extensions->Member Portal Tabs</strong> <?php esc_html_e(' module', 'ihc');?></p>
				</div>

				<?php
					if (!ihc_is_magic_feat_active('gifts')){
						unset($available_tabs['membeship_gifts']);
					}
					if (!ihc_is_magic_feat_active('membership_card')){
						unset($available_tabs['membership_cards']);
					}

					if (!ihc_is_magic_feat_active('pushover')){
						unset($available_tabs['pushover_notifications']);
					}

					if (!ihc_is_magic_feat_active('user_sites')){
						unset($available_tabs['user_sites']);
					}

					$tabs = explode(',', $meta_arr['ihc_ap_tabs']);

					?>
					<div class="ihc-ap-tabs-wrapper">
						<div class="ihc-ap-tabs-list">
							<?php foreach ($available_tabs as $k=>$v):?>
								<div class="ihc-ap-tabs-list-item" onClick="ihcApMakeVisible('<?php echo esc_attr($k);?>', this);" id="<?php echo esc_attr('ihc_tab-' . $k);?>">
									<?php $icon_class = 'fa-ihc fa-' . $k . '-account-ihc'; ?>
	                <i class="<?php echo esc_attr($icon_class); ?>"></i>
									<?php echo esc_html($v['label']);?>
								</div>
							<?php endforeach;?>
							<div class="ihc-clear"></div>
						</div>
						<div class="ihc-ap-tabs-settings">
					<?php
$i = 0;
					foreach ($available_tabs as $k=>$v){
						?>

							<div class="ihc-ap-tabs-settings-item" id="<?php echo esc_attr('ihc_tab_item_' . $k);?>">

								<div class="iump-form-line iump-no-border">
									<h2><?php echo esc_html($v['label']);?></h2>
										<div class="ihc-ap-tabs-item">
											<p><?php esc_html_e('Show/Hide', 'ihc');?> <?php echo esc_html($v['label']);?> <?php esc_html_e('from Member Portal', 'ihc');?></p>
											<label class="iump_label_shiwtch ihc-switch-button-margin">
												<?php $checked = (in_array($k, $tabs)) ? 'checked' : '';?>
												<input type="checkbox" class="iump-switch" onClick="ihcMakeInputhString(this, '<?php echo esc_attr($k);?>', '#ihc_ap_tabs');" <?php echo esc_attr($checked);?> />
												<div class="switch ihc-display-inline"></div>
											</label>
										</div>
							 	</div>
									<?php
										if (empty($meta_arr['ihc_ap_' . $k . '_menu_label'])){
											$meta_arr['ihc_ap_' . $k . '_menu_label'] = '';
										}
									?>
									<div class="iump-form-line iump-no-border">
									<div class="row">
                	<div class="col-xs-12">

										<div class="input-group">
											<span class="ihc-icon-select-box">
												<div class="ihc-icon-select-wrapper">
													<div class="ihc-icon-input">
														<div id="<?php echo esc_attr('indeed_shiny_select_' . $k);?>" class="ihc-shiny-select-html"></div>
													</div>
														<div class="ihc-icon-arrow ihc-display-none" id="<?php echo esc_attr('ihc_icon_arrow_' . $k);?>"><i class="fa-ihc fa-arrow-ihc"></i></div>
													<div class="ihc-clear"></div>
												</div>
											</span>
											<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Menu Label', 'ihc');?></span>
											<input type="text" class="form-control" placeholder="" value="<?php echo esc_attr($meta_arr['ihc_ap_' . $k . '_menu_label']);?>" name="<?php echo esc_attr('ihc_ap_' . $k . '_menu_label');?>">
										</div>

										<span class="ihc-js-data-for-indeed-shinny-select" data-type="<?php echo esc_attr($k);?>" data-value="<?php echo esc_attr($meta_arr['ihc_ap_' . $k . '_icon_code']);?>" ></span>

									</div>
									</div>
								</div>
									<?php
									if (empty($meta_arr['ihc_ap_' . $k . '_title'])){
										$meta_arr['ihc_ap_' . $k . '_title'] = '';
									}
									?>
									<?php if ($k!='logout'):?>
										<div class="iump-form-line iump-no-border">
										<div class="row">
	                	<div class="col-xs-12">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Tab Title', 'ihc');?></span>
											<input type="text" class="form-control" placeholder="" value="<?php echo esc_attr($meta_arr['ihc_ap_' . $k . '_title']);?>" name="<?php echo esc_attr('ihc_ap_' . esc_attr($k) . '_title');?>">
										</div>
									</div>
									</div>
									</div>
									<?php endif;?>

									<?php
										if (empty($meta_arr['ihc_ap_' . $k . '_msg'])){
											$meta_arr['ihc_ap_' . $k . '_msg'] = '';
										}
									?>
									<?php if ($k!='logout'):?>
										<div class="iump-form-line iump-no-border ihc-ap-tabs-settings-item-content">
											<div class="ihc-wp_editor">
												<?php
												wp_editor(stripslashes($meta_arr['ihc_ap_' . $k . '_msg']), 'ihc_tab_' . $k . '_msg', array('textarea_name' => 'ihc_ap_' . $k . '_msg', 'editor_height'=>300));
											?></div>
											<div class="iump-wp_editor-constants">
                        <h4><?php echo esc_html__('Templates  Tags', 'ihc');?></h4>
												<div class="ump-js-list-constants">
												<?php
													foreach ($constants as $key=>$val){
														?>
														<div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="<?php echo 'ihc_tab_' . $k . '_msg';?>" ><?php echo esc_html($key);?></span></div>
														<?php
													}
											?>
											</div>
										</div>
											<div class="iump-wp_editor-constants-coltwo">
												<h4><?php esc_html_e('Custom Fields Tags', 'ihc');?></h4>
												<div class="iump-wp_editor-constants-colthree">
												<div class="ump-js-list-constants">
											<?php
											$i = 1;
											$half = round(count($extra_constants)/2);
											foreach ($extra_constants as $key=>$val){
												?>
												<div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="<?php echo 'ihc_tab_' . $k . '_msg';?>" ><?php echo esc_html($key);?></span></div>
												<?php
												$i++;
												if($i == $half){
													?>
													</div>
												</div>
													<div class="iump-wp_editor-constants-colfour">
														<div class="ump-js-list-constants">
													<?php
												}
												}
												?>
												</div>
											</div>
											<div class="ihc-clear"></div>
										</div>
										</div>
									<?php endif;?>

									<?php
											switch ( $k ){
													case 'orders':
														?>
															<div class="iump-form-line iump-no-border">
																	<h2><?php esc_html_e('Additional Tab Settings', 'ihc');?></h2>
																	<p><?php esc_html_e('Manage extra sections inside selected tab', 'ihc');?></p>
															</div>
																<div class="iump-form-line iump-no-border">
																	<h4><?php esc_html_e('Show Orders Table', 'ihc');?></h4>
																	<p><?php esc_html_e('Members can see detailed informations about their Orders. You may replicate this showcase by using ','ihc');?> <strong> [ihc-account-page-orders-table]</strong><?php esc_html_e(' shortcode anywhere else.', 'ihc');?></p>
																	<label class="iump_label_shiwtch ihc-switch-button-margin">
																		<?php $checked = $meta_arr['ihc_account_page_orders_show_table'] == 1 ? 'checked' : '';?>
																		<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_account_page_orders_show_table');" <?php echo esc_attr($checked);?> />
																		<div class="switch ihc-display-inline"></div>
																	</label>
																	<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_account_page_orders_show_table']);?>" id="ihc_account_page_orders_show_table" name="ihc_account_page_orders_show_table" />
																</div>
														<?php
														break;
													case 'pushover_notifications':
													?>
														<div class="iump-form-line iump-no-border">
																<h2><?php esc_html_e('Additional Tab Settings', 'ihc');?></h2>
																<p><?php esc_html_e('Manage extra sections inside selected tab', 'ihc');?></p>
														</div>
															<div class="iump-form-line iump-no-border">
																<h4><?php esc_html_e('Show Pushover Form', 'ihc');?></h4>
																<p><?php esc_html_e('Members can see Pushover Settings Form. You may replicate this showcase by using ', 'ihc');?> <strong> [ihc-account-page-pushover-form]</strong><?php esc_html_e(' shortcode anywhere else.', 'ihc');?></p>
																<label class="iump_label_shiwtch ihc-switch-button-margin">
																	<?php $checked = $meta_arr['ihc_account_page_pushover_show_form'] == 1 ? 'checked' : '';?>
																	<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_account_page_pushover_show_form');" <?php echo esc_attr($checked);?> />
																	<div class="switch ihc-display-inline"></div>
																</label>
																<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_account_page_pushover_show_form']);?>" id="ihc_account_page_pushover_show_form" name="ihc_account_page_pushover_show_form" />
															</div>
													<?php
														break;
													case 'user_sites':
													?>
														<div class="iump-form-line iump-no-border">
																<h2><?php esc_html_e('Additional Tab Settings', 'ihc');?></h2>
																<p><?php esc_html_e('Manage extra sections inside selected tab', 'ihc');?></p>
														</div>
															<div class="iump-form-line iump-no-border">
																<h4><?php esc_html_e('Show User Sites Form & Table', 'ihc');?></h4>
																<p><?php esc_html_e('Members can see User Sites Form & Table. You may replicate this showcase by using ', 'ihc');?> <strong> [ihc-user-sites-table]</strong><?php esc_html_e(' and ', 'ihc');?><strong>[ihc-user-sites-add-new-form]</strong> <?php esc_html_e(' shortcodes anywhere else.', 'ihc');?></p>
																<label class="iump_label_shiwtch ihc-switch-button-margin">
																	<?php $checked = $meta_arr['ihc_account_page_user_sites_show_table'] == 1 ? 'checked' : '';?>
																	<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_account_page_user_sites_show_table');" <?php echo esc_attr($checked);?> />
																	<div class="switch ihc-display-inline"></div>
																</label>
																<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_account_page_user_sites_show_table']);?>" id="ihc_account_page_user_sites_show_table" name="ihc_account_page_user_sites_show_table" />
															</div>
														<?php
														break;
													case 'social':
														?>
														<div class="iump-form-line iump-no-border">
																<h2><?php esc_html_e('Additional Tab Settings', 'ihc');?></h2>
																<p><?php esc_html_e('Manage extra sections inside selected tab', 'ihc');?></p>
														</div>
															<div class="iump-form-line iump-no-border">
																<h4><?php esc_html_e('Show Social Links Buttons', 'ihc');?></h4>
																<p><?php esc_html_e('Members can see Social Links Buttons. You may replicate this showcase by using ', 'ihc');?> <strong> [ihc-social-links-profile]</strong><?php esc_html_e(' shortcode anywhere else.', 'ihc');?></p>
																<label class="iump_label_shiwtch ihc-switch-button-margin">
																	<?php $checked = $meta_arr['ihc_account_page_social_plus_show_buttons'] == 1 ? 'checked' : '';?>
																	<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_account_page_social_plus_show_buttons');" <?php echo esc_attr($checked);?> />
																	<div class="switch ihc-display-inline"></div>
																</label>
																<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_account_page_social_plus_show_buttons']);?>" id="ihc_account_page_social_plus_show_buttons" name="ihc_account_page_social_plus_show_buttons" />
															</div>
															<?php
														break;
														case 'profile':
															?>
															<div class="iump-form-line iump-no-border">
																	<h2><?php esc_html_e('Additional Tab Settings', 'ihc');?></h2>
																	<p><?php esc_html_e('Manage extra sections inside selected tab', 'ihc');?></p>
															</div>
																<div class="iump-form-line iump-no-border">
																	<h4><?php esc_html_e('Show Profile Form', 'ihc');?></h4>
																	<p><?php esc_html_e('Members can see Profile Form. You may replicate this showcase by using ', 'ihc');?> <strong>[ihc-edit-profile-form]</strong> <?php esc_html_e(' shortcode anywhere else.', 'ihc');?></p>
																	<label class="iump_label_shiwtch ihc-switch-button-margin">
																		<?php $checked = $meta_arr['ihc_account_page_profile_show_form'] == 1 ? 'checked' : '';?>
																		<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_account_page_profile_show_form');" <?php echo esc_attr($checked);?> />
																		<div class="switch ihc-display-inline"></div>
																	</label>
																	<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_account_page_profile_show_form']);?>" id="ihc_account_page_profile_show_form" name="ihc_account_page_profile_show_form" />
																</div>
															<?php
															break;

													case 'subscription':?>
														<div class="iump-form-line iump-no-border">
															  <h2><?php esc_html_e('Additional Tab Settings', 'ihc');?></h2>
				                        <p><?php esc_html_e('Manage extra sections inside selected tab', 'ihc');?></p>
				                    </div>
														<div class="iump-form-line iump-no-border">
															<h4><?php esc_html_e('Show Subscription Table', 'ihc');?></h4>
															<p><?php esc_html_e('Members can see detailed informations about their Memberships. You may replicate this showcase by using ', 'ihc');?> <strong>[ihc-account-page-subscriptions-table]</strong><?php esc_html_e(' shortcode anywhere else.', 'ihc');?></p>
															<label class="iump_label_shiwtch ihc-switch-button-margin">
																<?php
																if (!isset($meta_arr['ihc_ap_subscription_table_enable'])){
																	 $meta_arr['ihc_ap_subscription_table_enable'] = 1;
																}
																?>
																<?php $checked = ($meta_arr['ihc_ap_subscription_table_enable']==1) ? 'checked' : '';?>
																<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_ap_subscription_table_enable');" <?php echo esc_attr($checked);?> />
																<div class="switch ihc-display-inline"></div>
															</label>
															<input type="hidden" name="ihc_ap_subscription_table_enable" value="<?php echo esc_attr($meta_arr['ihc_ap_subscription_table_enable']);?>" id="ihc_ap_subscription_table_enable"/>
														</div>
														<div class="iump-form-line iump-no-border">
															<h4><?php esc_html_e('Display Subscription Plan Showcase', 'ihc');?></h4>
															<p><?php esc_html_e('Members can see sign on other Memberships directly from Member Portal', 'ihc');?></p>
															<label class="iump_label_shiwtch ihc-switch-button-margin">
																<?php
																if (!isset($meta_arr['ihc_ap_subscription_plan_enable'])){
																	 $meta_arr['ihc_ap_subscription_plan_enable'] = 1;
																}
																?>
																<?php $checked = ($meta_arr['ihc_ap_subscription_plan_enable']==1) ? 'checked' : '';?>
																<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_ap_subscription_plan_enable');" <?php echo esc_attr($checked);?> />
																<div class="switch ihc-display-inline"></div>
															</label>
															<input type="hidden" name="ihc_ap_subscription_plan_enable" value="<?php echo esc_attr($meta_arr['ihc_ap_subscription_plan_enable']);?>" id="ihc_ap_subscription_plan_enable"/>
														</div>
														<?php
														break;
											}
									?>

							</div>

						<?php

						////

					}
				?>
			</div>
			<div class="ihc-clear"></div>
		</div>
					<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_ap_tabs']);?>" id="ihc_ap_tabs" name="ihc_ap_tabs" />

					<div class="ihc-wrapp-submit-bttn">
						<input type="submit" id="ihc_submit_bttn" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>

			   </div>


	</div>

	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Bottom Section', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line iump-no-border">
				<h4><?php esc_html_e('Bottom Message', 'ihc');?></h4>
				<p><?php esc_html_e('Additional information may be placed on the bottom of Member Portal', 'ihc');?></p>
			</div>
			<div class="iump-form-line iump-no-border ihc-ap-tabs-settings-item-content">
				<div class="ihc-wp_editor"><?php
					wp_editor(stripslashes($meta_arr['ihc_ap_footer_msg']), 'ihc_ap_footer_msg', array('textarea_name' => 'ihc_ap_footer_msg', 'editor_height'=>300));
				?></div>
				<div class="iump-wp_editor-constants">
        <h4><?php echo esc_html__('Template Tags', 'ihc');?></h4>
				<div class="ump-js-list-constants">
					<?php
						foreach ($constants as $k=>$v){
						?>
							<div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_ap_footer_msg"><?php echo esc_html($k);?></span></div>
						<?php
						}
					?>
					</div>
				</div>
				<div class="iump-wp_editor-constants-coltwo">
					<h4><?php esc_html_e('Custom Fields Tags', 'ihc');?></h4>
					<div class="iump-wp_editor-constants-colthree">
						<div class="ump-js-list-constants">
				<?php
				$i = 1;
				$half = round(count($extra_constants)/2);
				foreach ($extra_constants as $k=>$v){
					?>
					<div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_ap_footer_msg"><?php echo esc_html($k);?></span></div>
					<?php
					$i++;
					if($i == $half){
						?>
						</div>
					</div>
						<div class="iump-wp_editor-constants-colfour">
							<div class="ump-js-list-constants">
						<?php
					}
					}
					?>
					</div>
					</div>
				</div>
				<div class="ihc-clear"></div>
			</div>
			<div class="ihc-wrapp-submit-bttn">
				<input type="submit" id="ihc_submit_bttn" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large"  />
			</div>
		</div>
	</div>

	<div class="ihc-stuffbox iump-custom-css-box-wrapper">
		<h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<textarea id="ihc_account_page_custom_css"  name="ihc_account_page_custom_css" class="ihc-dashboard-textarea-full"><?php echo stripslashes($meta_arr['ihc_account_page_custom_css']);?></textarea>
			</div>
			<div class="ihc-wrapp-submit-bttn">
				<input type="submit" id="ihc_submit_bttn" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large"  />
			</div>
		</div>
	</div>

</form>
</div>
