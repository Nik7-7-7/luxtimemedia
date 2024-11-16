<div id="uap_wizard_content_wrapp" class="uap-js-wizard-content-wrapp uap-wizard-main-wrapper" data-current_page="<?php echo $data['page'];?>" >

  <div class="uap-wizard-header-wrapper">
    <div class="uap-page-headline">Ultimate Affiliate Pro - <?php esc_html_e( 'Wizard Setup', 'uap' );?></div>
    <p class="uap-top-message"><?php esc_html_e( 'Easily set up your configurations to get started', 'uap');?></p>
  </div>
  <div class="uap-wizard-progress-bar-wrapp uap-wizard-js-progress-bar">
      <?php $isActive = $data['page'] === 1 ? 'uap-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 1 ? 'uap-wizard-progress-bar-item-completed' : '';?>
      <div class="uap-wizard-progress-bar-item uap-wizard-pbi-1 uap-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="uap-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="uap-wizard-progress-bar-step">1</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="uap-wizard-progress-bar-item-label">
          <?php esc_html_e('License', 'uap');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 2 ? 'uap-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 2 ? 'uap-wizard-progress-bar-item-completed' : '';?>
      <div class="uap-wizard-progress-bar-item uap-wizard-pbi-2 uap-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="uap-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="uap-wizard-progress-bar-step">2</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="uap-wizard-progress-bar-item-label">
          <?php esc_html_e('General Options', 'uap');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 3 ? 'uap-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 3 ? 'uap-wizard-progress-bar-item-completed' : '';?>
      <div class="uap-wizard-progress-bar-item uap-wizard-pbi-3 uap-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="uap-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="uap-wizard-progress-bar-step">3</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="uap-wizard-progress-bar-item-label">
          <?php esc_html_e('Affiliate Link Settings', 'uap');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 4 ? 'uap-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 4 ? 'uap-wizard-progress-bar-item-completed' : '';?>
      <div class="uap-wizard-progress-bar-item uap-wizard-pbi-4 uap-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="uap-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="uap-wizard-progress-bar-step">4</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="uap-wizard-progress-bar-item-label">
          <?php esc_html_e('Ranks', 'uap');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 5 ? 'uap-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 5 ? 'uap-wizard-progress-bar-item-completed' : '';?>
      <div class="uap-wizard-progress-bar-item uap-wizard-pbi-5 uap-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="uap-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="uap-wizard-progress-bar-step">5</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="uap-wizard-progress-bar-item-label">
          <?php esc_html_e('Email Notifications', 'uap');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 6 ? 'uap-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] === 6 ? 'uap-wizard-progress-bar-item-completed' : '';?>
      <div class="uap-wizard-progress-bar-item uap-wizard-pbi-6 uap-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="uap-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="uap-wizard-progress-bar-step">6</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="uap-wizard-progress-bar-item-label">
          <?php esc_html_e('Complete', 'uap');?>
        </div>
      </div>
  </div>

  <div id="uap_wizard_content" class="uap-wizard-wrap-for-content-wrapper">

    <!------------------------------------ step 1. ------------------------------------>
    <?php $show = $data['page'] === 1 ? 'uap-display-block' : 'uap-display-none';?>
    <div class="uap-js-wizard-step-1 <?php echo esc_attr($show);?>">
      <h3 class="uap-wizard-wrap-for-content-title"><span>01</span> - <?php esc_html_e('License', 'uap');?></h3>
      <div class="uap-wizard-wrap-for-content-description">
        <?php esc_html_e('Upon plugin activation, make sure to activate your license to access support and enable automatic upgrades. Please note that without license activation, access to advanced modules will not be available', 'uap');?>
      </div>
      <div class="uap-wizard-wrap-for-content">
        <div class="row">
          <div class="col-xs-10">
          <div class="lincs-row">
            <?php $nameC = get_option('uapl_nk_n');?>
            <h4><?php esc_html_e('Customer Name', 'uap');?></h4>
              <input name="cn" type="text" value="<?php echo $nameC;?>" class="uap-form-element "/>
          </div>
          <div class="lincs-row">
            <h4><?php esc_html_e('Vendor', 'uap');?><span>*</span></h4>
            <?php $currentVend = get_option('uap_lnk_v');?>
            <select name="ls" class="uap-form-select uap-form-element uap-form-select">
                <?php foreach ( UAP_VEND as $vendName => $vendValue ):?>
                    <option value="<?php echo $vendName;?>" <?php if ( $vendName === $currentVend ){ echo 'selected'; }?> ><?php echo $vendValue;?></option>
                <?php endforeach;?>
            </select>
          </div>
          <div>
              <h4><?php esc_html_e('License Key', 'uap');?><span>*</span></h4>
            <input name="h" type="text" value="<?php echo esc_attr($data['h']);?>" class="uap-js-wizard-pc uap-form-element uap-js-wizard-required-field"/>
            <input type="submit" value="<?php esc_html_e('Activate License', 'uap');?>" name="jcode"  class="uap-submit-button uap-js-wizard-submit-c-to-e" />

          </div>
          <div>
            <p class="uap-wizard-additional-details"> <a target="_blank" href="https://ultimateaffiliate.pro/find-my-license-code/"><?php esc_html_e('Where can I find my License Key?', 'uap');?></a></p>
          </div>

          <div id="uap_js_error_message_for_pv2" class="uap-display-none uap-wizard-field-notice"></div>

          <div class="uap-clear"></div>
          <span class="uap-js-help-page-data"
                data-nonce="<?php echo wp_create_nonce('uap_license_nonce');?>"
                data-revoke_url="<?php echo admin_url('admin.php?page=uap_manage&tab=help&revoke=true');?>"
                data-help="<?php esc_html_e( "Please Enter the License Key", 'uap' );?>"
          ></span>
          <?php if ( $data['wpiuap_message'] ):?>
              <?php echo $data['wpiuap_message'];?>
          <?php endif;?>
        </div>
      </div>
      </div>
    </div>
    <!------------------------------------ end of step 1. ------------------------------------>

    <!------------------------------------ step 2. ------------------------------------>
    <?php $show = $data['page'] === 2 ? 'uap-display-block' : 'uap-display-none';?>
    <div class="uap-js-wizard-step-2 <?php echo esc_attr($show);?>">
      <h3 class="uap-wizard-wrap-for-content-title"><span>02</span> - <?php esc_html_e('General Options', 'uap');?></h3>
      <div class="uap-wizard-wrap-for-content-description">
        <?php esc_html_e('Customize essential settings such as Sign-Up options and Currency in the General Options of the Wizard for a personalized experience', 'uap');?>
      </div>
      <div class="uap-wizard-wrap-for-content">
      <div class="uap-form-line">
          <h4><?php esc_html_e('New Affiliates WordPress Role', 'uap');?></h4>
          <select name="uap_register_new_user_role" class="uap-form-select uap-form-element uap-form-element-select uap-form-select">
              <?php foreach ( $data['roles'] as $key => $value ):?>
                  <option value="<?php echo esc_attr($key);?>" <?php if ( $data['uap_register_new_user_role'] == $key ){ echo esc_attr('selected');}?> ><?php echo esc_html($value);?></option>
              <?php endforeach;?>
          </select>
      </div>
      <div class="uap-form-line">
        <h4><?php esc_html_e('Default Currency', 'uap');?></h4>
        <select class="uap-form-select uap-form-element uap-form-element-select uap-form-select uap-wizard-default-currency" name="uap_currency">
          <?php
            foreach ($data['currency_arr'] as $k=>$v){
              ?>
              <option value="<?php echo esc_attr($k);?>" <?php if ($k==$data['uap_currency']){ echo esc_attr('selected');}?> >
                <?php echo esc_html($v);?>
                <?php if (is_array($data['custom_currencies']) && in_array($v, $data['custom_currencies'])){ esc_html_e(" (Custom Currency)");}?>
              </option>
              <?php
            }
          ?>
        </select>
      </div>
      <div class="uap-form-line">
        <h4><?php esc_html_e('Currency Position', 'uap');?></h4>
      <select class="uap-form-select uap-form-element uap-form-element-select uap-form-select " name="uap_currency_position">
        <?php
          foreach ($data['currency_position_arr'] as $k=>$v){
            ?>
            <option value="<?php echo esc_attr($k);?>" <?php if ($k==$data['uap_currency_position']){ echo esc_attr('selected');}?> >
              <?php echo esc_html($v);?>
            </option>
            <?php
          }
        ?>
      </select>
    </div>
    <div class="uap-form-line">
      <h4><?php esc_html_e('Default Country', 'uap');?></h4>
          <select class="uap-form-select uap-form-element uap-form-element-select uap-form-select" name="uap_default_country" >
              <?php foreach ( $data['countries'] as $key => $value ):?>
                  <option value="<?php echo esc_attr($key);?>" <?php if ( $data['uap_default_country'] == $key ){ echo esc_attr('selected');}?> ><?php echo esc_html($value);?></option>
              <?php endforeach;?>
          </select>
          <ul id="uap_countries_list_ul uap-display-none"></ul>
      </div>


        <div class="uap-form-line">
          <label class="uap_label_shiwtch uap-switch-button-margin">
            <?php $checked = ($data['uap_register_auto_login']) ? 'checked' : '';?>
            <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_register_auto_login');" <?php echo esc_attr($checked);?> />
            <div class="switch uap-display-inline"></div>
          </label>
          <input type="hidden" name="uap_register_auto_login" value="<?php echo esc_attr($data['uap_register_auto_login']);?>" id="uap_register_auto_login" />
          <?php esc_html_e('After signing up, customers are automatically logged in', 'uap');?>
        </div>

        <div class="uap-form-line">
            <label class="uap_label_shiwtch uap-switch-button-margin">
              <?php $checked = ($data['uap_all_new_users_become_affiliates']) ? 'checked' : '';?>
              <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_all_new_users_become_affiliates');" <?php echo esc_attr($checked);?> />
              <div class="switch uap-display-inline"></div>
            </label>
            <input type="hidden" name="uap_all_new_users_become_affiliates" value="<?php echo esc_attr($data['uap_all_new_users_become_affiliates']);?>" id="uap_all_new_users_become_affiliates" />

            <?php esc_html_e('All new Users become Affiliates', 'uap');?>
        </div>

          <div class="uap-form-line">
              <label class="uap_label_shiwtch uap-switch-button-margin">
                <?php $checked = ($data['uap_allow_tracking']) ? 'checked' : '';?>
                <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_allow_tracking');" <?php echo esc_attr($checked);?> />
                <div class="switch uap-display-inline"></div>
              </label>
              <input type="hidden" name="uap_allow_tracking" value="<?php echo esc_attr($data['uap_allow_tracking']);?>" id="uap_allow_tracking" />

              <?php esc_html_e('Enable Anonymous Usage Tracking Data', 'uap');?>
          </div>

        </div>
    </div>
    <!------------------------------------ end of step 2. ---------------------------->

    <!------------------------------------ step 3. ------------------------------>
    <?php $show = $data['page'] === 3 ? 'uap-display-block' : 'uap-display-none';?>
    <div class="uap-js-wizard-step-3 <?php echo esc_attr($show);?>">
          <h3 class="uap-wizard-wrap-for-content-title"><span>03</span> - <?php esc_html_e('Affiliate Link Settings', 'uap');?></h3>
          <div class="uap-wizard-wrap-for-content-description">
            <?php esc_html_e('Upon plugin activation, make sure to activate your license to access support and enable automatic upgrades. Please note that without license activation, access to advanced modules will not be available', 'uap');?>
          </div>

          <div class="uap-wizard-wrap-for-content">
            <div class="uap-form-line">
        			<div class="row">
        				<div class="col-xs-4">
        				<h4><?php esc_html_e('Affiliate Link Settings', 'uap');?></h4>
        				<br/>
        				<p><?php esc_html_e('Set the Affiliate Link Variable name', 'uap');?></p>
        					<div class="form-group">
        						<input type="text" class="form-control uap-js-wizard-required-field" value="<?php echo esc_attr($data['uap_referral_variable']);?>" name="uap_referral_variable" />
        					</div>
                  <div id="uap_js_error_message_for_uap_referral_variable" class="uap-display-none uap-wizard-field-notice"></div>
        				</div>
        			</div>

        			<div class="row">
        				<div class="col-xs-4">
        				<h4><?php esc_html_e('Base Affiliate Link', 'uap');?></h4>
        					<div class="form-group">
        						<?php if (empty($data['uap_referral_custom_base_link'])){
        							 $data['uap_referral_custom_base_link'] = get_home_url();
        						}?>
        						<input type="text" class="form-control uap-js-wizard-required-field" value="<?php echo esc_attr($data['uap_referral_custom_base_link']);?>" name="uap_referral_custom_base_link" />
        					</div>
                  <div id="uap_js_error_message_for_uap_referral_custom_base_link" class="uap-display-none uap-wizard-field-notice"></div>
        					<p id="base_referral_link_alert"><?php esc_html_e('Please provide a link from the website where this plugin is installed. Do not provide a link from a different website.', 'uap');?></p>
        				</div>
        			</div>

        		</div>
        		<div class="uap-form-line">
        			<div class="row">
        				<div class="col-xs-4">
        				<h4><?php esc_html_e('Affiliate Link Format', 'uap');?></h4>
        				<select name="uap_default_ref_format" class="form-control m-bot15"><?php
        				$referral_format = array('id' => 'Based on Affiliate ID', 'username'=>'Based on Username');
        				foreach ($referral_format as $k=>$v){
        					$selected = ($data['uap_default_ref_format']==$k) ? 'selected' : '';
        					?>
        					<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
        					<?php
        				}
        				?></select>

        				</div>
        			</div>
        		</div>

        		<div class="uap-form-line">
        			<div class="row">
        				<div class="col-xs-6">
        						<label class="uap_label_shiwtch uap-switch-button-margin">
        							<?php $checked = ($data['uap_search_into_url_for_affid_or_username']) ? 'checked' : '';?>
        							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_search_into_url_for_affid_or_username');" <?php echo esc_attr($checked);?> />
        							<div class="switch uap-display-inline"></div>
        						</label>
        						<input type="hidden" name="uap_search_into_url_for_affid_or_username" value="<?php echo esc_attr($data['uap_search_into_url_for_affid_or_username']);?>" id="uap_search_into_url_for_affid_or_username" />
                    <?php esc_html_e('Search the URL for both formats of affiliate links to ensure comprehensive coverage and accurate identification', 'uap');?>
        				</div>
        			</div>
        		</div>
          </div>
    </div>
    <!------------------------------------ end of step 3. ------------------------------------>

    <!------------------------------------ step 4. ------------------------------------>
    <?php $show = $data['page'] === 4 ? 'uap-display-block' : 'uap-display-none';?>
    <div class="uap-js-wizard-step-4 <?php echo esc_attr($show);?> ">
      <h3 class="uap-wizard-wrap-for-content-title"><span>04</span> - <?php esc_html_e( 'Ranks', 'uap' );?></h3>
      <div class="uap-wizard-wrap-for-content-description">
        <?php esc_html_e('Assign specific Rates and Titles to your affiliates based on their performance and achievements, providing a structured hierarchy within your affiliate program', 'uap');?>
      </div>
      <div class="uap-wizard-wrap-for-content">
        <div class="uap-form-line">
            <div class="row">
                  <div class="col-xs-6">
                    <div class="input-group">
                       <span class="input-group-addon input-group-addon-150"><?php esc_html_e('Name', 'uap');?></span>
                       <input name="label" class="form-control uap-js-wizard-required-field" type="text" value="<?php echo $data['label'];?>" placeholder="<?php esc_html_e('Untitled Rank', 'uap');?>"/>
                       <div class="uap-clear"></div>
                    </div>
                    <div id="uap_js_error_message_for_label" class="uap-display-none uap-wizard-field-notice"></div>
                  </div>
            </div>
        </div>

        <div class="uap-form-line">
          <div class="row">
            <div class="col-xs-6">
              <h2><?php esc_html_e("Rank's Rate", 'uap');?></h2>
              <p><?php esc_html_e('Certain Product Rates or other variable settings may take precedence over the default Rank Rate.', 'uap');?></p>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-4">
                <select name="amount_type" class="form-control m-bot15"><?php
                  foreach ($data['amount_types'] as $k=>$v):
                    $selected = ($data['amount_type']==$k) ? 'selected' : '';
                    ?>
                    <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
                    <?php
                  endforeach;
                ?></select>
           </div>
         </div>
        </div>
         <div class="uap-form-line">
          <div class="row">
            <div class="col-xs-4">
            <div class="input-group">
              <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Rate', 'uap');?></span>
              <input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="form-control uap-js-wizard-required-field" value="<?php echo esc_attr($data['amount_value']);?>" name="amount_value" aria-describedby="basic-addon1" />
            </div>
            <div id="uap_js_error_message_for_amount_value" class="uap-display-none uap-wizard-field-notice"></div>
          </div>
        </div>
      </div>
        <div class="uap-form-line">
  					<div class="row">
  							<div class="col-xs-12">
  							<h2><?php esc_html_e('Set the Rank as the default for Affiliates', 'uap');?></h2>
  							<p><?php esc_html_e('This ensures that new affiliates are automatically assigned to this default Rank upon registration, streamlining the onboarding process', 'uap');?></p>
  								<label class="uap_label_shiwtch uap-switch-button-margin">
  									<?php
  									$checked = 'checked';
  									?>
  									<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#set_as_default_rank');" <?php echo esc_attr($checked);?> />
  									<div class="switch uap-display-inline"></div>
  								</label>
  								<input type="hidden" name="set_as_default_rank" value="1" id="set_as_default_rank" />
  							</div>
  						</div>
  			</div>

        <?php if ( $data['rank_id'] !== false ):?>
            <input type="hidden" name="rank_id" value="<?php echo $data['rank_id'];?>" />
        <?php endif;?>
      </div>
      </div>

    <!------------------------------------ end of step 4. ------------------------------------>


    <!------------------------------------ step 5. ------------------------------------>
    <?php $show = $data['page'] === 5 ? 'uap-display-block' : 'uap-display-none';?>
    <div class="uap-js-wizard-step-5 <?php echo $show?> ">

        <h3 class="uap-wizard-wrap-for-content-title"><span>05</span> - <?php esc_html_e('Email Notifications', 'uap');?></h3>
        <div class="uap-wizard-wrap-for-content-description">
          <?php esc_html_e('Refine your Email Notifications by configuring sender details and enabling initial notifications', 'uap');?>
        </div>
        <div class="uap-wizard-wrap-for-content">
        <div class="uap-form-line  uap-no-border">
          <h4><?php esc_html_e('Sender Details', 'uap');?></h4>
        </div>
        <div class="uap-form-line uap-no-border">
          <div class="row">
            <div class="col-xs-6">
              <div class="input-group">
                <span class="input-group-addon" ><?php esc_html_e("Email Address", 'uap');?></span>
                <input type="text" name="uap_notification_email_from" value="<?php echo esc_attr($data['uap_notification_email_from']);?>"  class="form-control uap-js-wizard-required-field" />
              </div>
              <div id="uap_js_error_message_for_uap_notification_email_from" class="uap-display-none uap-wizard-field-notice"></div>
            </div>
          </div>
        </div>
        <div class="uap-form-line uap-no-border">
          <div class="row">
            <div class="col-xs-6">
          <div class="input-group">
            <span class="input-group-addon" ><?php esc_html_e("Name", 'uap');?></span>
            <input type="text" name="uap_notification_name" value="<?php echo esc_attr($data['uap_notification_name']);?>"  class="form-control uap-js-wizard-required-field" />
          </div>
        </div>
      </div>
    </div>
        <div class="uap-form-line">
          <h4><?php esc_html_e('Default Email Notifications', 'uap');?></h4>
          <p><?php esc_html_e('Enable specific default email notifications for initial experience during initial setup', 'uap');?></p>
            <?php foreach ( $data['notifications'] as $notificationSlug => $notificationData ):?>
                <div>
                  <label class="uap_label_shiwtch uap-switch-button-margin">
                      <?php $checked = ($notificationData['status']) ? 'checked' : '';?>
                      <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#<?php echo esc_attr($notificationSlug);?>');" <?php echo esc_attr($checked);?> />
                  <div class="switch uap-display-inline"></div>
                  </label>
                  <input type="hidden" name="<?php echo $notificationSlug;?>" value="<?php echo esc_attr($notificationData['status']);?>" id="<?php echo $notificationSlug;?>" />
                  <?php echo esc_html($notificationData['label']);?>
                </div>
            <?php endforeach;?>
        </div>
      </div>
    </div>
    <!------------------------------------ end of step 5. ------------------------------------>


    <!------------------------------------ step 6. ------------------------------------>
    <?php $show = $data['page'] === 6 ? 'uap-display-block' : 'uap-display-none';?>
    <div class="uap-js-wizard-step-6 <?php echo $show?> ">
        <div class="uap-wizard-complete-top">
          <div class="uap-page-headline">Setup - <span class="uap-page-headline-colored"><?php esc_html_e( 'Completed', 'uap' );?></span></div>
          <p class="uap-top-message"><?php esc_html_e( 'Congratulations! You successfully installed your Affiliate Program', 'uap');?></p>
        </div>
        <div class="uap-wizard-complete-middle">
          <h4><?php esc_html_e( "What to do Next?", 'uap' );?></h4>
          <div class="uap-wizard-complete-pages">
            <a href="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=dashboard');?>" class="uap-wizard-complete-page-link"><i class="fa-uap fa-affiliates-uap" aria-hidden="true"></i><?php esc_html_e( 'Go to Dashboard', 'uap' );?></a>
            <a href="https://ultimateaffiliate.pro/docs" target="_blank" class="uap-wizard-complete-page-link"><i class="fa-uap fa-book" aria-hidden="true"></i><?php esc_html_e( 'Check our Documentation', 'uap' );?></a>
            <a href="https://ultimateaffiliate.pro/videos" target="_blank" class="uap-wizard-complete-page-link"><i class="fa-uap fa-video" aria-hidden="true"></i><?php esc_html_e( 'See our video Tutorials', 'uap' );?></a>
            <a href="https://ultimateaffiliate.pro/pro-addons/" target="_blank" class="uap-wizard-complete-page-link"><i class="fa-uap fa-cart-plus" aria-hidden="true"></i><?php esc_html_e( 'Explore Pro Addons ', 'uap' );?></a>
          </div>
        </div>
        <div class="uap-display-none">
            <?php  if ( $data['register_page'] && $data['register_page_title'] ):?>
                <p>
                    <a href="<?php echo esc_url( $data['register_page'] );?>"><?php echo esc_html( $data['register_page_title'] );?></a> | <a href="<?php echo esc_url($data['edit_register_page']);?>"><?php esc_html_e('Edit Register Page', 'uap');?></a>
                </p>
            <?php endif;?>
            <?php if ( $data['profile_page'] && $data['profile_page_title'] ):?>
                <p>
                    <a href="<?php echo esc_url( $data['profile_page'] );?>"><?php echo esc_html( $data['profile_page_title'] );?></a> | <a href="<?php echo esc_url($data['edit_profile_page']);?>"><?php esc_html_e('Edit Profile Page', 'uap');?></a>
                </p>
            <?php endif;?>
            <?php if ( $data['login_page'] && $data['login_page_title'] ):?>
                <p>
                    <a href="<?php echo esc_url( $data['login_page'] );?>"><?php echo esc_html( $data['login_page_title'] );?></a> | <a href="<?php echo esc_url($data['edit_login_page']);?>"><?php esc_html_e('Edit Login Page', 'uap');?></a>
                </p>
            <?php endif;?>
        </div>
    </div>
    <!------------------------------------ end of step 6. ------------------------------------>

    <div class="uap-wizard-before-message"></div>

  </div>
    <div class="uap-wizard-general-buttons">
        <?php $show = $data['page']  < 6 ? 'uap-display-inline' : 'uap-display-none';?>
        <span class="uap-wizard-button uap-js-wizard-go-next <?php echo esc_attr($show);?> " id="uap_wizard_go_next_bttn" data-complete_label="<?php esc_html_e( 'Complete', 'uap');?>" data-next="<?php esc_html_e( 'Continue', 'uap');?>"><?php esc_html_e('Continue', 'uap');?></span>
        <?php $show = ( $data['page'] === 1 ) ? 'uap-display-inline' : 'uap-display-none';?>
        <span class="uap-wizard-button uap-js-wizard-skip-step-1 uap-cursor-pointer <?php echo esc_attr($show);?>" id="uap_wizard_skip_step_1"><?php esc_html_e('Skip this Step', 'uap');?></span>
        <?php $show = ( $data['page'] > 1 && $data['page'] < 6 ) ? 'uap-display-inline' : 'uap-display-none';?>
        <span class="uap-wizard-button uap-js-wizard-go-back <?php echo esc_attr($show);?> " id="uap_wizard_go_back_bttn" ><?php esc_html_e('Back', 'uap');?></span>

    </div>

    <?php $show = $data['page'] < 6 ? 'uap-display-block' : 'uap-display-none';?>
    <div class="uap-wizard-footer-wrapper">
      <span class="<?php echo esc_attr($show);?> uap-js-skip-wizard uap-cursor-pointer" id="uap_wizard_skip_the_setup_bttn" data-redirect="<?php echo esc_url( admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=dashboard' ) );?>"><?php esc_html_e('Skip The Wizard and Setup Manually', 'uap');?></span>
    </div>
</div>
