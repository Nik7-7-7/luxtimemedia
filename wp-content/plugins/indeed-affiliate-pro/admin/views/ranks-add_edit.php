			<form action="<?php echo esc_url($data['url-manage']);?>" method="post">
				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

		<div class="uap-wrapper">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Manage Rank', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/step-1-create-ranks/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
							<div class="uap-form-line">
							<div class="row">
							<div class="col-xs-6">
							<h2><?php esc_html_e('Activate/Hold Rank', 'uap');?></h2>
								<p><?php esc_html_e('The Rank can be turned on or off without having to be removed', 'uap');?></p>
								<label class="uap_label_shiwtch uap-switch-button-margin">
									<?php $checked = ($data['metas']['status']) ? 'checked' : '';?>
									<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#rank_status');" <?php echo esc_attr($checked);?> />
									<div class="switch uap-display-inline"></div>
								</label>
								<input type="hidden" name="status" value="<?php echo esc_attr($data['metas']['status']);?>" id="rank_status" />
							</div>
						</div>
					</div>
						<div class="uap-form-line">
							<div class="row">
						<div class="col-xs-8">
							<h2><?php esc_html_e('Rank Settings', 'uap');?></h2>
							<p><?php esc_html_e('The slug must be distinct and limited to lowercase letters.', 'uap');?></p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
								<span class="input-group-addon"><?php esc_html_e('Slug', 'uap');?></span>
								<input type="text" class="form-control" placeholder="<?php esc_html_e('unique rank name', 'uap');?>" value="<?php echo esc_attr($data['metas']['slug']);?>" name="slug" />
							</div>
						</div>
					</div>
				</div>
				<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
								<span class="input-group-addon"><?php esc_html_e('Name', 'uap')?></span>
								<input type="text" class="form-control"  value="<?php echo esc_attr($data['metas']['label']);?>" name="label" id="rank_label" />
							</div>
						</div>
					</div>
				</div>
				<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-6">
							<div class="form-group">
								<h5><?php esc_html_e('Short Description', 'uap')?></h5>
								<textarea name="description" class="form-control text-area" cols="30" rows="3" placeholder="<?php esc_html_e('Some details...', 'uap');?>"><?php echo esc_uap_content($data['metas']['description']);?></textarea>
							</div>
						</div>
					</div>
				</div>

			<div class="uap-form-line">
					<div class="row">
							<div class="col-xs-12">
							<h2><?php esc_html_e('Set the Rank as the default for Affiliates', 'uap');?></h2>
							<p><?php esc_html_e('This ensures that new affiliates are automatically assigned to this default Rank upon registration, streamlining the onboarding process', 'uap');?></p>
								<label class="uap_label_shiwtch uap-switch-button-margin">
									<?php
									if ( $data['metas']['uap_register_new_user_rank'] && (int)$data['metas']['uap_register_new_user_rank'] === (int)$data['metas']['id'] ){
											$checked = 'checked';
									} else {
											$checked = '';
									}
									?>
									<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#set_as_default_rank');" <?php echo esc_attr($checked);?> />
									<div class="switch uap-display-inline"></div>
								</label>
								<input type="hidden" name="set_as_default_rank" value="<?php if ( $checked === 'checked' ){echo 1;}else {echo 0;}?>" id="set_as_default_rank" />
							</div>
						</div>
			</div>

				<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-12">
							<h2><?php esc_html_e('Rank Position', 'uap');?></h2>
							<p><?php esc_html_e('Based on rank position, an affiliate may go to the next level if the achievement conditions are met', 'uap');?></p>
							<div class="uap-rank-graphic"><?php echo esc_uap_content($data['graphic']); ?></div>
						</div>
					</div>
					<div class="row">
							<div class="col-xs-3">
								<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e('Position', 'uap');?></span>
									<input type="number" min="1" class="form-control uap-rank-settings-position" onChange="uapRankChangeOrderPreview(<?php echo esc_attr($data['metas']['id']);?>, this.value);" onKeyUp="uapRankChangeOrderPreview(<?php echo esc_attr($data['metas']['id']);?>, this.value);" max="<?php echo esc_attr($data['maximum_ranks']);?>" value="<?php echo esc_attr($data['metas']['rank_order']);?>" name="rank_order" />
								</div>
							 </div>
				</div>
			</div>
				<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-12">
							<h2><?php esc_html_e('Rank rate', 'uap');?></h2>
							<p><?php esc_html_e('Certain Product Rates or other variable settings may take precedence over the default Rank Rate.', 'uap');?></p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-4">
						<div>
								<select name="amount_type" class="form-control m-bot15"><?php
									foreach ($data['amount_types'] as $k=>$v):
										$selected = ($data['metas']['amount_type']==$k) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
										<?php
									endforeach;
								?></select>

						 </div>
					 </div>
				 </div>
				 <div class="row">
					 <div class="col-xs-12">
						 <p><?php esc_html_e('Choose a referral rate type. Referrals can be based on either a percentage or a flat rate amount.', 'uap');?></p>
					 </div>
				 </div>
 					<div class="row">
 						<div class="col-xs-4">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Rate', 'uap');?></span>
							<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="form-control" value="<?php echo esc_attr($data['metas']['amount_value']);?>" name="amount_value" aria-describedby="basic-addon1">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-8">
						<div>
							<p>
<?php
$offerType = get_option( 'uap_referral_offer_type' );
if ( $offerType == 'biggest' ){
	$offerType = esc_html__( 'Biggest', 'uap' );
} else {
	$offerType = esc_html__( 'Lowest', 'uap' );
}
echo esc_html__( 'You can choose which rate is used if more than one is set for the same activity (e.g., Ranks, Custom Product Rates, Product or Category rate), the  ', 'uap' ) . '<strong>' . $offerType . '</strong> ' . esc_html__( 'rate being used. You can adjust this from', 'uap' ) . ' <a href="' . admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=settings' ) . '" target="_blank">' . esc_html__( 'here.', 'uap' ) . '</a>';
?>
</p>
						</div>

					</div>
				</div>
			</div>


				<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-12">
							<h2><?php esc_html_e('Achievements Rules', 'uap');?></h2>
							<p><?php esc_html_e('To jump into current rank, affiliates need to accomplish the required achievements.', 'uap');?></p>

					</div>
				</div>
							<div id="achieve_rules">

								<?php
								$is_edit = FALSE;
								$excluded_achieve_type = array();
								if (!empty($data['metas']['achieve'])){
									$is_edit = TRUE;
									$arr = json_decode($data['metas']['achieve'], true);
								}
								if (!empty($arr) && !empty($arr['i'])){
									for ($i=1; $i<=$arr['i']; $i++){
										$excluded_achieve_type[] = $arr['type_' . $i];
									}
								}
								?>
								<div class="row">
									<div class="col-xs-4">
								<div id="achieve_type_div">
									<select id="achieve_type" class="form-control m-bot15">
										<?php foreach ($data['achieve_types'] as $k=>$v):
												if (in_array($k, $excluded_achieve_type)){
													continue;
												}
											?>
											<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php endforeach;?>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-4">
								<div class="input-group" id="achieve_value_div">
									<span class="input-group-addon"><?php esc_html_e('Value', 'uap');?></span>
									<input  id="achieve_value" type="number" min="1" class="form-control" aria-describedby="basic-addon1">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-4">
								<div id="achieve_relation_div" class="<?php echo ($is_edit) ? 'uap-display-block' : 'uap-display-none';?>">
									<select id="achieve_relation" class="form-control m-bot15">
										<option value="and">AND</option>
										<option value="or">OR</option>
									</select>
								</div>

								<div onClick="uapAddNewAchieveRule();" id="add_new_achieve" class="uap-add-new-like-wp" ><i class="fa-uap fa-add-new-item-uap"></i><?php esc_html_e('Add New Rule', 'uap');?> </div>

								<div id="achieve_rules_view"><?php
										if (!empty($arr) && is_array($arr)){
											$display_reset = 'inline-block';
											if ($arr['i']>1){
												for ($i=1; $i<=$arr['i']; $i++){
													if (isset($arr['relation_' . $i])){
														?>
														<div class="achieve-item-relation"><?php echo esc_html($arr['relation_' . $i]);?></div>
														<?php
													}
													?>
													<div class="achieve-item" id="achieve_item_<?php echo esc_attr($i);?>"><div class="uap-rank-settings-achievement-label"><?php echo esc_html($data['achieve_types'][$arr['type_' . $i]]);?></div><div>From: <?php echo esc_html($arr['value_' . $i]);?></div></div>
													<?php
												}
											} else {
												?>
												<div class="achieve-item" id="achieve_item_1"><div class="uap-rank-settings-achievement-label"> <?php echo esc_html($arr['type_1']);?></div><div>From: <?php echo esc_html($arr['value_1']);?></div></div>
												<?php
											}
										}
								?></div>
								<div id="achieve_reset" onClick="uapAchieveReset();" class="uap-special-button uap-special-button-singular <?php echo ($is_edit) ? 'uap-display-inline' : 'uap-display-none';?>"><i class="fa-uap fa-remove-uap"></i><?php esc_html_e('Reset Achievement Rules', 'uap');?></div>
							</div>
						</div>
							</div>
							<input type="hidden" value='<?php echo esc_attr($data['metas']['achieve']);?>' name="achieve" id="achieve_type_value"/>
			</div>


			<!--Deprecated starting with v.8.5 -->
				<!--div class="uap-inside-item">
					<div class="row">
						<div class="uap-form-line">
						<div class="col-xs-4">
							<h2><?php esc_html_e('Rank Color', 'uap');?></h2>
							<div>
								<ul id="uap_colors_ul" class="uap-colors-ul">
                                <?php
                                    $color_scheme = array('0a9fd8', '38cbcb', '27bebe', '0bb586', '94c523', '6a3da3', 'f1505b', 'ee3733', 'f36510', 'f8ba01');
                                    $i = 0;
                                    if (empty($data['metas']['color'])){
                                 		$data['metas']['color'] = $color_scheme[rand(0,9)];
                                 	}
                                    foreach ($color_scheme as $color){
                                        if ($i==5){
																					 echo esc_uap_content("<li class='uap-clear'></li>");
																				}
                                        $class = ($color==$data['metas']['color']) ? 'uap-color-scheme-item-selected' : '';
                                        ?>
                                            <li class="uap-color-scheme-item <?php echo esc_attr($class);?>  uap-box-background-<?php echo esc_attr($color);?>" onClick="uapChageColor(this, '<?php echo esc_attr($color);?>', '#uap_color');"></li>
                                        <?php
                                        $i++;
                                    }
                                ?>
                            </ul>
                            <input type="hidden" name="color" id="uap_color" value="<?php echo esc_attr($data['metas']['color']);?>" />
							</div>

						</div>
					</div>
				</div>
			</div-->
				<div class="uap-form-line">
				<input type="hidden" name="id"	value="<?php echo esc_attr($data['metas']['id']);?>" />
				<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
				</div>
		</div>
		</div>


		<div class="uap-stuffbox uap-magic-stuffbox <?php echo  (empty($data['bonus_enabled'])) ? 'uap-display-none' : 'uap-display-block'; ?>">
			<h3 class="uap-h3"><?php esc_html_e('Bonus', 'uap');?></h3>
			<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('Achievement Bonus', 'uap');?></h2>
					<p><?php esc_html_e('Every time an affiliate advances to a higher rank, they will receive a bonus of a fixed amount.', 'uap');?></p>
						<div class="input-group">
							<span class="input-group-addon">Amount</span>
								 <input type="number" class="form-control" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($data['metas']['bonus']);?>" name="bonus" aria-describedby="basic-addon1">
								 <div class="input-group-addon"><?php echo esc_html($data['amount_types']['flat']);?></div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
				</div>
			</div>
		</div>
			</div>
		</div>

		<div class="uap-stuffbox uap-magic-stuffbox <?php echo ($data['display-signup_referrals']) ? 'uap-display-block' : 'uap-display-none'; ?>">
			<h3 class="uap-h3"><?php esc_html_e('SignUp Referrals', 'uap');?></h3>
			<div class="inside">
				<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('SignUp Referrals', 'uap');?></h2>
					<p><?php esc_html_e('Reward affiliates with referrals generated through user sign-ups.', 'uap');?></p>
						<div class="input-group">
							<span class="input-group-addon">Amount</span>
								<?php $value = ($data['metas']['sign_up_amount_value']>-1) ? $data['metas']['sign_up_amount_value'] : '';?>
								 <input type="number" class="form-control" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($value);?>" name="sign_up_amount_value" aria-describedby="basic-addon1">
								 <div class="input-group-addon"><?php echo esc_html($data['amount_types']['flat']);?></div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
				</div>
			</div>
		</div>
			</div>
		</div>

		<div class="uap-stuffbox uap-magic-stuffbox <?php echo ($data['display-lifetime_commissions']) ? 'uap-display-block' : 'uap-display-none'; ?>">
			<h3 class="uap-h3"><?php esc_html_e('LifeTime', 'uap');?></h3>
			<div class="inside">
				<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('LifeTime Comission', 'uap');?></h2>
					<p><?php esc_html_e('Permit your affiliates to get compensated for all lifetime referrals.', 'uap');?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<div>
										<select name="lifetime_amount_type" class="form-control m-bot15"><?php
										foreach ($data['amount_types'] as $k=>$v):
											$selected = ($data['metas']['lifetime_amount_type']==$k) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
											<?php
										endforeach;
									?></select>

						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-4">
								<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e('Rate', 'uap');?></span>
									<?php $value = ($data['metas']['lifetime_amount_value']>-1) ? $data['metas']['lifetime_amount_value'] : '';?>
									<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="form-control" value="<?php echo esc_attr($value);?>" name="lifetime_amount_value" aria-describedby="basic-addon1">
								</div>


						<div id="uap_save_changes" class="uap-submit-form">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
				</div>
			</div>
		</div>
			</div>
		</div>

		<div class="uap-stuffbox uap-magic-stuffbox <?php echo ($data['display-reccuring_referrals']) ? 'uap-display-block' : 'uap-display-none'; ?>">
			<h3 class="uap-h3"><?php esc_html_e('Reccurring Referrals', 'uap');?></h3>
			<div class="inside">
				<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('Reccurring Referrals', 'uap');?></h2>
					<p><?php esc_html_e('Commissions should be given to membership systems for recurrent subscriptions.', 'uap');?></p>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-4">
					<div>
										<select name="reccuring_amount_type" class="form-control m-bot15"><?php
									foreach ($data['amount_types'] as $k=>$v):
										$selected = ($data['metas']['reccuring_amount_type']==$k) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
										<?php
									endforeach;
								?></select>

						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-4">
								<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e('Rate', 'uap');?></span>
									<?php $value = ($data['metas']['reccuring_amount_value']>-1) ? $data['metas']['reccuring_amount_value'] : '';?>
									<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="form-control" value="<?php echo esc_attr($value);?>" name="reccuring_amount_value" aria-describedby="basic-addon1">
								</div>

					<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
				</div>
			</div>
		</div>
			</div>
		</div>

		<div class="uap-stuffbox uap-magic-stuffbox <?php echo ($data['display-mlm']) ? 'uap-display-block' : 'uap-display-none'; ?>">
			<h3 class="uap-h3"><?php esc_html_e("MLM Referrals For Each MLM Level", 'uap')?></h3>
			<div class="inside">

			<div class="row">
				<div class="col-xs-8">
						<div class="uap-form-line">
					<h2><?php esc_html_e('MLM Referrals For Each MLM Level', 'uap');?></h2>
					<p><?php esc_html_e('Set a multi-level marketing system for your affiliates.', 'uap');?></p>
					</div>
					<table class="uap-dashboard-inside-table uap-rank-mlm-table" id="mlm-amount-for-each-level">
								<thead>
									<tr>
									<th class="uap-mlm-levels-table-header-col"><?php esc_html_e('MLM Depth (Level)', 'uap');?></th>
									<th><?php esc_html_e('Rate', 'uap');?></th>
								</tr>
								</thead>
								<?php
									for ($i=1; $i<=$data['mlm_matrix_depth']; $i++):
										?>
										<tr data-tr="<?php echo esc_attr($i);?>" id="uap_mlm_level_<?php echo esc_attr( $i);?>">
											<td><?php echo esc_html__('Level', 'uap') . ' ' . $i;?></td>
											<td>
												<input type="number" step='<?php echo uapInputNumerStep();?>' min="0" class="uap-input-number" value="<?php echo isset($data['metas']['mlm_amount_value'][$i]) ? $data['metas']['mlm_amount_value'][$i] : '';?>" name="<?php echo esc_attr("mlm_amount_value[$i]");?>" />
												<select name="<?php echo esc_attr("mlm_amount_type[$i]");?>"><?php
													foreach ($data['amount_types'] as $k=>$v):
														$selected = (!empty($data['metas']['mlm_amount_type'][$i]) && $data['metas']['mlm_amount_type'][$i]==$k) ? 'selected' : '';
														?>
														<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
														<?php
													endforeach;
												?></select>
											</td>
										</tr>
										<?php
									endfor;
								?>
						</table>
					</div>

				</div>
				<div id="uap_save_changes" class="uap-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
				</div>
			</div>
			</div>

        <div class="uap-stuffbox uap-magic-stuffbox <?php echo  (empty($data['pay_per_click_enabled'])) ? 'uap-display-none' : 'uap-display-block'; ?>">
			<h3 class="uap-h3"><?php esc_html_e('PayPerClick (CPC) Campaign', 'uap');?></h3>
			<div class="inside">
				<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('PPC Amount', 'uap');?></h2>
					<p><?php esc_html_e('Affiliates will receive a PPC Referral with flat amount each time a new referred user visit your website.', 'uap');?></p>
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Rate', 'uap');?></span>
								 <input type="number" class="form-control" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($data['metas']['pay_per_click']);?>" name="pay_per_click" aria-describedby="basic-addon1">
								 <div class="input-group-addon"><?php echo esc_html($data['amount_types']['flat']);?></div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
				</div>
			</div>
		</div>
			</div>
		</div>
        <div class="uap-stuffbox uap-magic-stuffbox <?php echo  (empty($data['cpm_commission_enabled'])) ? 'uap-display-none' : 'uap-display-block'; ?>">
			<h3 class="uap-h3"><?php esc_html_e('Cost Per Mile (CPM) Campaign', 'uap');?></h3>
			<div class="inside">
			<div class="row">
				<div class="col-xs-4">
					<h2><?php esc_html_e('CPM Amount', 'uap');?></h2>
					<p><?php esc_html_e('Affiliates will receive a CPM Referral with flat amount rewarded for 1000 impressions (displaying your banners 1000 times)', 'uap');?></p>
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Rate', 'uap');?></span>
								 <input type="number" class="form-control" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($data['metas']['cpm_commission']);?>" name="cpm_commission" aria-describedby="basic-addon1">
								 <div class="input-group-addon"><?php echo esc_html($data['amount_types']['flat']);?></div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
				</div>
			</div>
			</div>
		</div>
	</div>
	</form>

<?php if ( $data['achieve_types'] ):?>
		<?php foreach ( $data['achieve_types'] as $value => $label ):?>
				<span class="uap-js-achieve-types-values" data-value="<?php echo esc_attr($value);?>" data-label="<?php echo esc_attr($label);?>"></span>
		<?php endforeach;?>
<?php endif;?>
