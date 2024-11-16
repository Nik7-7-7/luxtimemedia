<?php wp_enqueue_script('uap_admin_js', UAP_URL . 'assets/js/admin-functions.js', array('jquery'), 8.3 );?>
<div class="uap-padding">
  <div><strong><?php esc_html_e('Set this Post for affiliate:', 'uap');?></strong></div>

    <div class="input-group">
    		<span class="input-group-addon" id="basic-addon1">Username</span>
    		<input type="text" class="form-control ui-autocomplete-input" id="usernames_search" autocomplete="off">

        <input type="hidden" value="<?php echo esc_attr($data['uap_landing_page_affiliate_id']);?>" name="uap_landing_page_affiliate_id" id="uap_landing_page_affiliate_id" />
        <div id="uap_username_search_tags"><?php
              if ($data['uap_landing_page_affiliate_id']){
              $id = 'uap_username_tag_' . $data['uap_landing_page_affiliate_id'];
              ?>
              <div id="<?php echo esc_attr($id);?>" class="uap-tag-item"><?php echo esc_html($data['affiliate_usename']);?><div class="uap-remove-tag" onclick="uapRemoveTag('<?php echo esc_attr($data['uap_landing_page_affiliate_id']);?>', '#<?php echo esc_attr($id);?>', '#uap_landing_page_affiliate_id');" title="<?php esc_html_e('Removing tag', 'uap');?>">x</div></div>
              <?php
              }
        ?></div>

    </div>

</div>
