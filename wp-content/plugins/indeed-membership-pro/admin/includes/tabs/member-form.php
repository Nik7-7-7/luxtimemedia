<form action="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=users' );?>" method="post" class="ihc-form-create-edit" enctype="multipart/form-data" >
    <?php if ( $data['fields'] ) : ?>

        <?php foreach ( $data['fields'] as $field ): ?>
            <?php $attr = [];?>
            <?php $fieldName = isset( $field['name'] ) ? $field['name'] : '';?>
            <?php $fieldValue = isset( $data['userData'][$fieldName] ) ? $data['userData'][$fieldName] : '';?>
			<?php $field['label'] = isset( $field['native_wp'] ) && $field['native_wp'] ? esc_html__( $field['label'], 'ihc') : ihc_correct_text( $field['label'] ); ?>
            <?php
            if ( empty( $field['other_args'] ) ){
                $field['other_args'] = '';
            }
            if ( empty( $field['disabled'] ) ){
                $field['disabled'] = '';
            }
            if ( empty( $field['id'] ) ){
                $field['id'] = '';
            }
            if ( empty( $field['class'] ) ){
                $field['class'] = '';
            }
            if ( empty( $field['placeholder'] ) ){
                $field['placeholder'] = '';
            }
            ?>
            <?php switch( $field['type'] ):
                    case 'text':
                    case 'unique_value_text':
                    case 'conditional_text':?>
                      <div class="iump-form-line-register iump-form-text">
                          <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                          <input class="ihc-form-element-text" type="text" name="<?php echo esc_attr($fieldName);?>" value="<?php echo esc_attr($fieldValue);?>" />
                      </div>
                      <?php break;?>

                    <?php case 'number':?>
                      <div class="iump-form-line-register iump-form-number">
                          <label class="iump-labels-register"><?php echo esc_attr($field['label']);?></label>
                          <input class="ihc-form-element-number" type="number" name="<?php echo esc_attr($fieldName);?>" value="<?php echo esc_attr($fieldValue);?>" />
                      </div>
                      <?php break;?>

                    <?php case 'textarea':?>
                      <div class="iump-form-line-register iump-form-textarea">
                          <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                          <textarea class="iump-form-textarea ihc-form-element-textarea" name="<?php echo esc_attr($fieldName);?>"><?php echo esc_html($fieldValue);?></textarea>
                      </div>
                      <?php break;?>

                    <?php case 'password':?>
                      <div class="iump-form-line-register iump-form-password">
                          <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                          <input class="ihc-form-element-password" type="password" name="<?php echo esc_attr($fieldName);?>" value="<?php echo esc_attr($fieldValue);?>" />
                        </div>
                        <?php break;?>

                       <?php case 'single_checkbox':?>
                              <?php $checked = empty($fieldValue) ? '' : 'checked';?>
                              <div class="iump-form-line-register iump-form-single_checkbox">
                              <div class="ihc-tos-wrap" id="<?php echo  $field['id'];?>">
                				          <input type="checkbox" value="1" name="<?php echo esc_attr($fieldName);?>" class="<?php echo esc_attr($field['class']);?>" <?php echo esc_attr($checked);?>  />
                								  <?php echo (isset($field['label'])) ? esc_html($field['label']) : '';?>
                              </div>
                            </div>
                          <?php break;?>

                          <?php case 'checkbox':?>
                    					<?php
                              if ( empty ( $field['values'] ) ){
                                 break;
                              }
                              ?>
                              <div class="iump-form-line-register iump-form-checkbox">
                                <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                              <div class="iump-form-checkbox-wrapper" id="<?php echo esc_attr('ihc_checkbox_parent_' . rand(1,1000));?>">
                                <?php foreach ($field['values'] as $v):
                      						if (is_array( $fieldValue ) ){
                      							$checked = ( in_array( $v, $fieldValue ) ) ? 'checked' : '';
                      						} else {
                      							$checked = ( $v == $fieldValue ) ? 'checked' : '';
                      						}
                      						$id_field = (isset($field['id']) && $field['id'] != "" ) ? 'id="' . esc_attr($field['id']) . '"' : '';
                                  ?>
                                  <div class="iump-form-checkbox">
                          						<input type="checkbox" name="<?php echo esc_attr($fieldName);?>[]" <?php echo esc_attr($id_field);?> class="<?php echo esc_attr($field['class']);?>" <?php echo esc_attr($checked);?> value="<?php echo esc_attr(ihc_correct_text($v, false, true ));?>" <?php echo  esc_attr($field['other_args']);?> <?php echo esc_attr($field['disabled']);?>  />
                          						<?php echo esc_html(ihc_correct_text($v));?>
                      						</div>
                      					<?php endforeach;?>
                              </div>
                            </div>
                              <?php	break;?>

                            <?php case 'radio':?>
                      					<?php
                                if ( empty ( $field['values'] ) ){
                                   break;
                                }
                                ?>
                                <div class="iump-form-line-register iump-form-radio">
                                  <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                                <div class="iump-form-checkbox-wrapper" id="<?php echo esc_attr('ihc_checkbox_parent_' . rand(1,1000));?>">
                                <?php foreach ($field['values'] as $v):
                      							 $checked = ($v==$fieldValue) ? 'checked' : '';
                      						   $id_field = (isset($field['id']) && $field['id'] != "" ) ? 'id="' . esc_attr( $field['id'] ) . '"' : '';
                                  ?>
                                  <div class="iump-form-checkbox">
                          						<input type="radio" name="<?php echo esc_attr($fieldName);?>" <?php echo esc_attr($id_field);?> class="<?php echo esc_attr($field['class']);?>" <?php echo esc_attr($checked);?> value="<?php echo esc_attr( ihc_correct_text($v, false, true ) );?>" <?php echo  esc_attr($field['other_args']);?> <?php echo esc_attr($field['disabled']);?>  />
                          						<?php echo esc_html(ihc_correct_text($v));?>
                      						</div>
                      					<?php endforeach;?>
                                </div>
                              </div>
                              <?php break;?>

                            <?php case 'select':?>
                                <?php
                                if ( empty ( $field['values'] ) ){
                                   break;
                                }
                                ?>
                                <div class="iump-form-line-register iump-form-select">
                                    <?php $id_field = (isset($field['id']) && $field['id'] != "" ) ? 'id="' . esc_attr($field['id']) . '"' : '';?>
                                    <label class="iump-labels-register"><?php echo esc_html( $field['label'] );?></label>
                                    <select name="<?php echo esc_attr($fieldName);?>" <?php echo esc_attr($id_field);?> class="iump-form-select ihc-form-element-select<?php echo esc_attr($field['class']);?>" <?php echo esc_attr($field['other_args']);?> <?php echo esc_attr($field['disabled']);?> >
                                        <?php foreach ( $field['values'] as $k=>$v ):?>
                                            <?php $selected = ($v==$fieldValue) ? 'selected' : '';?>
                                            <option value="<?php echo esc_attr($v);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html(ihc_correct_text( $v, false, true ));?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                                <?php break;?>

                            <?php case 'multi_select':?>
                                <?php
                                if ( empty ( $field['values'] ) ){
                                   break;
                                }
                                ?>
                                <div class="iump-form-line-register iump-form-multi_select">
                                  <?php $id_field = (isset($field['id']) && $field['id'] != "" ) ? 'id="' . esc_attr($field['id']) . '"' : '';?>
                                      <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                                      <select name="<?php echo esc_attr($fieldName);?>[]" <?php echo esc_attr($id_field);?> class="iump-form-multiselect ihc-form-element-multi_select <?php echo esc_attr($field['class']);?>" <?php echo esc_attr($field['other_args']);?> <?php echo esc_attr($field['disabled']);?> multiple >
                                          <?php foreach ( $field['values'] as $k=>$v ):?>
                                              <?php
                                                if (is_array($fieldValue)){
                                    							$selected = (in_array($v, $fieldValue)) ? 'selected' : '';
                                    						} else {
                                    							$selected = ($v==$fieldValue) ? 'selected' : '';
                                    						}
                                              ?>
                                              <option value="<?php echo esc_attr($v);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html( ihc_correct_text( $v, false, true ) );?></option>
                                          <?php endforeach;?>
                                      </select>
                                    </div>
                                  <?php break;?>

                              <?php case 'ihc_country':?>
                                  <div class="iump-form-line-register iump-form-ihc_country iump-form-ihc_country-wrapper"><?php
                            				wp_enqueue_style( 'ihc_select2_style' );
                            				wp_enqueue_script( 'ihc-select2' );

                            				if (empty($field['id'])){
                            					$field['id'] = esc_attr($field['name']) . '_field';
                            				}
                            				$countries = ihc_get_countries();
                            				$update_cart = 'ihcUpdateCart();';
                            				if (isset($field['form_type']) && $field['form_type']=='edit'){
                            					$update_cart = '';
                            				}

                            				$onchange = 'onChange="ihcUpdateStateField();' . esc_attr($update_cart) . '"';

                            				if ( empty( $fieldValue ) ){
                            						$fieldValue = ihcGetDefaultCountry();
                            				}
                                    ?>
                                    <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                                    <select name="<?php echo esc_attr($fieldName);?>" id="<?php echo  esc_attr($field['id']);?>" <?php echo esc_attr($onchange);?> >
                                    <?php	foreach ($countries as $k=>$v): ?>
                            					<?php $selected = ($fieldValue==$k) ? 'selected' : '';?>
                                      <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_attr($v);?></option>
                            				<?php endforeach;?>

                                    </select>
                                    <ul id="ihc_countries_list_ul" class="ihc-display-none"></ul>
                            				<?php if ( empty( $field['is_modal'] ) ):?>
                                      <span class="ihc-js-countries-list-data"
                                            data-selector="<?php echo  esc_attr( "#" . $field['id'] );?>"
                                            data-placeholder="<?php esc_html_e( "Select Your Country", 'ihc' );?>"
                                      ></span>
                            				<?php endif;?>
                                    </div>
                            				<?php break;?>

                            		<?php case 'ihc_state':?>
                                    <div class="iump-form-line-register iump-form-ihc_state iump-form-ihc_state-wrapper">
                                        <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                                				<input class="ihc-form-element-ihc_state" type="text" onBlur="ihcUpdateCart();" name="<?php echo esc_attr($field['name']);?>"
                                            <?php echo isset( $field['id'] ) ? 'id="' . esc_attr( $field['id'] ) . '"' : '';?>" class="<?php echo isset( $field['class'] ) ? esc_attr($field['class']) : '';?>"
                                            value="<?php echo isset( $fieldValue ) ? esc_attr(ihc_correct_text( $fieldValue )) : '';?>" placeholder="<?php echo isset( $field['placeholder'] ) ? esc_attr($field['placeholder']) : '';?>"
                                            <?php echo isset( $field['other_args'] ) ? esc_attr($field['other_args']) : '';?> <?php echo isset( $field['disabled'] ) ? esc_attr($field['disabled']) : '';?>
                                        />
                                    </div>
                            				<?php break;?>

                                <?php case 'date':?>
                                <div class="iump-form-line-register iump-form-date">
                                  <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                                    <?php
                              				wp_enqueue_script('jquery-ui-datepicker', ['jquery'] );
                              				if (empty($field['class'])){
                              					$field['class'] = 'ihc-date-field';
                              				}

                              				global $ihc_jquery_ui_min_css;
                              				if (empty($ihc_jquery_ui_min_css)){
                              					$ihc_jquery_ui_min_css = true;
                                        ?>
                                        <link rel="stylesheet" type="text/css" href="<?php echo esc_url( IHC_URL  . 'admin/assets/css/jquery-ui.min.css' );?>"/>
                                        <?php
                              				}

                              				if (empty($field['callback'])){
                              					$field['callback'] = '';
                              				}

                                      ?>

                                      <input type="text" value="<?php echo esc_attr($fieldValue);?>" name="<?php echo esc_attr($fieldName);?>" id="<?php echo esc_attr($field['id']);?>" class="iump-form-datepicker <?php echo esc_attr($field['class']);?>"
                                      <?php echo isset( $field['other_args'] ) ? esc_attr($field['other_args']) : '';?> <?php echo isset( $field['disabled'] ) ? esc_attr($field['disabled']) : '';?>
                                      placeholder="<?php echo esc_attr($field['placeholder']);?>" />
                                      <span class="ihc-js-member-form-datepicker-data" data-selector="<?php echo esc_ump_content('.iump-form-datepicker.') . esc_attr($field['class']);?>"
                                            data-callback="<?php echo esc_attr($field['callback']);?>"></span>

                                  </div>
                              		<?php	break;?>

                              <?php case 'upload_image':?>
                                  <div class="iump-form-line-register iump-form-upload_image">
                                  <label class="iump-labels-register"><?php echo esc_html( $field['label'] );?></label>
                                  <div class="ihc-upload-image-wrapper">
                                    <?php
                              					$attr = $field;
                              					$attr['rand'] = rand(1, 10000);
                              					$attr['imageClass'] = 'ihc-member-photo';
                                        $attr['value'] = $fieldValue;
                              					if (empty($data['user_id'])){
                              					 		$attr['user_id'] = -1;
                              					}
                              					$attr['imageUrl'] = '';
                              					if ( !empty($attr['value']) ){
                              							if (strpos($attr['value'], "http")===0){
                              									$attr['imageUrl'] = $attr['value'];
                              							} else {
                              									$tempData = \Ihc_Db::getMediaBaseImage($attr['value']);
                              									if (!empty($tempData)){
                              										$attr['imageUrl'] = $tempData;
                              									}
                              							}
                              					}
                              					$viewObject = new \Indeed\Ihc\IndeedView();
                                                                $uploadImageHTML = $viewObject->setTemplate( IHC_PATH . 'admin/includes/tabs/upload_image.php' )->setContentData( $attr )->getOutput();
                                                                echo esc_ump_content($uploadImageHTML);
                                      ?>
                                    </div>
                                  </div>
                            			<?php break;?>

                                  <?php case 'file':?>
                                      <?php
                                          include IHC_PATH . 'admin/includes/tabs/upload_file.php';
                                      ?>
                            				<?php break;?>
                              <?php case 'plain_text':
                              		echo ihc_correct_text( $field['plain_text_value'] );
                                break;?>

            <?php endswitch;// end of switch ?>
            <?php if ( !empty( $attr['sublabel'] ) ):?>
                <label class="iump-form-sublabel"><?php echo ihc_correct_text( $attr['sublabel'] );?></label>
            <?php endif;?>
        <?php endforeach;?>
    <?php endif;?>
    <?php if ( $data['uid'] ) :?>
        <input type="hidden" name="ID" value="<?php echo esc_attr( $data['uid'] );?>" />
    <?php endif;?>

    <?php
    $attributes = [
      'user_levels' 			=> $data['userSubscriptions'],
      'userLevelsArray'		=> ( $data['userSubscriptions'] && $data['userSubscriptions']>-1 ) ? explode( ',', $data['userSubscriptions'] ) : array(),
      'uid'								=> $data['uid'],
      'subscriptions'     => $data['subscriptions'],
    ];
    $view = new \Indeed\Ihc\IndeedView();
    $membershipMemembershupPlanM = $view->setTemplate( IHC_PATH . 'admin/includes/tabs/user-membership-plans-management.php')
              ->setContentData( $attributes, true )
              ->getOutput();
    echo esc_ump_content($membershipMemembershupPlanM);
    ?>

    <div class="ihc-admin-edit-user-additional-settings-wrapper">
        <?php // Wp Role ?>
        <h2><?php esc_html_e('Additional Settings','ihc');?></h2>
        <p><?php esc_html_e('Extra settings available for Administration purpose','ihc');?></p>

        <?php
            include IHC_PATH . 'admin/includes/tabs/custom_banner.php';
            include IHC_PATH . 'admin/includes/tabs/register-wp_role.php';
        ?>

        <?php
        // Overview Post Select
        $default_pages_arr = ihc_return_meta_arr('general-defaults');
        $default_pages_arr = array_diff_key($default_pages_arr, array(
                                        'ihc_general_redirect_default_page'			=> '',
                                        'ihc_general_logout_redirect'						=> '',
                                        'ihc_general_register_redirect'					=> '',
                                        'ihc_general_login_redirect'						=> ''
                            )
        );//let's exclude the redirect pages
        $args = array(
            'posts_per_page'   => 100,
            'offset'           => 0,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'post_type'        => array( 'post', 'page' ),
            'post_status'      => 'publish',
            'post__not_in'	   => $default_pages_arr,
        );

        $posts_array = get_posts( $args );
        $arr['-1'] = '...';
        foreach ($posts_array as $k=>$v){
          $arr[$v->ID] = $v->post_title;
        }
        ?>

        <div class="iump-form-line">
            <h4><?php esc_html_e('Custom Dashboard message', 'ihc');?></h4>
            <p><?php esc_html_e('The Dashboard section from the Account Page can display general content or specific content for each member. You may choose a specific post content for this member or leave the default setup.', 'ihc');?></p>
            <select name="ihc_overview_post" class='ihc-form-element ihc-form-element-select ihc-form-select '>
                <?php foreach ( $arr as $key=>$value ):?>
                    <option value="<?php echo esc_attr($key);?>" <?php echo ( $key == $data['ihc_overview_post'] ) ? 'selected' : '' ;?> ><?php echo esc_html( $value );?></option>
                <?php endforeach;?>
            </select>
        </div>
        </div>
        <div class="iump-form-line iump-no-border">
          <div class="ihc-wrapp-submit-bttn">
              <input type="submit" name="ihc_save_member" id="ihc_submit_bttn" class="button button-primary button-large" value="<?php esc_html_e( 'Save Changes', 'ihc');?>" />
          </div>
      </div>

</form>
