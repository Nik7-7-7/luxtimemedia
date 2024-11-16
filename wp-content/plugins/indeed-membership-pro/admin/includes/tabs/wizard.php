
<?php //$data['page'] = 3;?>
<div id="iump_wizard_content_wrapp" class="iump-js-wizard-content-wrapp iump-wizard-main-wrapper" data-current_page="<?php echo $data['page'];?>" >

  <div class="iump-wizard-header-wrapper">
    <div class="iump-page-headline">Ultimate Membership Pro - <?php esc_html_e( 'Wizard Setup', 'ihc' );?></div>
    <p class="ihc-top-message"><?php esc_html_e( 'Easily set up your configurations to get started', 'ihc');?></p>
  </div>
  <div class="iump-wizard-progress-bar-wrapp iump-wizard-js-progress-bar">
      <?php $isActive = $data['page'] === 1 ? 'iump-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 1 ? 'iump-wizard-progress-bar-item-completed' : '';?>
      <div class="iump-wizard-progress-bar-item iump-wizard-pbi-1 iump-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="iump-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="iump-wizard-progress-bar-step">1</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="iump-wizard-progress-bar-item-label">
          <?php esc_html_e('License', 'ihc');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 2 ? 'iump-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 2 ? 'iump-wizard-progress-bar-item-completed' : '';?>
      <div class="iump-wizard-progress-bar-item iump-wizard-pbi-2 iump-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="iump-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="iump-wizard-progress-bar-step">2</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="iump-wizard-progress-bar-item-label">
          <?php esc_html_e('General Options', 'ihc');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 3 ? 'iump-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 3 ? 'iump-wizard-progress-bar-item-completed' : '';?>
      <div class="iump-wizard-progress-bar-item iump-wizard-pbi-3 iump-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="iump-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="iump-wizard-progress-bar-step">3</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="iump-wizard-progress-bar-item-label">
          <?php esc_html_e('Payment Options', 'ihc');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 4 ? 'iump-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 4 ? 'iump-wizard-progress-bar-item-completed' : '';?>
      <div class="iump-wizard-progress-bar-item iump-wizard-pbi-4 iump-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="iump-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="iump-wizard-progress-bar-step">4</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="iump-wizard-progress-bar-item-label">
          <?php esc_html_e('Membership Plan', 'ihc');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 5 ? 'iump-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] > 5 ? 'iump-wizard-progress-bar-item-completed' : '';?>
      <div class="iump-wizard-progress-bar-item iump-wizard-pbi-5 iump-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="iump-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="iump-wizard-progress-bar-step">5</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="iump-wizard-progress-bar-item-label">
          <?php esc_html_e('Email Notifications', 'ihc');?>
        </div>
      </div>
      <?php $isActive = $data['page'] === 6 ? 'iump-wizard-progress-bar-item-selected' : '';?>
      <?php $isCompleted = $data['page'] === 6 ? 'iump-wizard-progress-bar-item-completed' : '';?>
      <div class="iump-wizard-progress-bar-item iump-wizard-pbi-6 iump-wizard-js-progress-bar-item <?php echo esc_attr($isActive).esc_attr($isCompleted);?>">
        <div class="iump-wizard-progress-bar-item-icon-wrapper">
          <?php if($isCompleted): ?>
          <span>
            <svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg>
          </span>
        <?php else: ?>
          <span>
            <span class="iump-wizard-progress-bar-step">6</span>
          </span>
        <?php endif; ?>
        </div>
        <div class="iump-wizard-progress-bar-item-label">
          <?php esc_html_e('Complete', 'ihc');?>
        </div>
      </div>
  </div>

  <div id="iump_wizard_content" class="iump-wizard-wrap-for-content-wrapper">

    <!------------------------------------ step 1. ------------------------------------>
    <?php $show = $data['page'] === 1 ? 'ihc-display-block' : 'ihc-display-none';?>
    <div class="iump-js-wizard-step-1 <?php echo esc_attr($show);?>">
      <h3 class="iump-wizard-wrap-for-content-title"><span>01</span> - <?php esc_html_e('License', 'ihc');?></h3>
      <div class="iump-wizard-wrap-for-content-description">
        <?php esc_html_e('Upon plugin activation, make sure to activate your license to access support and enable automatic upgrades. Please note that without license activation, access to advanced modules will not be available', 'ihc');?>
      </div>
      <div class="iump-wizard-wrap-for-content">

        <div class="row ihc-row-no-margin">
          <div class="col-xs-10">
          <div class="lincs-row">
            <h4><?php esc_html_e('Customer Name', 'ihc');?></h4>
              <input name="cn" type="text" value="" class="ihc-form-element"/>
          </div>
          <div class="lincs-row">
            <h4><?php esc_html_e('Vendor', 'ihc');?><span>*</span></h4>
            <?php $currentVend = get_option('ium'.'p_l'.'nk_v');?>
            <select name="ls" class="iump-form-select ihc-form-element ihc-form-select">
                <?php foreach ( IUMP_VEND as $vendName => $vendValue ):?>
                    <option value="<?php echo $vendName;?>" <?php if ( $vendName === $currentVend ){ echo 'selected'; }?> ><?php echo $vendValue;?></option>
                <?php endforeach;?>
            </select>
          </div>
          <div class="lincs-row">
            <h4><?php esc_html_e('License Code', 'ihc');?><span>*</span></h4>
            <input name="pv2" type="text" value="<?php echo esc_attr($data['h']);?>" class="iump-js-wizard-pc ihc-form-element iump-js-wizard-required-field"/>
            <input type="submit" value="<?php esc_html_e('Activate License', 'ihc');?>" name="ihc_save_licensing_code"  class="iump-submit-button" />

          </div>
          <div>
            <p class="iump-wizard-additional-details"><a target="_blank" href="https://ultimatemembershippro.com/find-my-license-code/"><?php esc_html_e('Where can I find my License Code?', 'ihc');?></a></p>
          </div>

          <div id="iump_js_error_message_for_pv2" class="ihc-display-none iump-wizard-field-notice"></div>

          <div class="ihc-clear"></div>
          <span class="ihc-js-help-page-data"
                data-nonce="<?php echo wp_create_nonce('ihc_license_nonce');?>"
                data-revoke_url="<?php echo admin_url('admin.php?page=ihc_manage&tab=help&revoke=true');?>"
                data-help="<?php esc_html_e( "Please Enter the Purchase Code", 'ihc' );?>"
          ></span>
          <?php if ( $data['license_message'] ):?>
              <?php echo $data['license_message'];?>
          <?php endif;?>
        </div>
      </div>
      </div>
    </div>
    <!------------------------------------ end of step 1. ------------------------------------>

    <!------------------------------------ step 2. ------------------------------------>
    <?php $show = $data['page'] === 2 ? 'ihc-display-block' : 'ihc-display-none';?>
    <div class="iump-js-wizard-step-2 <?php echo esc_attr($show);?>">
      <h3 class="iump-wizard-wrap-for-content-title"><span>02</span> - <?php esc_html_e('General Options', 'ihc');?></h3>
      <div class="iump-wizard-wrap-for-content-description">
        <?php esc_html_e('Customize essential settings such as Sign-Up options and Currency in the General Options of the Wizard for a personalized experience', 'ihc');?>
      </div>
      <div class="iump-wizard-wrap-for-content">
      <div class="iump-form-line">
          <h4><?php esc_html_e('New Memebers WordPress Role', 'ihc');?></h4>
          <select name="ihc_register_new_user_role" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
              <?php foreach ( $data['roles'] as $key => $value ):?>
                  <option value="<?php echo esc_attr($key);?>" <?php if ( $data['ihc_register_new_user_role'] == $key ){ echo esc_attr('selected');}?> ><?php echo esc_html($value);?></option>
              <?php endforeach;?>
          </select>
      </div>
      <div class="iump-form-line">
        <h4><?php esc_html_e('Default Currency', 'ihc');?></h4>
        <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select iump-wizard-default-currency" name="ihc_currency">
          <?php
            foreach ($data['currency_arr'] as $k=>$v){
              ?>
              <option value="<?php echo esc_attr($k);?>" <?php if ($k==$data['ihc_currency']){ echo esc_attr('selected');}?> >
                <?php echo esc_html($v);?>
                <?php if (is_array($data['custom_currencies']) && in_array($v, $data['custom_currencies'])){ esc_html_e(" (Custom Currency)");}?>
              </option>
              <?php
            }
          ?>
        </select>
      </div>
      <div class="iump-form-line">
        <h4><?php esc_html_e('Currency Position', 'ihc');?></h4>
      <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_currency_position">
        <?php
          foreach ($data['currency_position_arr'] as $k=>$v){
            ?>
            <option value="<?php echo esc_attr($k);?>" <?php if ($k==$data['ihc_currency_position']){ echo esc_attr('selected');}?> >
              <?php echo esc_html($v);?>
            </option>
            <?php
          }
        ?>
      </select>
    </div>
    <div class="iump-form-line">
      <h4><?php esc_html_e('Default Country', 'ihc');?></h4>
          <select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" name="ihc_default_country" >
              <?php foreach ( $data['countries'] as $key => $value ):?>
                  <option value="<?php echo esc_attr($key);?>" <?php if ( $data['ihc_default_country'] == $key ){ echo esc_attr('selected');}?> ><?php echo esc_html($value);?></option>
              <?php endforeach;?>
          </select>
          <ul id="ihc_countries_list_ul ihc-display-none"></ul>
      </div>


        <div class="iump-form-line">
          <label class="iump_label_shiwtch ihc-switch-button-margin">
            <?php $checked = ($data['ihc_register_auto_login']) ? 'checked' : '';?>
            <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_register_auto_login');" <?php echo esc_attr($checked);?> />
            <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" name="ihc_register_auto_login" value="<?php echo esc_attr($data['ihc_register_auto_login']);?>" id="ihc_register_auto_login" />
          <?php esc_html_e('After signing up, customers are automatically logged in', 'ihc');?>
        </div>

          <div class="iump-form-line">
            <label class="iump_label_shiwtch ihc-switch-button-margin">
              <?php $checked = ($data['ihc_security_allow_search_engines']) ? 'checked' : '';?>
              <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_security_allow_search_engines');" <?php echo esc_attr($checked);?> />
              <div class="switch ihc-display-inline"></div>
            </label>
            <input type="hidden" name="ihc_security_allow_search_engines" value="<?php echo esc_attr($data['ihc_security_allow_search_engines']);?>" id="ihc_security_allow_search_engines" />
            <?php esc_html_e('Turn On Search engines Access for Restricted pages', 'ihc');?>
          </div>

          <div class="iump-form-line">
              <label class="iump_label_shiwtch ihc-switch-button-margin">
                <?php $checked = ($data['ihc_allow_tracking']) ? 'checked' : '';?>
                <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_allow_tracking');" <?php echo esc_attr($checked);?> />
                <div class="switch ihc-display-inline"></div>
              </label>
              <input type="hidden" name="ihc_allow_tracking" value="<?php echo esc_attr($data['ihc_allow_tracking']);?>" id="ihc_allow_tracking" />

              <?php esc_html_e('Enable Anonymous Usage Tracking Data', 'ihc');?>
          </div>
        </div>
    </div>
    <!------------------------------------ end of step 2. ---------------------------->

    <!------------------------------------ step 3. ------------------------------>
    <?php $show = $data['page'] === 3 ? 'ihc-display-block' : 'ihc-display-none';?>
    <div class="iump-js-wizard-step-3 <?php echo esc_attr($show);?>">
          <h3 class="iump-wizard-wrap-for-content-title"><span>03</span> - <?php esc_html_e('Payment Options', 'ihc');?></h3>
          <div class="iump-wizard-wrap-for-content-description">
            <?php esc_html_e('Upon plugin activation, make sure to activate your license to access support and enable automatic upgrades. Please note that without license activation, access to advanced modules will not be available', 'ihc');?>
          </div>

          <div class="iump-wizard-wrap-for-content">
          <!-- Stripe connect settings -->
          <div class="iump-wizard-payment-selection-wrapper">
          <label class="iump_label_shiwtch ihc-switch-button-margin">
            <?php $checked = ($data['ihc_stripe_connect_status']) ? 'checked' : '';?>
            <input type="checkbox" class="iump-switch iump-js-wizard-activate-stripe-connect" onClick="iumpCheckAndH(this, '#ihc_stripe_connect_status');" <?php echo esc_attr($checked);?> />
            <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" value="<?php echo esc_attr($data['ihc_stripe_connect_status']);?>" name="ihc_stripe_connect_status" id="ihc_stripe_connect_status" />
          <h5><?php esc_html_e('Stripe Connect Payment', 'ihc');?></h5>
          </div>
          <?php $show = (int)$data['ihc_stripe_connect_status'] === 1 ? 'ihc-display-block' : 'ihc-display-none';?>
          <div class="iump-wizard-payment-settings-wrapper iump-js-stripe-connect-wrapp <?php echo esc_attr($show);?>">
              <?php
                  $returnUrl = admin_url( 'admin.php?page=ihc_manage&tab=wizard&step=3' );
                  $stripeConnect = new \Indeed\Ihc\Gateways\StripeConnect();
                  $authUrl = $stripeConnect->getAuthUrl( 0, $returnUrl );
                  $notify_url = add_query_arg('ihc_action', 'stripe_connect', $data['site_url'] );
              ?>
              <input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
                  <div class="iump-form-line">
                      <label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
                        <input type="radio" name="radio_for_ihc_stripe_connect_live_mode" class="" value="0" <?php if ( (int)$data['ihc_stripe_connect_live_mode'] === 0 ) echo esc_attr('checked');?> onchange="iumpPutValueIntoHidden( 'ihc_stripe_connect_live_mode', 0 );iumpUpdateStripeConnectAuthUrlWizard();" />
                        <span class="iump-checkmark-rd"></span>
                      </label>
                      <label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
                        <input type="radio" name="radio_for_ihc_stripe_connect_live_mode" class="" value="1" <?php if ( (int)$data['ihc_stripe_connect_live_mode'] === 1 ) echo esc_attr('checked');?> onchange="iumpPutValueIntoHidden( 'ihc_stripe_connect_live_mode', 1 );iumpUpdateStripeConnectAuthUrlWizard();" />
                        <span class="iump-checkmark-rd"></span>
                      </label>
                  </div>
                  <input type="hidden" name="ihc_stripe_connect_live_mode" value="<?php echo (int)$data['ihc_stripe_connect_live_mode'];?>"/>


                  <div class="ihc-stripe-connect-live-wrapper">
                    <?php
                        $stripeConnect = new \Indeed\Ihc\Gateways\StripeConnect();
                        $authUrl = $stripeConnect->getAuthUrl( 1, admin_url( 'admin.php?page=ihc_manage&tab=wizard&step=3' ) );
                        $extraClass = empty( $data['ihc_stripe_connect_live_mode'] ) ? 'ihc-display-none' : 'ihc-display-block';
                    ?>
                    <div class="ihc-js-stripe-connect-live <?php echo esc_attr($extraClass);?>">
                      <div class="iump-form-line">
                        <?php
                          if ( $data['ihc_stripe_connect_publishable_key'] === '' && $data['ihc_stripe_connect_client_secret'] === ''
                                && $data['ihc_stripe_connect_account_id'] === '' ):
                        ?>
                          <h4><?php esc_html_e('Connect with your Stripe Account', 'ihc');?></h4>
                          <div class="ihc-stripe-connect-btn-wizard-wrapper">
                            <a href="<?php echo esc_url($authUrl);?>" class="ihc-stripe-connect-btn-wizard"><span><?php esc_html_e( 'Connect with Stripe', 'ihc');?></span></a>
                          </div>

                        <?php else :?>
                              <div class="ihc-stripe-connect-status"><?php esc_html_e('Your Stripe Account is connected in Live Mode', 'ihc');?></div>
                        <?php endif;?>
                        </div>

                        <input type="hidden" name="ihc_stripe_connect_publishable_key" value="<?php echo $data['ihc_stripe_connect_publishable_key'];?>" />
                        <input type="hidden" name="ihc_stripe_connect_client_secret" value="<?php echo $data['ihc_stripe_connect_client_secret'];?>" />
                        <input type="hidden" name="ihc_stripe_connect_account_id" value="<?php echo $data['ihc_stripe_connect_account_id'];?>" />
                        <div id="iump_js_error_message_for_ihc_stripe_connect_publishable_key" class="ihc-display-none iump-wizard-field-notice"></div>

                        <div class="iump-form-line ihc-stripe-connect-setup-details">
                          <h4><?php esc_html_e('Webhook Setup', 'ihc');?></h4>
                          <p><?php esc_html_e('Set up a WebHook URL in your Stripe account, including necessary event packages ', 'ihc'); echo esc_ump_content('<a href="https://dashboard.stripe.com/webhooks" target="_blank">'.esc_html__( 'here', 'ihc' ).'</a>'); ?></p>
                          <p><strong><?php echo esc_url($notify_url); ?></strong></p>
                          <p><?php esc_html_e('Choose all the events from "Charge", "Customer", "Invoice", "Subscription Schedule".', 'ihc');?></p>
                        </div>
                    </div>
                    <?php
                        $authUrl = $stripeConnect->getAuthUrl( 0, admin_url( 'admin.php?page=ihc_manage&tab=wizard&step=3' ) );
                        $extraClass = empty( $data['ihc_stripe_connect_live_mode'] ) ? 'ihc-display-block' : 'ihc-display-none';
                    ?>
                    <div class="ihc-js-stripe-connect-sandbox <?php echo esc_attr($extraClass);?>">
                      <div class="iump-form-line">
                      <?php
                        if ( $data['ihc_stripe_connect_test_publishable_key'] === '' && $data['ihc_stripe_connect_test_client_secret'] === ''
                              && $data['ihc_stripe_connect_test_account_id'] === '' ):
                      ?>
                          <h4><?php esc_html_e('Connect with your Stripe Test Account', 'ihc');?></h4>
                            <div class="ihc-stripe-connect-btn-wizard-wrapper">
                              <a href="<?php echo esc_url($authUrl);?>" class="ihc-stripe-connect-btn-wizard"><span><?php esc_html_e( 'Connect with Stripe', 'ihc' );?></span></a>
                            </div>
                      <?php else :?>
                          <div class="ihc-stripe-connect-status"><?php esc_html_e('Your Stripe Account is connected in Test Mode', 'ihc');?></div>
                      <?php endif;?>

                      <input type="hidden" name="ihc_stripe_connect_test_publishable_key" value="<?php echo $data['ihc_stripe_connect_test_publishable_key'];?>" />
                      <input type="hidden" name="ihc_stripe_connect_test_client_secret" value="<?php echo $data['ihc_stripe_connect_test_client_secret'];?>" />
                      <input type="hidden" name="ihc_stripe_connect_test_account_id" value="<?php echo $data['ihc_stripe_connect_test_account_id'];?>" />
                      <div id="iump_js_error_message_for_ihc_stripe_connect_test_publishable_key" class="ihc-display-none iump-wizard-field-notice"></div>

                      </div>
                      <div class="iump-form-line ihc-stripe-connect-setup-details">
                        <h4><?php esc_html_e('Webhook Setup', 'ihc');?></h4>
                        <p><?php esc_html_e('Setup WebHook URL into your Stripe Account (Test Mode) including specific events packages ', 'ihc'); echo esc_ump_content('<a href="https://dashboard.stripe.com/test/webhooks" target="_blank">'.esc_html__( 'here', 'ihc' ).'</a>'); ?></p>
                        <p><strong><?php echo esc_url($notify_url); ?></strong></p>
                        <p><?php esc_html_e('Choose all the events from "Charge", "Customer", "Invoice", "Subscription Schedule".', 'ihc');?></p>
                      </div>
                    </div>
                    <div class="iump-form-line">
                      <p><?php esc_html_e('Check out our ', 'ihc');?> <a href="https://ultimatemembershippro.com/stripe-connect-tutorial/" target="_blank"><?php esc_html_e('Documentation', 'ihc');?></a><?php esc_html_e(' for additional details and complete Setup Steps.', 'ihc');?></p>
                    </div>
              </div>

              <div class="row ihc-row-no-margin ihc-display-none">
                <div class="col-xs-6">
                  <div class="iump-form-line iump-no-border input-group">
                    <span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
                    <input type="text" name="ihc_stripe_connect_label" class="form-control iump-js-wizard-required-field" value="<?php echo esc_attr($data['ihc_stripe_connect_label']);?>" />
                  </div>
                  <div id="iump_js_error_message_for_ihc_stripe_connect_label" class="ihc-display-none iump-wizard-field-notice"></div>
                </div>
              </div>

          </div>
          <!-- Stripe connect settings -->

          <!-- paypal -->
          <div class="iump-wizard-payment-selection-wrapper">
          <label class="iump_label_shiwtch ihc-switch-button-margin">
              <?php $checked = ($data['ihc_paypal_status']) ? 'checked' : '';?>
              <input type="checkbox" class="iump-switch iump-js-wizard-activate-paypal" onClick="iumpCheckAndH(this, '#ihc_paypal_status');" <?php echo esc_attr($checked);?> />
              <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" value="<?php echo esc_attr($data['ihc_paypal_status']);?>" name="ihc_paypal_status" id="ihc_paypal_status" />
          <h5><?php esc_html_e('PayPal Payment', 'ihc');?> </h5>
          </div>
          <?php $show = (int)$data['ihc_paypal_status'] === 1 ? 'ihc-display-block' : 'ihc-display-none';?>

          <div class="iump-wizard-payment-settings-wrapper iump-js-paypal-wrapp <?php echo $show?>">

           <div class="iump-form-line input-group">
             <label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
               <input type="radio" name="radio_for_ihc_paypal_sandbox" onchange="iumpPutValueIntoHidden( 'ihc_paypal_sandbox', 1 );" class="" value="1" <?php if ( (int)$data['ihc_paypal_sandbox'] === 1 ){ echo esc_attr('checked');}?> />
               <span class="iump-checkmark-rd"></span>
             </label>
             <label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
               <input type="radio" name="radio_for_ihc_paypal_sandbox" onchange="iumpPutValueIntoHidden( 'ihc_paypal_sandbox', 0 );"  class="" value="0" <?php if ( (int)$data['ihc_paypal_sandbox'] === 0 ){ echo esc_attr('checked');}?> />
               <span class="iump-checkmark-rd"></span>
             </label>
            </div>

            <input type="hidden" name="ihc_paypal_sandbox" value="<?php echo (int)$data['ihc_paypal_sandbox'];?>"/>

           <div class="row ihc-row-no-margin">
             <div class="col-xs-6">
               <?php $showSandboxLabel = (int)$data['ihc_paypal_sandbox'] === 1 ? 'ihc-display-block' : 'ihc-display-none';?>
               <div class="iump-form-line input-group iump-no-border">
                  <span class="input-group-addon" ><?php esc_html_e('Merchant Email', 'ihc');?> <span class="iump-wizard-paypal-sandbox-sign <?php echo esc_attr($showSandboxLabel);?>"><?php esc_html_e( ' (Sandbox)', 'ihc');?></span></span>
                  <input type="text" value="<?php echo esc_attr($data['ihc_paypal_email']);?>" name="ihc_paypal_email" class="form-control iump-js-wizard-required-field"/>
               </div>
               <div id="iump_js_error_message_for_ihc_paypal_email" class="ihc-display-none iump-wizard-field-notice"></div>

               <div class="iump-form-line input-group iump-no-border">
                    <span class="input-group-addon" ><?php esc_html_e('Merchant account ID', 'ihc');?> <span class="iump-wizard-paypal-sandbox-sign <?php echo esc_attr($showSandboxLabel);?>"><?php esc_html_e( ' (Sandbox)', 'ihc');?></span></span>
                    <input type="text" value="<?php echo esc_attr($data['ihc_paypal_merchant_account_id']);?>" name="ihc_paypal_merchant_account_id"  class="form-control iump-js-wizard-required-field" />
               </div>

               <div id="iump_js_error_message_for_ihc_paypal_merchant_account_id" class="ihc-display-none iump-wizard-field-notice"></div>

             </div>
           </div>
           <div class="iump-form-line iump-no-border">
           <h4><?php esc_html_e("WebHook Setup", 'ihc');?></h4>
           <p><?php esc_html_e("Set up a Notification URL in your PayPal account, by going to 'Account Settings -> Website payments -> Instant payment Notifications' section", 'ihc');?></p>
           <p><strong><?php echo esc_url($data['site_url'] . '?ihc_action=paypal');?></strong></p>
         </div>
                <div class="row ihc-row-no-margin ihc-display-none">
                  <div class="col-xs-6">
                    <div class="iump-form-line iump-no-border input-group">
                      <span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
                      <input type="text" name="ihc_paypal_label" class="form-control iump-js-wizard-required-field" value="<?php echo esc_attr($data['ihc_paypal_label']);?>" />
                    </div>

                    <div id="iump_js_error_message_for_ihc_paypal_label" class="ihc-display-none iump-wizard-field-notice"></div>

                  </div>
                </div>
          </div>
          <!-- end of paypal -->

          <!-- bank transfer -->
          <div class="iump-wizard-payment-selection-wrapper">
            <label class="iump_label_shiwtch ihc-switch-button-margin">
              <?php $checked = ($data['ihc_bank_transfer_status']) ? 'checked' : '';?>
              <input type="checkbox" class="iump-switch iump-js-wizard-activate-bt" onClick="iumpCheckAndH(this, '#ihc_bank_transfer_status');" <?php echo esc_attr($checked);?> />
              <div class="switch ihc-display-inline"></div>
            </label>
            <h5><?php esc_html_e('Bank Transfer Payment', 'ihc');?> </h5>
            <input type="hidden" value="<?php echo esc_attr($data['ihc_bank_transfer_status']);?>" name="ihc_bank_transfer_status" id="ihc_bank_transfer_status" />
          </div>

          <?php $show = (int)$data['ihc_bank_transfer_status'] === 1 ? 'ihc-display-block' : 'ihc-display-none';?>
          <div class="iump-wizard-payment-settings-wrapper iump-js-bt-wrapp <?php echo esc_attr($show);?>">
              <div class="iump-form-line">
                      <h5><?php esc_html_e('Bank Transfer Instructions', 'ihc');?> </h5>
                      <p><?php esc_html_e('Buyers will receive instructions via the trank you page. For a dynamic and full description, make use of the accessible constants', 'ihc');?></p>

                <div class="ihc-payment-bank-editor">
                  <?php wp_editor( stripslashes( $data['ihc_bank_transfer_message'] ), 'ihc_bank_transfer_message', [ 'textarea_name'=>'ihc_bank_transfer_message', 'quicktags'=>false , 'media_buttons'=> false, 'textarea_rows ' => '15', 'teeny'=> true  ] );?>
                </div>
                <div class="ihc-payment-bank-editor-constants">
                  <div>{siteurl}</div>
                  <div>{username}</div>
                  <div>{first_name}</div>
                  <div>{last_name}</div>
                  <div>{user_id}</div>
                  <div>{level_id}</div>
                  <div>{level_name}</div>
                  <div>{amount}</div>
                  <div>{currency}</div>
                </div>
                <div class="ihc-clear"></div>
                <div id="iump_js_error_message_for_ihc_bank_transfer_message" class="ihc-display-none iump-wizard-field-notice"></div>

                <div class="row ihc-row-no-margin ihc-display-none">
                  <div class="col-xs-6">
                    <div class="iump-form-line iump-no-border input-group">
                      <span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
                      <input type="text" name="ihc_bank_transfer_label" class="form-control iump-js-wizard-required-field" value="<?php echo esc_attr($data['ihc_bank_transfer_label']);?>" />
                    </div>

                    <div id="iump_js_error_message_for_ihc_bank_transfer_label" class="ihc-display-none iump-wizard-field-notice"></div>
                  </div>
                </div>

            </div>
          </div>
          <!-- end of bank transfer -->

          <input type="hidden" name="ihc_payment_selected" value="<?php echo $data['ihc_payment_selected'];?>" class="iump-js-wizard-default-payment" />
        </div>
    </div>
    <!------------------------------------ end of step 3. ------------------------------------>

    <!------------------------------------ step 4. ------------------------------------>
    <?php $show = $data['page'] === 4 ? 'ihc-display-block' : 'ihc-display-none';?>
    <div class="iump-js-wizard-step-4 <?php echo esc_attr($show);?> ">
      <h3 class="iump-wizard-wrap-for-content-title"><span>04</span> - <?php esc_html_e( 'Membership Plan', 'ihc' );?></h3>
      <div class="iump-wizard-wrap-for-content-description">
        <?php esc_html_e('Explore a variety of Free and Paid membership plans with diverse general and Subscription Types & Amount-related options available here', 'ihc');?>
      </div>
      <div class="iump-wizard-wrap-for-content">
        <div class="iump-form-line iump-no-border">
        <div class="row ihc-row-no-margin">
          <div class="col-xs-6">
            <div class="input-group">
               <span class="input-group-addon input-group-addon-150"><?php esc_html_e('Membership Name', 'ihc');?></span>
               <input name="label" class="form-control iump-js-wizard-required-field" type="text" value="<?php echo esc_attr($data['membership']['label']);?>" placeholder="<?php esc_html_e('suggestive Membership Name', 'ihc');?>"/>
               <div class="ihc-clear"></div>
            </div>

            <div id="iump_js_error_message_for_label" class="ihc-display-none iump-wizard-field-notice"></div>

          </div>
        </div>
      </div>
      <div class="iump-form-line iump-no-border form-required">
        <h4><?php esc_html_e( 'Access Type', 'ihc' );?></h4>
          <div class="iump-wizard-access-type-wrapper ihc-cursor-pointer">
              <?php
              foreach ($data['access_types'] as $k=>$v){
                $selected = ($data['membership']['access_type']==$k) ? 'iump-wizard-ati-selected' : '';
                ?>
                  <div data-value="<?php echo esc_attr($k);?>" class="iump-wizard-access-type-item <?php echo esc_attr($selected);?>" ><?php echo esc_html($v);?></div>
                <?php
              }?>
          </div>
          <input type="hidden" name="access_type" value="<?php echo esc_attr($data['membership']['access_type']);?>" class="iump-wizard-js-access-type-value" />
      </div>

      <div class="iump-form-line iump-no-border">
        <div id="limited_access_metas" class="ihc-membership-type-settings <?php if ($data['membership']['access_type']=='limited'){ echo esc_attr('ihc-display-block'); } else { echo esc_attr('ihc-display-none'); }?>">
            <h4><?php esc_html_e('Duration', 'ihc');?></h4>
            <div class="ihc-membership-type-settings-regular-period">
                <input type="number" value="<?php echo esc_attr($data['membership']['access_limited_time_value']);?>" name="access_limited_time_value" min="1" max="31" class="ihc-access_limited_time_value iump-js-wizard-required-field"/>
                <select name="access_limited_time_type" class="ihc-access_limited_time_type">
                    <?php
                      foreach ($data['time_types'] as $k=>$v){
                        $selected = ($data['membership']['access_limited_time_type']==$k) ? 'selected' : '';
                        ?>
                          <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
                        <?php
                      }
                    ?>
                </select>
            </div>
            <div id="iump_js_error_message_for_access_limited_time_value" class="ihc-display-none iump-wizard-field-notice"></div>
        </div>

        <div id="date_interval_access_metas" class="ihc-membership-type-settings <?php if ($data['membership']['access_type']=='date_interval'){ echo esc_attr('ihc-display-block');} else {echo esc_attr('ihc-display-none');}?>">
          <div class="ihc-date_interval_access_metas">
            <h5><?php esc_html_e('Fix Starting Date', 'ihc');?></h5>
            <input type="text" value="<?php echo esc_attr($data['membership']['access_interval_start']);?>" name="access_interval_start" id="access_interval_start" class=" iump-js-wizard-required-field" />
            <div id="iump_js_error_message_for_access_interval_start" class="ihc-display-none iump-wizard-field-notice"></div>
          </div>
          <div>
            <h5><?php esc_html_e('Fix Expiration Date', 'ihc');?></h5>
            <input type="text" value="<?php echo esc_attr($data['membership']['access_interval_end']);?>" name="access_interval_end" id="access_interval_end" class=" iump-js-wizard-required-field" />
            <div id="iump_js_error_message_for_access_interval_end" class="ihc-display-none iump-wizard-field-notice"></div>
          </div>
        </div>

        <div id="regular_period_access_metas" class="ihc-membership-type-settings <?php if ($data['membership']['access_type']=='regular_period'){ echo esc_attr('ihc-display-block');} else{ echo esc_attr('ihc-display-none');}?>">
            <h4><?php esc_html_e('Billing Cycle', 'ihc');?></h4>
            <div class="ihc-membership-type-settings-regular-period">
                <input type="number" value="<?php echo esc_attr($data['membership']['access_regular_time_value']);?>" name="access_regular_time_value" min="1"  class="ihc-access_regular_time_value iump-js-wizard-required-field" />
                <select name="access_regular_time_type" class="ihc-access_regular_time_type">
                  <?php
                    foreach ($data['time_types'] as $k=>$v){
                      $selected = ($data['membership']['access_regular_time_type']==$k) ? 'selected' : '';
                      ?>
                        <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_attr($v);?></option>
                      <?php
                    }
                  ?>
                </select>
            </div>
            <div id="iump_js_error_message_for_access_regular_time_value" class="ihc-display-none iump-wizard-field-notice"></div>
        </div>
      </div>
      <div id="payment_options"  class="ihc-price-wrapper">
        <div class="iump-form-line iump-no-border" id="level_price_wd">
            <h4><?php esc_html_e('Membership Amount', 'ihc');?></h4>
            <div class="row ihc-row-no-margin">
              <div class="col-xs-6">
                  <div class="input-group">
                    <input type="number" min="0" value="<?php echo esc_attr($data['membership']['price']);?>" name="price" step="0.01" class="form-control iump-js-wizard-required-field" />
                    <div class="input-group-addon iump-wizard-currency-of-price">
                    <?php echo esc_html($data['ihc_currency']); ?>
                    </div>
                 </div>
                 <div id="iump_js_error_message_for_price" class="ihc-display-none iump-wizard-field-notice"></div>
             </div>
           </div>
        </div>
      </div>
      <input type="hidden" class="iump-js-wizard-lid-field" name="level_id" value="<?php echo $data['membership']['level_id'];?>" />
    </div>
    </div>
    <!------------------------------------ end of step 4. ------------------------------------>


    <!------------------------------------ step 5. ------------------------------------>
    <?php $show = $data['page'] === 5 ? 'ihc-display-block' : 'ihc-display-none';?>
    <div class="iump-js-wizard-step-5 <?php echo $show?> ">

        <h3 class="iump-wizard-wrap-for-content-title"><span>05</span> - <?php esc_html_e('Email Notifications', 'ihc');?></h3>
        <div class="iump-wizard-wrap-for-content-description">
          <?php esc_html_e('Refine your Email Notifications by configuring sender details and enabling initial notifications', 'ihc');?>
        </div>
        <div class="iump-wizard-wrap-for-content">
        <div class="iump-form-line  iump-no-border">
          <h4><?php esc_html_e('Sender Details', 'ihc');?></h4>
        </div>
        <div class="iump-form-line iump-no-border">
          <div class="row">
            <div class="col-xs-6">
              <div class="input-group">
                <span class="input-group-addon" ><?php esc_html_e("Email Address", 'ihc');?></span>
                <input type="text" name="ihc_notification_email_from" value="<?php echo esc_attr($data['ihc_notification_email_from']);?>"  class="form-control iump-js-wizard-required-field" />
              </div>
              <div id="iump_js_error_message_for_ihc_notification_email_from" class="ihc-display-none iump-wizard-field-notice"></div>
            </div>
          </div>
        </div>
        <div class="iump-form-line iump-no-border">
          <div class="row">
            <div class="col-xs-6">
          <div class="input-group">
            <span class="input-group-addon" ><?php esc_html_e("Name", 'ihc');?></span>
            <input type="text" name="ihc_notification_name" value="<?php echo esc_attr($data['ihc_notification_name']);?>"  class="form-control" />
          </div>
        </div>
      </div>
    </div>
        <div class="iump-form-line">
          <h4><?php esc_html_e('Default Email Notifications', 'ihc');?></h4>
          <p><?php esc_html_e('Enable specific default email notifications for initial experience during initial setup', 'ihc');?></p>
            <?php foreach ( $data['notifications'] as $notificationSlug => $notificationData ):?>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                      <?php $checked = ($notificationData['status']) ? 'checked' : '';?>
                      <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#<?php echo esc_attr($notificationSlug);?>');" <?php echo esc_attr($checked);?> />
                  <div class="switch ihc-display-inline"></div>
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
    <?php $show = $data['page'] === 6 ? 'ihc-display-block' : 'ihc-display-none';?>
    <div class="iump-js-wizard-step-6 <?php echo $show?> ">
        <div class="iump-wizard-complete-top">
          <div class="iump-page-headline">Setup - <span class="iump-page-headline-colored"><?php esc_html_e( 'Completed', 'ihc' );?></span></div>
          <p class="ihc-top-message"><?php esc_html_e( 'Congratulations! You successfully installed your Membership site', 'ihc');?></p>
        </div>
        <div class="iump-wizard-complete-middle">
          <h4><?php esc_html_e( "What to do Next?", 'ihc' );?></h4>
          <div class="iump-wizard-complete-pages">
            <a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=users');?>" class="iump-wizard-complete-page-link"><i class="fa-ihc fa-users-ihc" aria-hidden="true"></i><?php esc_html_e( 'Go to Dashboard', 'ihc' );?></a>
            <a href="https://ultimatemembershippro.com/docs" target="_blank" class="iump-wizard-complete-page-link"><i class="fa-ihc fa-book" aria-hidden="true"></i><?php esc_html_e( 'Check our Documentation', 'ihc' );?></a>
            <a href="https://ultimatemembershippro.com/videos" target="_blank" class="iump-wizard-complete-page-link"><i class="fa-ihc fa-video" aria-hidden="true"></i><?php esc_html_e( 'See hour video Tutorials', 'ihc' );?></a>
            <a href="https://ultimatemembershippro.com/pro-addons/" target="_blank" class="iump-wizard-complete-page-link"><i class="fa-ihc fa-cart-plus" aria-hidden="true"></i><?php esc_html_e( 'Explore Pro Addons ', 'ihc' );?></a>
          </div>
        </div>
        <div class="ihc-display-none">
            <?php  if ( $data['register_page'] && $data['register_page_title'] ):?>
                <p>
                    <a href="<?php echo esc_url( $data['register_page'] );?>"><?php echo esc_html( $data['register_page_title'] );?></a> | <a href="<?php echo esc_url($data['edit_register_page']);?>"><?php esc_html_e('Edit Register Page', 'ihc');?></a>
                </p>
            <?php endif;?>
            <?php if ( $data['profile_page'] && $data['profile_page_title'] ):?>
                <p>
                    <a href="<?php echo esc_url( $data['profile_page'] );?>"><?php echo esc_html( $data['profile_page_title'] );?></a> | <a href="<?php echo esc_url($data['edit_profile_page']);?>"><?php esc_html_e('Edit Profile Page', 'ihc');?></a>
                </p>
            <?php endif;?>
            <?php if ( $data['login_page'] && $data['login_page_title'] ):?>
                <p>
                    <a href="<?php echo esc_url( $data['login_page'] );?>"><?php echo esc_html( $data['login_page_title'] );?></a> | <a href="<?php echo esc_url($data['edit_login_page']);?>"><?php esc_html_e('Edit Login Page', 'ihc');?></a>
                </p>
            <?php endif;?>
        </div>
    </div>
    <!------------------------------------ end of step 6. ------------------------------------>

    <div class="iump-wizard-before-message"></div>

  </div>
    <div class="iump-wizard-general-buttons">
        <?php $show = $data['page']  < 6 ? 'ihc-display-inline' : 'ihc-display-none';?>
        <span class="iump-wizard-button iump-js-wizard-go-next <?php echo esc_attr($show);?> " id="iump_wizard_go_next_bttn" data-complete_label="<?php esc_html_e( 'Complete', 'ihc');?>" data-next="<?php esc_html_e( 'Continue', 'ihc');?>"><?php esc_html_e('Continue', 'ihc');?></span>
        <?php $show = ( $data['page'] === 1 ) ? 'ihc-display-inline' : 'ihc-display-none';?>
        <span class="iump-wizard-button iump-js-wizard-skip-step-1 ihc-cursor-pointer <?php echo esc_attr($show);?>" id="iump_wizard_skip_step_1"><?php esc_html_e('Skip this Step', 'ihc');?></span>
        <?php $show = ( $data['page'] > 1 && $data['page'] < 6 ) ? 'ihc-display-inline' : 'ihc-display-none';?>
        <span class="iump-wizard-button iump-js-wizard-go-back <?php echo esc_attr($show);?> " id="iump_wizard_go_back_bttn" ><?php esc_html_e('Back', 'ihc');?></span>

    </div>

    <?php $show = $data['page'] < 6 ? 'ihc-display-block' : 'ihc-display-none';?>
    <div class="iump-wizard-footer-wrapper">
      <span class="<?php echo esc_attr($show);?> iump-js-skip-wizard ihc-cursor-pointer" id="iump_wizard_skip_the_setup_bttn" data-redirect="<?php echo esc_url( admin_url( 'admin.php?page=ihc_manage&tab=users' ) );?>"><?php esc_html_e('Skip The Wizard and Setup Manually', 'ihc');?></span>
    </div>
</div>
