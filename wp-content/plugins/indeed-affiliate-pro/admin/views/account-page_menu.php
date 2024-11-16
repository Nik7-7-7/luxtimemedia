<?php $custom_css = '';

foreach ($data['menu'] as $slug => $item):
		if ( !isset($item['uap_tab_' . $slug . '_icon_code']) || ($item['uap_tab_' . $slug . '_icon_code'] === false || $item['uap_tab_' . $slug . '_icon_code'] === '') )continue;
		$custom_css .= ".fa-" . $slug . "-account-uap:before{".
			"content: '\\".$item['uap_tab_' . $slug . '_icon_code']."';".
			"font-size: 20px;".
		"}";
endforeach;
	?>


<div class="uap-wrapper uap-account-page-menu-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Affiliate Area Tabs', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/account-custom-tabs/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">

			<div class="uap-form-line">
				<h2><?php esc_html_e('Activate/Hold Affiliate Area Tabs', 'uap');?></h2>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_account_page_menu_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_account_page_menu_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_account_page_menu_enabled" value="<?php echo esc_attr($data['metas']['uap_account_page_menu_enabled']);?>" id="uap_account_page_menu_enabled" />
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
	</div>


	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Add new Tab', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-6">
				   		<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e('Slug', 'uap');?></span>
									<input type="text" name="slug" class="form-control" value="">
				   		</div>
				   		<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e('Label', 'uap');?></span>
									<input type="text" name="label" class="form-control" value="">
				   		</div>
							<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e('Link', 'uap');?></span>
									<input type="text" name="url" class="form-control" value="" />
							</div>
							<div><?php esc_html_e( 'Optional', 'uap' );?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-4">
				   		<div class="input-group">
							<label><?php esc_html_e('Icon', 'uap');?></label>
							<div class="uap-icon-select-wrapper">
								<div class="uap-icon-input">
									<div id="indeed_shiny_select_uap" class="uap-shiny-select-html"></div>
								</div>
				   				<div class="uap-icon-arrow"><i class="fa-uap fa-arrow-uap"></i></div>
								<div class="uap-clear"></div>
							</div>
						</div>
					</div>
				</div>
			 </div>


			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Reorder Menu Tabs', 'uap');?></h3>
		<div class="inside">
			<div class="uap-sortable-table-wrapp">
				<table class="wp-list-table widefat fixed tags uap-admin-tables" id="uap_reorder_menu_items">
					<thead>
						<tr>
							<th class="manage-column"><?php esc_html_e('Slug', 'uap');?></th>
							<th class="manage-column"><?php esc_html_e('Label', 'uap');?></th>
							<th class="manage-column"><?php esc_html_e('Icon', 'uap');?></th>
							<th class="manage-column"><?php esc_html_e('Link', 'uap');?></th>
							<th class="manage-column uap-table-small-col"><?php esc_html_e('Delete', 'uap');?></th>
						</tr>
					</thead>
					<tbody>
						<?php $k = 0;?>
						<?php $data['menu'] = uap_reorder_menu_items($data['metas']['uap_account_page_menu_order'], $data['menu']);?>
						<?php foreach ($data['menu'] as $slug=>$item):?>
							<?php $value = isset($data['metas']['uap_account_page_menu_order'][$slug]) ? $data['metas']['uap_account_page_menu_order'][$slug] : $k;?>
							<tr class="<?php echo ($k%2==0) ? 'alternate' : '';?>" id="tr_<?php echo esc_attr($slug);?>">
								<td class="uap-slug-col"><input type="hidden" value="<?php echo esc_attr($value);?>" name="uap_account_page_menu_order[<?php echo esc_attr($slug);?>]" class="uap_account_page_menu_order" />
								<?php echo esc_html($slug);?></td>
								<td class="uap-label-col"><?php
									if (isset($item['uap_tab_' . $slug . '_menu_label'])){
										echo esc_html($item['uap_tab_' . $slug . '_menu_label']);
									} else {
										echo esc_html($item['label']);
									}
								?></td>
								<td class="uap-icon-col">
									<?php if (!empty($item['uap_tab_' . $slug . '_icon_code'])):?>
										<i class="<?php echo esc_attr('fa-uap fa-' . $slug . '-account-uap');?>"></i></td>
									<?php else:?>
										-
									<?php endif;?>
								<td class="uap-link-col">
										<?php if ( empty( $item['url'] ) ):?>
												-
										<?php else:?>
												<?php echo esc_url($item['url']);?>
										<?php endif;?>
								</td>
								<td class="uap-link-col">
									<?php
										if (isset($data['standard_tabs'][$slug])){
											echo esc_html('-');
										} else {
											?>
											<a href="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=account_page_menu&delete=' . $slug);?>">
											<i class="fa-uap uap-icon-remove-e"></i></a>
											<?php
										}
									?>
								</td>
							</tr>
							<?php $k++;?>
						<?php endforeach;?>
					</tbody>
					<tfoot>
						<tr>
							<th class="manage-column"><?php esc_html_e('Slug', 'uap');?></th>
							<th class="manage-column"><?php esc_html_e('Label', 'uap');?></th>
							<th class="manage-column"><?php esc_html_e('Icon', 'uap');?></th>
							<th class="manage-column"><?php esc_html_e('Link', 'uap');?></th>
							<th class="manage-column"><?php esc_html_e('Delete', 'uap');?></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

</form>

<?php
require_once UAP_PATH . 'admin/font-awesome_codes.php';
$font_awesome = uap_return_font_awesome();
foreach ($font_awesome as $base_class => $code):
	if ( $code === '' )continue;
 $custom_css .= "." . $base_class . ":before{".
	 "content: '\\".$code."';".
 "}";
endforeach;

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
?>

</div>
