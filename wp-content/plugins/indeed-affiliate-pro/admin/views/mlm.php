<div class="uap-wrapper">
<div class="uap-inside-notification">
<?php esc_html_e('This module has a high level of complexity and requires proper knowledge about Ultimate Affiliate Pro system and Multi Level Marketing concept. It is recommended only for advanced users.', 'uap');?>
</div>

			<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Multi-Level Marketing', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/mlm-workflow-example/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-7">
							<h2><?php esc_html_e('Activate/Hold Multi-Level Marketing', 'uap');?></h2>
							<p><?php esc_html_e('You can activate this option to take in place into your Affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_mlm_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_mlm_enable');" <?php echo esc_attr( $checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_mlm_enable" value="<?php echo esc_attr($data['metas']['uap_mlm_enable']);?>" id="uap_mlm_enable" />
						</div>
						</div>

						<div class="row">
							<div class="col-xs-5">
								<h2><?php esc_html_e('MLM Matrix', 'uap');?></h2>
								<p><?php esc_html_e('There are multiple ways the MLM system may work.', 'uap');?></p>
								<select name="uap_mlm_matrix_type" onChange="uapMatrixTypeCondition(this.value);"  class="form-control m-bot15"><?php
								foreach ($data['matrix_types'] as $k=>$v):
									$selected = ($data['metas']['uap_mlm_matrix_type']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
									<?php
								endforeach;
							?></select>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-5">
								<h2><?php esc_html_e('Matrix Options', 'uap');?></h2>
								<?php $display = ($data['metas']['uap_mlm_matrix_type']=='unilevel') ? 'uap-display-none' : '';?>
								<div class="input-group <?php echo esc_attr($display);?>" id="children_limit_div">
									<span class="input-group-addon" ><?php esc_html_e('Children Limit', 'uap');?></span>
									<input type="number" class="form-control" id="uap_mlm_child_limit" name="uap_mlm_child_limit" value="<?php echo esc_attr($data['metas']['uap_mlm_child_limit']);?>" min="1" />
								</div>
								<div class="input-group input-group-extra-margin">
									<span class="input-group-addon" ><?php esc_html_e('Matrix Depth(Levels)', 'uap');?></span>
									<input type="number" class="form-control" name="uap_mlm_matrix_depth" onChange="uapMlmUpdateTbl(this.value);" value="<?php echo esc_attr($data['metas']['uap_mlm_matrix_depth']);?>" min="1"/>
								</div>
							</div>
						</div>
						<div class="uap-line-break uap-display-none" ></div>
						<?php if ( is_plugin_active( 'woocommerce/woocommerce.php') && !file_exists( WP_CONTENT_DIR . '/plugins/uap-mlm-based-on-price-product/uap-mlm-based-on-price-product.php' ) ){ ?>
							<div class="uap-top-message-new-extension"><?php esc_html_e('Extend  ','uap');?> <strong><?php esc_html_e('MLM functionality','uap');?></strong> <?php esc_html_e(' and workflow for WooCommerce by changing MLM calculation method and encourage affiliates to sell more. Check the external addon ', 'uap');?><a href="https://ultimateaffiliate.pro/addon-link/mlm-based-on-product-price/" target="_blank">here</a></div>
						<?php }?>
						<?php
								global $indeed_db;
								$isActive = $indeed_db->is_magic_feat_enable( 'uap_mlmpp' ) ? true : false;
						?>
						<div class="row <?php echo ( !$isActive ) ? 'uap-display-none' : '';?> " >
							<div class="col-xs-10">
								<h2><?php esc_html_e('MLM Workflow Concept', 'uap');?></h2>
                <p><?php esc_html_e('By default, a MLM system rewards the uplines affiliates based on the main affiliate referral amount, no matter the source of the Referral. An additional workflow is available and let you relate the MLM rewards directly to Product price/Order amount when the main Referral is based on a purchased from integrated Systems (WooCommerce, Ultimate Membership Pro, EDD)', 'uap');?></p>
								<div class="uap-form-line col-xs-6">
									<select name="uap_mlm_use_amount_from" id="uap_mlm_use_amount_from"  class="form-control m-bot15"><?php
										$types = array(
														'child_referral' => esc_html__('Child Referral amount', 'uap'),
														'product_price' => esc_html__('Product Price', 'uap'),
										);
										foreach ($types as $k=>$v):
											$selected = ($data['metas']['uap_mlm_use_amount_from']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
											<?php
										endforeach;
									?></select>
								</div>
							</div>
						</div>


						<div class="row">
							<div class="col-xs-5">
								<h2><?php esc_html_e('Default Amount', 'uap');?></h2>
								<p><?php esc_html_e('Set the default amount that will be used when no special amount is set for a certain rank.', 'uap');?></p>
								<div class="uap-form-line">
									<div class="form-group">
									<input type="number" class="form-control" step='<?php echo uapInputNumerStep();?>' id="uap_mlm_default_amount_value" name="uap_mlm_default_amount_value" value="<?php echo esc_attr($data['metas']['uap_mlm_default_amount_value']);?>" min="0.01"/>
									</div>
									<select name="uap_mlm_default_amount_type" id="uap_mlm_default_amount_type"><?php
										foreach ($data['amount_types'] as $k=>$v):
											$selected = ($data['metas']['uap_mlm_default_amount_type']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
											<?php
										endforeach;
									?></select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-8">

							<h2><?php esc_html_e('Amount For Each Level', 'uap');?></h2>
							<p><?php esc_html_e('Set a special MLM amount for each level of your ranks. This option will also become available in the "Rank Settings" page.', 'uap');?></p>

						<table class="uap-dashboard-inside-table" id="mlm-amount-for-each-level">
							<thead>
								<tr>
								<th><?php esc_html_e('#', 'uap');?></th>
								<th><?php esc_html_e('Value', 'uap');?></th>
							</tr>
							</thead>
							<?php
								for ($i=1; $i<=$data['metas']['uap_mlm_matrix_depth']; $i++):
									?>
									<tr data-tr="<?php echo esc_attr($i);?>" id="uap_mlm_level_<?php echo esc_attr($i);?>">
										<td><?php echo esc_html__('Level', 'uap') . ' ' . $i;?></td>
										<td>
											<input type="number" step='<?php echo uapInputNumerStep();?>' min="0" class="uap-input-number" value="<?php echo isset($data['metas']['mlm_amount_value_per_level'][$i]) ? $data['metas']['mlm_amount_value_per_level'][$i] : '';?>" name="<?php echo esc_attr("mlm_amount_value_per_level[$i]");?>" />
											<select name="<?php echo esc_uap_content("mlm_amount_type_per_level[$i]");?>"><?php
												foreach ($data['amount_types'] as $k=>$v):
													$selected = (!empty($data['metas']['mlm_amount_type_per_level'][$i]) && $data['metas']['mlm_amount_type_per_level'][$i]==$k) ? 'selected' : '';
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
				</div>
			</form>


						<!-- for js -->
						<table id="uap_mlm_model" class="uap-display-none">
							<tr data-tr="{{i}}" id="uap_mlm_level_{{i}}">
								<td><?php echo esc_html__('Level', 'uap') . ' {{i}}';?></td>
								<td>
									<input type="number" step='<?php echo uapInputNumerStep();?>' min="0" class="uap-input-number" value="" name="mlm_amount_value_per_level[{{i}}]" />
									<select name="<?php echo esc_attr("mlm_amount_type_per_level[{{i}}]");?>"><?php
									foreach ($data['amount_types'] as $k=>$v):
										?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
									endforeach;
									?></select>
								</td>
							</tr>
						</table>
						<!-- /for js -->

		<div class="uap-stuffbox">
			<h3 class="uap-h3"><?php esc_html_e('Search MLM Relations', 'uap');?></h3>
			<div class="inside">
				<form action="<?php echo esc_url($data['search_submit_url']);?>" method="post">
					<div>
						<?php esc_html_e('Affiliate Username', 'uap');?> <input type="text" id="affiliate_name" name="affiliate_name"/>
						<input type="hidden" id="affiliate_id_hidden" name="search_aff_id" value=""/>
						<input type="submit" value="<?php esc_html_e('Search', 'uap');?>" name="search" class="button button-primary button-large" />
					</div>
				</form>
			</div>
		</div>

        <div class="uap-stuffbox">
			<h3 class="uap-h3"><?php esc_html_e('How it works?', 'uap');?></h3>
			<div class="inside">
            <p>For a better understanding of how the MLM system works and provides rewards to affiliates, a common&nbsp;scenario is listed below:</p>
			<h5><span id="mlm-settings"><strong>MLM Settings</strong></span></h5>
			<p><strong>Matrix Depth(Levels)</strong> : <span><strong>3</strong></span></p>
			<p><strong>Amount For Each Level</strong></p>
			<ul>
			<li>Level 1: 20%</li>
			<li>Level 2: 10%</li>
			<li>Level 3: 5%</li>
			</ul>
			<h5><span id="john-mlm-matrix-sub-affiliates"><span><strong>John</strong></span> MLM Matrix sub-affiliates:</span></h5>
			<ul>
			<li><span><strong>Geo</strong></span>&nbsp;– Level 1</li>
			<li><span><strong>Bob</strong>&nbsp;–&nbsp;</span>Level 2</li>
			<li><span><strong>Max</strong>&nbsp;–&nbsp;</span>Level 3</li>
			</ul>
			<h5><span id="actions">Actions:</span></h5>
			<p><strong>Max</strong> has Rank “Premium” with Amount: <strong>%25</strong><br>
			<strong>Max</strong> refers a user that purchases a product of: <strong>$400</strong><br>
			Rank Reward:<br>
			<strong>Max</strong> gets a referral of: <strong>$100</strong></p>
			<h5><span id="mlm-rewards">MLM Rewards:</span></h5>
			<ul>
			<li>Bob gets: $20 (20% of $100)</li>
			<li>Geo gets: $10 (10% of $100)</li>
			<li>John gets: $5 (5% of $100)</li>
			</ul>
            </div>
       	</div>
</div>
