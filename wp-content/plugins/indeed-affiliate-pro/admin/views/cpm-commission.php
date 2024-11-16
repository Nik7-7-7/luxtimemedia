<div class="uap-wrapper">
		<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Cost Per Mile(CPM) Campaign', 'uap');?></h3>
					<div class="inside">
						<div class="uap-form-line">
						<div class="row">
						<div class="col-xs-12">
							<h2><?php esc_html_e('Activate/Hold CPM Campaign', 'uap');?></h2>
							<p><?php esc_html_e('Affiliates will receive a CPM Referral with flat amount rewarded for 1000 impressions (displaying your banners 1000 times).', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_cpm_commission_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_cpm_commission_enabled');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_cpm_commission_enabled" value="<?php echo esc_attr($data['metas']['uap_cpm_commission_enabled']);?>" id="uap_cpm_commission_enabled" />
                            <br/> <br/>
                            <p><?php esc_html_e('Once this module will be enabled Creatives (banners) listed on Affiliate Portal will have an additional code inside which will allow to track the Impressions. Affiliates will have to update their promotional banner codes to get this feature.', 'uap');?></p>

                        <p><strong><?php esc_html_e('Important: this feature may drain into your server over average traffic. Be sure that your Server Performance is good enough related to your number of affiliates otherwise disable the module to be avoided an overloading issue.', 'uap');?></strong></p>
						</div>

						</div>

						<div class="row">
							<div class="col-xs-10">
							<?php if (!empty($data['rank_list'])) :?>
							<h2><?php esc_html_e('CPM Amount For Each Rank', 'uap');?></h2>
								<p><?php esc_html_e('Set a special CPM amount for each rank. This option will also become available in the "Rank Settings" page.', 'uap');?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-5">
							<?php foreach ($data['rank_list'] as $id=>$label) :?>
								<div class="row">
									<div class="col-xs-12">
									<div class="input-group">
										<span class="input-group-addon"><?php echo esc_html($label);?></span>
									 		<input type="number" class="form-control uap-input-number" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($data['rank_value_array'][$id]);?>" name="<?php echo esc_uap_content("cpm_commission_value[$id]");?>" />
									 		<div class="input-group-addon"><?php echo esc_html($data['amount_types']['flat']);?></div>
										</div>
									</div>
								</div>
								<?php endforeach;?>
							<?php endif;?>
							</div>
						</div>

						<div class="uap-line-break"></div>

						<div class="uap-inside-item">
							<div class="row">
								<div class="col-xs-5">
									<h2><?php esc_html_e('Default Referral Status', 'uap');?></h2>
									<select name="uap_cpm_commission_default_referral_sts" class="form-control m-bot15"><?php
										foreach (array(1 => esc_html__('Pending', 'uap'), 2 => esc_html__('Approved', 'uap')) as $k=>$v){
											$selected = ($data['metas']['uap_cpm_commission_default_referral_sts']==$k) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
											<?php
										}
									?></select>
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
</div>
