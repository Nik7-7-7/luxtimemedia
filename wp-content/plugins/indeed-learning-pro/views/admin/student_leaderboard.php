<div class="ulp-user-list-wrap">
<div class="ulp-page-title"><?php esc_html_e('Student Leaderboard', 'ulp');?>
    </div>



    <div class="ulp-user-list-settings-wrapper">

      <div class="box-title">

        <h3><i class="fa-ulp fa-icon-angle-down-ulp"></i><?php esc_html_e("ShortCode Generator", 'ulp')?></h3>

        <div class="actions pointer">

            <a class="btn btn-mini content-slideUp ulp-js-student-leaderboard-toggle">

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

              <div class=" ulp-input-group-max ulp-margin-bottom">

              	<select id="theme" onChange="ulpPreviewShortcode();" class="form-control m-bot15"><?php

                  $themes = array(

                                  'theme_1' => esc_html__('Template', 'ulp') . ' 1',

                                  'theme_2' => esc_html__('Template', 'ulp') . ' 2',

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

              <div class=" ulp-input-group-max ulp-margin-bottom">

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

              <div class=" ulp-input-group-max ulp-margin-bottom">

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

               <div class=" ulp-input-group-max ulp-margin-bottom">

               		<div class="ulp-user-list-row">

              		<?php $checked = (empty($data ['metas']['align_center'])) ? '' : 'checked';?>

              		<input type="checkbox" id="align_center" <?php echo esc_attr($checked);?> onClick="ulpPreviewShortcode();"/> <?php esc_html_e("Align the Items Centered", 'ulp');?>

            		</div>



            		<!--div class="ulp-user-list-row">

              		<?php $checked = ($data ['metas']['include_fields_label']) ? 'checked' : '';?>


              		<?php esc_html_e('Show Fields Label', 'ulp');?>

            		</div-->

               </div>





              </div>

          </div>

        </div>

        <div class="ulp-line-break"></div>



        <div class="ulp-inside-item">

          <div class="row">

              <div class="col-xs-6">

        	  <h2><?php esc_html_e("Entity management", 'ulp');?></h2>

              <p><?php esc_html_e("How many stundents and how will be listed", 'ulp');?></p>



              <div class="input-group ulp-input-group-max ulp-input-group-space">

              	 <span class="input-group-addon" id="basic-addon1"><?php esc_html_e("Total Students", 'ulp');?></span>

              	 <input type="number" class="form-control" value="<?php echo esc_attr($data ['metas']['num_of_entries']);?>" id="num_of_entries" onKeyUp="ulpPreviewShortcode();" onChange="ulpPreviewShortcode();" min="0" />

              </div>

              <h4 class="ulp-input-group-space"><?php esc_html_e("Order Items", 'ulp');?></h4>

              <p><?php esc_html_e("The List of students can be ordered by specific criterias and different direction.", 'ulp');?></p>

              <div class=" ulp-input-group-max ulp-margin-bottom">

              	<select id="order_by" onChange="ulpPreviewShortcode();" class="form-control m-bot15">

                  <?php

                    $arr = array(

                            'reward_points' => esc_html__('Rewarded Points', 'ulp'),

                            'user_registered' => esc_html__('Register Date','ulp'),

                            'user_login' => esc_html__("UserName", 'ulp'),

                            'user_email' => esc_html__("E-mail Address", 'ulp'),

                            'random' => esc_html__("Random", 'ulp'),

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

         	   <h4 class="ulp-input-group-space"><?php esc_html_e("Displayed Fields", 'ulp');?></h4>

        	   <div class="ulp-user-list-row ulp-top-aff-ulp-user-list-row">

                              <?php $fields_in = ['full_name', 'feat_image', 'points','user_email'];?>

                              <?php $fields = array(

                                  'full_name' => esc_html__('Full Student Name', 'ulp'),

                                  'feat_image' => esc_html__('Photo Avatar', 'ulp'),

                                  'points' => esc_html__('Rewarded Points', 'ulp'),

                                  'user_email' => esc_html__('E-mail Address', 'ulp'),

                                  'user_registered' => esc_html__('User register date', 'ulp')

                              );?>

                              <?php foreach ($fields as $k=>$v):?>

                                  <div class="ulp-fields ulp-top-aff-fields-wrapper">

                  				  	         <input type="checkbox" <?php echo (in_array($k, $fields_in)) ? 'checked' : '';?> value="<?php echo esc_attr($k);?>" onclick="ulpSecondMakeInputhString(this, '<?php echo esc_attr($k);?>', '#ulp_grid_fields');ulpPreviewShortcode();"> <span class="ulp-top-aff-field-label"><?php echo esc_html($v);?></span>

                  				  	    </div>

                              <?php endforeach;?>

                              <input type="hidden" value="<?php echo implode(',', $fields_in);?>" id="ulp_grid_fields" />

                             <div class="ulp-clear"></div>

               </div>



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

                  <input type="number" class="form-control" value="<?php echo esc_attr($data ['metas']['entries_per_page']);?>" id="entries_per_page" onKeyUp="ulpPreviewShortcode();" onChange="ulpPreviewShortcode();" min="1" />

                </div>

                <div class="ulp-user-list-row ulp-input-group-max ulp-margin-bottom">

                  <h4><?php esc_html_e("Position", 'ulp');?></h4>

                  <select id="pagination_pos" onchange="ulpPreviewShortcode();" class="form-control m-bot15"> <?php

                    foreach (array('both' => 'Both' , 'top' => 'Top', 'bottom' => 'Bottom') as $k=>$v){

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



      </div>

      </div>


  <div class="ulp-clear"></div>

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

        <div class="box-title" class="ulp-top-aff-preview-box">

            <h2 class="ulp-top-aff-preview-label"><i class="fa-ulp fa-icon-eyes-ulp"></i><?php echo esc_html__('Preview', 'ulp');?></h2>

            <div class="actions-preview pointer">

               <a class="btn btn-mini content-slideUp ulp-top-aff-preview-link ulp-js-student-leaderboard-preview-toggle">

                  <i class="fa-ulp fa-icon-cogs-ulp"></i>

               </a>

            </div>

            <div class="ulp-clear"></div>

        </div>

        <div id="preview" class="ulp-preview"></div>

    </div>



</div>

<span class="ulp-js-student-leaderboard-preview-shortcode" data-ulp_grid_entity_type="students"></span>
