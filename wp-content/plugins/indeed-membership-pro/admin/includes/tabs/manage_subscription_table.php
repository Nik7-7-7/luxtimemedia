<?php
if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_manage_subscription_table_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_manage_subscription_table_nonce']), 'ihc_manage_subscription_table_nonce' ) ){
    ihc_save_update_metas('manage_subscription_table');
}
$data['metas'] = ihc_return_meta_arr('manage_subscription_table');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form method="post">
  <div class="iump-page-headline"><?php esc_html_e('Subscriptions Table Showcase', 'ihc');?></div>
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Manage Subscriptions Table', 'ihc');?></h3>
		<div class="inside">

      <input type="hidden" name="ihc_manage_subscription_table_nonce" value="<?php echo wp_create_nonce( 'ihc_manage_subscription_table_nonce' );?>" />

      <div class="iump-form-line iump-no-border">
        <div class="row">
          <div class="col-xs-10">
            <h2><?php esc_html_e("Subscriptions Table Showcase", 'ihc');?></h2>
            <p><?php esc_html_e("Customize what information will show up for Members and what Actions will be available. By default, Subscription Table will show up on Member Portal->Subscriptions tab and can be also displayed with own shortcode:", 'ihc');?><strong>[ihc-account-page-subscriptions-table]</strong></p>
          </div>
        </div>
      </div>
      <div class="iump-form-line iump-no-border">
      </div>

      <div class="iump-special-line">
      <div class="iump-form-line iump-no-border">
        <h2><?php esc_html_e("Subscriptions Table Columns", 'ihc');?></h2>
         <p><?php esc_html_e("Choose which columns will show up on Member Interface", 'ihc');?></p>
      </div>

      <div class="iump-form-line iump-no-border">
        <table class="ihc-subscription-table-table-box wp-list-table widefat tags">
          <thead>
            <tr class="ihc-subscription-table-row-box">
              <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Membership", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <input disabled checked type="checkbox" class="iump-switch"/>
                    <div class="switch ihc-display-inline ihc-disabled-opacity"></div>
                  </label>
                  <input type="hidden" name="ihc_show_membership_column" value="" id="ihc_show_membership_column" />
                </div>
              </div>
            </th>
              <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Plan Details", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_plan_details_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_plan_details_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_plan_details_column" value="<?php echo esc_attr($data['metas']['ihc_show_plan_details_column']);?>" id="ihc_show_plan_details_column" />
                </div>
              </div>
            </th>

            <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Amount", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_amount_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_amount_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_amount_column" value="<?php echo esc_attr($data['metas']['ihc_show_amount_column']);?>" id="ihc_show_amount_column" />
                </div>
              </div>
            </th>

            <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Payment Service", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_payment_service_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_payment_service_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_payment_service_column" value="<?php echo esc_attr($data['metas']['ihc_show_payment_service_column']);?>" id="ihc_show_payment_service_column" />
                </div>
              </div>
            </th>

            <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Trial Period", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_trial_period_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_trial_period_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_trial_period_column" value="<?php echo esc_attr($data['metas']['ihc_show_trial_period_column']);?>" id="ihc_show_trial_period_column" />
                </div>
              </div>
            </th>

            <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Grace Period", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_grace_period_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_grace_period_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_grace_period_column" value="<?php echo esc_attr($data['metas']['ihc_show_grace_period_column']);?>" id="ihc_show_grace_period_column" />
                </div>
              </div>
            </th>

            <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Starts On", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_starts_on_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_starts_on_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_starts_on_column" value="<?php echo esc_attr($data['metas']['ihc_show_starts_on_column']);?>" id="ihc_show_starts_on_column" />
                </div>
              </div>
            </th>

            <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Expires On", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_expires_on_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_expires_on_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_expires_on_column" value="<?php echo esc_attr($data['metas']['ihc_show_expires_on_column']);?>" id="ihc_show_expires_on_column" />
                </div>
              </div>
            </th>

            <th class="ihc-subscription-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Status", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_status_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_status_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_status_column" value="<?php echo esc_attr($data['metas']['ihc_show_status_column']);?>" id="ihc_show_status_column" />
                </div>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Recurring Plan Trial 15 day Free</td>
            <td>Subscription - Monthly</td>
            <td>0.00USD for 15 days then 100.00USD every  month</td>
            <td>Bank Transfer</td>
            <td>Yes</td>
            <td> Yes - 1 day after expires</td>
            <td>-</td>
            <td>-</td>
            <td>Active</td>
          </tr>
        </tbody>
        </table>
    </div>
    </div>


    <div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Subscriptions Table Actions", 'ihc');?></h2>
       <p><?php esc_html_e("Choose which actions will be available in Subscriptions Table", 'ihc');?></p>
    </div>

    <div class="iump-form-line">
      <div class="row">
        <div class="col-xs-10">
          <h4> <?php esc_html_e("Show Finish Payment Button", 'ihc');?></h4>
          <p><?php esc_html_e("When Members as a paid Membership on Hold, because no payment confirmation have been received, this button will show up in order to complete his payment process and activate the Memberhsip", 'ihc');?></p>
        </div>
      </div>
      <div>
        <label class="iump_label_shiwtch ihc-switch-button-margin">
          <?php $checked = ($data['metas']['ihc_show_finish_payment']) ? 'checked' : '';?>
          <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_finish_payment');" <?php echo esc_attr($checked);?> />
          <div class="switch ihc-display-inline"></div>
        </label>
        <input type="hidden" name="ihc_show_finish_payment" value="<?php echo esc_attr($data['metas']['ihc_show_finish_payment']);?>" id="ihc_show_finish_payment" />
      </div>
        <div class="row">
          <div class="col-xs-10">
            <p><?php esc_html_e("Show Finish Payment button only after a certain time in order to leave enough time for payment service to confirm the payment and avoid Members paying twice for the same Membership.", 'ihc');?></p>
          </div>
        </div>
      <div class="row">
        <div class="col-xs-3">
          <div class="input-group">
            <input type="number" min="0" max="24" value="<?php echo esc_attr($data['metas']['ihc_subscription_table_finish_payment_after']);?>" name="ihc_subscription_table_finish_payment_after" step="1" class="form-control">
            <div class="input-group-addon"><?php esc_html_e("hours", 'ihc');?></div>
          </div>
      </div>
    </div>
    </div>

      <div class="iump-form-line">
      <div class="row">
        <div class="col-xs-10">
          <h4> <?php esc_html_e("Show Renew Membership option", 'ihc');?></h4>
          <p><?php esc_html_e("When a Member has a Membership that has expired, will have the option to Renew it directly from his Subscriptions table.", 'ihc');?></p>
        </div>
      </div>
        <div>
          <label class="iump_label_shiwtch ihc-switch-button-margin">
            <?php $checked = ($data['metas']['ihc_show_renew_link']) ? 'checked' : '';?>
            <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_renew_link');" <?php echo esc_attr($checked);?> />
            <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" name="ihc_show_renew_link" value="<?php echo esc_attr($data['metas']['ihc_show_renew_link']);?>" id="ihc_show_renew_link" />
        </div>
      </div>

      <div class="iump-form-line">
        <div class="row">
          <div class="col-xs-10">
            <h4> <?php esc_html_e("Show Pause & Resume Buttons", 'ihc');?></h4>
            <p><?php esc_html_e("Members will have the option to pause their current Membership period and to Resume later. This option is available only for one-time Memberships and not for recurring Subscriptions.", 'ihc');?></p>
          </div>
        </div>
        <div>
          <label class="iump_label_shiwtch ihc-switch-button-margin">
            <?php $checked = ($data['metas']['ihc_show_pause_resume_link']) ? 'checked' : '';?>
            <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_pause_resume_link');" <?php echo esc_attr($checked);?> />
            <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" name="ihc_show_pause_resume_link" value="<?php echo esc_attr($data['metas']['ihc_show_pause_resume_link']);?>" id="ihc_show_pause_resume_link" />
        </div>
      </div>

      <div class="iump-form-line">
        <div class="row">
          <div class="col-xs-10">
            <h4> <?php esc_html_e("Show Cancel Subscription option", 'ihc');?></h4>
            <p><?php esc_html_e("Active recurring Subscriptions may be cancelled by Members in order to stop being Charged next time. Subscription will remain active until will expire without being renewed anymore.", 'ihc');?></p>
          </div>
        </div>
        <div>
          <label class="iump_label_shiwtch ihc-switch-button-margin">
            <?php
            $checked = ($data['metas']['ihc_show_cancel_link']) ? 'checked' : '';?>
            <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_cancel_link');" <?php echo esc_attr($checked);?> />
            <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" name="ihc_show_cancel_link" value="<?php echo esc_attr($data['metas']['ihc_show_cancel_link']);?>" id="ihc_show_cancel_link" />
        </div>
      </div>

      <div class="iump-form-line">
        <div class="row">
          <div class="col-xs-10">
            <h4> <?php esc_html_e("Show Remove option", 'ihc');?></h4>
            <p><?php esc_html_e("Members may remove their current Memberships from Subscriptions table. When is about an active recurring Subscription Remove button will not show up until Member Cancels the Subscription", 'ihc');?></p>
          </div>
        </div>
        <div>
          <label class="iump_label_shiwtch ihc-switch-button-margin">
            <?php $checked = ($data['metas']['ihc_show_delete_link']) ? 'checked' : '';?>
            <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_delete_link');" <?php echo esc_attr($checked);?> />
            <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" name="ihc_show_delete_link" value="<?php echo esc_attr($data['metas']['ihc_show_delete_link']);?>" id="ihc_show_delete_link" />
        </div>
      </div>

    <div class="ihc-wrapp-submit-bttn ihc-submit-form">
      <input type="submit" id="ihc_submit_bttn" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
    </div>

    </div>
  </div>
</form>
