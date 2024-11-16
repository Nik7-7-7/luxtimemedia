<div class="uap-wrapper">
			<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('SignUp Referrals (CPL - Cost Per Lead)', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/signup-referrals/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-5">
							<h2><?php esc_html_e('Activate/Hold SignUp Referrals (CPL)', 'uap');?></h2>
							<p><?php esc_html_e('You can activate this option to take place in your affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_sign_up_referrals_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_sign_up_referrals_enable');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_sign_up_referrals_enable" value="<?php echo esc_attr($data['metas']['uap_sign_up_referrals_enable']);?>" id="uap_sign_up_referrals_enable" />
						</div>
					</div>
					<div class="uap-line-break"></div>
					<div class="uap-inside-item">
						<div class="row">
							<div class="col-xs-5">
								<h2><?php esc_html_e('Default Amount', 'uap');?></h2>
								<p><?php esc_html_e('Set the default flat amount that will be used when no special amount is set for certain a rank.', 'uap');?></p>
								<div class="input-group">
								<span class="input-group-addon" >Amount</span>
									 <input type="number" class="form-control uap-input-number" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($data['metas']['uap_sign_up_amount_default']);?>" name="uap_sign_up_amount_default" />
									 <div class="input-group-addon"><?php echo esc_html($data['amount_types']['flat']);?></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-5">
								<h2><?php esc_html_e('Default Referral Status', 'uap');?></h2>
								<select name="uap_sign_up_default_referral_status" class="form-control m-bot15"><?php
									foreach (array(1 => esc_html__('Pending', 'uap'), 2 => esc_html__('Approved', 'uap')) as $k=>$v){
										$selected = ($data['metas']['uap_sign_up_default_referral_status']==$k) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
										<?php
									}
								?></select>
							</div>
						</div>
					</div>
					<div class="uap-line-break"></div>

					<div class="row">
						<div class="col-xs-10">
						<?php if (!empty($data['rank_list'])) :?>
						<h2><?php esc_html_e('Amount For Each Rank', 'uap');?></h2>
						<p><?php esc_html_e('Set a special sign up amount for each rank. This option will also become available in the "Rank Settings" page.', 'uap');?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-5">	
						<?php foreach ($data['rank_list'] as $id=>$label) :?>
							<div class="row">
								<div class="col-xs-12">
								<div class="input-group">
									<span class="input-group-addon" ><?php echo esc_html($label);?></span>
										<?php $value = ($data['rank_value_array'][$id]==-1) ? '' : $data['rank_value_array'][$id];?>
								 		<input type="number" class="form-control uap-input-number" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($value);?>" name="<?php echo esc_uap_content("signup_ranks_value[$id]");?>" />
								 		<div class="input-group-addon"><?php echo esc_html($data['amount_types']['flat']);?></div>
									</div>
								</div>
							</div>
							<?php endforeach;?>
						<?php endif;?>
						</div>
					</div>
					<div class="uap-line-break"></div>
					<div class="row">
						<div class="col-xs-4">
						</div>
					</div>

						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
					</div>
				</div>
			</form>
</div>
