<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo (isset($_REQUEST['new_level']) && $_REQUEST['new_level'] =='true') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab=levels&new_level=true' );?>"> <?php esc_html_e('Add New Membership', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo (!isset($_REQUEST['new_level'])) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url .'&tab=levels' );?>"><?php esc_html_e('Manage Memberships', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo esc_url( $url .'&tab=subscription_plan' );?>"><?php esc_html_e('Subscription Plan Showcase', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php

echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-wrapper ihc-admin-memberships">
<div id="col-right" class=" ihc-admin-memberships">
<?php
include_once IHC_PATH . 'admin/includes/functions/levels.php';

if (isset($_POST['ihc_save_level']) && isset( $_POST['save_level'] ) && wp_verify_nonce( sanitize_text_field($_POST['save_level']), 'ihc_save_level' ) ){
	unset( $_POST['ihc_save_level'] );
	unset( $_POST['save_level'] );
	$results = \Indeed\Ihc\Db\Memberships::save( indeed_sanitize_textarea_array($_POST) );
	$lid = isset( $results['id'] ) ? $results['id'] : 0;
	if ( $results['success'] ){
			$postData = indeed_sanitize_textarea_array($_POST);
			$postData['level_id'] = $lid;
			$lid = ihc_save_level( $postData );//save
	}

	if ($lid){
		/// MAGIC FEAT SETTINGS
		if (ihc_is_magic_feat_active('level_restrict_payment')){
			if (isset($_POST['ihc_level_restrict_payment_values'])){
				$ihc_level_restrict_payment_values = get_option('ihc_level_restrict_payment_values');
				$ihc_level_restrict_payment_values[$lid] = sanitize_text_field($_POST['ihc_level_restrict_payment_values']);
				update_option('ihc_level_restrict_payment_values', $ihc_level_restrict_payment_values);
			}
			if (isset($_POST['ihc_levels_default_payments'])){
				$ihc_levels_default_payments = get_option('ihc_levels_default_payments');
				$ihc_levels_default_payments[$lid] = sanitize_text_field($_POST['ihc_levels_default_payments']);
				update_option('ihc_levels_default_payments', $ihc_levels_default_payments);
			}
		}
		if (ihc_is_magic_feat_active('level_subscription_plan_settings')){
			if (isset($_POST['ihc_level_subscription_plan_settings_restr_levels'])){
				$restrict_arr = get_option('ihc_level_subscription_plan_settings_restr_levels');
				$restrict_arr[$lid] = sanitize_text_field($_POST['ihc_level_subscription_plan_settings_restr_levels']);
				update_option('ihc_level_subscription_plan_settings_restr_levels', $restrict_arr);
			}
			if (isset($_POST['ihc_level_subscription_plan_settings_condt'])){
				$conditions = get_option('ihc_level_subscription_plan_settings_condt');
				$conditions[$lid] = sanitize_text_field($_POST['ihc_level_subscription_plan_settings_condt']);
				update_option('ihc_level_subscription_plan_settings_condt', $conditions);
			}
		}
		do_action( 'ihc_admin_edit_save_level_after_submit_form', $lid, indeed_sanitize_textarea_array($_POST) );
	}

	if ( isset( $_POST['new_woo_product'] ) ){
		\Ihc_Db::unsign_woo_product_level_relation($lid);/// remove old relation
		if ( !empty( $_POST['new_woo_product'] ) ){
				update_post_meta( indeed_sanitize_textarea_array($_POST['new_woo_product']), 'iump_woo_product_level_relation', $lid);/// update
		}
	}

}

	if(isset($_REQUEST['edit_level']) || isset($_REQUEST['new_level'])){
		//add edit level
		?>

		<form method="post" action="<?php echo esc_url($url .'&tab=levels' );?>">
			<div class="ihc-stuffbox">
				<?php
				if(isset($_REQUEST['edit_level'])){
					$level_data = \Indeed\Ihc\Db\Memberships::getOne( sanitize_text_field($_REQUEST['edit_level']) );
					$label = esc_html__('Edit Membership Plan', 'ihc');
				}else{
					$order = 0;
					$level_arr = \Indeed\Ihc\Db\Memberships::getAll();
					if ($level_arr && count($level_arr)){
						 $order = count($level_arr);
					}
					$level_data = array( 'name'=>'',
										 'payment_type' => 'free',
										 'price' => '',
										 'label' => '',
										 //developer
										 'short_description' => '',
										 //end developer
										 'description'=>'',
										 'price_text' => '',
										 'button_label' => '',
										 'order' => $order,
										 'access_type' => 'unlimited',
										 'access_limited_time_type' => 'D',
										 'access_limited_time_value' => '',
										 'access_interval_start' => '',
										 'access_interval_end' => '',
										 'access_regular_time_type' => 'D',
										 'access_regular_time_value' => '',
										 'billing_type' => '',
										 'billing_limit_num' => '2',
										 'show_on' => '1',
			 							 'afterexpire_action' => 0,
										 'afterexpire_level' => -1,
			 							 'aftercancel_action' => 0,
			 						   'aftercancel_level' => -1,
			 							 'grace_period' => '',
										 'custom_role_level' => '',
										 'start_date_content' => '0',
										 'special_weekdays' => '',
										 //trial
										 'access_trial_time_value' => '',
										 'access_trial_time_type' => 'D',
										 'access_trial_price' => '',
										 'access_trial_couple_cycles' => 1,
										 'access_trial_type' => 1,
										);
					$label = esc_html__('Add Membership Plan', 'ihc');
				}

				/////////for old versions of indeed membership pro
				$check_arr = array( 'access_type'=>'unlimited',
									'access_limited_time_type'=>'D',
									'access_limited_time_value' => '',
									'access_interval_start' => '',
									'access_interval_end' => '',
									'access_regular_time_type' => 'D',
									'access_regular_time_value' => '',
									'billing_type' => '',
									'billing_limit_num' => 2,
									'show_on' => '1',
									'price_text' => '',
									'button_label' => '',
									'afterexpire_action' => 0,
									'afterexpire_level' => -1,
									'aftercancel_action' => 0,
									'aftercancel_level' => -1,
									'grace_period' => '',
									'custom_role_level' => '',
									'start_date_content' => '0',
									'special_weekdays' => '',
									//developer
									'short_description' => '',
									//end developer
									//trial
									'access_trial_time_value' => '',
									'access_trial_time_type' => 'D',
									'access_trial_price' => '',
									'access_trial_couple_cycles' => 1,
									'access_trial_type' => 1,
									);
				foreach ($check_arr as $k=>$v){
					if (!isset($level_data[$k])){
						$level_data[$k] = $v;
					}
				}

				/////////for old versions of indeed membership pro

				?>
				<h3>
					<?php echo esc_html($label);?>
				</h3>

				<div class="inside">
                 <div class="iump-form-line iump-no-border">
                 <h2><?php esc_html_e('Main Membership details', 'ihc');?></h2>
                 <p><?php esc_html_e('Customize Membership with a public name and a unique slug', 'ihc');?></p>
                 </div>
                 <div class="iump-form-line iump-no-border">
                <div class="row">
                	<div class="col-xs-6">
                             <div class="input-group">
                                <span class="input-group-addon input-group-addon-150"><?php esc_html_e('Membership Name', 'ihc');?></span>
                                <input name="label" class="form-control" type="text" value="<?php echo esc_attr($level_data['label']);?>" placeholder="<?php esc_html_e('suggestive Membership Name', 'ihc');?>"/>
                             </div>
                     </div>
                 </div>
                 </div>

                 <div class="iump-form-line iump-no-border">
                <div class="row">
                	<div class="col-xs-6">
                             <div class="input-group">
                                <span class="input-group-addon input-group-addon-150"><?php esc_html_e('Membership Slug', 'ihc');?></span>
                                <input name="name" class="form-control" type="text" value="<?php echo esc_attr($level_data['name']);?>" id="level_slug_id" onBlur="ihcUpdateLevelSlugSpan();"   placeholder="<?php esc_html_e('ex: unique_plan_slug', 'ihc');?>"/>
                            </div>

                    </div>
                </div>
								<p><?php esc_html_e('Slug must be uinque and based only on lowercase characters without extra spaces or symbols. Slug will not be visible for customers or on front-end side.', 'ihc');?></p>
								<input type="hidden" name="order" value="<?php echo (isset($level_data['the_order'])) ? $level_data['the_order'] : '';?>" />
                </div>
								<!-- developer -->
								 <div class="iump-form-line iump-no-border">
									 <div class="row">
										<div class="col-xs-6">
																<div class="input-group">
																	 <h4><?php esc_html_e('Membership Short Description', 'ihc');?></h4>
																	   <textarea name="short_description" class="form-control ihc-short_description" rows="2" cols="125" placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $level_data['short_description'] ) ? stripslashes($level_data['short_description']) : ''; ?></textarea>
								 								 </div>
											</div>
										</div>
									</div>
								<!-- end developer-->

								<?php do_action( 'ihc_filter_admin_section_edit_membership_after_membership_details', $level_data );?>

					<div class="iump-special-line ihc-level-access-section">
					<h2><?php esc_html_e('Membership Access', 'ihc');?></h2>
					<p><?php esc_html_e('If you wish to have Recurring steps choose "Recurring Subscription" type. All others have OneTime step.', 'ihc');?></p>
						<div class="iump-form-line iump-no-border form-required">
							<label for="tag-name" class="iump-labels"><?php esc_html_e('Access Type', 'ihc');?></label>

								<select name="access_type" onChange="ihcAccessPaymentType(this.value);" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-js-membership-select-access-type" >
									<?php
										$v_arr = array( 'unlimited' => 'LifeTime',
														'limited' => 'Limited Time',
														'date_interval' => 'Date Range',
														'regular_period' => 'Recurring Subscription',
													);
										foreach ($v_arr as $k=>$v){
											$selected = ($level_data['access_type']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
											<?php
										}
									?>
								</select>

						</div>
						<div class="iump-form-line iump-no-border form-required">
						<div id="limited_access_metas" class="ihc-membership-type-settings <?php if ($level_data['access_type']=='limited'){ echo esc_attr('ihc-display-block'); } else { echo esc_attr('ihc-display-none'); }?>">
							<div>
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Only for:', 'ihc');?></label>
								<input type="number" value="<?php echo esc_attr($level_data['access_limited_time_value']);?>" name="access_limited_time_value" min="1" max="31" class="ihc-access_limited_time_value"/>
								<select name="access_limited_time_type" class="ihc-access_limited_time_type">
									<?php
										$time_types = array('D'=>'Days', 'W'=>'Weeks', 'M'=>'Months', 'Y'=>'Years',);
										foreach ($time_types as $k=>$v){
											$selected = ($level_data['access_limited_time_type']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
											<?php
										}
									?>
								</select>
							</div>
							<div>


							</div>
						</div>

						<div id="date_interval_access_metas" class="ihc-membership-type-settings <?php if ($level_data['access_type']=='date_interval'){ echo esc_attr('ihc-display-block');} else {echo esc_attr('ihc-display-none');}?>">
							<div class="ihc-date_interval_access_metas">
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Fix Starting Date:', 'ihc');?></label>
								<input type="text" value="<?php echo esc_attr($level_data['access_interval_start']);?>" name="access_interval_start" id="access_interval_start" />
							</div>
							<div>
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Fix Expiration Date:', 'ihc');?></label>
								<input type="text" value="<?php echo esc_attr($level_data['access_interval_end']);?>" name="access_interval_end" id="access_interval_end"/>
							</div>
						</div>

						<div id="regular_period_access_metas" class="ihc-membership-type-settings <?php if ($level_data['access_type']=='regular_period'){ echo esc_attr('ihc-display-block');} else{ echo esc_attr('ihc-display-none');}?>">
							<div>
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Subscription Cycle', 'ihc');?></label>
								<input type="number" value="<?php echo esc_attr($level_data['access_regular_time_value']);?>" name="access_regular_time_value" min="1"  class="ihc-access_regular_time_value"/>
								<select name="access_regular_time_type" class="ihc-access_regular_time_type">
									<?php
										$time_types = array('D'=>'Days', 'W'=>'Weeks', 'M'=>'Months', 'Y'=>'Years',);
										foreach ($time_types as $k=>$v){
											$selected = ($level_data['access_regular_time_type']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_attr($v);?></option>
											<?php
										}
									?>
								</select>
							</div>
							<?php
								$pay_stat = ihc_check_payment_status('braintree');
								$display = ($pay_stat['active']=='braintree-active' && $pay_stat['settings']=='Completed') ? 'ihc-display-block' : 'ihc-display-none';
							?>
							<div class="ihc-specific-message <?php echo esc_attr($display); ?> ">
								<?php echo esc_ump_content('<strong>Braintree</strong>') . esc_html__(' gateway supports only "Months" period time and you have to create a Plan in Your Braintree Account Page with Plan ID set as  ', 'ihc') . esc_ump_content('<strong><span class="plan-slug-name"></span></strong>');?>
							</div>
							<?php
								$pay_stat = ihc_check_payment_status('authorize');
								$display = ($pay_stat['active']=='authorize-active' && $pay_stat['settings']=='Completed') ? 'ihc-display-block' : 'ihc-display-none';
							?>
							<div class="ihc-specific-message <?php echo esc_attr($display); ?> ">
								<?php echo esc_ump_content('<strong>Authorize.net</strong>') . esc_html__(' gateway requires the minimum time value for recurring payments at least 7 days.', 'ihc');?>
							</div>
                            <?php
								$pay_stat = ihc_check_payment_status('paypal');
								$display = ($pay_stat['active']=='paypal-active' && $pay_stat['settings']=='Completed') ? 'ihc-display-block' : 'ihc-display-none';
							?>
							<div class="ihc-specific-message <?php echo esc_attr($display); ?> ">
								<?php echo esc_ump_content('<strong>PayPal</strong>') . esc_html__(' gateway allows recurring periods no more than 12 months.', 'ihc');?>
							</div>
						</div>
					</div>
						<div  id="set_expired_level"  class="iump-form-line iump-no-border form-required ihc-set_expired_level <?php if (isset($level_data['access_type']) && $level_data['access_type']!='unlimited') {echo esc_attr('iump-display-table');} else{ echo esc_attr('ihc-display-none');}?>">
								<label for="tag-name" class="iump-labels ihc-end-of-term-label iump-display-cell"><?php esc_html_e('End of Term Action', 'ihc');?></label>
								<div class="ihc-display-inline iump-display-cell">
									<div>
									<select name="afterexpire_action"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" >
										<?php
											$afterexpire_type = array(
																 0 => esc_html__('Do Nothing', 'ihc'),
																 1 => esc_html__('Remove this Membership from Member', 'ihc'),
																);
											foreach($afterexpire_type as $k=>$v){
												?>
													<option value="<?php echo esc_attr($k);?>" <?php if($k==$level_data['afterexpire_action']){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
												<?php
											}
										?>
									</select>
									<p><?php echo esc_html__('Action to be performed after current Membership is Expired. Action is taken after Grace Period time or 24hrs if is missing.', 'ihc'); ?></p>
								  </div>
									<div class="ihc-assign-after">
										<h5><?php echo esc_html__('Assign new Membership after', 'ihc'); ?></h5>
								    <select name="afterexpire_level"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select"  >
									     <option value="-1" <?php if ($level_data['afterexpire_level']=='-1'){ echo esc_attr('selected');}?>>...</option>
									<?php
									$additional_levels = \Indeed\Ihc\Db\Memberships::getAll();
									if (isset($_GET['edit_level'])){
										if (isset($additional_levels[$_GET['edit_level']])){
											unset($additional_levels[$_GET['edit_level']]);
										}
									}
										if (isset($additional_levels) && is_array($additional_levels) && count($additional_levels)){
											foreach ($additional_levels as $k=>$v){
													$selected = ($level_data['afterexpire_level']==$k) ? 'selected' : '';
													?>
														<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v['name']);?></option>
													<?php
											}
										}
									?>
								      </select>
								</div>
							</div>
						</div>
						<div id="set_cancel_level" class="iump-form-line iump-no-border form-required ihc-set_expired_level  <?php if (isset($level_data['access_type']) && $level_data['access_type'] =='regular_period'){ echo esc_attr('iump-display-table');} else{ echo esc_attr('ihc-display-none');}?>">
								<label for="tag-name" class="iump-labels ihc-end-of-term-label iump-display-cell"><?php esc_html_e('Cancel Subscription Action', 'ihc');?></label>
								<div  class="ihc-display-inline  iump-display-cell">
									<select name="aftercancel_action"  onChange="ihcAfterCancelAction(this.value);" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" >
										<?php
											$aftercancel_type = array(
																 0 => esc_html__('Do Nothing (recommended)', 'ihc'),
																 1 => esc_html__('Remove this Subscription from Member', 'ihc'),
																 2 => esc_html__('Replace this Subscription', 'ihc'),
																);
											foreach($aftercancel_type as $k=>$v){
												?>
													<option value="<?php echo esc_attr($k);?>" <?php if($k==$level_data['aftercancel_action']){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
												<?php
											}
										?>
									</select>
									<p><?php echo esc_html__('Action to be performed when Member cancels this Subscription from Member Portal.', 'ihc'); ?></p>
									<div id="aftercancel_level" class="ihc-assign-after-cancel  <?php if (isset($level_data['aftercancel_action']) && $level_data['aftercancel_action'] == 2){ echo esc_attr('ihc-display-block');} else{ echo esc_attr('ihc-display-none');}?>">
										<h5><?php echo esc_html__('Assign new Membership after Cancel', 'ihc'); ?></h5>
								    <select name="aftercancel_level"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select"  >
									     <option value="-1" <?php if ($level_data['aftercancel_level']=='-1'){ echo esc_attr('selected');}?>>...</option>
									<?php
									$additional_levels = \Indeed\Ihc\Db\Memberships::getAll();
									if (isset($_GET['edit_level'])){
										if (isset($additional_levels[$_GET['edit_level']])){
											unset($additional_levels[$_GET['edit_level']]);
										}
									}
										if (isset($additional_levels) && is_array($additional_levels)  && count($additional_levels)){
											foreach ($additional_levels as $k=>$v){
													$selected = ($level_data['aftercancel_level']==$k) ? 'selected' : '';
													?>
														<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v['name']);?></option>
													<?php
											}
										}
									?>
								      </select>
								</div>
								</div>
						</div>
						<div id="set_grace_period" class="iump-form-line iump-no-border form-required ihc-set_expired_level <?php if (isset($level_data['access_type']) && $level_data['access_type']!='unlimited'){ echo esc_attr('iump-display-table');} else{ echo esc_attr('ihc-display-none');}?>">
								<label for="tag-name" class="iump-labels ihc-end-of-term-label iump-display-cell"><?php esc_html_e('Grace Period after Expire', 'ihc');?></label>
								<div  class="ihc-display-inline iump-display-cell">
									<input type="number" value="<?php echo esc_attr($level_data['grace_period']);?>" name="grace_period" min="1"  class="ihc-grace_period"/> <?php echo esc_html__('days', 'ihc'); ?>
									<p><?php echo esc_html__('Choose a particular Grace Period for this Membership or leave empty and use default value from ', 'ihc'); ?>
										<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=general&subtab=public_workflow');?>" target="_blank"><?php esc_html_e( 'General Options', 'ihc' );?></a></p>
								</div>
						</div>

						<?php do_action( 'ihc_filter_admin_section_edit_membership_after_membership_access', $level_data );?>

					 </div>
					<div class="inside ihc-additional-settings">
                     <div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e('Additional Access Settings', 'ihc');?></h2>
                        <p><?php esc_html_e('Optional setttings that may be necessary to customize membership workflow.', 'ihc');?></p>
                    </div>
						<div class="iump-form-line iump-no-border">
							<h4><?php esc_html_e('Custom Member WordPress Role', 'ihc');?></h4>
                            <div class="ihc-additional-settings-message"><?php esc_html_e('Available only during the registration step for new Registered users.', 'ihc');?></div>
                            <div>
							<select name="custom_role_level"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" >
								<option value="-1"><?php esc_html_e('...Default Register option', 'ihc');?></option>
								<?php
									$roles = ihc_get_wp_roles_list();
									if ($roles){
										foreach ($roles as $k=>$v){
											$selected = ($level_data['custom_role_level']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
                            </div>

						</div>

						<div class="iump-form-line iump-no-border ihc-display-none">
							<h4><?php esc_html_e('Show Only Content created Starting with the Assigned Date', 'ihc');?></h4>
							<div>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($level_data['start_date_content'] == 1) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#start_date_content');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($level_data['start_date_content']);?>" name="start_date_content" id="start_date_content" />
							</div>
							<div class="ihc-additional-settings-message"><?php esc_html_e('Available only for Pages and Posts', 'ihc');?></div>
						</div>

						<div class="iump-form-line iump-no-border" >

							<h4><?php esc_html_e('Special Week days Membership is running', 'ihc');?></h4>
                            <div class="ihc-additional-settings-message"><?php esc_html_e('Based on Server/Website Time', 'ihc');?></div>
                            <div>
							<select name="special_weekdays"  class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" >
								<?php
									$day_type = array(
														 '' => esc_html__('Entire Week', 'ihc'),
														 'weekdays' => esc_html__('WeekDays', 'ihc'),
														 'weekend' => esc_html__('WeekEnd', 'ihc'),
														);
									foreach($day_type as $k=>$v){
										?>
											<option value="<?php echo esc_attr($k);?>" <?php if($k==$level_data['special_weekdays']){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
										<?php
									}
								?>
							</select>
                            </div>

							<div  class="ihc-additional-settings-message"><?php esc_html_e('Ex: Membership "Test01" has the Special Week Days set to WeekDays. "Test01" is restricted from viewing certain content on the website. During the WeekEnd "Test01" will not be restricted. ', 'ihc');?></div>
						</div>
					</div>

					<?php do_action( 'ihc_filter_admin_section_edit_membership_after_membership_access_settings', $level_data );?>


					<div class="iump-special-line ihc-level-billing-section">
						<h2><?php esc_html_e('Membership Billing', 'ihc');?></h2>

						<div class="iump-form-line iump-no-border form-required">
							<label for="tag-name" class="iump-labels"><?php esc_html_e('Payment Type', 'ihc');?></label>
							<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" name="payment_type" onChange="ihcSelectShDiv(this, '#payment_options', 'payment');">
								<?php
									$price_type = array(
														 'free' => esc_html__('Free', 'ihc'),
														 'payment' => esc_html__('Payment', 'ihc'),
														);
									foreach($price_type as $k=>$v){
										?>
											<option value="<?php echo esc_attr($k);?>" <?php if($k==$level_data['payment_type']){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
										<?php
									}
								?>
							</select>
						</div>

					<div id="payment_options"  class="<?php if ($level_data['payment_type']=='free'){ echo esc_attr('ihc-display-none');} else { echo esc_attr(' ihc-display-block'); }?>" >
						<div class="iump-form-line iump-no-border" id="level_price_wd" >
							<div>
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Membership Price', 'ihc');?></label>
                                <div class="ihc-price-wrapper">
                                	<div class="input-group">
                                        <input type="number" min="0" value="<?php echo esc_attr($level_data['price']);?>" name="price" step="0.01" class="form-control"/>
                                        <div class="input-group-addon">
                                        <?php
                                            $currency = get_option('ihc_currency');
                                            if ($currency==FALSE){
                                                $currency = 'USD';
                                            }
                                            echo esc_html($currency);
                                        ?>
                                        </div>
                                     </div>
                                </div>
								<div class="ihc-price-message"><?php esc_html_e("Stripe payment gateways requires at least 0.50".$currency." subscription price", 'ihc');?></div>
							</div>
						</div>

						<div class="iump-form-line iump-no-border form-required">
							<label for="tag-name" class="iump-labels" id="billind_rec_label"><?php esc_html_e('Recurring Subscription Time', 'ihc');?></label>
							<select disabled="disabled" id="billing_type_1" class="<?php if ($level_data['access_type']=='regular_period'){ echo esc_attr('ihc-display-none');} else{ echo esc_attr('ihc-display-inline');} ?>">
								<option value="bl_onetime" >One Time</option>
							</select>
							<select name="billing_type" id="billing_type_2" onChange="ihcCheckBillingType(this.value);" class="<?php if ($level_data['access_type']=='regular_period'){ echo esc_attr(' ihc-display-inline');} else {echo esc_attr(' ihc-display-none');} ?>">
								<option value="bl_ongoing" <?php if (!empty($level_data['billing_type']) && $level_data['billing_type']=='bl_ongoing'){ echo esc_attr('selected');}?> ><?php esc_html_e('On Going', 'ihc');?></option>
								<option value="bl_limited" <?php if (!empty($level_data['billing_type']) && $level_data['billing_type']=='bl_limited'){ echo esc_attr('selected');}?> ><?php esc_html_e( 'Limited', 'ihc');?></option>
							</select>
						</div>

						<?php do_action( 'ihc_filter_admin_section_edit_membership_after_membership_billing', $level_data );?>

						<?php
							$display = 'ihc-display-none';
							if ($level_data['access_type']=='regular_period' && isset($level_data['billing_type']) && $level_data['billing_type']=='bl_limited'){
								$display = 'ihc-display-block';
							}
						?>
						<div class="iump-form-line iump-no-border <?php echo esc_attr($display);?>" id="regular_period_billing">
							<label for="tag-name" class="iump-labels"><?php esc_html_e('Max no of Periods', 'ihc');?></label>
							<input type="number" min="2" value="<?php if (!empty($level_data['billing_limit_num'])) echo esc_attr($level_data['billing_limit_num']);?>" max="52" name="billing_limit_num"  />
						</div>

						<?php
							$display = 'ihc-display-none';
							if ($level_data['access_type']=='regular_period' && isset($level_data['payment_type']) && $level_data['payment_type']=='payment'){
								$display = 'ihc-display-block';
							}
						?>
						<div class="iump-no-border ihc-trial_period_billing <?php echo esc_attr($display);?>" id="trial_period_billing">
                        <h2><?php esc_html_e('Trial/Initial Payment Settings', 'ihc');?></h2>
                        <p><?php esc_html_e('Setup trial or initial payment details only if you wish your Subscription to have one. Otherwise leave next fields ', 'ihc');?> <strong><?php esc_html_e(' empty ', 'ihc');?></strong></p>
							<div class="iump-form-line iump-no-border" >
								<label for="tag-name" class="iump-labels ihc-trial-price-label"><?php esc_html_e('Trial/Initial Payment Price', 'ihc');?></label>
                                <div class="ihc-trial-price-wrapper">
                                          <div class="input-group">
                                                  <input type="number" value="<?php echo esc_attr($level_data['access_trial_price']);?>" name="access_trial_price" min="0" step="0.01"  class="form-control ihc-trial-price"/>
                                                  <div class="input-group-addon">
                                                  <?php
                                                      echo esc_html($currency);
                                                  ?>
                                                  </div>
                                      </div>
                                </div>
                                <div class="ihc-trial-price-message"><?php
									esc_html_e('If you wish to setup Trial/Initial Payment as free you must set the price to 0'. $currency.'', 'ihc');
								?></div>
								<div class="ihc-trial-price-messagetwo"><?php
									esc_html_e('Braintree supports only 0'. $currency.' Trial/Initial Payment price ', 'ihc');
								?></div>
							</div>
							<div class="iump-form-line iump-no-border">
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Trial Period Type', 'ihc');?></label>
								<select name="access_trial_type" onChange="ihcChangeTrialType(this.value);">
									<?php
										$types = array('1' => esc_html__('Certain Period', 'ihc'), '2' => esc_html__('Couple cycles subscription payments', 'ihc'));
										foreach ($types as $k=>$v){
											$selected = ($level_data['access_trial_type']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
											<?php
										}
									?>
								</select>
							</div>
							<div id="trial_certain_period" class="iump-form-line iump-no-border iump-trial-period <?php if ($level_data['access_trial_type']==1){ echo esc_attr('ihc-display-block');} else { echo esc_attr('ihc-display-none');}?>">
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Trial Certain Period', 'ihc');?></label>
								<input type="number" value="<?php echo esc_attr($level_data['access_trial_time_value']);?>" name="access_trial_time_value" min="1" max="31" class="ihc-access_trial_time_value"/>
									<select name="access_trial_time_type" class="ihc-access_trial_time_type">
										<?php
											$access_time_types = array('D'=>'Days', 'W'=>'Weeks', 'M'=>'Months', 'Y'=>'Years',);
											foreach ($access_time_types as $k=>$v){
												$selected = ($level_data['access_trial_time_type']==$k) ? 'selected' : '';
												?>
													<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
												<?php
											}
										?>
									</select>
								<div class="ihc-trial-price-messagetwo"><?php
									esc_html_e('Certain Period workflow is not supported by Authorize.net and 2Checkout gateways.', 'ihc');
								?></div>

								<?php do_action( 'ihc_filter_admin_section_edit_membership_after_membership_payment_settings', $level_data );?>

							</div>
							<div class="iump-form-line iump-no-border <?php if ($level_data['access_trial_type']==2){ echo esc_attr('ihc-display-block'); } else { echo esc_attr('ihc-display-none'); }?>"  id="trial_couple_cycles">
								<label for="tag-name" class="iump-labels"><?php esc_html_e('Trial Couple Cycles:', 'ihc');?></label>
								<input type="number" value="<?php echo esc_attr($level_data['access_trial_couple_cycles']);?>" name="access_trial_couple_cycles" min="1" class="ihc-access_trial_couple_cycles"/>
                                <?php
								$pay_stat = ihc_check_payment_status('twocheckout');
								$display = ($pay_stat['active']=='twocheckout-active' && $pay_stat['settings']=='Completed') ? 'ihc-display-block' : 'ihc-display-none';
									?>
								<div class="ihc-trial-price-messagetwo <?php echo  $display; ?>" ><?php
									esc_html_e('Not more than 1 cycle for 2Checkout gateways.', 'ihc');
								?></div>
								<div class="ihc-clear"></div>
                                <?php
								$pay_stat = ihc_check_payment_status('braintree');
								$display = ($pay_stat['active']=='braintree-active' && $pay_stat['settings']=='Completed') ? 'ihc-display-block' : 'ihc-display-none';
									?>
								<div class="ihc-trial-price-messagetwo <?php echo esc_attr($display); ?>" ><?php
									esc_html_e('If You use Braintree the trial period must be set into Plan Details too.', 'ihc');
								?></div>
							</div>

						</div>

					</div>


					</div>
					<div class="form-field inside ihc-plan-details-wrapper">
                     	<div class="iump-form-line iump-no-border">
							<h2><?php esc_html_e('Subscriptions Plan Page details', 'ihc');?></h2>
                    	</div>
						<div class="iump-form-line iump-no-border">
						<h4><?php esc_html_e('Show/Hide in Subscription Plan showcase', 'ihc');?></h4>
                        <p><?php esc_html_e('If you choose to hide the membership on front-end showcase, it will still be active but users will not find it into default Subscription plan showcase to Sign Up on their own choice', 'ihc');?></p>
						<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($level_data['show_on'] == 1) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#show_on');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($level_data['show_on']);?>" name="show_on" id="show_on" />
						</div>
            <div  class="iump-form-line iump-no-border iump-membership-description">
						<h4><?php esc_html_e('Membership Description', 'ihc');?></h4>
                        <p><?php esc_html_e('How membership is described on Subscription Plan showcase may attract customers to Sign Up', 'ihc');?></p>
                        <div class="ihc-plan-details-editor">
						<?php
							$settings = array(
												'media_buttons' => true,
												'textarea_name'=>'description',
												'textarea_rows' => 5,
												'tinymce' => true,
												'quicktags' => true,
												'teeny' => true,
											);
							wp_editor(ihc_correct_text($level_data['description']), 'tag-description', $settings);
						?>
						</div>
					</div>
					<div  class="iump-form-line iump-no-border">
						<h4><?php esc_html_e('Price Details', 'ihc');?></h4>
						<input name="price_text" type="text" value="<?php echo stripslashes($level_data['price_text']);?>" class="ihc-plan-details-price-text">
						<div class="ihc-plan-details-price-message"><?php
									esc_html_e('It will not change the Membership price but just describes the costs', 'ihc');
								?>
            </div>
					</div>
					<div  class="iump-form-line iump-no-border">
						<h4><?php esc_html_e('Button Label', 'ihc');?></h4>
						<input name="button_label" type="text" value="<?php echo esc_attr($level_data['button_label']);?>" class="ihc-plan-details-price-text">

					  </div>

						<?php do_action( 'ihc_filter_admin_section_edit_membership_after_membership_plan', $level_data );?>

					</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input type="hidden" name="save_level" value="<?php echo wp_create_nonce( 'ihc_save_level' );?>" />
						<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" id="ihc_submit_bttn" name="ihc_save_level" class="button button-primary button-large" />
					</div>

					<?php
						if(isset($_REQUEST['edit_level'])){
							?>
							<input type="hidden" name="level_id" value="<?php echo sanitize_text_field($_REQUEST['edit_level']);?>" />
							<?php
						}
					?>
				</div>
			</div>

<?php
$lid = (isset($_GET['edit_level'])) ? sanitize_text_field($_GET['edit_level']) : -1;
$levels = \Indeed\Ihc\Db\Memberships::getAll();

?>

	<!-- BEGIN RESTRICT PAYMENT -->
	<?php if (ihc_is_magic_feat_active('level_restrict_payment') && $lid!=-1 && $levels[$lid]['payment_type']!='free'):?>
		<?php
			$data['metas'] = ihc_return_meta_arr('level_restrict_payment');//getting metas
			$default_payment = get_option('ihc_payment_selected');
			$payments = ihc_get_active_payments_services();
			if ($lid!=-1 && !empty($data['metas']['ihc_levels_default_payments'][$lid])){
				$default_payment_for_level = $data['metas']['ihc_levels_default_payments'][$lid];
			} else {
				$default_payment_for_level = -1;
			}
			$temp_payments = $payments;
			unset($temp_payments[$default_payment]);
			$current_default_label = $payments[$default_payment];
		?>
		<div class="ihc-stuffbox ihc-stuffbox-magic-feat">
				<h3 class="ihc-h3"><?php esc_html_e('Payment Method restriction', 'ihc');?> <span  class="ihc-extension-label">(<?php esc_html_e('Extension', 'ihc');?>)</span></h3>
			<div class="inside">
				<div class="iump-form-line">
					<p><?php esc_html_e('Choose which Payment Methods you will be available for current Membership when multiple Payment Methods are available. ', 'ihc');?></p>
				</div>
				<div class="iump-form-line">
					<div>
						<h4><?php esc_html_e('Default Payment Method', 'ihc');?></h4>
						<p><?php esc_html_e('Each membership may have a different payment method selected by default', 'ihc');?></p>
						<select name="ihc_levels_default_payments">
							<option value="-1" <?php if ($k==-1){ echo esc_attr('selected');}?> ><?php echo esc_html__('Current Default Payment Method', 'ihc') . '(' . $current_default_label . ')';?></option>
							<?php foreach ($temp_payments as $k=>$v):?>
								<?php $selected = ($k==$default_payment_for_level) ? 'selected' : '';?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
				<div class="iump-form-line">
					<?php
						if ($lid!=-1 && isset($data['metas']['ihc_level_restrict_payment_values'][$lid])){
							$excluded_values = $data['metas']['ihc_level_restrict_payment_values'][$lid];
							$excluded_values_array = explode(',', $excluded_values);
						} else {
							$excluded_values = '';
							$excluded_values_array = array();
						}
					?>
						<h4><?php esc_html_e('Payments Methods available:', 'ihc');?></h4>
						<p><?php esc_html_e('Uncheck what payment methods should not be available for current Membership. Check all of them if no restriction is applied.', 'ihc');?></p>
						<?php foreach ($payments as $k=>$v):?>
							<?php $checked = (!in_array($k, $excluded_values_array)) ? 'checked' : '';?>
							<div class="ihc-inline-block-item">
								<input type="checkbox" onClick="ihcAddToHiddenWhenUncheck(this, '<?php echo esc_attr($k);?>', '<?php echo esc_attr('#' . $lid . 'excludedforlevel');?>');" <?php echo esc_attr($checked);?> />
								<img src="<?php echo IHC_URL . 'assets/images/'.$k.'.png';?>" class="ihc-payment-icon ihc-payment-select-img-selected" />
							</div>
						<?php endforeach;?>
						<input type="hidden" name="ihc_level_restrict_payment_values" value="<?php echo esc_attr($excluded_values);?>" id="<?php echo esc_attr($lid) . 'excludedforlevel';?>"/>
				</div>

				<div class="ihc-wrapp-submit-bttn iump-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_level" class="button button-primary button-large" />
				</div>
			</div>
		</div>
	<?php endif;?>
	<!-- END OF RESTRICT PAYMENT -->


	<!-- BEGIN Membership - Subscription Plan Display Details -->
	<?php if (ihc_is_magic_feat_active('level_subscription_plan_settings')):?>
		<?php
			$data['metas'] = ihc_return_meta_arr('level_subscription_plan_settings');//getting metas
			$hidden_value = ($lid!=-1 && !empty($data['metas']['ihc_level_subscription_plan_settings_condt'][$lid])) ? $data['metas']['ihc_level_subscription_plan_settings_condt'][$lid] : '';
			$hidden_arr = array();
			if (!empty($hidden_value)){
				$hidden_arr = explode(',', $hidden_value);
			}
		?>
			<div class="ihc-stuffbox ihc-stuffbox-magic-feat">
					<h3 class="ihc-h3"><?php esc_html_e('Subscription Plan Display Details', 'ihc');?> <span  class="ihc-extension-label">(<?php esc_html_e('Extensions', 'ihc');?>)</span></h3>
				<div class="inside">
					<div class="iump-form-line">

						<div>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($lid!=-1 && !empty($data['metas']['ihc_level_subscription_plan_settings_restr_levels'][$lid])) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '<?php echo esc_attr('#ihc_level_subscription_plan_settings_restr_levels'.$lid);?>');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
                                                        <?php $value = isset( $data['metas']['ihc_level_subscription_plan_settings_restr_levels'][$lid] ) ? $data['metas']['ihc_level_subscription_plan_settings_restr_levels'][$lid] : 0;?>
							<input type="hidden" name="ihc_level_subscription_plan_settings_restr_levels" value="<?php echo esc_attr($value);?>" id="<?php echo esc_attr('ihc_level_subscription_plan_settings_restr_levels'.$lid);?>" />
						</div>
						<h4><?php esc_html_e('Turn On Restricted workflow', 'ihc');?></h4>
					</div>
					<div class="iump-form-line">
						<p><?php esc_html_e('You may restrict access to this Membership based on certain conditions. Customers will not see this Membership on Subscription Plan showcase if conditions are not covered. Leave this option turn off if you wish to have this Membership available for everyone. ', 'ihc');?></p>
					</div>
					<div class="iump-form-line">
						<h5><?php esc_html_e('Membership will not be available for members that never bought a membership yet:', 'ihc');?></h5>
						<div>
							<?php $checked = (in_array('unreg', $hidden_arr)) ? 'checked' : '';?>
							<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, 'unreg', '<?php echo esc_attr('#levelcond');?>');" /><span class="ihc-checkbox-align"> <?php esc_html_e('Unregistered Users', 'ihc');?></span>
						</div>
						<div>
							<?php $checked = (in_array('no_pay', $hidden_arr)) ? 'checked' : '';?>
							<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, 'no_pay', '<?php echo esc_attr('#levelcond');?>');" /><span class="ihc-checkbox-align"> <?php esc_html_e('Registered Users without any payment proceeded.', 'ihc');?></span>
						</div>
						<h4 class="ihc-membership-plus-message"><?php esc_html_e('Membership will not be available if member already bought any of those Memberships: ', 'ihc');?></h4>
						<?php foreach ($levels as $levelid=>$larr):?>
							<?php $spanclass = ($levelid==$lid) ? 'ihc-magic-feat-bold-span' : '';?>
							<div class="ihc-membership-plus-checkbox-wrapper">
								<?php $checked = (in_array($levelid, $hidden_arr)) ? 'checked' : '';?>
								<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, '<?php echo esc_attr($levelid);?>', '<?php echo esc_attr('#levelcond');?>');" /> <span class="ihc-checkbox-align"  class="<?php echo esc_attr($spanclass);?>"><?php echo esc_attr($larr['label']);?></span>
							</div>
						<?php endforeach;?>
						<input type="hidden" name="ihc_level_subscription_plan_settings_condt" id="<?php echo esc_attr('levelcond');?>" value="<?php echo esc_attr($hidden_value);?>" />
					</div>

					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_level" class="button button-primary button-large" />
					</div>
				</div>
			</div>
	<?php endif;?>
	<!-- END Membership - Subscription Plan Display Details -->

	<!-- START WooCommerce Payment Inttegration -->
	<?php if (ihc_is_magic_feat_active('woo_payment') && $lid!=-1 ):?>
			<div class="ihc-stuffbox ihc-stuffbox-magic-feat">
					<h3 class="ihc-h3"><?php esc_html_e('WooCommerce Product - Membership Relation', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line">
						<?php
							$product_id = Ihc_Db::get_woo_product_id_for_lid($lid);
							if ($product_id):
								$product_name = get_the_title($product_id);
						?>
							<div id="iump_current_product_level"><?php esc_html_e('Current Product assign: ', 'ihc');?><a href="<?php echo admin_url('post.php?post=' . $product_id . '&action=edit');?>" target="_blank"><?php echo esc_html($product_name);?></a>
								<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-levels-delete-product-assign"></i>
							</div>
						<?php
							$display_hidden = 'ihc-display-none';
							else :
								$display_hidden = ' ihc-display-block';
							endif;
						?>
						<div id="iump_change_level_product_relation" class="<?php echo esc_attr($display_hidden);?>">
							<span><?php esc_html_e('Products', 'ihc');?></span>
							<input type="text"  class="form-control" value="" name="" id="reference_search" />
							<input type="hidden" name="new_woo_product" value=""/>
						</div>
					</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_level" class="button button-primary button-large" />
					</div>
				</div>
			</div>
	<?php endif;?>
	<!-- END WooCommerce Payment Inttegration -->

	<!-- HOOK some extra html here --->
			<?php do_action('ihc_level_admin_html', $level_data, $lid );?>
	<!-- /HOOK some extra html here --->

	<!-- BADGES -->
	<?php if (ihc_is_magic_feat_active('badges')):?>
			<div class="ihc-stuffbox ihc-stuffbox-magic-feat">
				<h3 class="ihc-h3"><?php esc_html_e('Membership Badges', 'ihc');?> <span  class="ihc-extension-label">(<?php esc_html_e('Magic Feature', 'ihc');?>)</span></h3>
				<div class="inside">
					<div class="iump-form-line">
						<?php if (empty($level_data['badge_image_url'])) $level_data['badge_image_url'] = '';?>
						<input type="text" class="form-control ihc-badge-imag" onclick="openMediaUp(this);" value="<?php echo esc_attr($level_data['badge_image_url']);?>" name="badge_image_url" id="badge_image_url" >
						<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-badge-image-do-delete" title="<?php esc_html_e('Remove Badge', 'ihc');?>"></i>
					</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_level" class="button button-primary button-large" />
					</div>
				</div>
			</div>
	<?php endif;?>
	<!-- BADGES -->

</form>

		<?php
	} else {
		//manage
		?>

		<!-- div class="iump-page-title">Ultimate Membership Pro -
			<span class="second-text">
				<?php esc_html_e('Membership Plans', 'ihc');?>
			</span>
		</div-->
		<div class="iump-page-headline">
			<?php esc_html_e('Manage Membership Plans', 'ihc');?>
		</div>
		<div class="imup-page-top-options">
			<a href="<?php echo esc_attr($url).'&tab=levels&new_level=true';?>" class="indeed-add-new-like-wp">
				<i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Membership', 'ihc');?>
			</a>
			<span class="ihc-top-message"><?php esc_html_e('...free/paid Memberships with single payments or recurring Subscriptions!', 'ihc');?></span>
		</div>
	<form class="ihc-memberships-lists-wrapper" action="" method="post" data-delete_many_levels="<?php esc_html_e( 'Are You sure You wish to remove the selected memberships?', 'ihc' );?>" >
		<?php
				if ( isset( $results['success'] ) && $results['success'] === false ){
					?>
							<div class="ihc-warning-box"><?php echo esc_attr($results['reason']);?></div>
					<?php
				} else if ( isset( $results['success'] ) && $results['success'] === true ){
						?>
							<div class="ihc-success-box"><?php echo esc_attr($results['reason']);?></div>
						<?php
				}
				?>
			<div>
				<?php
					$levels = \Indeed\Ihc\Db\Memberships::getAll();
					$levels = ihc_reorder_arr($levels);
					$currency = get_option('ihc_currency');
					$woo_payment = ihc_is_magic_feat_active('woo_payment');
					if (!$currency) $currency = '';
					if($levels && count($levels)){
						$memberships_counts = \Indeed\Ihc\UserSubscriptions::getCountsMembersPerSubscription();
						?>
							<div onclick="ihcDatatableSortableOnOff(this, '#iump-dashboard-table tbody');" class="ihc-sortable-off" id="ihc-bttn-on-off-sortable">
								<?php esc_html_e('Arrange Memberships', 'ihc');?>
							</div>
							<div id="ihc-reorder-msg" class="ihc-display-none"> <?php esc_html_e('Move rows to reorder Memberships. Once you finish save changes.', 'ihc');?></div>

							<?php
								/*
							  if ( isset( $_POST['iump-datatable-submit'] ) && isset( $_POST['iump-action'] ) && $_POST['iump-action'] === 'remove' ){
							      // action is fired... do something
										$targets = indeed_sanitize_array( $_POST['memberhips'] );
										if ( count($targets) ){
												foreach ( $targets as $lid ){
														// delete each membership
														\Indeed\Ihc\Db\Memberships::deleteOne( $lid );
												}
										}
							  }
								*/
							  $tableDataType = 'memberships';
							  $columns = [
									[
												'data' 				=> 'id_checkbox',
								        'orderable'   => false,
												'sortable'		=> false,
									],
							    [
							        'data'        => 'the_order',
							        'render'      => [
							                  'display' => 'display',
							                  'sort'    => 'value',
							        ],
											'sortable'	=> false,
							        'orderable' => false,
							    ],
							    [
							        'data'        => 'id',
							        'render'      => [
							                  'display' => 'display',
							                  'sort'    => 'value',
							        ]
							    ],
							    [
							        'data'      => 'name',
											'render'      => [
																'display' => 'display',
																'sort'    => 'value',
											]
							    ],
							    [
							        'data'      => 'label',
							    ],
							    [
							        'data'      => 'woo_product',
							        'orderable' => false
							    ],
							    [
							        'data'      => 'type',
							        'orderable' => false
							    ],
							    [
							        'data'      => 'price',
							    ],
							    [
							        'data'      => 'users_per_membership',
											'render'      => [
																'display' => 'display',
																'sort'    => 'value',
											]
							    ],
							    [
							        'data'      => 'purchase_link_shortcode',
							        'orderable' => false
							    ],
							    [
							        'data'      => 'restriction_shortcode',
							        'orderable' => false
							    ],
							  ];
							  $wooIsActive = ihc_is_magic_feat_active('woo_payment');
							  if ( !$wooIsActive ){
							      unset( $columns[5] );
										$columns = array_values( $columns );
							  }

							  $labels = [
							      'search'				=> esc_html__( "Search&nbsp;:", 'ihc'),
							      'lengthMenu'		=> esc_html__( "Show _MENU_ entries", 'ihc'),
							      'info'					=> esc_html__( "Showing _START_ to _END_ of _TOTAL_ entries", 'ihc'),
							      'infoEmpty'			=> esc_html__( "No results available", 'ihc'),
							      'infoFiltered'	=> esc_html__( "", 'ihc'),
							      'loadingRecords'=> esc_html__( "Loading", 'ihc'),
							      'zeroRecords'		=> esc_html__( "No results available", 'ihc'),
							      'emptyTable'		=> esc_html__( "No results available", 'ihc'),
							      'paginate'			=> [
							            'first'					=> esc_html__( "First", 'ihc'),
							            'previous'			=> esc_html__( "Previous", 'ihc'),
							            'next'					=> esc_html__( "Next", 'ihc'),
							            'last'					=> esc_html__( "Last", 'ihc'),
							      ],
							      'aria'					=> [
							            'sortAscending'		=> esc_html__( "Ascending", 'ihc'),
							            'sortDescending'	=> esc_html__( "Descending", 'ihc'),
							      ],
							      'searchPlaceholder'			=> esc_html__( "Search", 'ihc'),
							      'show_hide_cols_label'	=> esc_html__( "Show / Hide columns", 'ihc'),
							  ];

							  // css
							  wp_enqueue_style( 'ihcdatabse', IHC_URL . 'admin/assets/css/datatables/datatables.min.css');
							  wp_enqueue_style( 'ihcdatabse-buttons', IHC_URL . 'admin/assets/css/datatables/buttons.dataTables.min.css');

							  // js
							  wp_enqueue_script( 'ihcdatabse', IHC_URL . 'admin/assets/js/datatables/datatables.min.js', ['jquery'], '12.7' );
							  wp_enqueue_script( 'ihcdatabse-buttons', IHC_URL . 'admin/assets/js/datatables/dataTables.buttons.min.js', ['jquery'], '12.7' );
							  wp_enqueue_script( 'ihcdatabse-colvis', IHC_URL . 'admin/assets/js/datatables/buttons.colVis.min.js', ['jquery'], '12.7' );
							  wp_enqueue_script( 'ihcdatabsescrolltop', IHC_URL . 'admin/assets/js/datatables/dataTables.scrollToTop.min.js', ['jquery'], '12.7' );
							  // iump datatable functions
							  wp_register_script( 'ihc-table', IHC_URL . 'admin/assets/js/table.js', ['jquery'], '12.7' );
							  // setting up the variables
							  global $wp_version;
							  if ( version_compare ( $wp_version , '5.7', '>=' ) ){
							      wp_add_inline_script( 'ihc-table', "var iump_datatable_cols='" . json_encode( $columns ) . "';" );
							      wp_add_inline_script( 'ihc-table', "var iump_datatable_labels='" . json_encode( $labels ) . "';" );
							      wp_add_inline_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
							  } else {
							      wp_localize_script( 'ihc-table', 'iump_datatable_cols', json_encode( $columns ) );
							      wp_localize_script( 'ihc-table', 'iump_datatable_labels', json_encode( $labels ) );
							      wp_add_inline_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
							  }
							  wp_enqueue_script( 'ihc-table' );

							  ?>
								<!-- Page State -->
								<?php $pageState = get_option( 'ihc_datatable_state_for-memberships', false );?>
								<?php if ( $pageState !== false ):?>
									<div class="iump-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>'></div>
								<?php endif;?>
								<!-- End of Page State -->

								<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none" >
												<thead>
														<tr>
							                  <th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
																<th class="iump-memberships-table-show-col-width iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Show', 'ihc');?></th>
																<th class="iump-memberships-table-id-col-width iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('ID', 'ihc');?></th>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Slug', 'ihc');?></th>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Name', 'ihc');?></th>
																<?php if ( $wooIsActive ):?>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Woocommerce Product', 'ihc');?></th>
																<?php endif;?>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Membership Type', 'ihc');?></th>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Price', 'ihc');?></th>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Members', 'ihc');?></th>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Membership direct Purchase Link', 'ihc');?></th>
																<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Direct Restriction Shortcode', 'ihc');?></th>
														</tr>
												</thead>
										</table>
										<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
												<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
														<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
														<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
												</select>
												<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-memberships-apply-bttn" />
										</div>
						<?php
					}else{
						?>

						<div class="ihc-warning-message"> <?php esc_html_e('No Memberships available! Please create your first Membership plan.', 'ihc');?></div>
						<?php
					}
				?>
			</div>
	</form>

		<?php
		$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
		$ol_dL_ogs = new $class();
		if ( (int)$ol_dL_ogs->FGCS() === 2){
				echo '<div class="ihc'.'-err' .'or-'.'glo'.'bal'.'-dash'.'board'.'-message">This'.' tri'.'al '.'ver'.'sion'.' of'.' the'.' plu'.'gin'.' all'.'ows'.' ac'.'ces'.'s '.'to'.' on'.'ly'. "<strong>" . ' 3'.' members'.'hi'.'ps' . "</strong>" . '.'.' T'.'o'.' un'.'lo'.'ck '.'fu'.'ll'.' function'.'ality'.' and'.' en'.'joy'.' un'.'limited'.' access'.', '.'cons'.'ider'.' activ'.'ating '.'the ' . '<a href="' . admin_url( 'admin.php?page=ihc_manage&tab=help' ). '">' . 'lice'.'nse' . '</a>' . '</div>';
		}

	}
?>

</div>
</div>
