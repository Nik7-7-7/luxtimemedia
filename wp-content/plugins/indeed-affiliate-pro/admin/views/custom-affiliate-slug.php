<div class="uap-js-custom-aff-base-url" data-value="<?php echo esc_attr($url);?>"></div>
<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Custom Affiliate Slug', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/custom-affiliate-slug/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-10">
					<h2><?php esc_html_e('Activate/Hold Custom Affiliate Slug', 'uap');?></h2>
					<p><?php esc_html_e('Provides personal slugs besides the default username or ID so affiliates can hide their identity or company name behind a custom slug.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_custom_affiliate_slug_on']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_custom_affiliate_slug_on');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_custom_affiliate_slug_on" value="<?php echo esc_attr($data['metas']['uap_custom_affiliate_slug_on']);?>" id="uap_custom_affiliate_slug_on" />
				</div>
			</div>
		</div>
		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-10">
					<p><?php esc_html_e('Establish conditional requirements when affiliates want to set their personal custom slug. Hint: The custom slug is unique and future users may not register it as their username or custom slug.', 'uap');?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<div class="input-group">
						<label class="input-group-addon"><?php esc_html_e('Minimum number of characters', 'uap');?></label>
						<input type="number" class="form-control" value="<?php echo esc_attr($data['metas']['uap_custom_affiliate_slug_min_ch']);?>" min="3" name="uap_custom_affiliate_slug_min_ch" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<div class="input-group">
						<label class="input-group-addon"><?php esc_html_e('Maximum number of characters', 'uap');?></label>
						<input type="number" class="form-control" value="<?php echo esc_attr($data['metas']['uap_custom_affiliate_slug_max_ch']);?>" min="3" name="uap_custom_affiliate_slug_max_ch" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<div class="input-group">
						<label class="input-group-addon"><?php esc_html_e('Slug characters rule', 'uap');?></label>
						<select name="uap_custom_affiliate_slug_rule">
							<?php foreach (array(0=> esc_html__('Standard', 'uap'), 1=> esc_html__('Characters and digits'), 2=> esc_html__('Characters, digits, minimum one uppercase letter', 'uap')) as $k=>$v):?>
								<?php $selected = ($k==$data['metas']['uap_custom_affiliate_slug_rule']) ? 'selected' : '';?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
</form>

<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Add/Edit Slug', 'uap');?></h3>
		<div class="inside">
			<div class="row">
				<div class="col-xs-7">
				<p><?php esc_html_e('You can add or edit a custom slug for a specific affiliate from your side.', 'uap');?></p>
					<div class="input-group">
						<span class="input-group-addon"><?php esc_html_e('Affiliate', 'uap');?></span>
						<input type="text"  class="form-control" value=""  id="affiliate_name" />
						<input type="hidden" id="affiliate_id_hidden" name="affiliate_id" value="<?php echo isset( $data['metas']['affiliate_id'] ) ? $data['metas']['affiliate_id'] : '';?>]"/>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-7">
					<div class="input-group">
						<span class="input-group-addon"><?php esc_html_e('Slug', 'uap');?></span>
						<input type="text"  class="form-control" value="" name="slug" />
					</div>
				</div>
			</div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save_slug" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>

<?php if ($data['items']):?>
	<div class="uap-stuffbox">
		<table class="wp-list-table widefat fixed tags uap-admin-tables">
			<thead>
				<tr>
					<th><?php esc_html_e('Username', 'uap');?></th>
					<th><?php esc_html_e('Slug', 'uap');?></th>
					<th><?php esc_html_e('Action', 'uap');?></th>
				</tr>
			</thead>
			<tbody>
				<?php  $i = 1;
				foreach ($data['items'] as $item):?>
					<tr class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
						<td><?php echo esc_html($item['username']);?></td>
						<td><?php echo esc_html($item['meta_value']);?></td>
						<td><i class="fa-uap fa-trash-uap" onClick="uapRemoveSlug(<?php echo esc_attr($item['user_id']);?>);"></i></td>
					</tr>
				<?php $i++;
				endforeach;?>
			</tbody>

			<tfoot>
				<tr>
					<th><?php esc_html_e('Username', 'uap');?></th>
					<th><?php esc_html_e('Slug', 'uap');?></th>
					<th><?php esc_html_e('Action', 'uap');?></th>
				</tr>
			</tfoot>
		</table>
	</div>
<?php endif;?>

<?php if ($data['pagination']):?>
<?php echo esc_uap_content($data['pagination']);?>
<?php endif?>


</div>
