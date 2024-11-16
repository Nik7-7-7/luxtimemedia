
<?php if (isset($data ['show']) && $data ['show'] == 'button'):?>
  <div class="ulp-watch-list-wrapp-button">

  <?php if ($data ['is_on']):?>
   <div class="ulp-watch-list-button ulp-watch-list-active" data-action="remove" id="<?php echo esc_attr('ulp_watch_list_' . $data['course_id']);?>" onClick="ulpLoveCourse(<?php echo esc_attr($data['course_id']);?>, this.id);">
      <i class="fa-ulp fa-watch_list-ulp"  id="wishlist-icon"></i>
  <?php else:?>
   <div class="ulp-watch-list-button ulp-watch-list-noactive" data-action="add" id="<?php echo esc_attr('ulp_watch_list_' . $data['course_id']);?>" onClick="ulpLoveCourse(<?php echo esc_attr($data['course_id']);?>, this.id);">
      <i class="fa-ulp fa-watch_list_not_assign-ulp" id="wishlist-icon"></i>
  <?php endif;?>
  <span><?php esc_html_e('Save to Wishlist','ulp'); ?></span>
  </div>
  </div>
<?php else:?>
<div class="ulp-watch-list-wrapp-icon">
  <?php if ($data ['is_on']):?>
  <span class="ulp-watch-list-button" data-action="remove" id="<?php echo esc_attr('ulp_watch_list_' . $data['course_id']);?>" onClick="ulpLoveCourse(<?php echo esc_attr($data['course_id']);?>, this.id);">
      <i class="fa-ulp fa-watch_list-ulp" data-action="remove" id="<?php echo esc_attr('ulp_watch_list_' . $data['course_id']);?>" onClick="ulpLoveCourse(<?php echo esc_attr($data['course_id']);?>, this.id);"></i>
  <?php else:?>
   <span class="ulp-watch-list-button" data-action="add" id="<?php echo esc_attr('ulp_watch_list_' . $data['course_id']);?>" onClick="ulpLoveCourse(<?php echo esc_attr($data['course_id']);?>, this.id);">
      <i class="fa-ulp fa-watch_list_not_assign-ulp" data-action="add" id="<?php echo esc_attr('ulp_watch_list_' . $data['course_id']);?>" onClick="ulpLoveCourse(<?php echo esc_attr($data['course_id']);?>, this.id);"></i>
  <?php endif;?>
 </span>
</span>
<?php endif;?>
