<div class="uap-wrapper">
	<div class="uap-stuffbox">
	<form action="<?php echo esc_url($data['url-manage']);?>" method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />
	<h3 class="uap-h3"><?php esc_html_e('Manage Product Rates', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/offers/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>

	<div class="inside">
			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-6">
					<h2><?php esc_html_e('Activate/Hold current Product Rate', 'uap');?></h2>
						<p><?php esc_html_e('The product rate can be turned on or off without having to be removed', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['status']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#offer_status');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="status" value="<?php echo esc_attr($data['metas']['status']);?>" id="offer_status" />
					</div>
					</div>
				</div>

				 <div class="uap-form-line">
					<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
								<span class="input-group-addon" ><?php esc_html_e('Name', 'uap');?></span>
								<input type="text" class="form-control" placeholder="special offer"  value="<?php echo esc_attr($data['metas']['name']);?>" name="name" />
							</div>
						</div>
					</div>
				</div>


			<div class="uap-form-line">
					<h2><?php esc_html_e('Custom Rate', 'uap');?></h2>
						<p><?php esc_html_e('To replace the usual Rate Rank, a unique amount rate must be established for this Custom Product Rate.', 'uap');?></p>
							<div class="row">
								<div class="col-xs-4">

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
					 <div class="row">
					 		<div class="col-xs-4">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Rate', 'uap');?></span>
								<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="form-control" name="amount_value" value="<?php echo esc_attr($data['metas']['amount_value']);?>" aria-describedby="basic-addon1">
							</div>
						</div>
							</div>
							<?php
							$offerType = get_option( 'uap_referral_offer_type' );
							if ( $offerType == 'biggest' ){
									$offerType = esc_html__( 'Biggest', 'uap' );
							} else {
									$offerType = esc_html__( 'Lowest', 'uap' );
							}
							echo esc_html__( 'You can choose which rate is used if more than one is set for the same activity (e.g., Ranks, Custom Product Rates, Product or Category rate), the ', 'uap' ) . '<strong>' . $offerType . '</strong> ' . esc_html__( 'rate being used. You can adjust this from', 'uap' ) . ' <a href="' . admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=settings' ) . '" target="_blank">' . esc_html__( 'here.', 'uap' ) . '</a>';
							?>
				</div>

				<div class="uap-form-line">
					<h4><?php esc_html_e('Date Range', 'uap');?></h4>
					<p><?php esc_html_e('Depending on your selling strategy, the Product Rate will be active for a specific amount of time.', 'uap');?></p>
					<div class="row">
						<div class="col-xs-6">
							<input type="text" placeholder="From - yyyy-mm-dd" id="start_date" name="start_date" value="<?php echo esc_attr($data['metas']['start_date']);?>" class="uap-datepick" />
							 -
							<input type="text" placeholder="To - yyyy-mm-dd" id="end_date" name="end_date" value="<?php echo esc_attr($data['metas']['end_date']);?>" class="uap-datepick" />
					</div>
				</div>
			</div>
				<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-6">
							<h2><?php esc_html_e('Targeting Settings', 'uap');?></h2>
							<p><?php esc_html_e('The Product Rate will be accessible exclusively to specified affiliates and based on particular Products.', 'uap');?></p>
						</div>
					</div>
				</div>
					<div class="uap-form-line">
							<div class="row">
								<div class="col-xs-6">
								<h4><?php esc_html_e('Particular Products', 'uap');?></h4>
									<p><?php esc_html_e('Choose the source and certain products to assign this custom Rates to all Products.', 'uap');?></p>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-6">
							<?php
									//$ajaxURL = UAP_URL . 'admin/uap-offers-ajax-autocomplete.php?&uapAdminAjaxNonce=' . wp_create_nonce( 'uapAdminAjaxNonce' ) . '&source=';
									$ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&uapAdminAjaxNonce=' . wp_create_nonce( 'uapAdminAjaxNonce' ) . '&source=';
							?>
							<select name="source" id="the_source"  class="form-control m-bot15 uap-js-offers-add-edit-select-source"
								data-target_url="<?php echo esc_url($ajaxURL);?>"
								><?php
								$values = uap_get_active_services();
								if ($values):
									foreach ($values as $k=>$v){
										$selected = ($data['metas']['source']==$k) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
										<?php
									}
								endif;
							?></select>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6">
							<div class="input-group">
								<span class="input-group-addon js-uap-product-label" >
											<?php if ( $data['metas']['source'] == 'woo' ):?>
													<?php esc_html_e( 'Products/Categories', 'uap' );?>
											<?php else :?>
													<?php esc_html_e('Products', 'uap');?>
											<?php endif;?>
											</span>
								<input type="text"  class="form-control" value="" name="reference_search" id="reference_search" />
							</div>
								<?php $value = (is_array($data['metas']['products'])) ? implode(',', $data['metas']['products']) : $data['metas']['products'];?>
								<input type="hidden" value="<?php echo esc_attr($value);?>" name="products" id="reference_search_hidden" />
								<div id="uap_reference_search_tags"><?php
									if (!empty($data['metas']['products'])){
										foreach ($data['metas']['products'] as $value){
											if ($value){
											$id = 'uap_reference_tag_' . $value;
											?>
											<div id="<?php echo esc_attr($id);?>" class="uap-tag-item"><?php echo esc_html($data['products']['label'][$value]);?><div class="uap-remove-tag" onclick="uapRemoveTag('<?php echo esc_attr($value);?>', '#<?php echo esc_attr($id);?>', '#reference_search_hidden');" title="<?php esc_html_e( 'Removing tag', 'uap' );?>">x</div></div>
											<?php
											}
										}
									}
								?></div>

								<p><?php esc_html_e('If You do not specify any product, it will consider all of them from that source.', 'uap');?></p>
						</div>
						</div>
					</div>
					<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-6">
							<h4 ><?php esc_html_e('Specific Affiliates', 'uap');?></h4>
							<p><?php esc_html_e('Choose certain affiliates or type "All" to provide this Custom Rate for all of your affiliate users.', 'uap');?></p>
							<div class="input-group">
								<span class="input-group-addon" ><?php esc_html_e('Username', 'uap');?></span>
								<input type="text"  class="form-control" id="usernames_search" />
							</div>
								<?php $value = (is_array($data['metas']['affiliates'])) ? implode(',', $data['metas']['affiliates']) : $data['metas']['affiliates'];?>
								<input type="hidden" value="<?php echo esc_attr($value);?>" name="affiliates" id="usernames_search_hidden" />
								<div id="uap_username_search_tags"><?php
									if (!empty($data['metas']['affiliates'])){
										foreach ($data['metas']['affiliates'] as $value){
											if ($value){
											$id = 'uap_username_tag_' . $value;
											?>
											<div id="<?php echo esc_attr($id);?>" class="uap-tag-item"><?php echo esc_html($data['affiliates']['username'][$value]);?><div class="uap-remove-tag" onclick="uapRemoveTag('<?php echo esc_attr($value);?>', '#<?php echo esc_attr($id);?>', '#usernames_search_hidden');" title="<?php esc_html_e('Removing tag', 'uap');?>">x</div></div>
											<?php
											}
										}
									}
								?></div>

					</div>
					</div>
			</div>
			 <!--Deprecated starting with v.8.5 -->
				<!--div class="uap-inside-item">
					<div class="row">
						<div class="col-xs-4">
							<div class="uap-form-line">
							<h4><?php esc_html_e('Offer Color', 'uap');?></h4>
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
                                            <li class="uap-color-scheme-item <?php echo esc_attr($class);?> uap-box-background-<?php echo esc_attr($color);?>" onClick="uapChageColor(this, '<?php echo esc_attr($color);?>', '#uap_color');"></li>
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
					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
					</div>
			</div>
		</div>

				<input type="hidden" name="id" value="<?php echo esc_attr($data['metas']['id']);?>" />

			</form>

			</div>
		</div>

<div class="uap-js-offers-add-edit-labels" data-products_and_cats="<?php esc_html_e( 'Products/Categories', 'uap' );?>" data-products="<?php esc_html_e( 'Products', 'uap' );?>"></div>
