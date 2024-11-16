<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('account_page_menu');//save update metas
}

$data['metas'] = ihc_return_meta_arr('account_page_menu');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

if ( !empty($_GET['delete']) && empty($_POST['ihc_account_page_menu_add_new-the_slug']) ){
	Ihc_Db::account_page_menu_delete_custom_item( sanitize_text_field( $_GET['delete'] ) );
}
if (!empty($_POST['ihc_account_page_menu_add_new-the_slug']) && !empty($_POST['ihc_account_page_menu_add_new-the_slug'])){
	Ihc_Db::account_page_menu_save_custom_item( indeed_sanitize_array( $_POST ) );
}
$menu_items = Ihc_Db::account_page_get_menu();
$standard_tabs = Ihc_Db::account_page_get_menu(TRUE);

$standard_tabs = apply_filters( 'ihc_admin_account_page_menu_standard_tabs', $standard_tabs );
// @description filter for listing the account page menu standard tabs items on admin section. @param array

$custom_css = '';

foreach ($menu_items as $slug => $item):
		$custom_css .= ".fa-" . $slug . "-account-ihc:before{".
			"content: '\\".$item['icon']."';".
			"font-size: 20px;".
		"}";
endforeach;
?>
<div>
<form method="post">

	 <input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Member Portal Custom Tabs', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Member Portal Custom Tabs', 'ihc');?></h2>
				<p><?php esc_html_e('With this option activated new tabs may be created and reordered from member account page.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_account_page_menu_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_account_page_menu_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_account_page_menu_enabled" value="<?php echo esc_attr($data['metas']['ihc_account_page_menu_enabled']);?>" id="ihc_account_page_menu_enabled" />
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>


	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Add new Menu Item', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<div class="row">
					<div class="col-xs-4">

				   		<div class="input-group">
									<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Slug', 'ihc');?></span>
									<input type="text" name="ihc_account_page_menu_add_new-the_slug" class="form-control" value="">
				   		</div>

				   		<div class="input-group">
									<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label', 'ihc');?></span>
									<input type="text" name="ihc_account_page_menu_add_new-the_label" class="form-control" value="">
				   		</div>

							<div class="input-group">
									<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Link', 'ihc');?></span>
									<input type="text" name="ihc_account_page_menu_add_new-url" class="form-control" value="" />
							</div>
							<div><?php esc_html_e( '(Optional)', 'ihc' );?></div>

				   		<div class="input-group">
							<label><?php esc_html_e('Icon', 'ihc');?></label>
							<div class="ihc-icon-select-wrapper">
								<div class="ihc-icon-input">
									<div id="indeed_shiny_select" class="ihc-shiny-select-html"></div>
								</div>
				   				<div class="ihc-icon-arrow"><i class="fa-ihc fa-arrow-ihc"></i></div>
								<div class="ihc-clear"></div>
							</div>
						</div>
					</div>
				</div>
			 </div>


			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('ReOrder Menu Items', 'ihc');?></h3>
		<div class="inside">
			<div class="ihc-sortable-table-wrapp">
				<table class="wp-list-table widefat fixed tags ihc-admin-tables ihc-acc-menu-table" id="ihc_reorder_menu_items">
					<thead>
						<tr>
							<th class="manage-column"><?php esc_html_e('Slug', 'ihc');?></th>
							<th class="manage-column"><?php esc_html_e('Label', 'ihc');?></th>
							<th class="manage-column"><?php esc_html_e('Icon', 'ihc');?></th>
							<th class="manage-column"><?php esc_html_e('Link', 'ihc');?></th>
							<th class="manage-column ihc-small-status-col"><?php esc_html_e('Delete', 'ihc');?></th>
						</tr>
					</thead>
					<tbody>
						<?php $k = 0;?>
						<?php foreach ($menu_items as $slug=>$item):?>
							<tr class="<?php if($k%2==0){
								 echo esc_attr('alternate');
							}
							?>" id="tr_<?php echo esc_attr($slug);?>" class="ihc-acc-menu-table-tr">

								<td class="ihc-acc-menu-table-col1"><input type="hidden" value="<?php echo esc_attr($k);?>" name="ihc_account_page_menu_order[<?php echo esc_attr($slug);?>]" class="ihc_account_page_menu_order" /><?php echo esc_html($slug);?></td>
								<td  class="ihc-acc-menu-table-col2"><?php echo esc_attr($item['label']);?></td>
								<td class="ihc-acc-menu-table-col3"><i class="<?php echo esc_attr('fa-ihc fa-' . $slug . '-account-ihc');?>"></i></td>
								<td  class="ihc-acc-menu-table-col4"><?php if ( isset( $item['url'] ) ){
									 echo esc_html($item['url']);
								}
								?>
							</td>
								<td class="ihc-acc-menu-table-col5">
									<?php
										if (isset($standard_tabs[$slug])){
											echo esc_html('-');
										} else {
											?>
											<i class="fa-ihc ihc-icon-remove-e ihc-js-account-page-menu-item-delete-action ihc-cursor-pointer" data-slug="<?php echo esc_attr($slug);?>"></i>
											<?php
										}
									?>
								</a></td>
							</tr>
							<?php $k++;?>
						<?php endforeach;?>
					</tbody>
					<tfoot>
						<tr>
							<th class="manage-column"><?php esc_html_e('Slug', 'ihc');?></th>
							<th class="manage-column"><?php esc_html_e('Label', 'ihc');?></th>
							<th class="manage-column"><?php esc_html_e('Icon', 'ihc');?></th>
							<th class="manage-column"><?php esc_html_e('Link', 'ihc');?></th>
							<th class="manage-column"><?php esc_html_e('Delete', 'ihc');?></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

</form>

<?php
$font_awesome = Ihc_Db::get_font_awesome_codes();
?>
<?php

foreach ($font_awesome as $base_class => $code):
 $custom_css .= "." . $base_class . ":before{".
	 "content: '\\".$code."';".
 "}";
endforeach;
wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
 ?>


</div>
