
<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Direct Link Tracking', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/referrer-links/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-7">
				<h2><?php esc_html_e('Activate/Hold Direct Link Tracking', 'uap');?></h2>
				<p><?php esc_html_e("An affiliate's name can now be masked by creating custom links. Users will no longer avoid links that could benefit a certain affiliate.", 'uap');?></p>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_simple_links_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_simple_links_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_simple_links_enabled" value="<?php echo esc_attr($data['metas']['uap_simple_links_enabled']);?>" id="uap_simple_links_enabled" />
				</div>
			</div>


			<div class="uap-inside-item">
				<div class="row">
					<div class="col-xs-4">
						<h4><?php esc_html_e('Referrer Links Limit', 'uap');?></h4>
						<p><?php esc_html_e('The number of links that can be submitted by an affiliate in his Affiliate Portal', 'uap');?></p>
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Links Limit per Affiliate', 'uap');?></span>
							<input type="number" min="0" step="1" class="uap-field-text-with-padding form-control" name="uap_simple_links_limit" value="<?php echo esc_attr($data['metas']['uap_simple_links_limit']);?>" />
						</div>
					</div>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Add New Referrer Link', 'uap');?></h3>
		<div class="inside">
				<div class="row">
					<div class="col-xs-5">
					<p><?php esc_html_e('Attach a referrer link to a specific affiliate user directly from the "UAP Dashboard".', 'uap');?></p>
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Affiliate', 'uap');?></span>
							<input type="text" class="uap-field-text-with-padding form-control" name="affiliate_name" id="affiliate_name"/>
							<input type="hidden" class="uap-field-text-with-padding form-control" name="affiliate_id" id="affiliate_id"/>
						</div>
					</div>
				</div>
						<div class="row">
							<div class="col-xs-5">
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Referrer Link', 'uap');?></span>
							<input type="text" class="uap-field-text-with-padding form-control" name="url" />
						</div>
					</div>
				</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

</form>


<?php if (!empty($data['items'])):?>
	<div class="uap-stuffbox">
			<h3 class="uap-h3"><?php esc_html_e('Search Links for Affiliate', 'uap');?></h3>
			<div class="inside">
				<div class="row">
					<div class="col-xs-5">
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Affiliate Username', 'uap');?></span>
						<input type="text" class="uap-field-text-with-padding form-control" id="affiliate_name_search" />
						<input type="hidden" id="search_aff_id" name="search_aff_id" />
						</div>
					</div>
				</div>

					<div class="row">
						<div class="col-xs-5">
						<span class="button button-primary button-large" onClick="uapDoRedirect('<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=simple_links');?>', 'affiliate_id', '#search_aff_id');"><?php esc_html_e('Search', 'uap');?></span>

					</div>
				</div>
			</div>
		</div>

<div class="uap-stuffbox">

	<table class="wp-list-table widefat fixed tags uap-admin-tables">
		<thead>
			<tr>
				<th><?php esc_html_e('Affiliate', 'uap');?></th>
				<th><?php esc_html_e('Referrer Link', 'uap');?></th>
				<th><?php esc_html_e('Status', 'uap');?></th>
				<th><?php esc_html_e('Action', 'uap');?></th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;
				foreach ($data['items'] as $item):?>
			<tr class="<?php  echo ($i%2==0) ? 'alternate' : '';?>">
				<td class="uap-list-affiliates-name-label"><?php echo esc_html($item['username']);?></td>
				<td><a href="<?php echo esc_url($item['url']);?>" target="_blank"><?php echo esc_url($item['url']);?></a></td>
				<td>
					<?php if ($item['status']):?>
						<div class="uap-subcr-type-list "><?php esc_html_e('Active', 'uap');?></div>
					<?php else:?>
						<div class="uap-subcr-type-list uap-pending"><?php esc_html_e('Pending', 'uap');?></div>
					<?php endif;?>
				</td>
				<td>
					<?php if (!$item['status']):?>
						<a href="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=simple_links&approve=' . $item['id']);?>"><?php esc_html_e('Approve', 'uap');?></a> |
					<?php endif;?>
					<a href="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=simple_links&delete=' . $item['id']);?>" class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>
				</td>
			</tr>
			<?php $i++;
			endforeach;?>
		</tbody>
		<tfoot>
			<tr>
				<th><?php esc_html_e('Affiliate', 'uap');?></th>
				<th><?php esc_html_e('Referrer Link', 'uap');?></th>
				<th><?php esc_html_e('Status', 'uap');?></th>
				<th><?php esc_html_e('Action', 'uap');?></th>
			</tr>
		</tfoot>
	</table>
	<?php if (!empty($data['pagination'])):?>
		<?php echo esc_uap_content($data['pagination']);?>
	<?php endif;?>
</div>
<?php endif;?>


</div>
