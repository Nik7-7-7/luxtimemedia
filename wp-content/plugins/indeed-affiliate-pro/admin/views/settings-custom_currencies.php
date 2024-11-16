<div class="uap-wrapper">
			<form  method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Add new Currency', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/custom-currencies/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-inside-item">
							<div class="row">
								<div class="col-xs-5">
									<div class="input-group">
										<label class="input-group-addon"><?php esc_html_e('Code', 'uap');?></label>
										<input type="text" class="form-control" name="new_currency_code" value="">
									</div>
									<p><?php esc_html_e('Insert a valid Currency Code, ex: ', 'uap');?><span><strong><?php esc_html_e('USD, EUR, CAD.', 'uap');?></strong></span></p>
								</div>
							</div>
						</div>
						<div class="uap-inside-item">
							<div class="row">
								<div class="col-xs-5">
									<div class="input-group">
										<label class="input-group-addon"><?php esc_html_e('Name', 'uap');?></label>
										<input type="text" class="form-control" name="new_currency_name" value="">
									</div>
								</div>
							</div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="uap_save" class="button button-primary button-large" />
						</div>
					</div>
				</div>

				<?php if ($currencies!==FALSE && count($currencies)>0): ?>
					<div>
						<table class="wp-list-table widefat fixed tags">
							<thead>
								<tr>
									<th class="manage-column"><?php esc_html_e('Code', 'uap');?></th>
									<th class="manage-column"><?php esc_html_e('Name', 'uap');?></th>
									<th class="manage-column uap-text-align-center"><?php esc_html_e('Action', 'uap');?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($currencies as $code=>$name):?>
									<tr id="uap_div_<?php echo esc_attr($code);?>">
										<td><?php echo esc_html($code);?></td>
										<td><?php echo esc_html($name);?></td>
										<td class="uap-text-align-center"><i class="fa-uap uap-icon-remove-e" onClick="uapRemoveCurrency('<?php echo esc_html($code);?>');"></i></td>
									</tr>
								<?php endforeach;?>
							</tbody>
						</table>
					</div>
				<?php endif;?>
			</form>
</div>
