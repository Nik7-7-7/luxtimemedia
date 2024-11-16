<div class="iump-form-line">
<h4> <?php  esc_html_e('WordPress Role', 'ihc'); ?> </h4>
<p><?php  esc_html_e('If is necessary choose a specific wp role for current member', 'ihc'); ?> </p>
    <?php $allValues =ihc_get_wp_roles_list();?>
    <select multiple name="role[]" class='ihc-form-element ihc-form-element-select ihc-form-select ' >

      <?php foreach ( $allValues as $key => $value ):?>
            <?php if ( is_array( $data['role'] ) ):?>
                <option value="<?php echo esc_attr($key);?>" <?php if ( in_array( $key, $data['role'] ) ){
                  echo esc_attr('selected');
                }
                ?> ><?php echo esc_html($value);?></option>
            <?php else: ?>
                <option value="<?php echo esc_attr($key);?>" <?php if ( $key == $data['role'] ){
                  echo esc_attr('selected');
                }
                ?>
                >
                <?php echo esc_html($value);?></option>
            <?php endif;?>
        <?php endforeach;?>
    </select>

</div>
