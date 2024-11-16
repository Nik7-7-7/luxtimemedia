<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
    ihc_save_update_metas('direct_login');//save update metas
}

$data['metas'] = ihc_return_meta_arr('direct_login');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$items = \Ihc_Db::directLoginGettAllItems();
$url = get_site_url();
if ( substr( $url, -1 ) != '/' ){

    $url .= '/';

}

?>
<div>
<form  method="post">

  <input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Direct Login', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Direct Login', 'ihc');?></h2>
                <p><?php esc_html_e('Users can login without standard credentials but with a special temporary link available. Once the link is used or expire will not be usable anymore. This feature is useful for emergency situations or when user forgot his credentials.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_direct_login_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_direct_login_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>

				<input type="hidden" name="ihc_direct_login_enabled" value="<?php echo esc_attr($data['metas']['ihc_direct_login_enabled']);?>" id="ihc_direct_login_enabled" />
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>



  <?php if ($data['metas']['ihc_direct_login_enabled']):?>

    <div class="ihc-stuffbox">
  		<h3 class="ihc-h3"><?php esc_html_e('Generate temporary Login links', 'ihc');?></h3>
  		<div class="iump-form-line">
        <p><?php esc_html_e('Specify member\'s username and the period during which login process may be done without credentials.', 'ihc');?></p>
        <div class="row">
          <div class="col-xs-5">
          <div class="input-group">
              <span class="input-group-addon"><?php esc_html_e('Username', 'ihc');?></span>
              <input type="text" id="direct_login_usernmae"  class="form-control" />
          </div>

        </div>
      </div>
      <div class="row">
        <div class="col-xs-5">
          <div class="input-group">
              <span class="input-group-addon"><?php esc_html_e('Timeout', 'ihc');?></span>
              <input type="number" id="direct_login_timeout" min=1  class="form-control"/>
              <div class="input-group-addon"><?php esc_html_e('hours', 'ihc');?></div>
          </div>
        </div>
      </div>
          <div class="iump-form-line">
              <h2 id="direct_link_value"></h2>
          </div>
          <div class="ihc-wrapp-submit-bttn ihc-submit-form">
            <button class="button button-primary button-large" id="direct_link_generate_link"><?php esc_html_e('Generate link', 'ihc');?></button>
          </div>
      </div>
    </div>
  <?php endif;?>

</form>

<?php if ( $items ):?>
<div class="iump-rsp-table ihc-dashboard-form-wrap ihc-admin-user-data-list">
<table class="wp-list-table widefat fixed tags" id="ihc-levels-table" >
   <thead>
      <tr>
      <th width="30%"><?php esc_html_e( 'Username', 'ihc' );?></th>
	  <th width="50%"><?php esc_html_e( 'URL', 'ihc' );?></th>
	  <th width="10%"><?php esc_html_e( 'Expire', 'ihc' );?></th>
	  <th width="10%"><?php esc_html_e( 'Action', 'ihc' );?></th>
      </tr>
    </thead>
    <tbody class="ihc-alternate">
        <?php $i = 1;
		foreach ( $items as $itemData ):?>
            <tr class="<?php if($i%2==0){
               echo esc_attr('alternate');
            }
            ?>">
                <td><strong><?php echo esc_html($itemData->user_login);?></strong></td>
                <td><?php echo add_query_arg( array('ihc_action' => 'dl', 'token' => $itemData->token), $url );?></td>
                <td <?php if (indeed_get_unixtimestamp_with_timezone()>$itemData->timeout){
                   ?>class='ihc-red-color'<?php
                }
                ?>
                ><?php echo date( 'Y-m-d h:i:s', $itemData->timeout );?></td>
                <td><i class="fa-ihc ihc-icon-remove-e ihc-pointer ihc-direct-login-remove-item" data-uid="<?php echo esc_attr($itemData->ID);?>"></i></td>
            </tr>
        <?php $i++;
		endforeach;?>
    </tbody>
</table>
</div>
<?php endif;?>

</div>
