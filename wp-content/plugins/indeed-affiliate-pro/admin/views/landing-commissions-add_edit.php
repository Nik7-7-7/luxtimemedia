<div class="uap-wrapper">
	<div class="uap-stuffbox">
	<form action="<?php echo esc_url($data['url-manage']);?>" method="post">

		<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<h3 class="uap-h3"><?php esc_html_e( 'Landing Commission (CPA)', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/landing-commissions/" target="_blank"><?php esc_html_e( 'Need Help?', 'uap');?></a></span></h3>
	<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-8">
				<h2><?php esc_html_e( 'Activate/Hold Landing Commission(CPA) trigger', 'uap');?></h2>
					<p><?php esc_html_e( 'You can turn on or off a Landing Commission trigger without having to remove it', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['status']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#the_status');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="status" value="<?php echo esc_attr($data['metas']['status']);?>" id="the_status" />
				</div>
			</div>
		</div>
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon"><?php esc_html_e( 'Slug', 'uap');?></span>
						<input type="text" class="form-control" placeholder="<?php esc_html_e( 'unique slug', 'uap');?>"  value="<?php echo esc_attr($data['metas']['slug']);?>" name="slug" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<p><i><?php esc_html_e( 'Make sure your unique slug is built solely on lowercase characters, without any extra spaces or symbols.', 'uap');?></i></p>
				</div>
			</div>
		</div>
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-8">
					<h2><?php echo esc_html__('Commission Price', 'uap');?></h2>
					<p><?php esc_html_e( 'The referral amount will be determined based on each affiliate rank amount and the Landing Commission Price.', 'uap');?></p>
					 <div class="input-group uap-lc-input-number">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e( 'Value', 'uap');?></span>
						<input type="number" step='<?php echo uapInputNumerStep();?>' min="0"   class="form-control" name="amount_value" value="<?php echo esc_attr($data['metas']['amount_value']);?>" aria-describedby="basic-addon1" />
						<div class="input-group-addon"><?php echo esc_html($currency);?></div>
					 </div>
				 		<br/>
					 		<h4><?php echo esc_html__('Extra Dynamic Workflow', 'uap');?></h4>
					 		<p><strong><?php esc_html_e( 'If the "lc_amount" variable is supplied where the Landing Commission shortcode is set, you can retrieve a Dynamic Commission Price Value via GET or POST.', 'uap');?></strong></p>
				 		<br/>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-8">
					<h2><?php esc_html_e( 'Referral Details', 'uap');?></h2>
					<p><?php esc_html_e( 'Change the options for how the Affiliate program creates and stores Landing Commission Referrals', 'uap');?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<h4><?php esc_html_e( 'Default Status', 'uap');?></h4>
						<select name="default_referral_status" class="form-control m-bot15 uap-select"><?php
							foreach (array(1=>esc_html__('Pending', 'uap'), 2=>esc_html__('Approved', 'uap')) as $k=>$v):
								$selected = ($data['metas']['default_referral_status']==$k) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
								<?php
							endforeach;
						?></select>
				</div>
			</div>
		</div>
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-4">
					<h4><?php esc_html_e( 'Source', 'uap');?></h4>
					<div class="input-group">
						<span class="input-group-addon"><?php esc_html_e( 'Source Name', 'uap');?></span>
						<input type="text" class="form-control" placeholder=""  value="<?php echo esc_attr($data['metas']['source']);?>" name="source" />
					</div>
				</div>
			</div>
		</div>
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-4">
					<div class="form-group">
						<h4><?php esc_html_e( 'Description', 'uap');?></h4>
						<textarea class="form-control text-area" name="description"><?php echo esc_uap_content($data['metas']['description']);?></textarea>
					</div>
				</div>
			</div>
		</div>
	<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php echo esc_html__('Cookie Expiration Duration', 'uap');?></h2>
					<p><?php esc_html_e( 'Control how long cookies stay active on your website for enhanced user experiences', 'uap');?></p>
					<div class="input-group uap-lc-input-number">
						<?php if (!isset($data['metas']['cookie_expire'])){
							$data['metas']['cookie_expire'] = 0;
						}?>
						<input type="number" min="0" step="1" class="form-control" name="cookie_expire" value="<?php echo esc_attr($data['metas']['cookie_expire']);?>" aria-describedby="basic-addon1" />
						<div class="input-group-addon"><?php esc_html_e( 'Hours', 'uap');?></div>
					 </div>
				</div>
			</div>
		</div>
		 <!--Deprecated starting with v.8.5 -->
		<!--div class="uap-form-line">
					<div class="row">
						<div class="col-xs-4">
							<h4><?php esc_html_e( 'Color', 'uap');?></h4>
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
				</div-->

				<div class="uap-form-line">
					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e( 'Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
					</div>
				</div>
				</div>

				<input type="hidden" name="id" value="<?php echo esc_attr($data['metas']['id']);?>" />

			</form>
		</div>

</div>
