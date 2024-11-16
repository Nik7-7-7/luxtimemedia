<div id="posttype-ihc-items" class="posttypediv">
  <div id="tabs-panel-ihc-items" class="tabs-panel tabs-panel-active">
    <ul id="ihc-items-checklist" class="categorychecklist form-no-clear">
      <?php
      $siteUrl = get_option( 'siteurl' );
      $items = array(
                      'login'         => esc_html__( 'Login', 'ihc' ),
                      'register'      => esc_html__( 'Register', 'ihc' )
      );
      $i = 0;
      ?>
      <?php foreach ( $items as $key => $value ) :?>
        <?php $i--;?>
        <li>
          <label class="menu-item-title">
            <input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr($i);?>][menu-item-object-id]" value="<?php echo esc_attr($i);?>" /> <?php echo esc_html($value);?>
          </label>
          <input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr($i);?>][menu-item-type]" value="custom" />
          <input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr($i);?>][menu-item-title]" value="<?php echo esc_attr($value);?>" />
          <input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr($i);?>][menu-item-url]" value="<?php echo esc_url( $siteUrl . '?ihc-modal=' . $key ); ?>" />
        </li>
      <?php endforeach;?>
    </ul>
  </div>
  <p class="button-controls">
    <span class="add-to-menu">
      <button type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'ihc' ); ?>" name="add-post-type-menu-item" id="submit-posttype-ihc-items"><?php esc_html_e( 'Add to menu', 'ihc' ); ?></button>
      <span class="spinner"></span>
    </span>
  </p>
</div>
