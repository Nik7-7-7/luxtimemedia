<div class="uap-ap-wrap">

	<?php do_action( 'uap_public_action_account_page_affiliate_links_before_content' );?>

<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>
	<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Tracking details", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-8">
                	<div class=" uap-account-affiliatelinks-tab1">
                	<?php if (empty($data['print_username'])): ?>
						<span><?php esc_html_e("Tracking ID:", 'uap');?></span> <span class=" uap-special-label"><?php echo esc_html($data['affiliate_id']);?></span>
					<?php else:?>
						<span><?php esc_html_e("Tracking Code:", 'uap');?></span> <span class=" uap-special-label"><?php echo esc_html($data['print_username']);?></span>
					<?php endif;?>
                    <div class="uap-account-notes">
                    		<?php esc_html_e("Your unique identificator will be used for tracking new leads", 'uap');?>
                    </div>
                    </div>
                    <div class="uap-account-affiliatelinks-tab2">
        				<span><?php esc_html_e("Default Affiliate Link:", 'uap');?></span> <span class=" uap-special-label"><a href="<?php echo esc_url($data['home_url']);?>" target="_blank"><?php echo esc_url($data['home_url']);?></a></span>
                	</div>
                </div>
                <div class="uap-col-xs-4">
                	<?php if (!empty($data['qr_home'])):?>
                      <div class="uap-qr-code-wrapper uap-account-affiliatelinks-tab3">
                          <img src="<?php echo esc_url($data['qr_home']);?>" />
                          <a href="<?php echo esc_url($data['qr_home']);?>" class="uap-button-primary" download="<?php echo basename($data['qr_home']);?>"><?php esc_html_e('Download', 'uap');?></a>
                      </div>
                  <?php endif;?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($data['home_url_slug'])):?>
    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Custom Slug", 'uap');?></span></div>
    	<div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-8">
                    <div class="uap-account-affiliatelinks-tab7">
        				<span><?php esc_html_e("Alternative Slug Link:", 'uap');?></span> <span class=" uap-special-label"><a href="<?php echo esc_url($data['home_url_slug']);?>" target="_blank"><?php echo esc_url($data['home_url_slug']);?></a></span>
                         <div class="uap-account-notes">
                    		<?php esc_html_e("You can change your custom Affiliate slug anytime from ", 'uap');?>
                            <a href="<?php echo esc_url($data['affiliate_slug_url']);?>">
			  						<?php esc_html_e('here', 'uap');?>
              					</a>
                    	</div>
                	</div>
                </div>
                <div class="uap-col-xs-4">
                	<?php if (!empty($data['qr_custom_slug'])):?>
                      <div class="uap-qr-code-wrapper uap-account-affiliatelinks-tab4">
                          <img src="<?php echo esc_url($data['qr_custom_slug']);?>" />
                          <a href="<?php echo esc_url($data['qr_custom_slug']);?>" class="uap-button-primary" download="<?php echo basename($data['qr_custom_slug']);?>"><?php esc_html_e('Download', 'uap');?></a>
                      </div>
                  <?php endif;?>
                </div>
            </div>
        </div>
	<?php endif;?>

     <?php if (!empty($data['social_links'])):?>
     <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Social Links", 'uap');?></span></div>
    	<div class="uap-profile-box-content">
        	<?php echo esc_uap_content($data['social_links']);?>
        </div>
     </div>
    <?php endif;?>

<?php do_action( 'uap_public_action_account_page_affiliate_links_after_content' );?>

    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Affiliate Link Generator", 'uap');?></span></div>
    	<div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                	<div class="uap-account-link-generator uap-account-affiliatelinks-tab5">
                      <p><?php esc_html_e("If you'd like to add your own affiliate links with a different URL, follow this structure. Simply take the following URL and create a custom affiliate link for it.", 'uap');?></p>
                      <?php if (!empty($data['campaigns'])) : ?>
                      <div class="uap-ap-field">
                          <label class="uap-ap-label uap-special-label"><?php esc_html_e("Campaign:", 'uap');?> </label>
                          <select id="campaigns_select" class="uap-public-form-control ">
                          <?php foreach ($data['campaigns'] as $value) : ?>
                              <option value="<?php echo esc_attr($value);?>"><?php echo esc_html($value);?></option>
                          <?php endforeach;?>
                          </select>
                      </div>
                      <?php endif; ?>

                      <?php if (!empty($friendly_links)):?>
                      <div class="uap-ap-field">
                          <label class="uap-ap-label uap-special-label"><?php esc_html_e("Friendly Links:", 'uap');?> </label>
                          <select id="friendly_links" class="uap-public-form-control ">
                              <option value="0"><?php esc_html_e('Default link format', 'uap');?></option>
                              <option value="1"><?php esc_html_e('Friendly link format', 'uap');?></option>
                          </select>
                      </div>
                      <?php endif;?>

                      <?php if (!empty($custom_affiliate_slug) && !empty($the_slug)):?>
                          <?php
                              $ref_type = ($this->general_settings['uap_default_ref_format']=='username') ? esc_html__('Tracking Code', 'uap') : 'Tracking ID';
                          ?>
                      <div class="uap-ap-field">
                          <label class="uap-ap-label uap-special-label"><?php esc_html_e("Referrence Type:", 'uap');?> </label>
                          <select id="ref_type" class="uap-public-form-control ">
                              <option value="0"><?php echo esc_html($ref_type);?></option>
                              <option value="1"><?php esc_html_e('Custom Slug', 'uap');?></option>
                          </select>
                      </div>
                      <?php endif;?>

                      <div class="uap-ap-field">
                          <label class="uap-ap-label uap-special-label"><?php esc_html_e("Specific Website page:", 'uap');?> </label>
                          <input type="text" value="" id="ia_generate_aff_custom_url"  class="uap-public-form-control ">
                      </div>
                      <span><?php esc_html_e("Enter any URL from this website in the form above to generate a referral link", 'uap');?></span>
                      <div class="uap-ap-generate-links-result uap-visibility-hidden"></div>
                      <div class="uap-ap-generate-social-result uap-visibility-hidden"></div>
                      <div class="uap-ap-generate-qr-code uap-visibility-hidden"></div>
                      <div class="uap-ap-field">
                          <button type="button" class="uap-button-primary" onClick="iaGenerateLink(<?php echo esc_attr($data['affiliate_id']);?>);"><?php esc_html_e("Generate Link", 'uap');?></button>
                      </div>
                     </div>
                </div>
            </div>
        </div>
     </div>

    <!--div class="uap-account-alert-warning uap-account-affiliatelinks-tab1">
		<?php if (empty($data['print_username'])): ?>
			<label class="uap-ap-label"><?php esc_html_e("Your Affiliate ID is:", 'uap');?></label> <strong class=" uap-special-label"><?php echo esc_html($data['affiliate_id']);?></strong>
		<?php else:?>
			<label class="uap-ap-label"><?php esc_html_e("Your Affiliate Name is:", 'uap');?></label> <strong class=" uap-special-label"><?php echo esc_html($data['print_username']);?></strong>
		<?php endif;?>
	</div>
	<div class="uap-ap-field uap-account-affiliatelinks-tab2">
		<label class="uap-ap-label"><?php esc_html_e("Your referral URL is:", 'uap');?> </label>
	<div class="uap-account-url">
		<a href="<?php echo esc_url($data['home_url']);?>" target="_blank"><?php echo esc_url($data['home_url']);?></a>
	</div>

	<?php if (!empty($data['qr_home'])):?>
		<div class="uap-qr-code-wrapper uap-account-affiliatelinks-tab3">
			<img src="<?php echo esc_url($data['qr_home']);?>" />
			<a href="<?php echo esc_url($data['qr_home']);?>" class="uap-qr-code-download" download="<?php echo basename($data['qr_home']);?>"><?php esc_html_e('Download', 'uap');?></a>
		</div>
	<?php endif;?>

	<?php if (!empty($data['home_url_slug'])):?>
		<label class="uap-ap-label"><?php esc_html_e("Your Custom Slug:", 'uap');?> </label>
		<div class="uap-account-url-slug">
			<a href="<?php echo esc_url($data['home_url_slug']);?>" target="_blank"><?php echo esc_url($data['home_url_slug']);?></a>
		</div>

		<?php if (!empty($data['qr_custom_slug'])):?>
			<div class="uap-qr-code-wrapper uap-account-affiliatelinks-tab4">
				<img src="<?php echo esc_url($data['qr_custom_slug']);?>" />
				<a href="<?php echo esc_url($data['qr_custom_slug']);?>" class="uap-qr-code-download" download="<?php echo basename($data['qr_custom_slug']);?>"><?php esc_html_e('Download', 'uap');?></a>
			</div>
		<?php endif;?>

	<?php endif;?>

	</div>
	<div><?php echo esc_uap_content($data['social_links']);?></div>
  <div class="uap-account-link-generator uap-account-affiliatelinks-tab5">
	<h4><?php esc_html_e("Link Generator", 'uap');?></h4>
	<p><?php esc_html_e("If you'd prefer to append your own affiliate links with an alternate incoming URL, use the following structure. To build your link, take the following URL and append it with the Alternate Incoming URL you want to use.", 'uap');?></p>
	<?php if (!empty($data['campaigns'])) : ?>
	<div class="uap-ap-field">
		<label class="uap-ap-label uap-special-label"><?php esc_html_e("Campaign:", 'uap');?> </label>
		<select id="campaigns_select" class="uap-public-form-control ">
		<?php foreach ($data['campaigns'] as $value) : ?>
			<option value="<?php echo esc_attr($value);?>"><?php echo esc_html($value);?></option>
		<?php endforeach;?>
		</select>
	</div>
	<?php endif; ?>

	<?php if (!empty($friendly_links)):?>
	<div class="uap-ap-field">
		<label class="uap-ap-label uap-special-label"><?php esc_html_e("Friendly Links:", 'uap');?> </label>
		<select id="friendly_links" class="uap-public-form-control ">
			<option value="0"><?php esc_html_e('Off', 'uap');?></option>
			<option value="1"><?php esc_html_e('On', 'uap');?></option>
		</select>
	</div>
	<?php endif;?>

	<?php if (!empty($custom_affiliate_slug) && !empty($the_slug)):?>
		<?php
			$ref_type = ($this->general_settings['uap_default_ref_format']=='username') ? esc_html__('Username', 'uap') : 'Id';
		?>
	<div class="uap-ap-field">
		<label class="uap-ap-label uap-special-label"><?php esc_html_e("Referrence Type:", 'uap');?> </label>
		<select id="ref_type" class="uap-public-form-control ">
			<option value="0"><?php echo esc_html($ref_type);?></option>
			<option value="1"><?php esc_html_e('Custom Affiliate Slug', 'uap');?></option>
		</select>
	</div>
	<?php endif;?>

	<div class="uap-ap-field">
		<label class="uap-ap-label uap-special-label"><?php esc_html_e("Specific URL:", 'uap');?> </label>
		<input type="text" value="" id="ia_generate_aff_custom_url"  class="uap-public-form-control ">
		<span><?php esc_html_e("Enter any URL from this website in the form above to generate a referral link!", 'uap');?></span>
	</div>
	<div class="uap-ap-generate-links-result uap-visibility-hidden"></div>
	<div class="uap-ap-generate-social-result uap-visibility-hidden"></div>
	<div class="uap-ap-generate-qr-code uap-visibility-hidden"></div>
	<div class="uap-ap-field">
		<button type="button" onClick="iaGenerateLink(<?php echo esc_attr($data['affiliate_id']);?>);"><?php esc_html_e("Generate Link", 'uap');?></button>
	</div>
   </div-->


</div>
