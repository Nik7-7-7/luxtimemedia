<?php
if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_manage_order_table_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_manage_order_table_nonce']), 'ihc_manage_order_table_nonce' ) ){
    ihc_save_update_metas('manage_order_table');
}

$data['metas'] = ihc_return_meta_arr('manage_order_table');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

?>
<form method="post">
  <div class="iump-page-headline"><?php esc_html_e('Payments Table Showcase', 'ihc');?></div>
  <div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Manage Payments Table', 'ihc');?></h3>
		<div class="inside">
      <input type="hidden" name="ihc_manage_order_table_nonce" value="<?php echo wp_create_nonce( 'ihc_manage_order_table_nonce' );?>" />
      <div class="iump-form-line iump-no-border">
        <div class="row">
          <div class="col-xs-10">
            <h2><?php esc_html_e("Payments Table Showcase", 'ihc');?></h2>
            <p><?php esc_html_e("Customize what informations will be available for Members in Payments table from Member Portal -> Payments tab. By default Payments Table will show up in Orders tab and can be also displayed with own shortcode","ihc");?> <b>[ihc-account-page-orders-table]</b></p>

          </div>
        </div>
      </div>

      <div class="iump-form-line iump-no-border">
      </div>

      <div class="iump-special-line">
        <div class="iump-form-line iump-no-border">
          <h2><?php esc_html_e("Payments Table Columns", 'ihc');?></h2>
           <p><?php esc_html_e("Choose which columns will show up on Member Interface", 'ihc');?></p>
        </div>
      <div class="iump-form-line iump-no-border">
        <table class="ihc-order-table-table-box wp-list-table widefat tags">
            <thead>
              <tr class="ihc-order-table-row-box">
                <th class="ihc-order-table-box">
                    <div class="iump-form-line iump-no-border">
                      <h5> <?php esc_html_e("Code", 'ihc');?></h5>
                      <div>
                        <label class="iump_label_shiwtch ihc-switch-button-margin">
                          <input disabled checked type="checkbox" class="iump-switch"/>
                          <div class="switch ihc-display-inline ihc-disabled-opacity"></div>
                        </label>
                        <input type="hidden" name="ihc_show_order_column" value="" id="ihc_show_order_column" />
                      </div>
                    </div>
                </th>

                <th class="ihc-order-table-box">
                <div class="iump-form-line iump-no-border">
                  <h5> <?php esc_html_e("Memberships", 'ihc');?></h5>
                  <div>
                    <label class="iump_label_shiwtch ihc-switch-button-margin">
                      <?php $checked = ($data['metas']['ihc_show_order_memberships_column']) ? 'checked' : '';?>
                      <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_order_memberships_column');" <?php echo esc_attr($checked);?> />
                      <div class="switch ihc-display-inline"></div>
                    </label>
                    <input type="hidden" name="ihc_show_order_memberships_column" value="<?php echo esc_attr($data['metas']['ihc_show_order_memberships_column']);?>" id="ihc_show_order_memberships_column" />
                  </div>
                </div>
              </th>

              <th class="ihc-order-table-box">
              <div class="iump-form-line iump-no-border">
                <h5> <?php esc_html_e("Total Amount", 'ihc');?></h5>
                <div>
                  <label class="iump_label_shiwtch ihc-switch-button-margin">
                    <?php $checked = ($data['metas']['ihc_show_total_amount_column']) ? 'checked' : '';?>
                    <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_total_amount_column');" <?php echo esc_attr($checked);?> />
                    <div class="switch ihc-display-inline"></div>
                  </label>
                  <input type="hidden" name="ihc_show_total_amount_column" value="<?php echo esc_attr($data['metas']['ihc_show_total_amount_column']);?>" id="ihc_show_total_amount_column" />
                </div>
              </div>
            </th>

            <th class="ihc-order-table-box">
            <div class="iump-form-line iump-no-border">
              <h5> <?php esc_html_e("Payment Method", 'ihc');?></h5>
              <div>
                <label class="iump_label_shiwtch ihc-switch-button-margin">
                  <?php $checked = ($data['metas']['ihc_show_payment_method_column']) ? 'checked' : '';?>
                  <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_payment_method_column');" <?php echo esc_attr($checked);?> />
                  <div class="switch ihc-display-inline"></div>
                </label>
                <input type="hidden" name="ihc_show_payment_method_column" value="<?php echo esc_attr($data['metas']['ihc_show_payment_method_column']);?>" id="ihc_show_payment_method_column" />
              </div>
            </div>
          </th>

          <th class="ihc-order-table-box">
          <div class="iump-form-line iump-no-border">
            <h5> <?php esc_html_e("Date", 'ihc');?></h5>
            <div>
              <label class="iump_label_shiwtch ihc-switch-button-margin">
                <?php $checked = ($data['metas']['ihc_show_date_column']) ? 'checked' : '';?>
                <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_date_column');" <?php echo esc_attr($checked);?> />
                <div class="switch ihc-display-inline"></div>
              </label>
              <input type="hidden" name="ihc_show_date_column" value="<?php echo esc_attr($data['metas']['ihc_show_date_column']);?>" id="ihc_show_date_column" />
            </div>
          </div>
        </th>

        <th class="ihc-order-table-box">
        <div class="iump-form-line iump-no-border">
          <h5> <?php esc_html_e("Coupon", 'ihc');?></h5>
          <div>
            <label class="iump_label_shiwtch ihc-switch-button-margin">
              <?php $checked = ($data['metas']['ihc_show_coupon_column']) ? 'checked' : '';?>
              <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_coupon_column');" <?php echo esc_attr($checked);?> />
              <div class="switch ihc-display-inline"></div>
            </label>
            <input type="hidden" name="ihc_show_coupon_column" value="<?php echo esc_attr($data['metas']['ihc_show_coupon_column']);?>" id="ihc_show_coupon_column" />
          </div>
        </div>
        </th>

        <th class="ihc-order-table-box">
        <div class="iump-form-line iump-no-border">
          <h5> <?php esc_html_e("Transaction", 'ihc');?></h5>
          <div>
            <label class="iump_label_shiwtch ihc-switch-button-margin">
              <?php $checked = ($data['metas']['ihc_show_transaction_column']) ? 'checked' : '';?>
              <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_transaction_column');" <?php echo esc_attr($checked);?> />
              <div class="switch ihc-display-inline"></div>
            </label>
            <input type="hidden" name="ihc_show_transaction_column" value="<?php echo esc_attr($data['metas']['ihc_show_transaction_column']);?>" id="ihc_show_transaction_column" />
          </div>
        </div>
        </th>

      <!--  <th class="ihc-order-table-box">
        <div class="iump-form-line iump-no-border">
          <h5> <?php /*esc_html_e("Invoice", 'ihc');?></h5>
          <div>
            <label class="iump_label_shiwtch ihc-switch-button-margin">
              <?php $checked = ($data['metas']['ihc_show_invoice_column']) ? 'checked' : '';?>
              <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_invoice_column');" <?php echo esc_attr($checked);?> />
              <div class="switch ihc-display-inline"></div>
            </label>
            <input type="hidden" name="ihc_show_invoice_column" value="<?php echo esc_attr($data['metas']['ihc_show_invoice_column']);*/?>" id="ihc_show_invoice_column" />
          </div>
        </div>
      </th>-->

        <th class="ihc-order-table-box">
        <div class="iump-form-line iump-no-border">
          <h5> <?php esc_html_e("Status", 'ihc');?></h5>
          <div>
            <label class="iump_label_shiwtch ihc-switch-button-margin">
              <?php $checked = ($data['metas']['ihc_show_order_status_column']) ? 'checked' : '';?>
              <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_order_status_column');" <?php echo esc_attr($checked);?> />
              <div class="switch ihc-display-inline"></div>
            </label>
            <input type="hidden" name="ihc_show_order_status_column" value="<?php echo esc_attr($data['metas']['ihc_show_order_status_column']);?>" id="ihc_show_order_status_column" />
          </div>
        </div>
        </th>
        </tr>
        </thead>
        </table>
      </div>
      </div>

<?php
$show_taxes = get_option('ihc_enable_taxes');
$show_invoices = get_option('ihc_invoices_on');

  if($show_taxes || $show_invoices):?>
      <div class="iump-form-line iump-no-border">
        <h2><?php esc_html_e("Payments Table Actions", 'ihc');?></h2>
         <p><?php esc_html_e("Choose which actions will be available in Payments Table", 'ihc');?></p>
      </div>
  <?php
  if($show_taxes) : ?>
  <div class="iump-form-line">
    <div class="row">
      <div class="col-xs-6">
        <h4> <?php esc_html_e("Show Taxes In Payments Table", 'ihc');?></h4>
        <p><?php esc_html_e("With this button activated Taxes will be displayed in Payments table. ","ihc");?></p>
      </div>
    </div>
    <div>
      <label class="iump_label_shiwtch ihc-switch-button-margin">
        <?php $checked = ($data['metas']['ihc_show_taxes_column']) ? 'checked' : ''; ?>
        <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_taxes_column');" <?php echo esc_attr($checked);?> />
        <div class="switch ihc-display-inline"></div>
      </label>
      <input type="hidden" name="ihc_show_taxes_column" value="<?php echo esc_attr($data['metas']['ihc_show_taxes_column']);?>" id="ihc_show_taxes_column" />
    </div>
  </div>
<?php endif; ?>
  <?php
  if($show_invoices) : ?>

      <div class="iump-form-line">
        <div class="row">
          <div class="col-xs-10">
            <h4> <?php esc_html_e("Show Invoices In Payments Table", 'ihc');?></h4>
            <p><?php esc_html_e("With this button activated Invoices will be displayed in Payments table. ","ihc");?></p>
          </div>
        </div>
        <div>
          <label class="iump_label_shiwtch ihc-switch-button-margin">
            <?php $checked = ($data['metas']['ihc_show_invoice_column']) ? 'checked' : '';?>
            <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_show_invoice_column');" <?php echo esc_attr($checked);?> />
            <div class="switch ihc-display-inline"></div>
          </label>
          <input type="hidden" name="ihc_show_invoice_column" value="<?php echo esc_attr($data['metas']['ihc_show_invoice_column']);?>" id="ihc_show_invoice_column" />
        </div>
      </div>
<?php endif; ?>

<?php endif;?>
<div class="ihc-wrapp-submit-bttn ihc-submit-form">
  <input type="submit" id="ihc_submit_bttn" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
</div>
    </div>
  </div>
</form>
