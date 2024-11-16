<?php
wp_enqueue_script( 'uap-public-functions', UAP_URL . 'assets/js/public-functions.js', array('jquery'), 8.3 );
$meta_arr = array(
							'num_of_entries' => 10,
							'entries_per_page' => 5,
							'order_by' => 'earnings',
							'order_type' => 'desc',
							'user_fields' => 'user_login,user_email,first_name,last_name,uap_avatar',
							'include_fields_label' => 0,
							'theme' => 'ihc-theme_1',
							'color_scheme' => '0a9fd8',
							'columns' => 5,
							'inside_page' => 0,
							'align_center' => 1,
							'slider_set' => 0,
							'items_per_slide' => 2,
							'speed' => 5000,
							'pagination_speed' => 500,
							'bullets' => 1,
							'nav_button' => 1,
							'autoplay' => 1,
							'stop_hover' => 0,
							'autoplay' => 1,
							'stop_hover' => 0,
							'responsive' => 0,
							'autoheight' => 0,
							'lazy_load' => 0,
							'loop' => 1,
							'pagination_theme' => 'pag-theme1',
						);
							wp_enqueue_script( 'uap-owl-carousel', UAP_URL . 'public/listing_users/assets/js/owl.carousel.js', ['jquery'], 8.3 );

?>
<div class="uap-js-top-affiliates-plugin-url" data-value='<?php echo UAP_URL;?>' ></div>

	<div class="uap-user-list-wrap">
			<div class="uap-page-title"><span class="second-text"><?php esc_html_e('Top Affiliates Showcase', 'uap');?></span>
			</div>
			<div class="uap-wrapper">
			<div class="uap-user-list-settings-wrapper">
				<div class="box-title">
		            <h3><i class="fa-uap fa-icon-angle-down-uap"></i><?php esc_html_e("ShortCode Generator", 'uap')?></h3>
		            <div class="actions pointer">
					    <a id="uap_js_top_affiliates_the_toggle" onclick="" class="btn btn-mini content-slideUp">
		                    <i class="fa-uap fa-icon-cogs-uap"></i>
		                </a>
					</div>
				 	<div class="clear"></div>
				</div>
				<div id="the_uap_user_list_settings" class="uap-list-users-settings">

					<!-- DISPLAY ENTRIES -->
					<div class="uap-column column-one">
                   		<h4 class="uap-top-affiliates-box1-background"><i class="fa-uap fa-icon-dispent-uap"></i><?php esc_html_e('Display Entries', 'uap');?></h4>
						<div class="uap-settings-inner">
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Number Of Entries:", 'uap');?></div>
								<div class="uap-field"><input type="number" value="<?php echo esc_attr($meta_arr['num_of_entries']);?>" id="num_of_entries" onKeyUp="uapPreviewUList();" onChange="uapPreviewUList();" min="0" /></div>
							</div>
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Entries Per Page:", 'uap');?></div>
								<div class="uap-field"><input type="number" value="<?php echo esc_attr($meta_arr['entries_per_page']);?>" id="entries_per_page" onKeyUp="uapPreviewUList();" onChange="uapPreviewUList();" min="1" /></div>
							</div>
							<div class="uap-spacewp_b_divs"></div>
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Order By:", 'uap');?></div>
								<div class="uap-field">
									<select id="order_by" onChange="uapPreviewUList();">
										<?php
											$arr = array(
														  'referrals' => esc_html__('Referrals', 'uap'),
														  'earnings' => esc_html__('Earnings', 'uap'),
														  'visits' => esc_html__('Visits', 'uap'),
														  'user_registered' => esc_html__('Register Date','uap'),
														  'user_login' => esc_html__("UserName", 'uap'),
														  'user_email' => esc_html__("E-mail Address", 'uap'),
														  'random' => esc_html__("Random", 'uap'),
											);
											foreach ($arr as $k=>$v){
												$selected = ($meta_arr['order_by']==$k) ? 'selected' : '';
												?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
												<?php
											}
										?>
									</select>
								</div>
							</div>
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Order Type:", 'uap');?></div>
								<div class="uap-field">
									<select id="order_type" onChange="uapPreviewUList();">
										<?php
											foreach (array('asc'=>'ASC', 'desc'=>'DESC') as $k=>$v){
												$selected = ($meta_arr['order_type']==$k) ? 'selected' : '';
												?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
												<?php
											}
										?>
									</select>
								</div>
							</div>

							<div class="uap-spacewp_b_divs"></div>
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Filter By Rank", 'uap');?></div>
								<div class="uap-field">
									<label class="uap-checkbox-wrapp">
									<input type="checkbox" id="filter_by_rank" onClick="uapCheckboxDivRelation(this, '#ranks_in__wrap_div');uapPreviewUList();" />
									<span class="uap-checkmark"></span>
								</label>
								</div>
							</div>
							<div class="uap-user-list-row uap-top-affiliates-half-opacity" id="ranks_in__wrap_div">
								<div class="uap-label"><?php esc_html_e("User's Ranks:", 'uap');?></div>
								<div class="uap-field">
									<?php
										$ranks = $indeed_db->get_ranks();
										if ($ranks){
											?>
											<select class="uap-form-select " onchange="uapWriteTagValueListUsers(this, '#ranks_in', '#uap-select-ranks-view-values', 'uap-ranks-select-v-');uapPreviewUList();">
												<option value="">...</option>
											<?php
											foreach ($ranks as $object){
												?>
													<option value="<?php echo esc_attr($object->id);?>"><?php echo esc_html($object->label);?>
												<?php
											}
											?>
											</select>
											<?php
										}
									?>

								</div>
								<div id="uap-select-ranks-view-values"></div>
									<input type="hidden" value="" id="ranks_in" />
							</div>
						</div>
					</div>
					<!-- /DISPLAY ENTRIES -->



					<!-- TEMPLATE -->
					<div class="uap-column column-three">
						<h4 class="uap-top-affiliates-box2-background"><i class="fa-uap fa-icon-temp-uap"></i>Template</h4>
						<div class="uap-settings-inner">
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Select Theme", 'uap');?></div>
								<div class="uap-field">
									<select id="theme" onChange="uapPreviewUList();"><?php
										$themes = array('uap-theme_1' => esc_html__('Theme', 'uap') . ' 1',
														'uap-theme_2' => esc_html__('Theme', 'uap') . ' 2',
														'uap-theme_3' => esc_html__('Theme', 'uap') . ' 3',
														'uap-theme_4' => esc_html__('Theme', 'uap') . ' 4',
														'uap-theme_5' => esc_html__('Theme', 'uap') . ' 5',
														'uap-theme_6' => esc_html__('Theme', 'uap') . ' 6',
														'uap-theme_7' => esc_html__('Theme', 'uap') . ' 7',
														'uap-theme_8' => esc_html__('Theme', 'uap') . ' 8',
														'uap-theme_9' => esc_html__('Theme', 'uap') . ' 9',
														'uap-theme_10' => esc_html__('Theme', 'uap') . ' 10',
												);
										foreach ($themes as $k=>$v){
											$selected = ($meta_arr['theme']==$k) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
											<?php
										}
									?></select>
								</div>
							</div>
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Color Scheme", 'uap');?></div>
								<div class="uap-field">
		                            <ul id="colors_ul" class="colors_ul">
		                                <?php
		                                    $color_scheme = array('0a9fd8', '38cbcb', '27bebe', '0bb586', '94c523', '6a3da3', 'f1505b', 'ee3733', 'f36510', 'f8ba01');
		                                    $i = 0;
		                                    foreach ($color_scheme as $color){
		                                        if( $i==5 ){
																							 echo esc_uap_content("<li class='uap-clear'></li>");
																						}
		                                        $class = ($meta_arr['color_scheme']==$color) ? 'color-scheme-item color-scheme-item-selected' : 'color-scheme-item';
		                                        ?>
		                                            <li class="<?php echo esc_attr($class);?>  uap-box-background-<?php echo esc_attr($color);?>" onClick="uapChangeColorTop(this, '<?php echo esc_attr($color);?>', '#color_scheme');uapPreviewUList();" ></li>
		                                        <?php
		                                        $i++;
		                                    }
		                                ?>
										<li class='uap-clear'></li>
		                            </ul>
		                            <input type="hidden" id="color_scheme" value="<?php echo esc_attr($meta_arr['color_scheme']);?>" />
								</div>
							</div>
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Columns", 'uap');?></div>
								<div class="uap-field">
									<select id="columns" onChange="uapPreviewUList();"><?php
										for ($i=1; $i<7; $i++){
											$selected = ($i==$meta_arr['columns']) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr($i);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($i) . esc_html__(" Columns", 'uap')?></option>
											<?php
										}
									?></select>
								</div>
							</div>
							<div class="uap-user-list-row">
								<div class="uap-label"><?php esc_html_e("Additional Options", 'uap');?></div>
							</div>
							<div class="uap-user-list-row">
								<label class="uap-checkbox-wrapp">
								<?php $checked = (empty($meta_arr['align_center'])) ? '' : 'checked';?>
								<input type="checkbox" id="align_center" <?php echo esc_attr($checked);?> onClick="uapPreviewUList();"/> <?php esc_html_e("Align the Items Centered", 'uap');?>
								<span class="uap-checkmark"></span>
							</label>
							</div>

							<div class="uap-user-list-row">
								<label class="uap-checkbox-wrapp">
								<?php $checked = ($meta_arr['include_fields_label']) ? 'checked' : '';?>
								<input type="checkbox"  id="include_fields_label" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> />
								<?php esc_html_e('Show Fields Label', 'uap');?>
								<span class="uap-checkmark"></span>
							</label>
							</div>
						</div>
					</div>
					<!-- /TEMPLATE -->

					<!-- SLIDER -->
					<div class="uap-column column-four uap-column-four">
						<h4 class="uap-top-affiliates-box3-background"><i class="fa-uap fa-icon-slider-uap"></i><?php esc_html_e("Slider ShowCase", 'uap');?></h4>
						<div class="uap-settings-inner">
							<div class="uap-user-list-row">
								<label class="uap-checkbox-wrapp">
								<?php $checked = (empty($meta_arr['slider_set'])) ? '' : 'checked';?>
								<input type="checkbox" <?php echo esc_attr($checked);?> id="slider_set" onClick="uapCheckboxDivRelation(this, '#slider_options');uapPreviewUList();"/> <b><?php echo esc_html__('Show as Slider', 'uap');?></b>
	                 		 	<div class="extra-info uap-display-block"><?php echo esc_html__('If Slider Showcase is used, Pagination Showcase is disabled.', 'uap');?></div>
												<span class="uap-checkmark"></span>
											</label>
							</div>
							<div class="uap-top-affiliates-half-opacity" id="slider_options" >

						     <div class="splt-1">
								<div class="uap-user-list-row">
									<div class="uap-label"><?php esc_html_e('Items per Slide:', 'uap');?></div>
									<div class="uap-field">
										<input type="number" min="1" id="items_per_slide" onChange="uapPreviewUList();" onKeyup="uapPreviewUList();" value="<?php echo esc_attr($meta_arr['items_per_slide']);?>" />
									</div>
								</div>
								<div class="uap-user-list-row">
									<div class="uap-label"><?php esc_html_e('Slider Timeout:', 'uap');?></div>
									<div class="uap-field">
										<input type="number" min="1" id="speed" onChange="uapPreviewUList();" onKeyup="uapPreviewUList();" value="<?php echo esc_attr($meta_arr['speed']);?>" />
									</div>
								</div>
								<div class="uap-user-list-row">
									<div class="uap-label"><?php esc_html_e('Pagination Speed:', 'uap');?></div>
									<div class="uap-field">
										<input type="number" min="1" id="pagination_speed" onChange="uapPreviewUList();" onKeyup="uapPreviewUList();" value="<?php echo esc_attr($meta_arr['pagination_speed']);?>" />
									</div>
								</div>
								 <div class="uap-user-list-row">
	                          		<div class="uap-label"><?php esc_html_e('Pagination Theme:', 'uap');?></div>
	                          		<div class="uap-field">
		                          		<select id="pagination_theme" onChange="uapPreviewUList();"><?php
		                          			$array = array(
		                          								'pag-theme1' => esc_html__('Pagination Theme 1', 'uap'),
		                          								'pag-theme2' => esc_html__('Pagination Theme 2', 'uap'),
		                          								'pag-theme3' => esc_html__('Pagination Theme 3', 'uap'),
		                          							);
		                          			foreach ($array as $k=>$v){
		                          				$selected = ($k==$meta_arr['pagination_theme']) ? 'selected' : '';
		                          				?>
		                          				<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
		                          				<?php
		                          			}
		                          		?>
		                                </select>
	                          		</div>
	                          </div>

	                            <div class="uap-user-list-row">
	                          		<div class="uap-label"><?php esc_html_e('Animation Slide In', 'uap');?></div>
	                          		<div class="uap-field">
	                                  <select onChange="uapPreviewUList();" id="animation_in">
										  <option value="none">None</option>
										  <option value="fadeIn">fadeIn</option>
										  <option value="fadeInDown">fadeInDown</option>
										  <option value="fadeInUp">fadeInUp</option>
										  <option value="slideInDown">slideInDown</option>
										  <option value="slideInUp">slideInUp</option>
										  <option value="flip">flip</option>
										  <option value="flipInX">flipInX</option>
										  <option value="flipInY">flipInY</option>
										  <option value="bounceIn">bounceIn</option>
										  <option value="bounceInDown">bounceInDown</option>
										  <option value="bounceInUp">bounceInUp</option>
										  <option value="rotateIn">rotateIn</option>
										  <option value="rotateInDownLeft">rotateInDownLeft</option>
										  <option value="rotateInDownRight">rotateInDownRight</option>
										  <option value="rollIn">rollIn</option>
										  <option value="zoomIn">zoomIn</option>
										  <option value="zoomInDown">zoomInDown</option>
										  <option value="zoomInUp">zoomInUp</option>
									  </select>
	                          		</div>
	                          	</div>


	                          <div class="uap-user-list-row">
	                          		<div class="uap-label"><?php esc_html_e('Animation Slide Out', 'uap');?></div>
	                          		<div class="uap-field">
	                                    <select onChange="uapPreviewUList();" id="animation_out">
										  <option value="none">None</option>
										  <option value="fadeOut">fadeOut</option>
										  <option value="fadeOutDown">fadeOutDown</option>
										  <option value="fadeOutUp">fadeOutUp</option>
										  <option value="slideOutDown">slideOutDown</option>
										  <option value="slideOutUp">slideOutUp</option>
										  <option value="flip">flip</option>
										  <option value="flipOutX">flipOutX</option>
										  <option value="flipOutY">flipOutY</option>
										  <option value="bounceOut">bounceOut</option>
										  <option value="bounceOutDown">bounceOutDown</option>
										  <option value="bounceOutUp">bounceOutUp</option>
										  <option value="rotateOut">rotateOut</option>
										  <option value="rotateOutUpLeft">rotateOutUpLeft</option>
										  <option value="rotateOutUpRight">rotateOutUpRight</option>
										  <option value="rollOut">rollOut</option>
										  <option value="zoomOut">zoomOut</option>
										  <option value="zoomOutDown">zoomOutDown</option>
										  <option value="zoomOutUp">zoomOutUp</option>
									  </select>
	                          		</div>
	                          </div>
							</div>
							<div class="splt-2">

								<div class="uap-user-list-row">
	                          		<div class="uap-label"><?php esc_html_e('Additional Options', 'uap');?></div>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['bullets'])) ? '' : 'checked';?>
									<input type="checkbox" id="bullets" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Bullets", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['nav_button'])) ? '' : 'checked';?>
									<input type="checkbox" id="nav_button" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Nav Button", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['autoplay'])) ? '' : 'checked';?>
									<input type="checkbox" id="autoplay" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("AutoPlay", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['stop_hover'])) ? '' : 'checked';?>
									<input type="checkbox" id="stop_hover" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Stop On Hover", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['responsive'])) ? '' : 'checked';?>
									<input type="checkbox" id="responsive" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Responsive", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['autoheight'])) ? '' : 'checked';?>
									<input type="checkbox" id="autoheight" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Auto Height", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['lazy_load'])) ? '' : 'checked';?>
									<input type="checkbox" id="lazy_load" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Lazy Load", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
								<div class="uap-user-list-row">
									<label class="uap-checkbox-wrapp">
									<?php $checked = (empty($meta_arr['loop'])) ? '' : 'checked';?>
									<input type="checkbox" id="loop" onClick="uapPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Play in Loop", 'uap');?>
									<span class="uap-checkmark"></span>
								</label>
								</div>
							</div>

		        			<div class="clear"></div>
							</div>
						</div>
					</div>
					<!-- /SLIDER -->
		        <div class="clear"></div>
					<!-- ENTRY INFO -->
					<div class="uap-column column-two uap-column-two">
                  		<h4 class="uap-top-affiliates-box4-background"><i class="fa-uap fa-icon-entryinfo-uap"></i><?php esc_html_e('Displayed User Fields', 'uap');?></h4>
				  		<div class="uap-settings-inner">
				  			<div class="uap-user-list-row">
				  				<?php
				  					$fields = array('user_login' => 'Username',
				  									'uap_avatar' => 'Avatar',
				  									'user_email' => 'Email',
				  									'first_name'=>'First Name',
				  									'last_name' => 'Last Name',
				  									'earnings' => esc_html__('Earnings', 'uap'),
				  									'referrals' => esc_html__('Referrals', 'uap'),
				  									'visits' => esc_html__('Visits', 'uap'),
				  									);
									$green_color = array('earnings', 'referrals', 'visits');
				  					$defaults = explode(',', $meta_arr['user_fields']);
									global $indeed_db;
									$reg_fields = $indeed_db->register_get_custom_fields();

				  					$exclude = array('pass1', 'pass2', 'tos', 'recaptcha', 'confirm_email');
									foreach ($reg_fields as $k=>$v){
										if (!in_array($v['name'], $exclude)){
											if (isset($v['native_wp']) && $v['native_wp']){
												$extra_fields[$v['name']] = esc_html__($v['label'], 'uap');
											} else {
												$extra_fields[$v['name']] = $v['label'];
											}
											if (empty($extra_fields[$v['name']])){
												unset($extra_fields[$v['name']]);
											}
										}
									}

				  					$fields_arr = array_merge($fields, $extra_fields);

				  					foreach ($fields_arr as $k=>$v){
				  						$checked = (in_array($k, $defaults)) ? 'checked' : '';
				  						$color = (in_array($v, $fields)) ? '0a9fd8' : '000000';
				  						if (in_array($k, $green_color)){
				  							$color = '0bb586';
				  						}
				  						?>
				  						<div class="uap-memberslist-fields uap-top-affiliates-fields-color-<?php echo esc_attr($color);?>">
												<label class="uap-checkbox-wrapp">
												<input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="uapMakeInputhString(this, '<?php echo esc_attr($k);?>', '#user_fields');uapPreviewUList();" /> <?php echo esc_html($v);?>
												<span class="uap-checkmark"></span>
											</label>
											</div>
				  						<?php
				  					}
				  				?>
				  				<input type="hidden" value="<?php echo esc_attr($meta_arr['user_fields']);?>" id="user_fields" />
				  			</div>
				  		</div>
				  	</div>
					<!-- /ENTRY INFO -->
				</div>
		        <div class="clear"></div>
			</div>

			<div class="uap-user-list-shortcode-wrapp">
		        <div class="content-shortcode">
		            <div>
		                <span class="uap-top-affiliates-shortcode-wrapper"><?php echo esc_html__('ShortCode :', 'uap');?> </span>
		                <span class="the-shortcode"></span>
		            </div>
		            <div class="uap-code-warpper">
		                <span  class="uap-top-affiliates-shortcode-wrapper"><?php echo esc_html__('PHP Code:', 'uap');?> </span>
		                <span class="php-code"></span>
		            </div>
		        </div>
		    </div>

	    	<div class="uap-user-list-preview">
			    <div class="box-title">
			        <h2><i class="fa-uap fa-icon-eyes-uap"></i><?php echo esc_html__('Preview', 'uap');?></h2>
			            <div class="actions-preview pointer">
						    <a id="uap_js_toggle_preview" onclick="" class="btn btn-mini content-slideUp">
			                    <i class="fa-uap fa-icon-cogs-uap"></i>
			                </a>
						</div>
			        <div class="clear"></div>
			    </div>
			    <div id="preview" class="uap-preview"></div>
			</div>
		</div>
	</div>

<?php
