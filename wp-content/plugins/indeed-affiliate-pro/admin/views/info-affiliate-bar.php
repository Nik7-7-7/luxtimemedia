<div class="uap-wrapper uap-admin-affiliate-bar-wrapper">

		<form  method="post">
			<div class="uap-stuffbox">
				<h3 class="uap-h3"><?php esc_html_e('Affiliate FlashBar', 'uap');?></h3>
				<div class="inside">
				<div class="row">
					<div class="col-xs-6">

					<div class="uap-form-line">
						<h2><?php esc_html_e('Activate/Hold Info Affiliate FlashBar', 'uap');?></h2>
                        <p><?php esc_html_e('The quickest and easiest way to link to any page.FlashBar will show up at the top of every page affiliate visits. To generate an affiliate link just go to the specific page and use the available options from FlashBar.', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_info_affiliate_bar_enabled']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_info_affiliate_bar_enabled');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_info_affiliate_bar_enabled" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_enabled']);?>" id="uap_info_affiliate_bar_enabled" />
          </div>
          			</div>
                    </div>
          <div class="row">
						<div class="col-xs-11">
						<img src="<?php echo UAP_URL;?>assets/images/flashbar-demo.png" class="uap-demo-image" alt="demo"/>
                        </div>
						</div>
        <div class="uap-form-line">
					<div class="row">
					<div class="col-xs-6">
                    <h2><?php esc_html_e('FlashBar Logo', 'uap');?></h2>
								<p><?php esc_html_e('Personalize your FlashBar with your logo or leave the default one. The image will show up on the left side of the bar.', 'uap');?></p>
					<div class="form-group">
							<input type="text" class="form-control uap-edit-input" name="uap_info_affiliate_bar_logo" onClick="openMediaUp(this);" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_logo']);?>" />
							<i class="fa-uap fa-trash-uap uap-display-inline" id="uap_js_flashbar_trash_img" onclick="" title="<?php esc_html_e('Remove Logo', 'uap');?>" ></i>
					</div>
					</div>
          </div>

					<div class="row">
					<div class="col-xs-4">
							<h2><?php esc_html_e( 'Links Section', 'uap' );?></h2>
                            <p><?php esc_html_e('Will help affiliates to easily generates affiliates links with just 2 clicks. ', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_info_affiliate_bar_links_section_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_info_affiliate_bar_links_section_enabled');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_info_affiliate_bar_links_section_enabled" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_links_section_enabled']);?>" id="uap_info_affiliate_bar_links_section_enabled" />
							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Section name', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_links_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_links_label'] );?>" />
							</div>
							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( "'Get affiliate link' label", 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_links_get_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_links_get_label'] );?>" />
							</div>
					</div>
                    </div>


					<div class="row">
					<div class="col-xs-4">
							<h2><?php esc_html_e( 'Banner Section', 'uap' );?></h2>
							<p><?php esc_html_e('Based on current page Featured Image, affiliates may generated banners links. ', 'uap');?></p>
                            <label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_info_affiliate_bar_banner_section_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_info_affiliate_bar_banner_section_enabled');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_info_affiliate_bar_banner_section_enabled" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_banner_section_enabled']);?>" id="uap_info_affiliate_bar_banner_section_enabled" />

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Section name', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_banner_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_banner_label'] );?>" />
							</div>

							<div class="form-group">
									<h6><?php esc_html_e( 'Default Banner', 'uap' );?></h6>
									<input type="text" class="form-control uap-edit-input"  name="uap_info_affiliate_bar_banner_default_value" onClick="openMediaUp(this);" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_banner_default_value']);?>" />
									<i class="fa-uap fa-trash-uap uap-display-inline" id="uap_js_iab_trash_banner" onclick="" title="<?php esc_html_e('Remove Logo', 'uap');?>"></i>
							</div>

					</div>
                    </div>


					<div class="row">
					<div class="col-xs-4">
							<h2><?php esc_html_e( 'Social Section', 'uap' );?></h2>
                            <p><?php esc_html_e('Place social Share icons with one generated Shortcode. We recommend "Social Share&Locker" plugin. ', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_info_affiliate_bar_social_section_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_info_affiliate_bar_social_section_enabled');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_info_affiliate_bar_social_section_enabled" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_social_section_enabled']);?>" id="uap_info_affiliate_bar_social_section_enabled" />

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Section name', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_social_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_social_label'] );?>" />
							</div>

							<div>
									<h2><?php esc_html_e( 'Shortcode', 'uap' );?></h2>
									<p><?php esc_html_e( 'You can generate the social share shortcode from the "Social Share &amp; Locker" dashboard and paste it here.' , 'uap' );?><strong><?php esc_html_e( '"Social Share&Locker" plugin is required.' , 'uap' );?></strong></p>
									<p><a href="<?php echo admin_url( 'admin.php?page=ism_manage&amp;tab=shortcode' );?>" target="_blank"><?php esc_html_e( 'Click here', 'uap' );?></a><?php esc_html_e( ' to grab a new shortcode.', 'uap' );?></p>
									<textarea name="uap_info_affiliate_bar_social_shortcode" class="uap-special-shortcode"><?php echo stripslashes($data['metas']['uap_info_affiliate_bar_social_shortcode']);?></textarea>
							</div>
						</div>
            </div>


					<div class="row">
					<div class="col-xs-5">
							<h2><?php esc_html_e( 'Stats Section', 'uap' );?></h2>
                            <p><?php esc_html_e('Important stats and counts about current Page may show up into FlashBar so affiliate user will better understand the potential of that page.". ', 'uap');?></p>
							<h4><?php esc_html_e( 'Personal Stats', 'uap' );?></h4>
                            <p><?php esc_html_e('Counts about how many visits and referrals current page generated for logged affiliate user.". ', 'uap');?></p>
                            <label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_info_affiliate_bar_stats_personal_section_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_info_affiliate_bar_stats_personal_section_enabled');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_info_affiliate_bar_stats_personal_section_enabled" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_stats_personal_section_enabled']);?>" id="uap_info_affiliate_bar_stats_personal_section_enabled" />

							<h2><?php esc_html_e( 'General Stats', 'uap' );?></h2>
                            <p><?php esc_html_e('Conversion Rate value is provided being calculated based on all affiliates performances related to current page.". ', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_info_affiliate_bar_stats_general_section_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_info_affiliate_bar_stats_general_section_enabled');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_info_affiliate_bar_stats_general_section_enabled" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_stats_general_section_enabled']);?>" id="uap_info_affiliate_bar_stats_general_section_enabled" />

                            <h4><?php esc_html_e( 'Customizable Labels', 'uap' );?></h4>
							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Section name', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_stats_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_stats_label'] );?>" />
							</div>

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Visits label', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_visits_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_visits_label'] );?>" />
							</div>

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Referrals label', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_referrals_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_referrals_label'] );?>" />
							</div>

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Insigts label', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_insigts_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_insigts_label'] );?>" />
							</div>

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( "'Conversion rate' label", 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_conversion_rate_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_conversion_rate_label'] );?>" />
							</div>

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( "Perfomance label", 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_overall_performance_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_overall_performance_label'] );?>" />
							</div>


					</div>
                    </div>


					<div class="row">
					<div class="col-xs-4">
							<h2><?php esc_html_e( 'Menu Section', 'uap' );?></h2>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_info_affiliate_bar_menu_section_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_info_affiliate_bar_menu_section_enabled');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_info_affiliate_bar_menu_section_enabled" value="<?php echo esc_attr($data['metas']['uap_info_affiliate_bar_menu_section_enabled']);?>" id="uap_info_affiliate_bar_menu_section_enabled" />

							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e( 'Section name', 'uap' );?></span>
									<input type="text" class="form-control" name="uap_info_affiliate_bar_menu_label" value="<?php echo stripslashes( $data['metas']['uap_info_affiliate_bar_menu_label'] );?>" />
							</div>

					</div>
          </div>


					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
				</div>
				</div>
			</div>
		</form>
</div>

<?php
