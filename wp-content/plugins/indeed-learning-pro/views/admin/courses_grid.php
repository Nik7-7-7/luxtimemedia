<?php
wp_enqueue_style( 'ulp-owl-carousel', ULP_URL . 'assets/css/owl.carousel.css', [], 3.6, 'all' );
wp_enqueue_style( 'ulp-grid-owl-theme', ULP_URL . 'assets/css/owl.theme.css', [], 3.6, 'all' );
wp_enqueue_style( 'ulp-owl-transitions', ULP_URL . 'assets/css/owl.transitions.css', [], 3.6, 'all' );
wp_enqueue_style( 'ulp-grid-layouts', ULP_URL . 'assets/css/layouts.css', [], 3.6, 'all' );
wp_enqueue_script( 'ulp-owl', ULP_URL . 'assets/js/owl.carousel.js', ['jquery'], 3.6, false );
?>
<div class="ulp-user-list-wrap"><div class="ulp-page-title"><?php esc_html_e('Courses Grid', 'ulp');?>
    </div>

    <div class="ulp-user-list-settings-wrapper">
      <div class="box-title">
        <h3><i class="fa-ulp fa-icon-angle-down-ulp"></i><?php esc_html_e("ShortCode Generator", 'ulp')?></h3>
        <div class="actions pointer">
            <a class="btn btn-mini content-slideUp ulp-js-course-grid-list-settings-toggle">
              <i class="fa-ulp fa-icon-cogs-ulp"></i>
            </a>
        </div>
        <div class="ulp-clear"></div>
      </div>

      <div id="the_ulp_user_list_settings" class="ulp-list-users-settings ulp-stuffbox">
      <div class="inside">
      	<div class="ulp-inside-item">
          <div class="row">
              <div class="col-xs-6">
        	  <h2><?php esc_html_e("Style options", 'ulp');?></h2>
              <p><?php esc_html_e("Choose one of the available templates provided by the system", 'ulp');?></p>
              <h4 class="ulp-input-group-space"><?php esc_html_e("Select Template", 'ulp');?></h4>
             <div class="ulp-input-group-max ulp-margin-bottom">
              	<select id="theme" onChange="ulpPreviewShortcode();" class="form-control m-bot15"><?php
                  $themes = array(
                          'theme_1' => esc_html__('Template', 'ulp') . ' 1',
                      );
                  foreach ($themes as $k=>$v){
                    $selected = ($data ['metas']['theme']==$k) ? 'selected' : '';
                    ?>
                    <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                    <?php
                  }
                ?></select>
              </div>
              <h4 class="ulp-input-group-space"><?php esc_html_e("Color Scheme", 'ulp');?></h4>
              <p><?php esc_html_e("Choose one of the predefined colors", 'ulp');?></p>
             <div class="ulp-input-group-max ulp-margin-bottom">
              	<ul id="colors_ul" class="colors_ul">
                                  <?php
                                      $color_scheme = array('0a9fd8', '38cbcb', '27bebe', '0bb586', '94c523', '6a3da3', 'f1505b', 'ee3733', 'f36510', 'f8ba01');
                                      $i = 0;
                                      foreach ($color_scheme as $color){
                                          $class = ($data ['metas']['color_scheme']==$color) ? 'color-scheme-item-selected' : 'color-scheme-item';
                                          ?>
                                              <li class="<?php echo esc_attr($class);?> ulp-background-color-<?php echo esc_attr($color);?>" onClick="ulpChangeColorScheme(this, '<?php echo esc_attr($color);?>', '#color_scheme');ulpPreviewShortcode();"></li>
                                          <?php
                                          $i++;
                                      }
                                  ?>
                  <div class='ulp-clear'></div>
                              </ul>
                              <input type="hidden" id="color_scheme" value="<?php echo esc_attr($data ['metas']['color_scheme']);?>" />
              </div>
              <h4 class="ulp-input-group-space"><?php esc_html_e("Column Layout", 'ulp');?></h4>
             <div class="ulp-input-group-max ulp-margin-bottom">
              		<select id="columns" onChange="ulpPreviewShortcode();" class="form-control m-bot15"><?php
                  for ($i=1; $i<7; $i++){
                    $selected = ($i==$data ['metas']['columns']) ? 'selected' : '';
                    ?>
                    <option value="<?php echo esc_attr($i);?>" <?php echo esc_attr($selected);?>><?php echo esc_attr($i) . esc_html__(" Columns", 'ulp')?></option>
                    <?php
                  }
                ?></select>
              </div>
              <h4 class="ulp-input-group-space"><?php esc_html_e("Additional display options", 'ulp');?></h4>
              <div class="ulp-input-group-max ulp-margin-bottom">
               		<div class="ulp-user-list-row">
              		<?php $checked = (empty($data ['metas']['align_center'])) ? '' : 'checked';?>
              		<input type="checkbox" id="align_center" <?php echo esc_attr($checked);?> onClick="ulpPreviewShortcode();"/> <?php esc_html_e("Align the Items Centered", 'ulp');?>
            		</div>

           			 <div class="ulp-user-list-row">
              		<?php $checked = ($data ['metas']['include_fields_label']) ? 'checked' : '';?>
             		 <input type="checkbox"  id="include_fields_label" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> />
              		<?php esc_html_e('Show Fields Label', 'ulp');?>
            		</div>
               </div>


              </div>
          </div>
        </div>
        <div class="ulp-line-break"></div>

        <div class="ulp-inside-item">
          <div class="row">
              <div class="col-xs-6">
        	  <h2><?php esc_html_e("Entity management", 'ulp');?></h2>
              <p><?php esc_html_e("How many courses and how will be listed", 'ulp');?></p>

              <div class="input-group ulp-input-group-max ulp-input-group-space">
              	 <span class="input-group-addon" id="basic-addon1"><?php esc_html_e("Total Courses", 'ulp');?></span>
              	 <input type="number" class="form-control" value="<?php echo esc_attr($data ['metas']['num_of_entries']);?>" id="num_of_entries" onKeyUp="ulpPreviewShortcode();" onChange="ulpPreviewShortcode();" min="1" />
              </div>

              <h4 class="ulp-input-group-space"><?php esc_html_e("Order Items", 'ulp');?></h4>
              <p><?php esc_html_e("The List of courses can be ordered by specific criterias and different direction.", 'ulp');?></p>
             <div class="ulp-input-group-max ulp-margin-bottom">
              	<select id="order_by" onChange="ulpPreviewShortcode();" class="form-control m-bot15">
                  <?php
                    $arr = array(
                            'post_date' => esc_html__('Create date','ulp'),
                            'post_title' => esc_html__("Course title", 'ulp'),
                    );
                    foreach ($arr as $k=>$v){
                      $selected = ($data ['metas']['order_by']==$k) ? 'selected' : '';
                      ?>
                      <option value="<?php echo esc_attr($k)?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
                      <?php
                    }
                  ?>
                </select>

                <select id="order_type" onChange="ulpPreviewShortcode();" class="form-control m-bot15 ulp-input-group-space">
                  <?php
                    foreach (array( 'desc'=>'DESC' , 'asc'=>'ASC') as $k=>$v){
                      $selected = ($data ['metas']['order_type']==$k) ? 'selected' : '';
                      ?>
                      <option value="<?php echo esc_attr($k)?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
                      <?php
                    }
                  ?>
                </select>
              </div>

              <h4 class="ulp-input-group-space"><?php esc_html_e("Select by category", 'ulp');?></h4>
              <div class="ulp-user-list-row ulp-input-group-extra-max">
               <div class="ulp-input-group-max ulp-margin-bottom">
                    <?php if (!empty($data ['cats'])):?>
                        <?php foreach ($data ['cats'] as $cat_object):?>
                          <div class="ulp-fields ulp-top-aff-fields-wrapper">
                              <input type="checkbox" onClick="ulpSecondMakeInputhString(this, <?php echo esc_attr($cat_object->id);?>, '#ulp_filter_by_cats');ulpPreviewShortcode();" /> <span class="ulp-top-aff-field-label"><?php echo esc_attr($cat_object->name);?> </span>
          				  	    </div>
                        <?php endforeach;?>
                    <?php endif;?>
                    <input type="hidden" name="ulp_filter_by_cats" id="ulp_filter_by_cats" />
                </div>
              </div>
              <div class="ulp-clear"></div>

              <h4 class="ulp-input-group-space"><?php esc_html_e("Displayed Fields", 'ulp');?></h4>
              <div class="ulp-user-list-row  ulp-input-group-extra-max">
                    <?php $fields_in = ['post_title', 'feat_image', 'price', 'category'];?>
                    <?php $fields = array(
                        'post_title' => esc_html__('Course Title', 'ulp'),
                        'feat_image' => esc_html__('Course Thumbnail', 'ulp'),
                        'price' => esc_html__('Price', 'ulp'),
                        'category' => esc_html__('Category', 'ulp'),
                    );?>
                    <?php foreach ($fields as $k=>$v):?>
                        <div class="ulp-fields  ulp-top-aff-fields-wrapper">
        				  	         <input type="checkbox" <?php echo (in_array($k, $fields_in)) ? 'checked' : '';?> value="<?php echo esc_attr($k);?>" onclick="ulpSecondMakeInputhString(this, '<?php echo esc_attr($k);?>', '#ulp_grid_fields');ulpPreviewShortcode();"> <span class="ulp-top-aff-field-label"><?php echo esc_html($v);?></span>
        				  	    </div>
                    <?php endforeach;?>
                 </div>
                 <input type="hidden" value="<?php echo implode(',', $fields_in);?>" id="ulp_grid_fields" />

               </div>
          </div>
        </div>
        <div class="ulp-line-break"></div>

        <div class="ulp-inside-item">
          <div class="row">
              <div class="col-xs-6">
        	  <h2><?php esc_html_e("Pagination Settings", 'ulp');?></h2>
               <div class="input-group ulp-input-group-max ulp-input-group-space">
                  <span class="input-group-addon" id="basic-addon1"><?php esc_html_e("Entries Per Page", 'ulp');?></span>
                  <input type="number" class="form-control" value="<?php echo esc_attr($data ['metas']['entries_per_page']);?>" id="entries_per_page" onKeyUp="ulpPreviewShortcode();" onChange="ulpPreviewShortcode();"  min="1" />
               </div>
               <div class="ulp-user-list-row ulp-input-group-max ulp-margin-bottom">
                  <h4><?php esc_html_e("Position", 'ulp');?></h4>
                  <select id="pagination_pos" onchange="ulpPreviewShortcode();" class="form-control m-bot15"> <?php
                foreach (array( 'both' => 'Both' , 'top' => 'Top', 'bottom' => 'Bottom') as $k=>$v){
                  ?>
                  <option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
                  <?php
                }
              ?></select>
               </div>
                <div class="ulp-user-list-row ulp-input-group-max ulp-margin-bottom">
                  <h4 class="ulp-top-aff-theme-label"><?php esc_html_e("Theme", 'ulp');?></h4>
                  <select id="general_pagination_theme" onchange="ulpPreviewShortcode();" class="form-control m-bot15"><?php
                foreach (array('ulp-listing-users-pagination-1' => 'Theme One') as $k=>$v){
                  ?>
                  <option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
                  <?php
                }
              ?></select>
                </div>

               </div>
          </div>
        </div>
        <div class="ulp-line-break"></div>

        <div class="ulp-inside-item">
          <div class="row">
              <div class="col-xs-6">
        	  <h2><?php esc_html_e("SlideShow display", 'ulp');?></h2>
              		<div class="ulp-settings-inner ulp-course-grid-slide-box" >
            <div class="ulp-user-list-row">
              <?php $checked = (empty($data ['metas']['slider_set'])) ? '' : 'checked';?>
              <input type="checkbox" <?php echo esc_attr($checked);?> id="slider_set" onClick="ulpCheckboxDivRelation(this, '#slider_options');ulpPreviewShortcode();"/> <h4 class="ulp-course-grid-slide-box-title"><?php echo esc_html__('Activate Slider showcase', 'ulp');?></h4>
                      <div class="extra-info ulp-course-grid-slide-box-mess"><?php echo esc_html__('If the “Slider Showcase” is used, then the “Pagination Showcase” is disabled.', 'ulp');?></div>
            </div>
            <div class="ulp-course-grid-slide-options" id="slider_options" >

               <div class="splt-1">
              <div class="ulp-user-list-row">
                <div class="ulp-label"><?php esc_html_e('Items per Slide:', 'ulp');?></div>
                <div class="ulp-field">
                  <input type="number" min="1" id="items_per_slide" onChange="ulpPreviewShortcode();" onKeyup="ulpPreviewShortcode();" value="<?php echo esc_attr($data ['metas']['items_per_slide']);?>" />
                </div>
              </div>
              <div class="ulp-user-list-row">
                <div class="ulp-label"><?php esc_html_e('Slider Timeout:', 'ulp');?></div>
                <div class="ulp-field">
                  <input type="number" min="1" id="speed" onChange="ulpPreviewShortcode();" onKeyup="ulpPreviewShortcode();" value="<?php echo esc_attr($data ['metas']['speed']);?>" />
                </div>
              </div>
              <div class="ulp-user-list-row">
                <div class="ulp-label"><?php esc_html_e('Pagination Speed:', 'ulp');?></div>
                <div class="ulp-field">
                  <input type="number" min="1" id="pagination_speed" onChange="ulpPreviewShortcode();" onKeyup="ulpPreviewShortcode();" value="<?php echo esc_attr($data ['metas']['pagination_speed']);?>" />
                </div>
              </div>
               <div class="ulp-user-list-row">
                              <div class="ulp-label"><?php esc_html_e('Pagination Theme:', 'ulp');?></div>
                              <div class="ulp-field">
                                <select id="pagination_theme" onChange="ulpPreviewShortcode();" class="ulp-course-grid-pagination-select"><?php
                                  $array = array(
                                            'pag-theme1' => esc_html__('Pagination Theme 1', 'ulp'),
                                            'pag-theme2' => esc_html__('Pagination Theme 2', 'ulp'),
                                            'pag-theme3' => esc_html__('Pagination Theme 3', 'ulp'),
                                          );
                                  foreach ($array as $k=>$v){
                                    $selected = ($k==$data ['metas']['pagination_theme']) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                                    <?php
                                  }
                                ?>
                                  </select>
                              </div>
                          </div>

                            <div class="ulp-user-list-row">
                              <div class="ulp-label"><?php esc_html_e('Animation Slide In', 'ulp');?></div>
                              <div class="ulp-field">
                                  <select onChange="ulpPreviewShortcode();" id="animation_in" class="ulp-course-grid-pagination-select">
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


                          <div class="ulp-user-list-row">
                              <div class="ulp-label"><?php esc_html_e('Animation Slide Out', 'ulp');?></div>
                              <div class="ulp-field">
                                    <select onChange="ulpPreviewShortcode();" id="animation_out" class="ulp-course-grid-pagination-select">
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

              <div class="ulp-user-list-row">
                              <div class="ulp-label"><?php esc_html_e('Additional Options', 'ulp');?></div>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['bullets'])) ? '' : 'checked';?>
                <input type="checkbox" id="bullets" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Bullets", 'ulp');?>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['nav_button'])) ? '' : 'checked';?>
                <input type="checkbox" id="nav_button" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Nav Button", 'ulp');?>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['autoplay'])) ? '' : 'checked';?>
                <input type="checkbox" id="autoplay" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("AutoPlay", 'ulp');?>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['stop_hover'])) ? '' : 'checked';?>
                <input type="checkbox" id="stop_hover" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Stop On Hover", 'ulp');?>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['responsive'])) ? '' : 'checked';?>
                <input type="checkbox" id="responsive" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Responsive", 'ulp');?>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['autoheight'])) ? '' : 'checked';?>
                <input type="checkbox" id="autoheight" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Auto Height", 'ulp');?>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['lazy_load'])) ? '' : 'checked';?>
                <input type="checkbox" id="lazy_load" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Lazy Load", 'ulp');?>
              </div>
              <div class="ulp-user-list-row">
                <?php $checked = (empty($data ['metas']['loop'])) ? '' : 'checked';?>
                <input type="checkbox" id="loop" onClick="ulpPreviewShortcode();" <?php echo esc_attr($checked);?> /> <?php esc_html_e("Play in Loop", 'ulp');?>
              </div>
            </div>

                <div class="ulp-clear"></div>
            </div>
          </div>
               </div>
          </div>
        </div>
      </div>
      </div>






    <div class="ulp-user-list-shortcode-wrapp">
          <div class="content-shortcode">
              <div>
                  <span class="ulp-shortcode-wrapper"><?php echo esc_html__('ShortCode :', 'ulp');?> </span>
                  <span class="the-shortcode"></span>
              </div>
              <div class="ulp-input-group-space">
                  <span class="ulp-shortcode-wrapper"><?php echo esc_html__('PHP Code:', 'ulp');?> </span>
                  <span class="php-code"></span>
              </div>
          </div>
      </div>

      <div class="ulp-user-list-preview">
        <div class="box-title ulp-top-aff-preview-box">
            <h2 class="ulp-top-aff-preview-label"><i class="fa-ulp fa-icon-eyes-ulp"></i><?php echo esc_html__('Preview', 'ulp');?></h2>
            <div class="actions-preview pointer">
               <a class="btn btn-mini content-slideUp ulp-top-aff-preview-link ulp-js-course-grid-list-settings-toggle-preview">
                  <i class="fa-ulp fa-icon-cogs-ulp"></i>
               </a>
            </div>
            <div class="ulp-clear"></div>
        </div>
        <div id="preview" class="ulp-preview"></div>
    </div>

</div>
<span class="ulp-js-courses-grid-preview-shortcode" data-grid_entity_type='courses' ></span>
