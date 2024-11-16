<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=manage');?>"><?php esc_html_e('Manage Discounts', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=settings');?>"><?php esc_html_e('Settings', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
if (isset($_GET['id'])){
	$id = sanitize_text_field($_GET['id']);
} else {
	$id = 0;
}
$data['metas'] = Ihc_Db::ihc_woo_product_custom_price_get_item($id);
$data['metas']['levels'] = Ihc_Db::ihc_woo_product_custom_price_lid_product_get_lid_list($id);
$data['metas']['products'] = Ihc_Db::ihc_woo_product_custom_price_lid_product_get_products_list($id);
if ($data['metas']['levels']){
	$data['metas']['levels'] = implode(',', $data['metas']['levels']);
} else {
	$data['metas']['levels'] = '';
}
if ($data['metas']['products']){
	$data['metas']['products'] = implode(',', $data['metas']['products']);
} else {
	$data['metas']['products'] = '';
}
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>

<form action="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=manage');?>" method="post">
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('WooCommerce Discounts', 'ihc');?></h3>
		<div class="inside">

			<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

			<div class="iump-form-line">
				<div class="row">
					<div class="col-xs-5">
						<h4><?php esc_html_e('Activate/Hold', 'ihc');?></h4>
						<p><?php esc_html_e('Activate or deactivate a discount without needing to delete it.', 'ihc');?></p>
						<label class="iump_label_shiwtch ihc-switch-button-margin">
							<?php $checked = ($data['metas']['status']) ? 'checked' : '';?>
							<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#the_status');" <?php echo esc_attr($checked);?> />
							<div class="switch ihc-display-inline"></div>
						</label>
						<input type="hidden" name="status" value="<?php echo esc_attr($data['metas']['status']);?>" id="the_status" />
					</div>
				</div>
			</div>

			<div class="ihc-line-break"></div>

			<div class="iump-form-line">
				<div class="row">
					<div class="col-xs-5">
						<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Name', 'ihc');?></span>
						<input type="text" class="form-control" name="slug" value="<?php echo esc_attr($data['metas']['slug']);?>" />
						</div>
					</div>
				</div>
			</div>

			<div class="ihc-line-break"></div>

			<div class="iump-form-line">
				<div class="row">
				<div class="col-xs-5">
					<h2><?php esc_html_e('Discount Amount', 'ihc');?></h2>
					<p><?php esc_html_e('Set the value and type of Discount', 'ihc');?></p>
					<div class="input-group">
						<select name="discount_type" class="form-control m-bot15">
							<?php
								$types = array('%'=>esc_html__('Percentage Value', 'ihc'), 'flat' => esc_html__('Flat Value', 'ihc') );
								foreach ($types as $k => $v):?>
							<option value="<?php echo esc_attr($k);?>" <?php if ($data['metas']['discount_type']==$k){
								 echo esc_attr('selected');
							}
							?>
							><?php echo esc_html($v);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Value', 'ihc');?></span>
						<input type="number" class="form-control" name="discount_value" value="<?php echo esc_attr($data['metas']['discount_value']);?>" min=0 step="0.01" />
					</div>
				</div>
				</div>
			</div>

			<div class="iump-form-line iump-special-line">
				<div class="row">
					<div class="col-xs-5">
						<h2><?php esc_html_e('Targeting', 'ihc');?></h2>
						<p><?php esc_html_e('Users with certain memberships will get a special price for some products.' , 'ihc');?></p>
						<br/>
						<h4><?php esc_html_e('Memberships', 'ihc');?></h4>
						<div class="input-group">
							<p><?php esc_html_e('Discount available only for users that have one of the selected memberships.', 'ihc');?></p>
							<?php
							$posible_values = array( -2 => '...', -1 => esc_html__('All', 'ihc') );
							$levels = \Indeed\Ihc\Db\Memberships::getAll();
							if ($levels){
								foreach ($levels as $id=>$level){
									$posible_values[$id] = $level['name'];
								}
							}
							?>
							<select class="form-control" id="ihc-change-target-user-set" onChange="ihcWriteLevelTagValue(this, '#list_levels_hidden', '#ihc_tags_field_2', 'ihc_select_tag_' );">
								<?php
									foreach ($posible_values as $k=>$v){
										?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
									}
								?>
							</select>
							<input type="hidden" name="levels" class="form-control" id="list_levels_hidden" value="<?php echo esc_attr($data['metas']['levels']);?>" />
						</div>
						<div id="ihc_tags_field_2">
			            	<?php if (isset($data['metas']['levels'])):
			                    	if (strpos($data['metas']['levels'], ',')!==FALSE){
			                    		$values = explode(',', $data['metas']['levels']);
			                    	} else {
			                        	$values[] = $data['metas']['levels'];
			                        }
			                        if (count($values)){
			                        	foreach ($values as $value){
			                        		if (isset($posible_values[$value])){
			                        			?>
					                        		<div id="ihc_select_tag_<?php echo esc_attr($value);?>" class="ihc-tag-item">
					                        	    	<?php echo esc_html($posible_values[$value]);?>
					                        	    	<div class="ihc-remove-tag" onclick="ihcremoveTagForEditPost('<?php echo esc_attr($value);?>', '#ihc_select_tag_', '#list_levels_hidden');" title="<?php esc_html_e('Removing tag', 'ihc');?>">x</div>
					                        	    </div>
					                        	<?php
			                        		}
			                        	}//end of foreach
			                        }
			                        ?>
			                    <div class="ihc-clear"></div>
			                    <?php endif; ?>
						</div>
						<br/>
						<h4><?php esc_html_e('Products', 'ihc');?></h4>
						<p><?php esc_html_e('You can chose a list of products, a list of categories, or all products.', 'ihc');?></p>
						<?php
								//$ajaxURL = IHC_URL . 'admin/ajax-custom.php?ihcAdminAjaxNonce=' . wp_create_nonce( 'ihcAdminAjaxNonce' ) . "&woo_type=";
								$ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=ihc_ajax_custom_admin_ajax_gate&ihcAdminAjaxNonce=' . wp_create_nonce( 'ihcAdminAjaxNonce' ) . "&woo_type=";
						?>
						<select data-url="<?php echo esc_url($ajaxURL);?>"
							 id="search_woo_type" class="form-control ihc-js-woo-product-custom-prices-select" name="settings[woo_item_type]">
							<?php
								if (empty($data['metas']['settings']['woo_item_type'])){
									$data['metas']['settings']['woo_item_type'] = '';
								}
								$possible_values = array(
															'-1' => '...',
															'all' => esc_html__('All', 'ihc'),
															'category' => esc_html__('Specific Categories', 'ihc'),
															'product' => esc_html__('Specific Products', 'ihc'),
								);
								foreach ($possible_values as $k => $v){
									$selected = $data['metas']['settings']['woo_item_type']==$k ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
									<?php
								}
							?>
						</select>

						<div id="woo_the_search_box" class="<?php if ($data['metas']['settings']['woo_item_type']=='category' || $data['metas']['settings']['woo_item_type']=='product'){
							 echo esc_attr('ihc-display-block');
						}else{
							 echo esc_attr('ihc-display-none');
						}
						?>">
							<div class="input-group">
								<span class="input-group-addon" id="the_search_label"><?php
									$label = esc_html__('Search ', 'ihc');
									if ($data['metas']['settings']['woo_item_type']=='products'){
										$label .= esc_html__('Product', 'ihc');
									} else if ($data['metas']['settings']['woo_item_type']=='categories'){
										$label .= esc_html__('Category', 'ihc');
									}
								echo esc_html($label);?></span>
								<input type="text" class="form-control" id="product_search"/>
								<input type="hidden" name="products" class="form-control" id="product_search_input" value="<?php echo (isset($data['metas']['products'])) ? esc_attr($data['metas']['products']) : '';?>"/>
							</div>
							<div id="ihc_reference_search_tags"><?php
								if (!empty($data['metas']['products'])){
									$data['metas']['products'] = explode(',', $data['metas']['products']);
									foreach ($data['metas']['products'] as $value){
										if ($value){
											$id = 'ihc_reference_tag_' . $value;
											if ($data['metas']['settings']['woo_item_type']=='category'){
												/// get cat by id
												$title = Ihc_Db::get_category_name($value);
											} else {
												/// get product title
												$title = get_the_title($value);
											}
											?>
											<div id="<?php echo esc_attr($id);?>" class="ihc-tag-item"><?php echo esc_html($title);?><div class="ihc-remove-tag" onclick="ihcRemoveTag('<?php echo esc_attr($value);?>', '#<?php echo esc_attr($id);?>', '#product_search_input');" title="<?php esc_html_e( 'Removing tag', 'ihc' );?>">x</div></div>
											<?php
										}
									}
								}
							?></div>
						</div>

						<div id="ihc_woo_all_products" class="<?php if ($data['metas']['settings']['woo_item_type']=='all'){
							 echo esc_attr('ihc-display-block');
						}else{
							 echo esc_attr('ihc-display-none');
						}
						?>"><div class="ihc-tag-item"><?php esc_html_e('All Products', 'ihc');?></div></div>

					</div>
				</div>
			</div>

			<div class="iump-form-line">
				<div class="row">
				<div class="col-xs-4">
					<h2><?php esc_html_e('Date Range', 'ihc');?></h2>
					<p><?php esc_html_e('The discount will be active during a certain time interval based on your selling strategy.', 'ihc');?></p>
					<input type="text" class="form-control ihc-date-range-fields" id="start_date_input" name="start_date" value="<?php echo esc_attr($data['metas']['start_date']);?>"  /> - <input type="text" class="form-control ihc-date-range-fields" id="end_date_input" name="end_date" value="<?php echo esc_attr($data['metas']['end_date']);?>"  />
				</div>
				</div>
			</div>


			<div class="ihc-line-break"></div>

			<?php
				if (empty($data['metas']['settings']['color'])){
					$data['metas']['settings']['color'] = ihc_generate_color_hex();
				}
			?>

			<div class="iump-form-line">
				<div class="row">
					<div class="col-xs-5">
						<div class="input-group">
							<h2><?php esc_html_e("Color Scheme", 'ihc');?></h2>
							<div class="ihc-user-list-wrap">
		                    	<ul id="colors_ul" class="colors_ul">
		                        <?php
		                        	$color_scheme = array(
		                        							'#0a9fd8',
		                        							'#38cbcb',
		                        							'#27bebe',
		                        							'#0bb586',
		                        							'#94c523',
		                        							'#6a3da3',
		                        							'#f1505b',
		                        							'#ee3733',
		                        							'#f36510',
		                        							'#f8ba01',
									);
		                            $i = 0;
		                                    foreach ($color_scheme as $color){
		                                        if( $i==5 ){
                                                            ?><div class='clear'></div><?php
							}
		                                        $class = ($data['metas']['settings']['color']==$color) ? 'color-scheme-item-selected' : '';
		                                        ?>
		                                            <li class=" color-scheme-item <?php echo esc_attr($class);?>  ihc-box-background-<?php echo substr($color,1);?>" onClick="ihcChageColor(this, '<?php echo esc_attr($color);?>', '#color_scheme');"></li>
		                                        <?php
		                                        $i++;
		                                    }
		                        ?>
									<div class='clear'></div>
		                        </ul>
		                        <input type="hidden" name="settings[color]" id="color_scheme" value="<?php echo esc_attr($data['metas']['settings']['color']);?>" />
							</div>
						</div>
					</div>
				</div>
			</div>

			<input type="hidden" name="id" value="<?php echo esc_attr($data['metas']['id']);?>" />

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
</form>
