
<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Affiliate Landing Pages', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
        	<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-7">
				<h2><?php esc_html_e('Activate/Hold Landing Pages', 'uap');?></h2>
				<p><?php esc_html_e('An affiliate can be linked with a specific page from your website. Users will no longer avoid links that could benefit a certain affiliate because no affiliate link will be required on this case.', 'uap');?></p>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_landing_pages_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_landing_pages_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_landing_pages_enabled" value="<?php echo esc_attr($data['metas']['uap_landing_pages_enabled']);?>" id="uap_landing_pages_enabled" />
				</div>
			</div>
            </div>
			<div class="uap-line-break"></div>

			<div class="uap-inside-item">
				<div class="row">
					<div class="col-xs-8">
						<h2><?php esc_html_e('How it Works', 'uap');?></h2>
						<p><?php esc_html_e('Once this Module is enabled, you will find on your editing page/post section an additional MetaBox dedicated for this purpose. There you can search for a specific ', 'uap');?> <strong><?php esc_html_e(' Affiliate user ', 'uap');?></strong> <?php esc_html_e(' and assiging him with current page.', 'uap');?></p>

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


<?php if (!empty($data['items'])):?>
	<div class="uap-stuffbox">
			<h3 class="uap-h3"><?php esc_html_e('Associated Landing pages with Affiliates', 'uap');?></h3>
			<div class="inside">


	<table class="wp-list-table widefat fixed tags uap-admin-tables">
		<thead>
			<tr>
				<th><?php esc_html_e('Affiliate', 'uap');?></th>
				<th><?php esc_html_e('Landing Page', 'uap');?></th>
				<th><?php esc_html_e('Action', 'uap');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php esc_html_e('Affiliate', 'uap');?></th>
				<th><?php esc_html_e('Landing Page', 'uap');?></th>
				<th><?php esc_html_e('Action', 'uap');?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php $i = 1;
				foreach ($data['items'] as $item):?>
			<tr class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
				<td >
					<?php echo esc_html($item->user_login);?>
				</td>
				<?php $link = get_permalink( $item->ID );?>
				<td><a href="<?php echo esc_url($link);?>" target="_blank"><?php echo esc_html($item->post_title) . " ( " . esc_url($link) . " )";?></a></td>
				<td>
					<a href="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=landing_pages&delete=' . $item->post_meta_id);?>"  class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>
				</td>
			</tr>
			<?php $i++;
			endforeach;?>
		</tbody>
	</table>
	<?php if (!empty($data['pagination'])):?>
		<?php echo esc_uap_content($data['pagination']);?>
	<?php endif;?>
</div>
</div>
<?php endif;?>



</div>
