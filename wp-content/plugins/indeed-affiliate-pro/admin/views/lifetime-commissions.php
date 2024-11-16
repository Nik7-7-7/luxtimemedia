<div class="uap-wrapper">
		<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('LifeTime Commissions', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/lifetime-commissions/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-form-line">
						<div class="row">
						<div class="col-xs-5">
							<h2><?php esc_html_e('Activate/Hold LifeTime Commissions', 'uap');?></h2>
							<p><?php esc_html_e('You can activate this option to take place in your affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_lifetime_commissions_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_lifetime_commissions_enable');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_lifetime_commissions_enable" value="<?php echo esc_attr($data['metas']['uap_lifetime_commissions_enable']);?>" id="uap_lifetime_commissions_enable" />
						</div>
						</div>
					</div>

						<div class="row">
						<div class="col-xs-10">

						<?php if (!empty($data['rank_list'])) :?>
						<div class="uap-form-line">
							<h2><?php esc_html_e('LifeTime Amount For Each Rank', 'uap');?></h2>
							<p><?php esc_html_e('Set a special lifetime amount for each rank that will replace the default amount rank. This option will also become available in the "Rank Settings" page.', 'uap');?></p>
							</div>
							<div class="uap-form-line">
							<table class="uap-dashboard-inside-table">
								<tr>
									<th><?php esc_html_e('Rank Name', 'uap');?></th>
									<th><?php esc_html_e('Default Amount Rank', 'uap');?></th>
									<th><?php esc_html_e('LifeTime Amount', 'uap');?></th>
								</tr>
								<?php foreach ($data['rank_list'] as $id=>$label) :?>
									<tr>
										<td><?php echo esc_html($label);?></td>
										<td><?php echo esc_html($data['default_rank_amount_value_array'][$id]) . ' ' . esc_html($data['amount_types'][$data['default_rank_amount_type_array'][$id]]);?></td>
										<td>
											<?php $value = ($data['rank_amount_value_array'][$id]>-1) ? $data['rank_amount_value_array'][$id] : '';?>
											<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="uap-input-number" value="<?php echo esc_attr($value);?>" name="<?php echo esc_attr("lifetime_ranks_value[".esc_attr($id)."]");?>" />
											<select name="<?php echo esc_attr("lifetime_ranks_amount_type[$id]");?>"><?php
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
							<p>
								<?php
								$offerType = get_option( 'uap_referral_offer_type' );
								if ( $offerType == 'biggest' ){
										$offerType = esc_html__( 'Biggest', 'uap' );
								} else {
										$offerType = esc_html__( 'Lowest', 'uap' );
								}
								echo esc_html__( 'If there are multiple Amounts set for the same action, like Ranks, Offers, Product or Category rate the ', 'uap' ) . '<strong>' . $offerType . '</strong> ' . esc_html__( 'will be taken in consideration. You may change that from', 'uap' ) . ' <a href="' . admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=settings' ) . '" target="_blank">' . esc_html__( 'here.', 'uap' ) . '</a>';
								?>
							</p>
						<?php endif;?>
					</div>
						</div>
					</div>
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>


				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Search Lifetime User-Affiliates', 'uap');?></h3>
					<div class="inside">
						<form  method="post">
							<div>
								<?php esc_html_e('Affiliate Username', 'uap');?> <input type="text" name="affiliate_username" value="<?php echo isset($_POST['affiliate_username']) ? $_POST['affiliate_username'] : '';?>"/>
								<?php esc_html_e('Referrer Username', 'uap');?> <input type="text" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : '';?>" />
								<input type="submit" value="<?php esc_html_e('Search', 'uap');?>" name="search" class="button button-primary button-large" />
							</div>
						</form>
						<?php if (!empty($data['affiliate_referrals_table_data'])):?>
							<table class="uap-dashboard-inside-table">
								<tr>
									<th><?php esc_html_e('Affiliate Username', 'uap');?></th>
									<th><?php esc_html_e('Referral Username', 'uap');?></th>
									<th><?php esc_html_e('Actions', 'uap');?></th>
								</tr>
								<?php foreach ($data['affiliate_referrals_table_data'] as $id=>$item) : ?>
									<tr>
										<td><?php echo esc_html($item['affiliate_username']);?></td>
										<td><?php echo esc_html($item['referral_username']);?></td>
										<td><a href="<?php echo esc_url($data['edit_relation'] . '&id=' . $id);?>"><?php esc_html_e('Edit', 'uap');?></a> | <a href="<?php echo esc_url($data['current_url'] . '&delete=' . $id);?>"><?php esc_html_e('Delete', 'uap');?></a></td>
									</tr>
								<?php endforeach;?>
							</table>
						<?php endif;?>
					</div>
				</div>


				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('LifeTime Commissions - Relations', 'uap');?></h3>
						<div class="inside">
							<p><?php esc_html_e('You may assign a potential client as a referrer with an affiliate such that, affiliate will receive a lifetime commission based on referrer actions.', 'uap'); ?></p>
							<div><a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=add_new_affiliate_referral_relation' );?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><span><?php esc_html_e( 'Add New Relation', 'uap' );?></span></a></div>
						</div>

				</div>
</div>
