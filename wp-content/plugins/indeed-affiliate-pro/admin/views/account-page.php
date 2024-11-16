<?php
require_once UAP_PATH . 'admin/font-awesome_codes.php';
$font_awesome = uap_return_font_awesome();
$custom_css = '';
?>

<?php
 foreach ($font_awesome as $base_class => $code):
	$custom_css .= "." . $base_class . ":before{".
		"content: '\\".$code."';".
	"}";
endforeach;
foreach ($data['available_tabs'] as $k=>$v):
  if ( !isset( $v['uap_tab_' . $k . '_icon_code'] ) || $v['uap_tab_' . $k . '_icon_code'] === ''|| $v['uap_tab_' . $k . '_icon_code'] === false ){ continue;}
$custom_css .= ".fa-" . $k . "-account-uap:before{".
"content:'\\".$v['uap_tab_' . $k . '_icon_code']."';".
"}";
endforeach;

wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
 ?>

<div class="uap-page-title">
	<span class="second-text">
		<?php esc_html_e('Affiliate Portal', 'uap');?>
	</span>
</div>
<div class="uap-wrapper">
<div class="uap-stuffbox">
	<div class="uap-shortcode-display">
		[uap-account-page]
	</div>
</div>

<div class="metabox-holder indeed uap-admin-account-page-settings">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Top Section', 'uap');?></h3>

			<div class="inside">

			<div class="uap-register-select-template">
				<?php esc_html_e('Select Template', 'uap');?>
				<select name="uap_ap_top_theme"><?php
					foreach ($data['top_themes'] as $k=>$v){
						$selected = ($k==$data['metas']['uap_ap_top_theme']) ? 'selected' : '';
						?>
						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
						<?php
					}
				?></select>
			</div>
      <div class="inside">
        <div class="uap-form-line uap-no-border">
      				<h2><?php esc_html_e('Affiliate Banner Image', 'uap');?></h2>
      				<p><?php esc_html_e('The cover or background image, based on what theme you have chosen', 'uap');?></p>
      				<div class="input-group">
      				<label class="uap_label_shiwtch uap-onbutton">
  					<?php $checked = ($data['metas']['uap_ap_edit_background']) ? 'checked' : '';?>
  					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_background');" <?php echo esc_attr($checked);?> />
  					<div class="switch uap-display-inline"></div>
  				</label>
  				<input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_edit_background']);?>" name="uap_ap_edit_background" id="uap_ap_edit_background" />
  				<label></label>
  				</div>
  					<div class="row">
  						<div class="col-xs-6">
                <p><?php esc_html_e('Upload a custom Banner image to replace the default one.', 'uap');?></p>
  							<div class="form-group">
  								<input type="text" class="form-control uap-banner-field" onClick="openMediaUp(this);" value="<?php  echo esc_attr($data['metas']['uap_ap_edit_background_image']);?>" name="uap_ap_edit_background_image" id="uap_ap_edit_background_image"/>
  								<i class="fa-uap fa-trash-uap" id="uap_js_edit_background_image_trash" onclick="" title="<?php esc_html_e('Remove Background Image', 'uap');?>"></i>

  							</div>
  						</div>
  					</div>
  			</div>

        <div class="uap-form-line uap-no-border">
          <h2><?php esc_html_e('Affiliate Avatar Image', 'uap');?></h2>
          <p><?php esc_html_e('If Affiliates have the option to upload their own Avatar, this one can show on Affiliate Portal. ', 'uap');?></p>
          <label class="uap_label_shiwtch uap-onbutton">
  					<?php $checked = ($data['metas']['uap_ap_edit_show_avatar']) ? 'checked' : '';?>
  					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_avatar');" <?php echo esc_attr($checked);?> />
  					<div class="switch uap-display-inline"></div>
  				</label>
  				<input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_edit_show_avatar']);?>" name="uap_ap_edit_show_avatar" id="uap_ap_edit_show_avatar" />
        </div>

        <div class="uap-form-line uap-no-border">
          <h2><?php esc_html_e('Display Earnings', 'uap');?></h2>
          <p><?php esc_html_e('showing or presenting the amount of money earned or received. ', 'uap');?></p>
          <label class="uap_label_shiwtch uap-onbutton">
  					<?php $checked = ($data['metas']['uap_ap_edit_show_earnings']) ? 'checked' : '';?>
  					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_earnings');" <?php echo esc_attr($checked);?> />
  					<div class="switch uap-display-inline"></div>
  				</label>
  				<input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_edit_show_earnings']);?>" name="uap_ap_edit_show_earnings" id="uap_ap_edit_show_earnings" />
        </div>

        <div class="uap-form-line uap-no-border">
          <h2><?php esc_html_e('Display Referrals Count', 'uap');?></h2>
          <p><?php esc_html_e('Showing the total number of referrals that have been made. The count is displayed to track the success and impact of the affiliate program', 'uap');?></p>
          <label class="uap_label_shiwtch uap-onbutton">
            <?php $checked = ($data['metas']['uap_ap_edit_show_referrals']) ? 'checked' : '';?>
            <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_referrals');" <?php echo esc_attr($checked);?> />
            <div class="switch uap-display-inline"></div>
          </label>
          <input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_edit_show_referrals']);?>" name="uap_ap_edit_show_referrals" id="uap_ap_edit_show_referrals" />
        </div>

        <div class="uap-form-line uap-no-border">
          <h2><?php esc_html_e('Display Achievement Status until the new Rank', 'uap');?></h2>
          <p><?php esc_html_e('This means to show or present the current level of accomplishment or success until reaching the next rank. ', 'uap');?></p>
          <label class="uap_label_shiwtch uap-onbutton">
  					<?php $checked = ($data['metas']['uap_ap_edit_show_achievement']) ? 'checked' : '';?>
  					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_achievement');" <?php echo esc_attr($checked);?> />
  					<div class="switch uap-display-inline"></div>
  				</label>
  				<input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_edit_show_achievement']);?>" name="uap_ap_edit_show_achievement" id="uap_ap_edit_show_achievement" />
        </div>

        <div class="uap-form-line uap-no-border">
          <h2><?php esc_html_e("Display current Affiliate's Rank", 'uap');?></h2>
          <p><?php esc_html_e('Showcasing the current position or level of an affiliate within a ranking system ', 'uap');?></p>
          <label class="uap_label_shiwtch uap-onbutton">
            <?php $checked = ($data['metas']['uap_ap_edit_show_rank']) ? 'checked' : '';?>
            <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_rank');" <?php echo esc_attr($checked);?> />
            <div class="switch uap-display-inline"></div>
          </label>
          <input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_edit_show_rank']);?>" name="uap_ap_edit_show_rank" id="uap_ap_edit_show_rank" />
        </div>

        <div class="uap-form-line uap-no-border">
          <h2><?php esc_html_e('Display EPC Metrics', 'uap');?></h2>
          <p><?php esc_html_e('Show the key performance indicators related to Earnings Per Click (EPC). EPC metrics are used to measure the effectiveness and profitability of affiliate campaigns. ', 'uap');?></p>
          <label class="uap_label_shiwtch uap-onbutton">
            <?php $checked = ($data['metas']['uap_ap_edit_show_metrics']) ? 'checked' : '';?>
            <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_metrics');" <?php echo esc_attr($checked);?> />
            <div class="switch uap-display-inline"></div>
          </label>
          <input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_edit_show_metrics']);?>" name="uap_ap_edit_show_metrics" id="uap_ap_edit_show_metrics" />
        </div>

      </div>

			<div class="inside">
				<h2><?php esc_html_e('Welcome Message', 'uap');?></h2>
        <p><?php esc_html_e('Customize the Top Message with Affiliate personal information', 'uap');?></p>
				<div class="uap-wp_editor uap-wp-editor">
				<?php wp_editor(stripslashes($data['metas']['uap_ap_welcome_msg']), 'uap_ap_welcome_msg', array('textarea_name'=>'uap_ap_welcome_msg', 'editor_height'=>200));?>
				</div>
				<div class="uap-constants-first">
					<h4><?php esc_html_e('Regular Tags', 'uap');?></h4>
					<?php
						$constants = array(	"{username}",
											"{first_name}",
											"{last_name}",
											"{user_id}",
											"{user_email}",
											"{user_registered}",
											"{flag}",
											"{account_page}",
											"{login_page}",
											"{blogname}",
											"{blogurl}",
											"{siteurl}",
											'{rank_id}',
											'{rank_name}'
							);
						$extra_constants = uap_get_custom_constant_fields();
						foreach ($constants as $v){
							?>
							<div><?php echo esc_html($v);?></div>
							<?php
						}
						?>
						</div>
						<div class="uap-constants-second">
							<h4><?php esc_html_e('Custom Fields Tags', 'uap');?></h4>
						<?php
						foreach ($extra_constants as $k=>$v){
							?>
							<div><?php echo esc_html($k);?></div>
							<?php
						}
					?>
							</div>
				</div>
				<div class="uap-clear"></div>


			<div class="inside">
				<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large"  />

					</div>
			</div>
		</div>
		</div>
		<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Content Section', 'uap');?></h3>

			<div class="inside">
			<div class="uap-register-select-template">
				<?php esc_html_e('Select Template', 'uap');?>
				<select name="uap_ap_theme"><?php
					foreach ($data['themes'] as $k=>$v){
						$selected = ($k==$data['metas']['uap_ap_theme']) ? 'selected' : '';
						?>
						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
						<?php
					}
				?></select>
			</div>
			</div>
      
			<div class="inside">
      <div class="uap-form-line">
        <div class="uap-ap-tabs-wrapper">
					<div class="uap-ap-tabs-list">
						<?php  foreach ($data['available_tabs'] as $k=>$v):?>
							<div class="uap-ap-tabs-list-item <?php echo (isset($v['type']) && $v['type'] == 'subtab') ? 'uap-ap-subtabs' : '';?> <?php echo (isset($v['print_link']) && $v['print_link'] == FALSE) ? 'uap-ap-tab-nolink' : ''; ?>"
                onClick="<?php echo esc_attr((isset($v['print_link']) && $v['print_link'] == FALSE) ? '' : 'uapApMakeVisible("'.$k.'", this);'); ?>" id="<?php echo esc_attr('uap_tab-' . $k);?>">
                <?php $icon_class = '';
                    if (isset($v['type']) && $v['type'] == 'tab' && isset($v['print_link']) && $v['print_link'] == FALSE){
                      $icon_class = 'fa-uap fa-account-down-uap';
                    }else{
                    $icon_class = 'fa-uap fa-' . $k . '-account-uap';
                    }?>
                <i class="<?php echo esc_attr($icon_class); ?>"></i>
                <?php echo esc_html($v['uap_tab_' . $k . '_menu_label']);?>
              </div>
						<?php endforeach;?>
						<div class="uap-clear"></div>
					</div>
					<div class="uap-ap-tabs-settings">
						<?php

						$tabs = explode(',', $data['metas']['uap_ap_tabs']);
						$i = 0;

						foreach ($data['available_tabs'] as $k=>$v):?>
							<div class="uap-ap-tabs-settings-item" id="<?php echo esc_attr('uap_tab_item_' . $k);?>">
								<div class="input-group">
									<h2><?php echo esc_html($v['uap_tab_' . $k . '_menu_label']);?></h2>
									<p><?php esc_html_e('Show/Hide', 'uap');?> <?php echo esc_html($v['uap_tab_' . $k . '_menu_label']);?> <?php esc_html_e('from Affiliate Portal', 'uap');?></p>
									<label class="uap_label_shiwtch  uap-onbutton">
										<?php $checked = (in_array($k, $tabs)) ? 'checked' : '';?>
										<input type="checkbox" class="uap-switch" onClick="uapMakeInputhString(this, '<?php echo esc_attr($k);?>', '#uap_ap_tabs');" <?php echo esc_attr($checked);?> />
										<div class="switch uap-display-inline uap-activate-tab-btn"></div>
									</label>
								</div>
								<?php if (isset($data['metas']['uap_tab_' . $k . '_menu_label'])) : ?>
									<div class="row">
										<div class="col-xs-12">
											<div class="input-group">
												<span class="input-group-addon"><?php esc_html_e('Menu Label', 'uap');?></span>
												<input type="text" class="form-control" placeholder="" value="<?php echo esc_attr($data['metas']['uap_tab_' . $k . '_menu_label']);?>" name="<?php echo esc_attr('uap_tab_' . $k . '_menu_label');?>">
											</div>
								</div>
							</div>
								<?php endif;?>
								<?php if (isset($data['metas']['uap_tab_' . $k . '_title'])) : ?>
									<div class="row">
										<div class="col-xs-12">
											<div class="input-group">
												<span class="input-group-addon"><?php esc_html_e('Tab Title', 'uap');?></span>
												<input type="text" class="form-control" placeholder="" value="<?php echo esc_attr($data['metas']['uap_tab_' . $k . '_title']);?>" name="<?php echo esc_attr('uap_tab_' . $k . '_title');?>">
											</div>
										</div>
									</div>
								<?php endif;?>


									<!-- ICON SELECT - SHINY -->
									<div class="row uap-row-icon">
										<div class="col-xs-4">
									   		<div class="input-group">
												<label><?php esc_html_e('Menu Icon', 'uap');?></label>
											<div class="uap-icon-select-wrapper">
												<div class="uap-icon-input">
													<div id="<?php echo esc_attr('indeed_shiny_select_' . $k);?>" class="uap-shiny-select-html"></div>
												</div>
								   				<div class="uap-icon-arrow" id="<?php echo esc_attr('uap_icon_arrow_' . $k);?>"><i class="fa-uap fa-arrow-uap"></i></div>
												<div class="uap-clear"></div>
											</div>

									   		</div>
										</div>
									</div>
									<span class="uap-js-account-page-icon-details" data-slug="<?php echo esc_attr($k);?>"
										data-icon_code="<?php echo isset($data['metas']['uap_tab_' . $k . '_icon_code']) ? esc_attr($data['metas']['uap_tab_' . $k . '_icon_code']) : '';?>" ></span>

									<!-- ICON SELECT - SHINY -->


								<?php if (isset($data['metas']['uap_tab_' . $k . '_content'])) : ?>
									<div>
										<div class="uap-wp-editor"><?php
											wp_editor(stripslashes($data['metas']['uap_tab_' . $k . '_content']), 'uap_tab_' . $k . '_content', array('textarea_name' => 'uap_tab_' . $k . '_content', 'editor_height'=>200));
										?></div>
										<div class="uap-constants-first">
											<?php
												echo esc_uap_content("<h4>" . esc_html__('Regular Tags', 'uap') . "</h4>");
												foreach ($constants as $v){
													?>
													<div><?php echo esc_html($v);?></div>
													<?php
												}
										?>
										</div>
										<div class="uap-constants-second">
										<?php
												echo esc_uap_content("<h4>" . esc_html__('Custom Fields Tags', 'uap') . "</h4>");
												foreach ($extra_constants as $k=>$v){
													?>
													<div><?php echo esc_html($k);?></div>
													<?php
												}
											?>
										</div>
									</div>
								<?php endif;?>
							</div>
						<?php endforeach;?>
					</div>
          <div class="uap-clear"></div>
        </div>
				<input type="hidden" value="<?php echo esc_attr($data['metas']['uap_ap_tabs']);?>" id="uap_ap_tabs" name="uap_ap_tabs" />
				<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large"  />
					</div>
        </div>
			   </div>
		</div>
		<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Bottom Section', 'uap');?></h3>

			<div class="inside">
        <div class="uap-form-line">
				<h2><?php esc_html_e('Bottom Message', 'uap');?></h2>
        <p><?php esc_html_e('Additional information may be placed on the bottom of Affiliate Portal', 'uap');?></p>
				<div class="uap-wp_editor uap-wp-editor">
				<?php wp_editor(stripslashes($data['metas']['uap_ap_footer_msg']), 'uap_ap_footer_msg', array('textarea_name'=>'uap_ap_footer_msg', 'editor_height'=>200));?>
				</div>
				<div class="uap-constants-first">
					<h4><?php esc_html_e('Regular Tags', 'uap');?></h4>
					<?php
						$constants = array(	"{username}",
											"{first_name}",
											"{last_name}",
											"{user_id}",
											"{user_email}",
											"{user_registered}",
											"{account_page}",
											"{login_page}",
											"{blogname}",
											"{blogurl}",
											"{siteurl}",
											'{rank_id}',
											'{rank_name}'
							);
						$extra_constants = uap_get_custom_constant_fields();
						foreach ($constants as $v){
							?>
							<div><?php echo esc_html($v);?></div>
							<?php
						}
						?>
						</div>
						<div class="uap-constants-second">
							<h4><?php esc_html_e('Custom Fields Tags', 'uap');?></h4>
						<?php
						foreach ($extra_constants as $k=>$v){
							?>
							<div><?php echo esc_html($k);?></div>
							<?php
						}
					?>
							</div>
            </div>
				</div>
				<div class="uap-clear"></div>
				<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
		</div>
			<div class="uap-stuffbox uap-custom-css-box-wrapper">
		<h3 class="uap-h3"><?php esc_html_e('Custom CSS:', 'uap');?></h3>
			<div class="uap-form-line">
					<textarea id="uap_account_page_custom_css"  name="uap_account_page_custom_css" class="uap-dashboard-textarea-full uap-custom-css-box"><?php echo stripslashes($data['metas']['uap_account_page_custom_css']);?></textarea>
					<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
			</div>

		</div>
</form>
</div>
</div>
