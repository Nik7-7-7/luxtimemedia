<?php
$subtab = isset( $_GET['subtab'] ) ? sanitize_text_field($_GET['subtab']) : 'shortcode_generator';
wp_enqueue_script( 'ihc-owl-carousel', IHC_URL . 'public/listing_users/assets/js/owl.carousel.js', ['jquery'], 10.1 );
?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='shortcode_generator') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab='. $tab . '&shortcode_generator' );?>"><?php esc_html_e('Shortcode Generator', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='inside_page') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=inside_page' );?>"><?php esc_html_e('Public Individual Page', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='settings') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=settings' );?>"><?php esc_html_e('Additional Settings', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$meta_arr = array(
							'num_of_entries' => 10,
							'entries_per_page' => 5,
							'order_by' => 'date',
							'order_type' => 'desc',
							'user_fields' => 'user_login,user_email,first_name,last_name,ihc_avatar,ihc_sm',
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
							'search_filter' => 0,
						);
?>

<?php
	$tab = (empty($_GET['subtab'])) ? 'shortcode_generator' : sanitize_text_field($_GET['subtab']);
	switch ($tab){
		case 'shortcode_generator':

			$showWorning = \Ihc_Db::isListingUserAcceptEnabled();
?>
<?php if ( $showWorning ):?>
	<div  class="ihc-alert-warning-wrapper">
		<div class='ihc-alert-warning'>
				<div class='ihc-close-notice ihc-js-close-admin-dashboard-notice' data-type="listing_user">x</div>
				<?php esc_html_e( 'Warning: "Accept display on MembersList" field is activated on Register form. Members Directory showcase will include only users who accepted', 'ihc' );?>
		</div>
	</div>
<?php endif;?>

	<div class="ihc-user-list-wrap ihc-admin-list-users">
			<div class="iump-page-headline"><?php esc_html_e('Members Directory Showcase', 'ihc');?></div>
			<div class="ihc-user-list-settings-wrapper">
				<div class="box-title">
		            <h3><i class="fa-ihc fa-icon-angle-down-ihc"></i><?php esc_html_e("ShortCode Generator", 'ihc');?></h3>
		            <div class="actions pointer">
					    <a class="btn btn-mini content-slideUp ihc-js-listing-users-slide-up">
		                    <i class="fa-ihc fa-icon-cogs-ihc"></i>
		                </a>
					</div>
				 	<div class="clear"></div>
				</div>
				<div id="the_ihc_user_list_settings" class="ihc-list-users-settings">

					<!-- DISPLAY ENTRIES -->
					<div class="ihc-column column-one">
                   		<h4 class="ihc-top-background-box1"><i class="fa-ihc fa-icon-dispent-ihc"></i><?php esc_html_e('Display Entries', 'ihc');?></h4>
						<div class="ihc-settings-inner">
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Number Of Entries:", 'ihc');?></div>
								<div class="ihc-field"><input type="number" value="<?php echo esc_attr($meta_arr['num_of_entries']);?>" id="num_of_entries" onKeyUp="ihcPreviewUList();" onChange="ihcPreviewUList();" class="ihc-small-inout" min="0" /></div>
							</div>
							<div class="ihc-spacewp_b_divs"></div>
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Order By:", 'ihc');?></div>
								<div class="ihc-field">
									<select id="order_by" onChange="ihcPreviewUList();">
										<?php
											$arr = array(
															'user_registered' 	=> esc_html__('Register Date','ihc'),
														  'user_login' 				=> esc_html__("UserName", 'ihc'),
														  'user_email' 				=> esc_html__("E-mail Address", 'ihc'),
														  'random' 						=> esc_html__("Random", 'ihc'),
															'last_name'					=> esc_html__( 'Last name', 'ihc' ),
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
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Order Type:", 'ihc');?></div>
								<div class="ihc-field">
									<select id="order_type" onChange="ihcPreviewUList();">
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
							<div class="ihc-spacewp_b_divs"></div>
							<div class="ihc-user-list-row">
								<?php $checked = (empty($meta_arr['inside_page'])) ? '' : 'checked';?>
								<input type="checkbox" id="inside_page" <?php echo esc_attr($checked);?> onClick="ihcPreviewUList();"/> <?php esc_html_e("Activate Public Individual Page", 'ihc');?>
								<div class="extra-info"><?php esc_html_e('Use this option only if you have the View User Page properly set up.', 'ihc');?></div>
							</div>
							<div class="ihc-spacewp_b_divs"></div>
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Filter By Membership", 'ihc');?></div>
								<div class="ihc-field">
									<input type="checkbox" id="filter_by_level" onClick="ihcCheckboxDivRelation(this, '#levels_in__wrap_div');ihcPreviewUList();" />
								</div>
							</div>
							<div class="ihc-user-list-row" id="levels_in__wrap_div" class="ihc-half-opacity">
								<div class="ihc-label"><?php esc_html_e("User's Memberships:", 'ihc');?></div>
								<div class="ihc-field">
									<?php
										$levels = \Indeed\Ihc\Db\Memberships::getAll();
										if ($levels){
											?>
											<select class="iump-form-select " onchange="ihcWriteTagValueListUsers(this, '#levels_in', '#ihc-select-level-view-values', 'ihc-level-select-v-');ihcPreviewUList();">
											<option value="-1" selected>...</option>
											<?php
											foreach ($levels as $id=>$level_arr){
												?>
													<option value="<?php echo esc_attr($id);?>"><?php echo esc_html($level_arr['label']);?>
												<?php
											}
											?>
											</select>
											<?php
										}
									?>

								</div>
								<div id="ihc-select-level-view-values"></div>
									<input type="hidden" value="" id="levels_in" />
							</div>

							<div class="ihc-spacewp_b_divs"></div>

							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Exclude Pending Users", 'ihc');?></div>
								<div class="ihc-field">
									<input type="checkbox" id="exclude_pending" onClick="ihcPreviewUList();" />
								</div>
							</div>

						</div>
					</div>
					<!-- /DISPLAY ENTRIES -->



					<!-- TEMPLATE -->
					<div class="ihc-column column-three">
						<h4 class="ihc-top-background-box2"><i class="fa-ihc fa-icon-temp-ihc"></i>Template</h4>
						<div class="ihc-settings-inner">
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Select Theme", 'ihc');?></div>
								<div class="ihc-field">
									<select id="theme" onChange="ihcPreviewUList();"><?php
										$themes = array('ihc-theme_1' => esc_html__('Theme', 'ihc') . ' 1',
														'ihc-theme_2' => esc_html__('Theme', 'ihc') . ' 2',
														'ihc-theme_3' => esc_html__('Theme', 'ihc') . ' 3',
														'ihc-theme_4' => esc_html__('Theme', 'ihc') . ' 4',
														'ihc-theme_5' => esc_html__('Theme', 'ihc') . ' 5',
														'ihc-theme_6' => esc_html__('Theme', 'ihc') . ' 6',
														'ihc-theme_7' => esc_html__('Theme', 'ihc') . ' 7',
														'ihc-theme_8' => esc_html__('Theme', 'ihc') . ' 8',
														'ihc-theme_9' => esc_html__('Theme', 'ihc') . ' 9',
														'ihc-theme_10' => esc_html__('Theme', 'ihc') . ' 10',
												);
										foreach ($themes as $k=>$v){
											$selected = ($meta_arr['theme']==$k) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v);?></option>
											<?php
										}
									?></select>
								</div>
							</div>
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Color Scheme", 'ihc');?></div>
								<div class="ihc-field">
		                            <ul id="colors_ul" class="colors_ul">
		                                <?php
		                                    $color_scheme = array('0a9fd8', '38cbcb', '27bebe', '0bb586', '94c523', '6a3da3', 'f1505b', 'ee3733', 'f36510', 'f8ba01');
		                                    $i = 0;
		                                    foreach ($color_scheme as $color){
		                                        if( $i==5 ){
                                                            ?><div class='clear'></div><?php
							}
		                                        $class = ($meta_arr['color_scheme']==$color) ? 'color-scheme-item-selected' : '';
		                                        ?>
		                                            <li class="color-scheme-item <?php echo esc_attr($class);?> ihc-box-background-<?php echo esc_attr($color);?>" onClick="ihcChageColor(this, '<?php echo esc_attr($color);?>', '#color_scheme');ihcPreviewUList();"></li>
		                                        <?php
		                                        $i++;
		                                    }
		                                ?>
										<div class='clear'></div>
		                            </ul>
		                            <input type="hidden" id="color_scheme" value="<?php echo esc_attr($meta_arr['color_scheme']);?>" />
								</div>
							</div>
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Columns", 'ihc');?></div>
								<div class="ihc-field">
									<select id="columns" onChange="ihcPreviewUList();"><?php
										for ($i=1; $i<7; $i++){
											$selected = ($i==$meta_arr['columns']) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr($i);?>" <?php echo esc_attr($selected);?>><?php echo esc_attr($i) . esc_html__(" Columns", 'ihc');?></option>
											<?php
										}
									?></select>
								</div>
							</div>
							<div class="ihc-user-list-row">
								<div class="ihc-label"><?php esc_html_e("Additional Options", 'ihc');?></div>
							</div>
							<div class="ihc-user-list-row">
								<?php $checked = (empty($meta_arr['align_center'])) ? '' : 'checked';?>
								<input type="checkbox" id="align_center" <?php echo esc_attr($checked);?> onClick="ihcPreviewUList();"/> <?php esc_html_e("Align the Items Centered", 'ihc');?>
							</div>

							<div class="ihc-user-list-row">
								<?php $checked = ($meta_arr['include_fields_label']) ? 'checked' : '';?>
								<input type="checkbox" id="include_fields_label" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> />
								<?php esc_html_e('Show Fields Label', 'ihc');?>
							</div>
						</div>
					</div>
					<!-- /TEMPLATE -->

					<!-- SLIDER -->
					<div class="ihc-column column-four ihc-half-column">
						<h4 class="ihc-top-background-box3"><i class="fa-ihc fa-icon-slider-ihc"></i><?php esc_html_e("Slider ShowCase", 'ihc');?></h4>
						<div class="ihc-settings-inner">
							<div class="ihc-user-list-row">
								<?php $checked = (empty($meta_arr['slider_set'])) ? '' : 'checked';?>
								<input type="checkbox" <?php echo esc_attr($checked);?> id="slider_set" onClick="ihcCheckboxDivRelation(this, '#slider_options');ihcPreviewUList();"/> <b><?php echo esc_html__('Show as Slider', 'ihc');?></b>
	                 		 	<div class="extra-info ihc-display-block"><?php echo esc_html__('If the Slider Showcase is used, then the Pagination Showcase is disabled.', 'ihc');?></div>
							</div>
							<div class="ihc-half-opacity" id="slider_options" >

						     <div class="splt-1">
								<div class="ihc-user-list-row">
									<div class="ihc-label"><?php esc_html_e('Items per Slide:', 'ihc');?></div>
									<div class="ihc-field">
										<input type="number" min="1" id="items_per_slide" onChange="ihcPreviewUList();" onKeyup="ihcPreviewUList();" value="<?php echo esc_attr($meta_arr['items_per_slide']);?>"/>
									</div>
								</div>
								<div class="ihc-user-list-row">
									<div class="ihc-label"><?php esc_html_e('Slider Timeout:', 'ihc');?></div>
									<div class="ihc-field">
										<input type="number" min="1" id="speed" onChange="ihcPreviewUList();" onKeyup="ihcPreviewUList();" value="<?php echo esc_attr($meta_arr['speed']);?>"/>
									</div>
								</div>
								<div class="ihc-user-list-row">
									<div class="ihc-label"><?php esc_html_e('Pagination Speed:', 'ihc');?></div>
									<div class="ihc-field">
										<input type="number" min="1" id="pagination_speed" onChange="ihcPreviewUList();" onKeyup="ihcPreviewUList();" value="<?php echo esc_attr($meta_arr['pagination_speed']);?>"/>
									</div>
								</div>
								 <div class="ihc-user-list-row">
	                          		<div class="ihc-label"><?php esc_html_e('Pagination Theme:', 'ihc');?></div>
	                          		<div class="ihc-field">
		                          		<select id="pagination_theme" onChange="ihcPreviewUList();" class="ihc-small-select"><?php
		                          			$array = array(
		                          								'pag-theme1' => esc_html__('Pagination Theme 1', 'ihc'),
		                          								'pag-theme2' => esc_html__('Pagination Theme 2', 'ihc'),
		                          								'pag-theme3' => esc_html__('Pagination Theme 3', 'ihc'),
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

	                            <div class="ihc-user-list-row">
	                          		<div class="ihc-label"><?php esc_html_e('Animation Slide In', 'ihc');?></div>
	                          		<div class="ihc-field">
	                                  <select onChange="ihcPreviewUList();" id="animation_in" class="ihc-small-select">
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


	                          <div class="ihc-user-list-row">
	                          		<div class="ihc-label"><?php esc_html_e('Animation Slide Out', 'ihc');?></div>
	                          		<div class="ihc-field">
	                                    <select onChange="ihcPreviewUList();" id="animation_out" class="ihc-small-select">
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

								<div class="ihc-user-list-row">
	                          		<div class="ihc-label"><?php esc_html_e('Additional Options', 'ihc');?></div>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['bullets'])) ? '' : 'checked';?>
									<input type="checkbox" id="bullets" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Bullets", 'ihc');?>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['nav_button'])) ? '' : 'checked';?>
									<input type="checkbox" id="nav_button" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Nav Button", 'ihc');?>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['autoplay'])) ? '' : 'checked';?>
									<input type="checkbox" id="autoplay" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("AutoPlay", 'ihc');?>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['stop_hover'])) ? '' : 'checked';?>
									<input type="checkbox" id="stop_hover" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Stop On Hover", 'ihc');?>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['responsive'])) ? '' : 'checked';?>
									<input type="checkbox" id="responsive" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Responsive", 'ihc');?>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['autoheight'])) ? '' : 'checked';?>
									<input type="checkbox" id="autoheight" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Auto Height", 'ihc');?>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['lazy_load'])) ? '' : 'checked';?>
									<input type="checkbox" id="lazy_load" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Lazy Load", 'ihc');?>
								</div>
								<div class="ihc-user-list-row">
									<?php $checked = (empty($meta_arr['loop'])) ? '' : 'checked';?>
									<input type="checkbox" id="loop" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Play in Loop", 'ihc');?>
								</div>
							</div>

		        			<div class="clear"></div>
							</div>
						</div>
					</div>
					<!-- /SLIDER -->
		        <div class="clear"></div>
					<!-- ENTRY INFO -->
					<div class="ihc-column column-two ihc-full-col">
                  		<h4 class="iump-memberslist-box"><i class="fa-ihc fa-icon-entryinfo-ihc"></i><?php esc_html_e('Displayed User Fields', 'ihc');?></h4>
				  		<div class="ihc-settings-inner">
				  			<div class="ihc-user-list-row">
				  				<?php
				  					$fields = array('user_login' => 'Username',
				  									'ihc_avatar' => 'Avatar',
				  									'user_email' => 'Email',
				  									'ihc_sm' => 'Social Media',
				  									'first_name'=>'First Name',
				  									'last_name' => 'Last Name',
				  									);
				  					$defaults = explode(',', $meta_arr['user_fields']);
				  					$reg_fields = ihc_get_user_reg_fields();
				  					$exclude = array('pass1', 'pass2', 'tos', 'recaptcha', 'confirm_email', 'ihc_social_media', 'payment_select');
									foreach ($reg_fields as $k=>$v){
										if (!in_array($v['name'], $exclude)){
											if (isset($v['native_wp']) && $v['native_wp']){
												$extra_fields[$v['name']] = esc_html__($v['label'], 'ihc');
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
				  						$color = (in_array($v, $fields)) ? '#0a9fd8' : '#000';
				  						?>
				  						<div class="ihc-memberslist-fields" style = ' color: <?php echo esc_attr($color);?>;'>
				  							<input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="ihcMakeInputhString(this, '<?php echo esc_attr($k);?>', '#user_fields');ihcPreviewUList();" /> <span class="ihc-vertical-align-bottom"><?php echo esc_attr($v);?></span>
				  						</div>
				  						<?php
				  					}
				  				?>
				  				<input type="hidden" value="<?php echo esc_attr($meta_arr['user_fields']);?>" id="user_fields" />
				  			</div>
				  		</div>
				  	</div>
					<!-- /ENTRY INFO -->

					<div class="ihc-column column-two ihc-half-column">
                  		<h4 class="iump-memberslist-box"><i class="fa-ihc fa-search-ihc"></i><?php esc_html_e('Search Bar', 'ihc');?></h4>
				  		<div class="ihc-settings-inner">
							<div class="ihc-user-list-row">
								<?php $checked = (empty($meta_arr['show_search'])) ? '' : 'checked';?>
								<span class="ihc-field ihc-search-bar-field">
									<input type="checkbox" id="show_search" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> />
								</span>
								<span class="ihc-special-option"><?php esc_html_e("Enable Search bar", 'ihc');?></span>

							</div>
							<div class="ihc-spacewp_b_divs ihc-spacewp_spec"></div>
							<div class="ihc-user-list-row">
								<h3 class="iump-memberslist-h3"><?php esc_html_e("Search by", 'ihc');?></h3>
								<?php
									if (isset($fields_arr['ihc_avatar'])){
										unset($fields_arr['ihc_avatar']);
									}
									if (isset($fields_arr['user_login'])){
										unset($fields_arr['user_login']);
									}
									if (isset($fields_arr['ihc_sm'])){
										unset($fields_arr['ihc_sm']);
									}
									$s_fields['nickname'] = esc_html__('Nickname', 'ihc');
									$fields['nickname'] = esc_html__('Nickname', 'ihc');
									$s_fields = $s_fields + $fields_arr;
									$defaults = array('nickname', 'user_email', 'first_name', 'last_name');
				  					foreach ($s_fields as $k=>$v){
				  						$checked = (in_array($k, $defaults));
				  						$color = (in_array($v, $fields)) ? '#0a9fd8' : '#000';
				  						?>
				  						<div class="ihc-memberslist-fields" style = ' color: <?php echo esc_attr($color);?>;'>
				  							<input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="ihcMakeInputhString(this, '<?php echo esc_attr($k);?>', '#search_by');ihcPreviewUList();" />  <span class="ihc-vertical-align-bottom"><?php echo esc_html($v);?></span>
				  						</div>
				  						<?php
				  					}
								?>
								<input type="hidden" value="" id="search_by" />
							</div>
				  		</div>
				  	</div>

					<div class="ihc-column column-two ihc-half-column">
                  		<h4 class="iump-memberslist-box"><i class="fa-ihc fa-dot-ihc"></i><?php esc_html_e('Pagination', 'ihc');?></h4>
				  		<div class="ihc-settings-inner">
							<div class="ihc-user-list-row">
								<h3 class="ihc-small-title"><?php esc_html_e("Entries Per Page:", 'ihc');?></h3>
								<div class="ihc-field"><input type="number"  class="ihc-extra-input" value="<?php echo esc_attr($meta_arr['entries_per_page']);?>" id="entries_per_page" onKeyUp="ihcPreviewUList();" onChange="ihcPreviewUList();" class="ihc-small-inout" min="1" /></div>
							</div>
							<div class="ihc-user-list-row">
								<h3 class="ihc-small-title"><?php esc_html_e("Position", 'ihc');?></h3>
								<select id="pagination_pos" onchange="ihcPreviewUList();"  class="ihc-extra-input"> <?php
									foreach (array('top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both') as $k=>$v){
										?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
									}
								?></select>
							</div>
							<div class="ihc-user-list-row">
								<h3 class="ihc-small-title"><?php esc_html_e("Theme", 'ihc');?></h3>
								<select id="general_pagination_theme" onchange="ihcPreviewUList();"  class="ihc-extra-input"><?php
									foreach (array('ihc-listing-users-pagination-1' => 'Theme One') as $k=>$v){
										?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
									}
								?></select>
							</div>
				  		</div>
				  	</div>
		        <div class="clear"></div>

					<div class="ihc-column column-two ihc-half-column">
                  		<h4 class="iump-memberslist-box"><i class="fa-ihc fa-filter-ihc"></i><?php esc_html_e('Members Filter', 'ihc');?></h4>
				  		<div class="ihc-settings-inner">
							<div class="ihc-user-list-row">
								<?php $checked = (empty($meta_arr['show_search_filter'])) ? '' : 'checked';?>
								<span class="ihc-field ihc-search-bar-field">
									<input type="checkbox" id="show_search_filter" onClick="ihcPreviewUList();" <?php echo esc_attr($checked);?> />
								</span>
								<span class="ihc-special-option"><?php esc_html_e("Enable Filter", 'ihc');?></span>

							</div>
							<div class="ihc-spacewp_b_divs ihc-spacewp_spec"></div>
							<div class="ihc-user-list-row">
								<h3 class="iump-memberslist-h3"><?php esc_html_e("Filter by", 'ihc');?></h3>
								<?php
									$fields = ihc_listing_user_get_filter_fields();
				  					foreach ($fields as $k=>$v){
				  						?>
				  						<div class="ihc-memberslist-fields" style = ' color: <?php echo esc_attr($color);?>;'>
				  							<input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="ihcMakeInputhString(this, '<?php echo esc_attr($k);?>', '#search_filter');ihcPreviewUList();" />  <span class="ihc-vertical-align-bottom"><?php echo esc_attr($v);?></span>
				  						</div>
				  						<?php
				  					}
								?>
								<input type="hidden" value="" id="search_filter" />
							</div>
				  		</div>
				  	</div>
		        	<div class="clear"></div>

				</div>
		        <div class="clear"></div>
			</div>



			<div class="ihc-user-list-shortcode-wrapp">
		        <div class="content-shortcode">
		            <div>
		                <span class="ihc-shortcode-text"><?php echo esc_html__('ShortCode :', 'ihc');?> </span>
		                <span class="the-shortcode"></span>
		            </div>
		            <div class="ihc-extra-margin">
		                <span class="ihc-shortcode-text"><?php echo esc_html__('PHP Code:', 'ihc');?> </span>
		                <span class="php-code"></span>
		            </div>
		        </div>
		    </div>

	    	<div class="ihc-user-list-preview">
			    <div class="box-title">
			        <h2><i class="fa-ihc fa-icon-eyes-ihc"></i><?php echo esc_html__('Preview', 'ihc');?></h2>
			            <div class="actions-preview pointer">
						    <a class="btn btn-mini content-slideUp ihc-js-listing-users-slide-up-preview">
			                    <i class="fa-ihc fa-icon-cogs-ihc"></i>
			                </a>
						</div>
			        <div class="clear"></div>
			    </div>
			    <div id="preview" class="ihc-preview"></div>
			</div>

	</div>
<?php
	break;
	case 'settings':
		//SETTINGS
		if (!empty($_POST['ihc_save']) && !empty( $_POST['ihc_admin_listing_users_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_listing_users_nonce']), 'ihc_admin_listing_users_nonce' ) ){
			///save
			ihc_save_update_metas('listing_users');
		}
		$meta_arr = ihc_return_meta_arr('listing_users');

		wp_enqueue_script( 'wp-theme-plugin-editor' );
		wp_enqueue_style( 'wp-codemirror' );
		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );

		?>
	<div class="ihc-user-list-wrap ihc-admin-list-users">
		<div class="iump-page-headline"><?php esc_html_e('Members Directory Showcase Settings', 'ihc');?></div>
		<form  method="post">
			<input type="hidden" name="ihc_admin_listing_users_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_listing_users_nonce' );?>" />
			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Responsive Settings', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line">
						<span class="iump-labels-special"><?php esc_html_e('Screen Max-Width:', 'ihc');?> 479px</span>
						<div class="ihc-general-options-link-pages"><select name="ihc_listing_users_responsive_small"><?php
							$arr = array( '1' => 1 . esc_html__(' Columns', 'ihc'),
										  '2' => 2 . esc_html__(' Columns', 'ihc'),
										  '3' => 3 . esc_html__(' Columns', 'ihc'),
										  '4' => 4 . esc_html__(' Columns', 'ihc'),
									 	  '5' => 5 . esc_html__(' Columns', 'ihc'),
									 	  '6' => 6 . esc_html__(' Columns', 'ihc'),
										  '0' => esc_html__('Auto', 'ihc'),
							);
							foreach ($arr as $k=>$v){
								$selected = ($meta_arr['ihc_listing_users_responsive_small']==$k) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?>
						</select></div>
					</div>
					<div class="iump-form-line">
						<span class="iump-labels-special"><?php esc_html_e('Screen Min-Width:', 'ihc');?> 480px <?php esc_html_e(" and Screen Max-Width:");?> 767px</span>
						<div class="ihc-general-options-link-pages"><select name="ihc_listing_users_responsive_medium"><?php
							foreach ($arr as $k=>$v){
								$selected = ($meta_arr['ihc_listing_users_responsive_medium']==$k) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?>
						</select></div>
					</div>
					<div class="iump-form-line">
						<span class="iump-labels-special"><?php esc_html_e('Screen Min-Width:', 'ihc');?> 768px <?php esc_html_e(" and Screen Max-Width:");?> 959px</span>
						<div class="ihc-general-options-link-pages"><select name="ihc_listing_users_responsive_large"><?php
							foreach ($arr as $k=>$v){
								$selected = ($meta_arr['ihc_listing_users_responsive_large']==$k) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?>
						</select></div>
					</div>
					<div class="ihc-wrapp-submit-bttn">
		            	<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large">
		            </div>
				</div>
			</div>

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Settings', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line">
						<div class="ihc-general-options-link-pages">
							<span class="iump-labels-onbutton"><?php esc_html_e("Open 'Public Individual Page' in new Window", 'ihc');?></span>
							<label class="iump_label_shiwtch iump-onbutton">
								<?php $checked = ($meta_arr['ihc_listing_users_target_blank']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_target_blank');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_target_blank']);?>" name="ihc_listing_users_target_blank" id="ihc_listing_users_target_blank" />
						</div>
					</div>
					<div class="ihc-wrapp-submit-bttn">
			           	<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large">
			           </div>
				</div>
			</div>

			<div class="ihc-stuffbox  iump-custom-css-box-wrapper">
				<h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
				<div class="inside">
					<div class="iump-form-line">
						<div class="ihc-general-options-link-pages"><textarea id="ihc_listing_users_custom_css" name="ihc_listing_users_custom_css" class="ihc-custom-css-textarea"><?php echo stripslashes($meta_arr['ihc_listing_users_custom_css']);?></textarea></div>
					</div>
					<div class="ihc-wrapp-submit-bttn">
		            	<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large">
		            </div>
				</div>
			</div>
		</form>
	</div>
		<?php
		break;
	case 'inside_page':
		//SETTINGS
		if (!empty($_POST['ihc_save'])  && !empty( $_POST['ihc_admin_listing_users_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_listing_users_nonce']), 'ihc_admin_listing_users_nonce' ) ){
			///save
			ihc_save_update_metas('listing_users_inside_page');
		}
		$meta_arr = ihc_return_meta_arr('listing_users_inside_page');
		wp_enqueue_script( 'wp-theme-plugin-editor' );
		wp_enqueue_style( 'wp-codemirror' );
		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );
		?>
		<div class="ihc-user-list-wrap ihc-admin-list-users">
			<div class="iump-page-headline"><?php esc_html_e('Public Individual Page', 'ihc');?></div>
			<form  method="post">
				<input type="hidden" name="ihc_admin_listing_users_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_listing_users_nonce' );?>" />
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Content', 'ihc');?></h3>
					<div class="inside">

						<div class="ihc-individual-page-wrapper">
						   <div class="iump-switch-field ihc-display-inline">
						      <h4><?php esc_html_e('Individual Page Structure', 'ihc');?></h4>
						      <div class="ihc-clear"></div>
						      <?php $checked = ($meta_arr['ihc_listing_users_inside_page_type']=='basic') ? 'checked' : '';?>
						      <input type="radio" id="iump-switch_left" onClick="ihcInsidePageChangeContentType();" name="ihc_listing_users_inside_page_type" value="basic" <?php echo esc_attr($checked);?> />
						      <label for="iump-switch_left"><?php esc_html_e('Custom Content', 'ihc');?></label>
						      <?php $checked = ($meta_arr['ihc_listing_users_inside_page_type']=='custom') ? 'checked' : '';?>
						      <input type="radio" id="iump-switch_right" onClick="ihcInsidePageChangeContentType();" name="ihc_listing_users_inside_page_type" value="custom" <?php echo esc_attr($checked);?> />
						      <label for="iump-switch_right"><?php esc_html_e('Predefined Templates', 'ihc');?></label>
						    </div>

						</div>

		<!--------------------- BASIC ---------------------->
						<?php $display = ($meta_arr['ihc_listing_users_inside_page_type']=='basic') ? 'ihc-display-block' : 'ihc-display-none';?>
						<div id="ihc_listing_users_content_basic" class="<?php echo esc_attr($display);?>">
							<span class="iump-labels-onbutton ihc-inside-page-content-title"><?php esc_html_e('Content:', 'ihc');?></span>
							<div class="iump-wp_editor ihc-inside-page-editor">
							<?php wp_editor(stripslashes($meta_arr['ihc_listing_users_inside_page_content']), 'ihc_listing_users_inside_page_content', array('textarea_name'=>'ihc_listing_users_inside_page_content', 'editor_height'=>200));?>
							</div>
							<div class="ihc-inside-page-constants-wrapper">
								<?php
									$constants = array( '{AVATAR_HREF}' => '',
														'{username}'=>'',
														'{user_email}'=>'',
														'{user_id}'		=> '',
														'{first_name}'=>'',
														'{last_name}'=>'',
														'{level_list}'=>'',
														'{blogname}'=>'',
														'{blogurl}'=>'',
														'{IHC_SOCIAL_MEDIA_LINKS}' => '', );
									$extra_constants = ihc_get_custom_constant_fields();
									foreach ($constants as $k=>$v){
									?>
										<div><?php echo esc_html($k);?></div>
									<?php
									}
									?>
										<h4><?php esc_html_e('Custom Fields Constants', 'ihc');?></h4>
									<?php
									foreach ($extra_constants as $k=>$v){
									?>
										<div><?php echo esc_html($k);?></div>
									<?php
									}
								?>
							</div>
							<div class="ihc-clear"></div>
						</div>

		<!--------------------- CUSTOM ---------------------->
						<?php $display = ($meta_arr['ihc_listing_users_inside_page_type']=='custom') ? 'ihc-display-block' : 'ihc-display-none';?>
						<div id="ihc_listing_users_content_extra_custom" class="<?php echo esc_attr($display);?>">

							<div class="iump-register-select-template">
								<div>
									<?php esc_html_e(' Individual Page Template ', 'ihc');?>
									<?php
										$templates = array(
															'template-1' => esc_html__('Template One', 'ihc'),
															'template-2' => esc_html__('Template Two', 'ihc'),
										);
									?>
									<select name="ihc_listing_users_inside_page_template" onChange="" class="ihc-extra-input">
										<?php foreach ($templates as $k=>$v):?>
											<?php $selected = ($k==$meta_arr['ihc_listing_users_inside_page_template']) ? 'selected' : '';?>
											<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v);?></option>
										<?php endforeach;?>
									</select>
								</div>

								<div class="ihc-clear"></div>

								<div class="ihc-extra-margin">
									<?php esc_html_e("Color Scheme ", 'ihc');?>
									<div class="ihc-inside-page-colors-wrapper">
			                            <ul id="colors_ul" class="colors_ul">
			                                <?php
			                                    $color_scheme = array('0a9fd8', '38cbcb', '27bebe', '0bb586', '94c523', '6a3da3', 'f1505b', 'ee3733', 'f36510', 'f8ba01');
			                                    $i = 0;
			                                    foreach ($color_scheme as $color){
			                                        if( $i==5 ){
                                                                    ?><div class='clear'></div><?php
								}
			                                        $class = ($meta_arr['ihc_listing_users_inside_page_color_scheme']==$color) ? 'color-scheme-item-selected' : 'color-scheme-item';
			                                        ?>
			                                            <li class="<?php echo esc_attr($class);?>" onClick="ihcChangeColorSchemeWd(this, '<?php echo esc_attr($color);?>', '#ihc_listing_users_inside_page_color_scheme');" style = ' background-color: #<?php echo esc_attr($color);?>;'></li>
			                                        <?php
			                                        $i++;
			                                    }
			                                ?>
											<div class='clear'></div>
			                            </ul>
			                            <input type="hidden" id="ihc_listing_users_inside_page_color_scheme" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_color_scheme']);?>" name="ihc_listing_users_inside_page_color_scheme"/>
									</div>
								</div>
							</div>

							<div class="ihc-show-main-details-wrapper">
							 <div class="iump-form-line">
								<h2 class="ihc-show-custom-fields-title"><?php esc_html_e('Main Details', 'ihc');?></h2>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_avatar']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_avatar');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_avatar']);?>" name="ihc_listing_users_inside_page_show_avatar" id="ihc_listing_users_inside_page_show_avatar" />
									<label><?php esc_html_e('Show Avatar', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_flag']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_flag');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_flag']);?>" name="ihc_listing_users_inside_page_show_flag" id="ihc_listing_users_inside_page_show_flag" />
									<label><?php esc_html_e('Show Flag', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_name']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_name');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_name']);?>" name="ihc_listing_users_inside_page_show_name" id="ihc_listing_users_inside_page_show_name" />
									<label><?php esc_html_e('Show Name', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_username']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_username');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_username']);?>" name="ihc_listing_users_inside_page_show_username" id="ihc_listing_users_inside_page_show_username" />
									<label><?php esc_html_e('Show Username', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_email']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_email');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_email']);?>" name="ihc_listing_users_inside_page_show_email" id="ihc_listing_users_inside_page_show_email" />
									<label><?php esc_html_e('Show E-mail', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_website']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_website');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_website']);?>" name="ihc_listing_users_inside_page_show_website" id="ihc_listing_users_inside_page_show_website" />
									<label><?php esc_html_e('Show Website', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_since']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_since');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_since']);?>" name="ihc_listing_users_inside_page_show_since" id="ihc_listing_users_inside_page_show_since" />
									<label><?php esc_html_e('Show Since', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_level']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_level');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_level']);?>" name="ihc_listing_users_inside_page_show_level" id="ihc_listing_users_inside_page_show_level" />
									<label><?php esc_html_e('Show Membership', 'ihc');?></label>
								</div>

								<div class="ihc-extra-margin">
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_listing_users_inside_page_show_banner']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_listing_users_inside_page_show_banner');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_banner']);?>" name="ihc_listing_users_inside_page_show_banner" id="ihc_listing_users_inside_page_show_banner" />
									<label><?php esc_html_e('Show Banner', 'ihc');?></label>
								</div>
								<div class="form-group ihc-banner-image-wrapper">
								<div><label><?php esc_html_e('Banner Image', 'ihc');?></label></div>
								<input type="text" class="form-control ihc-listing_users_inside_page_banner_href" onClick="openMediaUp(this);" value="<?php  echo esc_attr($meta_arr['ihc_listing_users_inside_page_banner_href']);?>" name="ihc_listing_users_inside_page_banner_href" id="ihc_listing_users_inside_page_banner_href"/>
								<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-listing-users-delete-banner-image" title="<?php esc_html_e('Remove Background Image', 'ihc');?>"></i>
							</div>
							  </div>
							</div>

							<div class="ihc-show-custom-fields-wrapper">
							<div class="iump-form-line">
								<h2 class="ihc-show-custom-fields-title"><?php esc_html_e('Show Custom Fields ', 'ihc');?></h2>
								<div class="ihc-show-custom-fields-content">
									<?php
					  					$defaults = explode(',', $meta_arr['ihc_listing_users_inside_page_show_custom_fields']);
					  					$reg_fields = ihc_get_user_reg_fields();
					  					$exclude = array(
					  									  'user_login',
					  									  'pass1',
					  									  'pass2',
					  									  'tos',
					  									  'recaptcha',
					  									  'confirm_email',
					  									  'ihc_social_media',
					  									  'payment_select',
														  'user_email',
														  'first_name',
														  'last_name',
														  'ihc_avatar',
														  'ihc_coupon',
														  'ihc_invitation_code_field',
														  'ihc_country',
										);
										foreach ($reg_fields as $k=>$v){
											if (!in_array($v['name'], $exclude)){
												if (isset($v['native_wp']) && $v['native_wp']){
													$fields[$v['name']] = esc_html__($v['label'], 'ihc');
												} else {
													$fields[$v['name']] = $v['label'];
												}
											}
										}

					  					foreach ($fields as $k=>$v){
					  						$checked = (in_array($k, $defaults)) ? 'checked' : '';
											$the_label = (empty($v)) ? $k : $v;
					  						?>
					  						<div class="ihc-memberslist-fields ihc-display-block">
					  							<input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="ihcMakeInputhString(this, '<?php echo esc_attr($k);?>', '#ihc_listing_users_inside_page_show_custom_fields');" /> <span class="ihc-vertical-align-bottom"><?php echo esc_html($the_label);?></span>
					  						</div>
					  						<?php
					  					}
					  				?>
								</div>
				  				<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_listing_users_inside_page_show_custom_fields']);?>" id="ihc_listing_users_inside_page_show_custom_fields" name="ihc_listing_users_inside_page_show_custom_fields" />
								</div>
							</div>



							<div>
								<h2> <?php esc_html_e('Additional Content', 'ihc');?></h2>
								<div class="iump-wp_editor ihc-half-column">
									<?php wp_editor(stripslashes($meta_arr['ihc_listing_users_inside_page_extra_custom_content']), 'ihc_listing_users_inside_page_extra_custom_content', array('textarea_name'=>'ihc_listing_users_inside_page_extra_custom_content', 'editor_height'=>200));?>
								</div>
							</div>

							<div class="ihc-clear"></div>

						</div>


						<div class="ihc-wrapp-submit-bttn">
			            	<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large">
			            </div>
					</div>
				</div>
				<div class="ihc-stuffbox iump-custom-css-box-wrapper">
					<h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
							<div class="ihc-general-options-link-pages"><textarea name="ihc_listing_users_inside_page_custom_css" class="ihc-custom-css-textarea" id="ihc_listing_users_inside_page_custom_css"><?php echo stripslashes($meta_arr['ihc_listing_users_inside_page_custom_css']);?></textarea></div>
						</div>
						<div class="ihc-wrapp-submit-bttn">
			            	<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large">
			            </div>
					</div>
				</div>
			</form>
</div>
		<?php
		break;
	}
