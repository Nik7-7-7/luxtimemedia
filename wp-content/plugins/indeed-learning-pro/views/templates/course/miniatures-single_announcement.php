<div class="ulp-announcements-list-item">
  <div class="ulp-announcements-list-item-top">
      <div class="ulp-announcements-list-item-author-image">
          <img src="<?php echo DbUlp::getAuthorImage($object->post_author);?>" />
        </div>
        <div class="ulp-announcements-list-item-author-name">
            <?php echo DbUlp::getUserFulltName($object->post_author);?>
            <div class="ulp-announcements-list-item-author-date"><?php esc_html_e('posted', 'ulp'); echo esc_html(" "); echo indeed_time_elapsed_string($object->post_date);?></div>
        </div>
        <div class="ulp-clear"></div>

    </div>
   <div class="ulp-announcements-list-item-title"><a href="<?php echo Ulp_Permalinks::getForAnnouncement($object->ID);?>"><?php echo esc_ulp_content(stripslashes($object->post_title));?></a></div>
    <div class="ulp-announcements-list-item-content"><?php echo esc_ulp_content($object->post_content);?></div>
    <div class="ulp-announcements-list-item-no-comments">
        <a href="<?php echo esc_ulp_content(Ulp_Permalinks::getForAnnouncement($object->ID));?>"><i class="fa-ulp fa-no-comments-ulp"></i> <?php echo esc_ulp_content($object->comment_count).' '; esc_html_e('Comments', 'ulp');?></a>
    </div>
</div>
