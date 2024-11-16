<div class="uap-wrapper">
	<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Maximum Amount Rate', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/maximum-amount/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
		<div class="row">
		  <div class="col-xs-5">
			<div class="uap-form-line">
				<h2><?php esc_html_e('Activate/Hold Maximum Amount Rate', 'uap');?></h2>
				<p><?php esc_html_e('Set a maximum amount that can not be passed for a referral. It is a safety limit decided by the Admin for avoiding big referrals that have to be paid.', 'uap'); ?></p>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_maximum_amount_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_maximum_amount_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_maximum_amount_enabled" value="<?php echo esc_attr($data['metas']['uap_maximum_amount_enabled']);?>" id="uap_maximum_amount_enabled" />
			</div>
		 </div>
		</div>


			<div class="uap-inside-item">
				<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-5">
						<h4><?php esc_html_e('Default Amount Limit', 'uap');?></h4>
						<p><?php esc_html_e('Set the default flat amount limit that will be used when no special limit is set for a certain rank.', 'uap');?></p>
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Max Amount Limit', 'uap');?></span>
							<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="uap-field-text-with-padding form-control" name="uap_maximum_amount_value" value="<?php echo esc_attr($data['metas']['uap_maximum_amount_value']);?>" />
							<div class="input-group-addon"><?php echo esc_uap_content($data['amount_types']['flat']);?></div>
						</div>
					</div>
				</div>
			</div>
			</div>

			<?php if (!empty($data['ranks'])):?>
				<div class="uap-inside-item">
					<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-4">
						<h4><?php esc_html_e('Max Amount Limit for Each Rank', 'uap');?></h4>
						<p><?php esc_html_e('Set a special max amount limit for each rank. This option will also become available in the "Rank Settings" page.', 'uap');?></p>

				<?php foreach ($data['ranks'] as $rank_data):?>
								<div class="input-group">
									<span class="input-group-addon"><?php echo esc_uap_content($rank_data->label);?></span>
									<input type="number" min="0" step='<?php echo uapInputNumerStep();?>'
										class="uap-field-text-with-padding form-control" name="uap_maximum_amount_value_per_rank[<?php echo isset( $rank_data->id ) ? $rank_data->id : '';?>]"
										value="<?php echo isset( $data['metas']['uap_maximum_amount_value_per_rank'][$rank_data->id] ) ? $data['metas']['uap_maximum_amount_value_per_rank'][$rank_data->id] : '';?>" />
									<div class="input-group-addon"><?php echo isset($data['amount_types']['flat']) ? $data['amount_types']['flat'] : '';?></div>
								</div>
								<div class="uap-space"></div>
				<?php endforeach;?>

					</div>
				</div>
			</div>
			</div>
			<?php endif;?>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

</form>
</div>
