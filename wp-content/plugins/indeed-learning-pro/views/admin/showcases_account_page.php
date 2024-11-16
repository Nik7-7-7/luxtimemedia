<div class="ulp-page-title">

		    <?php esc_html_e('Account Page', 'ulp');?>


</div>

<div class="ulp-stuffbox">

    <div class="ulp-shortcode-display">[ulp-student-profile]</div>

</div>

    <div class="metabox-holder indeed">

        <form  method="post">
          <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />


          <div class="ulp-stuffbox">

        		<h3 class="ulp-h3"><?php esc_html_e('Top Section:', 'ulp');?></h3>

        		<div class="inside ulp-admin-account-top-section-template-wrapper">



        			<div class="ulp-register-select-template ulp-admin-account-top-section-template">

        				<?php esc_html_e('Select Template:', 'ulp');?>

        				<select name="ulp_ap_top_template"  class="ulp-admin-account-top-section-template-select">

                    <?php	foreach ($data ['themes'] as $k=>$v):?>

          						      <option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['ulp_ap_top_template']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

                    <?php endforeach;?>

                </select>

        			</div>



		<div class="ulp-inside-item">

            <div class="row">

                <div class="col-xs-12">

              <h4><?php esc_html_e('Welcome Message:', 'ulp');?></h4>

      				<div class="ulp-wp_editor ulp-admin-account-top-editor">

      				<?php wp_editor(stripslashes($data['metas']['ulp_ap_welcome_msg']), 'ulp_ap_welcome_msg', array('textarea_name'=>'ulp_ap_welcome_msg', 'editor_height'=>200));?>

      				</div>

      				<div class="ulp-admin-account-top-constants">
      				      <h4><?php esc_html_e('Regular constants', 'ulp');?></h4>

                  <?php foreach ($data['constants'] as $k=>$v):?>

                    <div><?php echo esc_html($k);?></div>

                  <?php endforeach;?>

      				</div>

      				<div class="ulp-clear"></div>

			        </div>

            	</div>

        	</div>

            <div class="ulp-inside-item">

            <div class="row">

                <div class="col-xs-6">

              <h2><?php esc_html_e('Background/Banner Image', 'ulp');?></h2>

    					<p><?php esc_html_e('The cover or background image, based on what theme you have chosen.', 'ulp');?></p>

    					<label class="ulp_label_shiwtch  ulp-onbutton">

    						<?php if (!isset($data['metas']['ulp_ap_edit_background'])){
                   $data['metas']['ulp_ap_edit_background'] = 1;
                } ?>

    						<?php $checked = ($data['metas']['ulp_ap_edit_background']==1) ? 'checked' : '';?>

    						<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_ap_edit_background');" <?php echo esc_attr($checked);?> />

    						<div class="switch ulp-display-inline"></div>

    					</label>

    					<input type="hidden" name="ulp_ap_edit_background" value="<?php echo esc_attr($data['metas']['ulp_ap_edit_background']);?>" id="ulp_ap_edit_background"/>



    				<div class="form-group ulp-admin-account-top-bk-wrapper">

    					<input type="text" class="form-control ulp-admin-account-top-bk-img" onClick="openMediaUp(this);" value="<?php  echo esc_url($data['metas']['ulp_ap_top_background_image']);?>" name="ulp_ap_top_background_image" id="ulp_ap_top_background_image" />

    					<i class="fa-ulp fa-remove-ulp ulp-js-showcase-account-page-do-delete" title="<?php esc_html_e('Remove Background Image', 'ulp');?>"></i>

    				</div>

                </div>

            </div>

        </div>

        <div class="ulp-inside-item">

            <div class="row">

                <div class="col-xs-6">

                 <h2><?php esc_html_e('Extra Elements', 'ulp');?></h2>

                 <div>

                 <h4><?php esc_html_e('Show Avatar Image', 'ulp');?></h4>

    					<label class="ulp_label_shiwtch  ulp-onbutton">

    						<?php if (!isset($data['metas']['ulp_ap_edit_show_avatar'])){
                   $data['metas']['ulp_ap_edit_show_avatar'] = 1;
                } ?>

    						<?php $checked = ($data['metas']['ulp_ap_edit_show_avatar']==1) ? 'checked' : '';?>

    						<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_ap_edit_show_avatar');" <?php echo esc_attr($checked);?> />

    						<div class="switch ulp-display-inline"></div>

    					</label>

    					<input type="hidden" name="ulp_ap_edit_show_avatar" value="<?php echo esc_attr($data['metas']['ulp_ap_edit_show_avatar']);?>" id="ulp_ap_edit_show_avatar"/>

                </div>

                <div>

                 <h4><?php esc_html_e('Show Reward Points', 'ulp');?></h4>

    					<label class="ulp_label_shiwtch  ulp-onbutton">

    						<?php if (!isset($data['metas']['ulp_ap_edit_show_points'])){
                   $data['metas']['ulp_ap_edit_show_points'] = 1;
                } ?>

    						<?php $checked = ($data['metas']['ulp_ap_edit_show_points']==1) ? 'checked' : '';?>

    						<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_ap_edit_show_points');" <?php echo esc_attr($checked);?> />

    						<div class="switch ulp-display-inline"></div>

    					</label>

    					<input type="hidden" name="ulp_ap_edit_show_points" value="<?php echo esc_attr($data['metas']['ulp_ap_edit_show_points']);?>" id="ulp_ap_edit_show_points"/>

                </div>

                <div>

                 <h4><?php esc_html_e('Show Student Badges', 'ulp');?></h4>

    					<label class="ulp_label_shiwtch  ulp-onbutton">

    						<?php if (!isset($data['metas']['ulp_ap_edit_show_badges'])){
                   $data['metas']['ulp_ap_edit_show_badges'] = 1;
                } ?>

    						<?php $checked = ($data['metas']['ulp_ap_edit_show_badges']==1) ? 'checked' : '';?>

    						<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_ap_edit_show_badges');" <?php echo esc_attr($checked);?> />

    						<div class="switch ulp-display-inline"></div>

    					</label>

    					<input type="hidden" name="ulp_ap_edit_show_badges" value="<?php echo esc_attr($data['metas']['ulp_ap_edit_show_badges']);?>" id="ulp_ap_edit_show_badges"/>

                </div>

                </div>

            </div>

        </div>

        <div class="ulp-inside-item">

            <div class="row">

                <div class="col-xs-6">

              <div class="ulp-wrapp-submit-bttn">

        				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="ulp_save" class="btn btn-primary pointer" />

        			</div>

              </div>

              </div>

          </div>

            </div>

          </div>



          <div class="ulp-stuffbox">

        		<h3 class="ulp-h3"><?php esc_html_e('Content Section:', 'ulp');?></h3>

        			  <div class="inside ulp-admin-account-top-section-template-wrapper">



        				<div class="ulp-register-select-template ulp-admin-account-top-section-template">

        					<?php esc_html_e('Select Template:', 'ulp');?>

        					<select name="ulp_ap_theme"  class="ulp-admin-account-top-section-template-select"><?php

        						$themes = array(

        												'ulp-ap-theme-1' => '(#1) '. esc_html__('Blue New Theme', 'ulp'),

        												'ulp-ap-theme-2' => '(#2) '. esc_html__('Dark Theme', 'ulp'),

        												'ulp-ap-theme-3' => '(#3) '. esc_html__('Light Theme', 'ulp'),

        						);

        						foreach ($themes as $k=>$v){

        							?>

        							<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['ulp_ap_theme']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

        							<?php

        						}

        					?></select>

        				</div>



        				<h2 class="ulp-admin-account-menu-tabs-title"><?php esc_html_e('Menu Tabs:', 'ulp');?></h2>

        				<?php	$tabs = explode(',', $data['metas']['ulp_ap_tabs']);?>

        						<div class="ulp-ap-tabs-list">

        							<?php foreach ($data['available_tabs'] as $k=>$v):?>

        								<div class="ulp-ap-tabs-list-item" onClick="ulpApMakeVisible('<?php echo esc_attr($k);?>', this);" id="<?php echo esc_attr('ulp_tab-' . $k);?>"><?php echo esc_html($v['label']);?></div>

        							<?php endforeach;?>

        							<div class="ulp-clear"></div>

        						</div>

        					<?php

                      $i = 0;

        					    foreach ($data['available_tabs'] as $k=>$v){

        						?>

        							<div class="ulp-ap-tabs-settings-item ulp-input-group-space" id="<?php echo esc_attr('ulp_tab_item_' . $k);?>">

        								<h4><?php echo esc_html($v['label']);?></h4>

        								<div class="ulp-admin-account-menu-tabs-btt-wrapper">
        									<span class="ulp-labels-onbutton"><?php esc_html_e('Activate the Tab:', 'ulp');?></span>

        									<label class="ulp_label_shiwtch  ulp-onbutton">

        										<?php $checked = (in_array($k, $tabs)) ? 'checked' : '';?>

        										<input type="checkbox" class="ulp-switch" onClick="ulpSecondMakeInputhString(this, '<?php echo esc_attr($k);?>', '#ulp_ap_tabs');" <?php echo esc_attr($checked);?> />

        										<div class="switch ulp-display-inline"></div>

        									</label>

        								</div>



        									<?php

        										if (empty($data['metas']['ulp_ap_' . $k . '_menu_label'])){

                                if (empty($data ['menu_items'][$k]['label'])){

        											      $data['metas']['ulp_ap_' . $k . '_menu_label'] = '';

                                } else {

                                    $data['metas']['ulp_ap_' . $k . '_menu_label'] = $data ['menu_items'][$k]['label'];

                                }

        										}

        									?>

        									<div class="input-group ulp-input-group-extra-max">

        										<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Menu Label', 'ulp');?></span>

        										<input type="text" class="form-control" placeholder="" value="<?php echo esc_attr($data['metas']['ulp_ap_' . $k . '_menu_label']);?>" name="<?php echo esc_attr('ulp_ap_' . $k . '_menu_label');?>">

        									</div>



        									<?php

        									if (empty($data['metas']['ulp_ap_' . $k . '_title'])){

        										$data['metas']['ulp_ap_' . $k . '_title'] = '';

        									}



        									?>

        										<div class="input-group ulp-input-group-extra-max ulp-input-group-space">

        											<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Title', 'ulp');?></span>

        											<input type="text" class="form-control" placeholder="" value="<?php echo esc_attr($data['metas']['ulp_ap_' . $k . '_title']);?>" name="<?php echo esc_attr('ulp_ap_' . $k . '_title');?>">

        										</div>



                          <div class="ulp-input-group-space">
                              <label><?php esc_html_e('Icon', 'ulp');?></label>

                              <div class="ulp-icon-select-wrapper">

                                  <div class="ulp-icon-input">

                                    <div id="<?php echo esc_attr('indeed_shiny_select_' . $k);?>" class="ulp-shiny-select-html"></div>

                                  </div>

                                    <div class="ulp-icon-arrow" id="<?php echo esc_attr('ulp_icon_arrow_' . $k);?>"><i class="fa-ulp fa-arrow-ulp"></i></div>

                                  <div class="ulp-clear"></div>

                              </div>

                          </div>



                          <?php

                          		if (empty($data['metas']['ulp_ap_' . $k . '_msg'])){

                          				$data['metas']['ulp_ap_' . $k . '_msg'] = '';

                          		}

                          ?>

        										<div class="ulp-input-group-space">

        											<div class="ulp-admin-account-content-editor"><?php

        												wp_editor(stripslashes($data['metas']['ulp_ap_' . $k . '_msg']), 'ulp_tab_' . $k . '_msg', array('textarea_name' => 'ulp_ap_' . $k . '_msg', 'editor_height'=>200));

        											?></div>

        											<div class="ulp-admin-account-content-constants">

        												<?php

        													echo esc_ulp_content("<h4>" . esc_html__('Regular constants', 'ulp') . "</h4>");

        													foreach ($data['constants'] as $key=>$val){

        														?>

        														<div><?php echo esc_attr($key);?></div>

        														<?php

        													}

        											?>

        											</div>

        										</div>


							<span class="ulp-js-showcase-account-page-shiny-select-data" data-k="<?php echo esc_attr($k);?>" data-default_code="<?php echo esc_attr($data ['metas']['ulp_ap_' . $k . '_icon_code']);?>"></span>


        							</div>



        						<?php



        						////



        					}

        				?>

        					<input type="hidden" value="<?php echo esc_attr($data['metas']['ulp_ap_tabs']);?>" id="ulp_ap_tabs" name="ulp_ap_tabs" />



                <div class="ulp-inside-item">

                	<div class="row">

                    	<div class="col-xs-8">

        					<div class="ulp-wrapp-submit-bttn ulp-input-group-space">

        						<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="ulp_save" class="btn btn-primary pointer"  />

        					</div>

                     </div>

        		  </div>

      			</div>

        			   </div>

        	</div>



        	<div class="ulp-stuffbox">

        		<h3 class="ulp-h3"><?php esc_html_e('Footer Section:', 'ulp');?></h3>

        		<div class="inside">



                <div class="ulp-inside-item">

                	<div class="row">

                    	<div class="col-xs-12">

        			<h2><?php esc_html_e('Footer Content:', 'ulp');?></h2>

        			<div class="ulp-input-group-space">

        				<div class="ulp-admin-account-content-editor"><?php

        					wp_editor(stripslashes($data['metas']['ulp_ap_footer_msg']), 'ulp_ap_footer_msg', array('textarea_name' => 'ulp_ap_footer_msg', 'editor_height'=>200));

        				?></div>



        			</div>

                    </div>

        		  </div>

      			</div>

                <div class="ulp-inside-item">

                	<div class="row">

                    	<div class="col-xs-8">

        			<div class="ulp-wrapp-submit-bttn">

        				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="ulp_save" class="btn btn-primary pointer"  />

        			</div>

                   </div>

        		  </div>

      			</div>

        		</div>

        	</div>



        	<div class="ulp-stuffbox">

        		<h3 class="ulp-h3"><?php esc_html_e('Additional Settings:', 'ulp');?></h3>

        		<div class="inside">

                <div class="ulp-inside-item">

                	<div class="row">

                    	<div class="col-xs-8">

        			<div class="ulp-form-line ulp-margin-bottom">

        				<h2><?php esc_html_e('Custom CSS:', 'ulp');?></h2>

        				<textarea id="ulp_account_page_custom_css" name="ulp_account_page_custom_css" class="ulp-dashboard-textarea-full ulp-admin-custom-css"><?php echo stripslashes($data['metas']['ulp_account_page_custom_css']);?></textarea>

        			</div>

        			<div class="ulp-wrapp-submit-bttn">

        				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="ulp_save" class="btn btn-primary pointer" />

        			</div>

                    </div>

        		  </div>

      			</div>

        		</div>

        	</div>



        </form>

    </div>

<span class="ulp-js-showcase-account-page-make-ap-visible"></span>


<?php
$custom_css = '';
foreach ($data ['menu_items'] as $slug => $item):

	$custom_css .= ".fa-" . $slug . "-account-ulp:before{".
		"content: '\\".$item['icon']."';".
    "font-size: 20px;".
	"}";

endforeach;
if ($data ['font_awesome']):
  foreach ($data ['font_awesome'] as $base_class => $code):
    $custom_css .= ".". $base_class .":before{".
      "content: '\\".$code."';".
      "}";
  endforeach;
endif;
wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );

?>
