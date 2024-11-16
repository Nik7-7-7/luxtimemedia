
<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Ranks PRO Options', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
        	<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-7">
				<h2><?php esc_html_e('Activate/Hold Ranks PRO', 'uap');?></h2>
				<p><?php esc_html_e('A dynamic way to update Ranks periodically based on affiliate performance for a more interactive workflow. Despite the default Rank achievement process, where an affiliate will keep the rank forever once is assigned, with Ranks Pro module, affiliates may be downgraded if their performances where slowed down.', 'uap');?></p>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_ranks_pro_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ranks_pro_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_ranks_pro_enabled" value="<?php echo esc_attr($data['metas']['uap_ranks_pro_enabled']);?>" id="uap_ranks_pro_enabled" />
				</div>
			</div>
    </div>

			<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-8">
				<h4><?php esc_html_e('Achievements Calculation', 'uap');?></h4>
                <p><?php esc_html_e('Ranks are calculated and assigned 2 times per day via Cron jobs or it can be manually triggered from Affiliates section.', 'uap');?></p>
                <p><?php esc_html_e('If is set an ','uap');?> <strong>Unlimited</strong> <?php  esc_html_e(' period affiliates can receive a ', 'uap');?> <strong><?php esc_html_e('higher rank', 'uap');?></strong> <?php esc_html_e(' only if the achievements are accomplished. ', 'uap');?></p>
				 <p><?php esc_html_e('For ', 'uap');?><strong>Limited</strong> <?php esc_html_e(' calculation time, will be taken in consideration for achievement verification only Referrals from a specific period. Affiliates who did not achieved at least the current rank requirements may receive a ', 'uap');?>  <strong><?php esc_html_e(' lower rank', 'uap');?>.</strong></p>
            	</div>
            </div>
            </div>
            <div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
				<select name="uap_default_achieve_calculation" class="form-control m-bot15" onChange="uapHideDivIfValue( this.value, 'unlimited', '#uap_achieve_period_div_wrapp');"><?php
				$referral_format = array('unlimited' => 'Unlimited (default)', 'limited'=>'Limited back in Time');
				foreach ($referral_format as $k=>$v){
					$selected = ($data['metas']['uap_default_achieve_calculation']==$k) ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
					<?php
				}
				?></select>

				</div>
			</div>
			</div>

      <div class="uap-inside-item">
					<div class="row" id="uap_achieve_period_div_wrapp  <?php echo ($data['metas']['uap_default_achieve_calculation']=='unlimited') ? "uap-display-none" : '';?>" >
						<div class="col-xs-4">
							<div class="input-group">
								<span class="input-group-addon"><?php esc_html_e('Period of :', 'uap');?></span>
								<input type="number" min="1" id="uap_achieve_period" class="form-control" value="<?php echo esc_attr($data['metas']['uap_achieve_period']);?>" name="uap_achieve_period"/>
								<div class="input-group-addon"> <?php esc_html_e("days", 'uap');?></div>
							</div>
						</div>
					</div>
			</div>
			<div class="uap-inside-item">
				<div class="row">
				<div class="col-xs-7">
				<h4><?php esc_html_e('Reset Ranks', 'uap');?></h4>
				<p><?php esc_html_e('Reset all affiliates ranks to the Basic one monthly. Choose the desired date of the month', 'uap');?></p>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_ranks_pro_reset']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ranks_pro_reset');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_ranks_pro_reset" value="<?php echo esc_attr($data['metas']['uap_ranks_pro_reset']);?>" id="uap_ranks_pro_reset" />
				</div>
			</div>
			</div>
			<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
				<div class="input-group">
					<span class="input-group-addon"><?php esc_html_e('Reset on day', 'uap');?></span>
					<input type="number" min="1" max="30" class="form-control" value="<?php echo esc_attr($data['metas']['uap_ranks_pro_reset_day']);?>" name="uap_ranks_pro_reset_day"/>
					<div class="input-group-addon"> <?php esc_html_e("of every month", 'uap');?></div>
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
</form>
</div>
