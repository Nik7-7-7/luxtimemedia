<form name="<?php echo esc_attr($form_name);?>" id="<?php echo esc_attr($form_id);?>" class="<?php echo esc_attr($form_class);?>" enctype="multipart/form-data" method="post" action="">

    <?php do_action( 'ihc_action_template_form_file_before_fields', $uid, $fields );?>
    
        <!-- loop throught fields -->
        <?php foreach ( $fields as $field ):?>
            <?php
                // setting the value

                $value = isset( $user_data[ $field['name'] ] ) ? $user_data[ $field['name'] ] : '';
                // maybe it's plain text
                if ( empty( $value ) && $field['type'] === 'plain_text' ){
                    $value = $field['plain_text_value'];
                }
                $placeholder = '';
                if ($field['type'] == 'text' || $field['type'] == 'password' || $field['type'] == 'date' || $field['type'] == 'unique_value_text' || $field['type'] == 'single_checkbox'
					|| $field['type'] == 'ihc_invitation_code_field'){
                    if ( $field['required_field'] ){
                        $field['label_inside'] = '*' . $field['label_inside'];
                    }
                    $placeholder =  $field['label_inside'];
                }
                if ( $field['type'] == 'select' || $field['type'] == 'multi_select' || $field['type'] == 'file' || $field['type'] == 'upload_image' || $field['type'] == 'ihc_country'  || $field['type'] == 'ihc_state'
					 || $field['type'] == 'single_checkbox'){
                  $field['parent_field_class'] .= ' ihc-no-backs';
                }
            ?>
            <div class="iump-form-line-register iump-form-<?php echo esc_attr($field['type']);?> <?php echo esc_attr($field['parent_field_class']);?>" id="<?php echo esc_attr($field['parent_field_id']);?>" >

                <?php if ( $placeholder == '' ):?>
                    <label class="iump-labels-register">
                        <?php if ( $field['required_field']  && isset($field['label_inside']) && $field['label_inside'] !=='' ):?>
                            <span class="ihc-required-sign">*</span>
                        <?php endif;?>
                        <?php echo esc_html($field['label_inside']);?>
                    </label>
                <?php endif;?>

                <?php
                echo \Indeed\Ihc\IndeedForms::generateFieldByType( $field['type'], [
                      'name'              => $field['name'],
                      'value'             => isset( $field['value_to_print'] ) ? $field['value_to_print'] : '',
                      'disabled'          => $field['disabled_field'],
                      'multiple_values'   => $field['multiple_values'],
                      'user_id'           => $uid,
                      'sublabel'          => isset( $field['sublabel'] ) ? $field['sublabel'] : '',
                      'class'             => isset( $field['class'] ) ? $field['class'] : '',
                      'form_type'         => $form_type,
                      'is_public'         => true,
                      'ihc_form_type'     => 'edit',
                      'label'             => $field['label_inside'],
                      'placeholder'       => $placeholder,
                ]);
                ?>

                <!-- print the errors if its case -->
                <?php if ( isset( $errors[ $field['name'] ] ) && $errors[ $field['name'] ] !== '' ):?>
                    <div class="ihc-register-notice"><?php echo esc_html($errors[ $field['name'] ]);?></div>
                <?php endif;?>

            </div>
        <?php endforeach;?>
        <!-- end of loop fields -->

        <?php do_action( 'ihc_action_template_form_file_before_submit_button', $uid, $fields );?>

        <div class="iump-submit-form">
            <input type="submit" name="<?php echo esc_attr($submit_bttn_name);?>" value="<?php echo esc_attr($submit_bttn_label);?>" class="button button-primary button-large" id="<?php echo isset( $submit_bttn_id ) ? esc_attr( $submit_bttn_id ) : 'ihc_submit_bttn';?>" data-standard-label="<?php echo esc_attr($submit_bttn_label);?>" data-loading-label="<?php esc_attr_e( 'Please wait...', 'ihc');?>" <?php if ( !empty( $disableSubmit ) ) echo esc_attr('disabled');?>  />
        </div>

        <?php do_action( 'ihc_action_template_form_file_after_submit_button', $uid, $fields );?>

  <?php if ( isset( $extra_fields ) && count( $extra_fields ) > 0 ):?>
      <?php foreach ( $extra_fields as $field ):?>
        <?php
            echo \Indeed\Ihc\IndeedForms::generateFieldByType( $field['type'], [
                  'name'              => $field['name'],
                  'value'             => $field['value'],
                  'user_id'           => $uid,
                  'sublabel'          => '',
                  'class'             => '',
                  'form_type'         => 'edit',
                  'is_public'         => true,
                  'ihc_form_type'     => 'edit',
                  'label'             => '',
            ]);
        ?>
      <?php endforeach;?>
  <?php endif;?>

</form>
