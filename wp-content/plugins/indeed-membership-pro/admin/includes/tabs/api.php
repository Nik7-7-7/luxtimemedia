<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('api');//save update metas
		if (isset($_POST['ihc_api_actions'])){
				$save = array();
				foreach ($_POST['ihc_api_actions'] as $k=>$v){
					$save[sanitize_text_field($k)] = sanitize_text_field($v);
				}
				update_option('ihc_api_actions', $save);
		}
}

$data['metas'] = ihc_return_meta_arr('api');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

?>
<form  method="post">

	 <input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('API Gate', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold API Gate', 'ihc');?></h2>
				<p><?php esc_html_e('Manage your membership system and access data from it through an API with access based on URL calls.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_api_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_api_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_api_enabled" value="<?php echo esc_attr($data['metas']['ihc_api_enabled']);?>" id="ihc_api_enabled" />
			</div>

			<?php
			if (empty($data['metas']['ihc_api_hash'])){
				$data['metas']['ihc_api_hash'] = ihc_generate_random_string(rand(20, 30));
			}
			?>
			<div class="iump-form-line">
				<h2><?php esc_html_e('API Secret Hash', 'ihc');?></h2>
				<p><?php esc_html_e('Only with the right hash key can a call be provided, otherwise access to the membership system will not be provided.', 'ihc');?></p>
				<input type="text" name="ihc_api_hash" value="<?php echo esc_attr($data['metas']['ihc_api_hash']);?>" id="ihc_api_hash" />
				<span class="ihc-generate-coupon-button" onclick="ihcGenerateCode('#ihc_api_hash', <?php echo rand(25, 35);?>);ihcDoUpdateHashField();"><?php esc_html_e('Generate Code', 'ihc');?></span>
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>


<?php
$base_link = IHC_URL . 'apigate.php?ihch=';
$actions = array(
					'verify_user_level' => array(
						'label' => esc_html__('"Verify User Membership" API Call', 'ihc'),
						'return' => esc_html__("True if User got the membership and it's active. (Boolean Value)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc'), 'lid' => esc_html__('Membership Id', 'ihc'))
					),
					'user_approve' => array(
						'label' => esc_html__('"Approve User" API Call', 'ihc'),
						'return' => esc_html__("True if user has been approved. (Boolean Value)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc')),
					),
					'user_add_level' => array(
						'label' => esc_html__('"Add new membership for User" API Call', 'ihc'),
						'return' => esc_html__("True if the membership was succesfully added to User. (Boolean Value)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc'), 'lid' => esc_html__('Membership Id', 'ihc')),
					),
					'user_get_details' => array(
						'label' => esc_html__('"Get all User Data"  API Call', 'ihc'),
						'return' => esc_html__("List of all User Metas. (Array)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc')),
					),
					'user_activate_level' => array(
						'label' => esc_html__('"Activate User Membership" API Call', 'ihc'),
						'return' => esc_html__("True if the Mmbership was succesfully activated. (Boolean)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc'), 'lid' => esc_html__('Membership Id', 'ihc')),
					),
					'get_user_field_value' => array(
						'label' => esc_html__('"Get User Field Value" API Call', 'ihc'),
						'return' => esc_html__("The value of required field. (String, Boolean, Integer or Array)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc'), 'field' => esc_html__('Field Name', 'ihc')),
					),
					'get_user_levels' => array(
						'label' => esc_html__('"Get User Memberships" API Call', 'ihc'),
						'return' => esc_html__("A List of User Memberships. (Array)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc')),
					),
					'get_user_level_details' => array(
						'label' => esc_html__('"Get User Membership Details" API Call', 'ihc'),
						'return' => esc_html__("Create time, Update time and Expiration time. (Array)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc'), 'lid' => esc_html__('Membership Id', 'ihc')),
					),
					'get_user_posts' => array(
						'label' => esc_html__('"Get User Available Posts" API Call', 'ihc'),
						'return' => esc_html__("List of Posts that User can see. (Array)", 'ihc'),
						'params' => array('uid' => esc_html__('User Id', 'ihc'), 'limit' => esc_html__('Limit', 'ihc'), 'order_by' => esc_html__('Order By', 'ihc'), 'order' => esc_html__('Order', 'ihc'), 'post_types' => esc_html__('Post Types', 'ihc') ),
					),
					'search_users' => array(
						'label' => esc_html__('"Search User" API Call', 'ihc'),
						'return' => esc_html__("List of User Ids. (Array)", 'ihc'),
						'params' => array('term_name' => esc_html__('Term Name', 'ihc'), 'term_value' => esc_html__('Term Value', 'ihc')),
					),
					'list_levels' => array(
						'label' => esc_html__('"List all Memberships" API Call', 'ihc'),
						'return' => esc_html__("List of Memberships. (Array)", 'ihc'),
						'params' => array(),
					),
					'get_level_users' => array(
						'label' => esc_html__('"Get Membership Users" API Call', 'ihc'),
						'return' => esc_html__("List of Users that have a certain Membership. (Array)", 'ihc'),
						'params' => array('lid' => esc_html__('Membership Id', 'ihc')),
					),
					'get_level_details' => array(
						'label' => esc_html__('"Get Membership details" API Call', 'ihc'),
						'return' => esc_html__("List of Users that have a certain Membership. (Array)", 'ihc'),
						'params' => array('lid' => esc_html__('Membership Id', 'ihc')),
					),
					'orders_listing' => array(
						'label' => esc_html__('"Listing Orders" API Call', 'ihc'),
						'return' => esc_html__("List of Orders. (Array)", 'ihc'),
						'params' => array('limit' => esc_html__('Limit', 'ihc'), 'uid' => esc_html__('User Id', 'ihc')),
					),
					'order_get_status' => array(
						'label' => esc_html__('"Get Order status" API Call', 'ihc'),
						'return' => esc_html__("Order status. (String)", 'ihc'),
						'params' => array('order_id' => esc_html__('Order Id', 'ihc')),
					),
					'order_get_data' => array(
						'label' => esc_html__('"Get Order data" API Call', 'ihc'),
						'return' => esc_html__("Order Data. (Array)", 'ihc'),
						'params' => array('order_id' => esc_html__('Order Id', 'ihc')),
					),
);
$siteUrl = site_url();
$siteUrl = trailingslashit($siteUrl);

foreach ($actions as $slug=>$array):?>
	<?php
		if (empty($data['metas']['ihc_api_actions'][$slug])){
			$value = 0;
		} else {
			$value = 1;
		}

	?>
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php echo esc_html($array['label']);?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php echo esc_html__('Activate/Hold ', 'ihc') . $array['label'];?></h2>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($value) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '<?php echo esc_attr('#ihc_api_actions' . $slug);?>');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_api_actions[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr($value);?>" id="<?php echo esc_attr('ihc_api_actions' . $slug);?>" />
			</div>
			<div class="iump-form-line">
			<h4><?php esc_html_e('API Link', 'ihc');?></h4>
			<div class="ihc-api-link">
			<a href="" target="_blank"><?php echo esc_url($siteUrl . '?ihc_action=api-gate&ihch=');?><span class="ihc-base-api-link-hash"><?php echo esc_html($data['metas']['ihc_api_hash']);?></span><?php echo esc_ump_content('&action='.$slug);
				$i = 1;
				if (!empty($array['params'])){
					foreach ($array['params'] as $k=>$v){
						echo esc_ump_content('&' . $k . '=exemple_' . $i);
						$i++;
					}
				}
			?></a>
			</div>
			<h4><?php esc_html_e('Deprecated API Link', 'ihc');?></h4>
			<div class="ihc-api-link">
			<a href="" target="_blank"><?php echo esc_url($base_link);?><span class="ihc-base-api-link-hash"><?php echo esc_html($data['metas']['ihc_api_hash']);?></span><?php echo esc_ump_content('&action='.$slug);
				$i = 1;
				if (!empty($array['params'])){
					foreach ($array['params'] as $k=>$v){
						echo esc_ump_content('&' . $k . '=exemple_' . $i);
						$i++;
					}
				}
			?></a>
			</div>
			<div class="ihc-api-details">
				<div><?php echo esc_ump_content('<span>'.esc_html__('Action name: ', 'ihc') .'</span>' . $slug);?></div>
				<div><span><?php esc_html_e('Params available:', 'ihc');?></span></div>
				<?php
				if (!empty($array['params'])){
					foreach ($array['params'] as $k=>$v){
						?>
						<div><?php echo esc_ump_content('<strong>'.$v . ' : '.'</strong>' . $k);?></div>
						<?php
					}
				}
				?>
				<div><?php echo esc_ump_content('<span>'.esc_html__('Return : ', 'ihc').'</span>' . $array['return']);?></div>
			</div>
		</div>
			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
<?php endforeach;?>

</form>

<?php
