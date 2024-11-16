<div class="uap-wrapper">

<div class="uap-inside-notification">
<?php esc_html_e('This module has a high level of complexity and requires proper knowledge about Ultimate Affiliate Pro system and may restrict registration process for new affiliate users. It is recommended only for advanced users.', 'uap');?>
</div>
		<form  method="post">
			<div class="uap-stuffbox">
				<h3 class="uap-h3"><?php esc_html_e('Pay to become Affiliate', 'uap');?></h3>
				<div class="inside">

					<div class="uap-form-line">
						<h2><?php esc_html_e('Activate/Hold Pay to become affiliate', 'uap');?></h2>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_pay_to_become_affiliate_enabled']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_pay_to_become_affiliate_enabled');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_pay_to_become_affiliate_enabled" value="<?php echo esc_attr($data['metas']['uap_pay_to_become_affiliate_enabled']);?>" id="uap_pay_to_become_affiliate_enabled" />
                        <p><?php esc_html_e('Once this module is enabled new registered users will not be set as Affiliates until a specific purchase is confirmed. Based on Module settings, the system will check for current user if any complete Order with targeted products exist. ', 'uap');?></p>
                        <p><?php esc_html_e('For "Become an Affiliate" button dedicated for logged users the same restriction will apply. That button will not show up at all until current user will not complete the required payment.', 'uap');?></p>
					</div>

					<div class="uap-form-line">
							<h2><?php esc_html_e('Targeting', 'uap');?></h2>
							<p><?php esc_html_e('Based source of products', 'uap');?></p>
							<h4 ><?php esc_html_e('Source', 'uap');?></h4>
							<?php
							$services = uap_get_active_services();
							$possibleServicesAllowed = array( 'ump', 'woo' );

							//$ajaxURL = UAP_URL . 'admin/uap-offers-ajax-autocomplete.php?uapAdminAjaxNonce=' . wp_create_nonce( 'uapAdminAjaxNonce' ) . '&source=';
							$ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&uapAdminAjaxNonce=' . wp_create_nonce( 'uapAdminAjaxNonce' ) . '&source=';
							?>
							<select <?php echo (!$services) ? 'disabled' : '';?> name="uap_pay_to_become_affiliate_target_product_group"
									id="the_source"  class="form-control m-bot15 uap-js-pay-to-become-aff-select-target"
									data-url="<?php echo esc_url($ajaxURL);?>"
							><?php
								if ( $services ):
									foreach ($services as $k=>$v){
										if ( !in_array($k, $possibleServicesAllowed) ){
											 continue;
										}
										$selected = ($data['metas']['uap_pay_to_become_affiliate_target_product_group']==$k) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
										<?php
									}
								endif;
							?></select>
							<div>
									<h6><?php esc_html_e( 'All products', 'uap' );?></h6>
									<label class="uap_label_shiwtch uap-switch-button-margin">
										<?php $checked = ($data['metas']['uap_pay_to_become_affiliate_target_all_products']) ? 'checked' : '';?>
										<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_pay_to_become_affiliate_target_all_products');uap_make_disable_if_checked(this, '#reference_search');uap_reset_autocomplete_fields();" <?php echo esc_attr($checked);?> />
										<div class="switch uap-display-inline"></div>
									</label>
									<input type="hidden" name="uap_pay_to_become_affiliate_target_all_products" value="<?php echo esc_attr($data['metas']['uap_pay_to_become_affiliate_target_all_products']);?>" id="uap_pay_to_become_affiliate_target_all_products" />
							</div>

				<div class="row">
					<div class="col-xs-4">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Products', 'uap');?></span>
								<input type="text" <?php echo ($data['metas']['uap_pay_to_become_affiliate_target_all_products']) ? 'disabled' : '';?> class="form-control" value="" name="reference_search" id="reference_search" />
							</div>
								<?php $value = (is_array($data['metas']['products'])) ? implode(',', $data['metas']['products']) : $data['metas']['products'];?>
								<input type="hidden" value="<?php echo esc_attr($data['metas']['uap_pay_to_become_affiliate_target_products']);?>" name="uap_pay_to_become_affiliate_target_products" id="uap_pay_to_become_affiliate_target_products" />
								<div id="uap_reference_search_tags"><?php
									if (!empty($data['metas']['products'])){
										foreach ($data['metas']['products'] as $value){
											if ($value && !empty($data['products']['label'][$value])){
											$id = 'uap_reference_tag_' . $value;
											?>
											<div id="<?php echo esc_attr($id);?>" class="uap-tag-item"><?php echo esc_html($data['products']['label'][$value]);?><div class="uap-remove-tag" onclick="uapRemoveTag('<?php echo esc_attr($value);?>', '#<?php echo esc_attr($id);?>', '#uap_pay_to_become_affiliate_target_products');" title="Removing tag">x</div></div>
											<?php
											}
										}
									}
								?>
							</div>
						</div>
					</div>
					</div>


					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>

				</div>
			</div>
		</form>
</div>
