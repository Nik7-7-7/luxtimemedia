
<?php if ( isset($data['errors']) ) :?>
		<div class="uap-error-message"><?php echo esc_html($data['errors']);?></div>
<?php endif;?>
<div class="uap-wrapper">
			<form  method="post" id="form_coupons">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Static Coupons', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/coupons/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-form-line">
						<div class="row">
							<div class="col-xs-7">
								<h2><?php esc_html_e('Activate/Hold Static Coupons option for your Affiliates', 'uap');?></h2>
								<p><?php esc_html_e('You can activate this option to take place in your affiliate system.', 'uap');?></p>
								<label class="uap_label_shiwtch uap-switch-button-margin">
									<?php $checked = ($data['metas']['uap_coupons_enable']) ? 'checked' : '';?>
									<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_coupons_enable');" <?php echo esc_attr($checked);?> />
									<div class="switch uap-display-inline"></div>
								</label>
								<input type="hidden" name="uap_coupons_enable" value="<?php echo esc_attr($data['metas']['uap_coupons_enable']);?>" id="uap_coupons_enable" />
							</div>
						</div>
						<input type="hidden" name="delete_coupons" value="" id="delete_coupons" />
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="uap_save" class="button button-primary button-large" />
						</div>
					</div>
					</div>
				</div>
			</form>
</div>
<div class="uap-wrapper">

	<a href="<?php echo esc_url($data['url-add_edit'] . '&add_edit=0');?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><span><?php esc_html_e('Add new Coupon', 'uap');?></span></a>


	<?php if (!empty($data['listing_items'])):?>
		<table class="wp-list-table widefat fixed tags uap-admin-tables uap-coupons-table">
			<thead>
				<tr>
					<th><?php esc_html_e('Coupon', 'uap');?></th>
					<th><?php esc_html_e('Source', 'uap');?></th>
					<th><?php esc_html_e('Affiliate', 'uap');?></th>
					<th><?php esc_html_e('Status', 'uap');?></th>
				</tr>
			</thead>
			<tbody>
		<?php $i = 0; foreach ($data['listing_items'] as $k=>$array):?>
			<tr onmouseover="uapDhSelector('#aff_<?php echo esc_attr($array['id']);?>', 1);" onmouseout="uapDhSelector('#aff_<?php echo esc_attr($array['id']);?>', 0);" class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
				<th><?php
					echo esc_html($array['code']);?>
					<div id="<?php echo esc_attr( 'aff_' . $array['id']);?>"  class="uap-visibility-hidden">
						<a href="<?php echo esc_url($data['url-add_edit'] . '&add_edit=' . $array['code']);?>"><?php esc_html_e('Edit', 'uap');?></a> | <a onclick="uapDeleteFromTable(<?php echo esc_attr($array['id']);?>, 'Coupon', '#delete_coupons', '#form_coupons');" href="javascript:return false;"  class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>
					</div>
				</th>
				<th><?php echo esc_html($array['type']);?></th>
				<th><?php echo esc_html($indeed_db->get_wp_username_by_affiliate_id($array['affiliate_id']));?></th>
				<th><?php if ($array['status']){
					 esc_html_e('Enabled');
				}
				else esc_html_e('Disabled');?></th>
			</tr>
		<?php $i++; endforeach;?>
	</tbody>

	<tfoot>
		<tr>
			<th><?php esc_html_e('Coupon', 'uap');?></th>
			<th><?php esc_html_e('Source', 'uap');?></th>
			<th><?php esc_html_e('Affiliate', 'uap');?></th>
			<th><?php esc_html_e('Status', 'uap');?></th>
		</tr>
	</tfoot>
		</table>
	<?php endif;?>
</div>
<?php
