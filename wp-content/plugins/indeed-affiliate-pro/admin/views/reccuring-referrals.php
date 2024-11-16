<div class="uap-wrapper">
			<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Recurring Referrals', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/recurring-referrals/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-10">
							<h2><?php esc_html_e('Activate/Hold Recurring Referrals', 'uap');?></h2>
							<p><?php esc_html_e('You can activate this option to take place in your affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_reccuring_referrals_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_reccuring_referrals_enable');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_reccuring_referrals_enable" value="<?php echo esc_attr($data['metas']['uap_reccuring_referrals_enable']);?>" id="uap_reccuring_referrals_enable" />
						</div>
						</div>
					</div>
						<div class="row">

						<div class="col-xs-10">
						<div class="uap-form-line">
						<?php if (!empty($data['rank_list'])) :?>
							<h2><?php esc_html_e('Recurring Amount For Each Rank', 'uap');?></h2>
							<p><?php esc_html_e('Set a special recurring amount for each rank that will replace the default amount rank. This option will also become available in the "Rank Settings" page.', 'uap');?></p>
						</div>
						<div class="uap-form-line">
							<table class="uap-dashboard-inside-table">
								<tr>
									<th><?php esc_html_e('Rank Name', 'uap');?></th>
									<th><?php esc_html_e('Default Amount Rank', 'uap');?></th>
									<th><?php esc_html_e('Recurring Amount', 'uap');?></th>
								</tr>
							<?php foreach ($data['rank_list'] as $id=>$label) :?>
								<tr>
									<td><?php echo esc_html($label);?></td>

									<td>
											<?php if ( isset( $data['default_rank_amount_value_array'][$id] ) && isset( $data['amount_types'][$data['default_rank_amount_type_array'][$id]] ) ):?>
													<?php echo esc_uap_content($data['default_rank_amount_value_array'][$id] . ' ' . $data['amount_types'][$data['default_rank_amount_type_array'][$id]]);?></td>
											<?php endif;?>
									<td>
										<?php $value = ($data['rank_amount_value_array'][$id]>-1) ? $data['rank_amount_value_array'][$id] : '';?>
										<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="uap-input-number" value="<?php echo esc_attr($value);?>" name="<?php echo esc_attr("reccuring_ranks_value[$id]");?>" />
										<select name="<?php echo esc_attr("reccuring_ranks_amount_type[$id]");?>"><?php
											foreach ($data['amount_types'] as $k=>$v):
												$selected = ($data['rank_amount_type_array'][$id]==$k) ? 'selected' : '';
												?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
												<?php
											endforeach;
										?></select>
									</td>
								</tr>
							<?php endforeach;?>
						</table>
					</div>
						<?php endif;?>
						</div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>
		</div>
