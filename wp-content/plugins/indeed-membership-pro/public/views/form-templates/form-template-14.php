<form name="<?php echo esc_attr($form_name);?>" id="<?php echo esc_attr($form_id);?>" class="<?php echo esc_attr($form_class);?>" enctype="multipart/form-data" method="post" action="">

    <?php do_action( 'ihc_action_template_form_file_before_fields', $uid, $fields );?>
    
        <!-- loop throught fields -->
        <?php foreach ( $fields as $field ):?>
            <div class="iump-form-line-register iump-form-<?php echo esc_attr($field['type']);?> <?php echo esc_attr($field['parent_field_class']);?>" id="<?php echo esc_attr($field['parent_field_id']);?>" >
                <?php if ( empty( $field['hide_outside_label'] ) ):?>
                    <label class="iump-labels-register">
                        <?php if ( $field['required_field'] ):?>
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
            <div class="iump-register-row-left">
                  <input type="submit" name="<?php echo esc_attr($submit_bttn_name);?>" value="<?php echo esc_attr($submit_bttn_label);?>" class="button button-primary button-large" id="<?php echo isset( $submit_bttn_id ) ? esc_attr( $submit_bttn_id ) : 'ihc_submit_bttn';?>" data-standard-label="<?php echo esc_attr($submit_bttn_label);?>" data-loading-label="<?php esc_attr_e( 'Please wait...', 'ihc');?>" <?php if ( !empty( $disableSubmit ) ) echo esc_attr('disabled');?>  />
            </div>
            <?php if ( empty( $uid ) ):?>
                <div class="iump-register-row-right">
                    <div class="ihc-login-link"><a href="<?php echo ihcLoginLink();?>"><?php esc_html_e('LogIn', 'ihc');?></a></div>
                </div>
            <?php endif;?>
            <div class="iump-clear"></div>
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
