<div class="ulp-questionsandanswers-list-item">
  <div class="ulp-questionsandanswers-list-item-top">
      <div class="ulp-questionsandanswers-list-item-author-image">
          <img src="<?php echo DbUlp::getAuthorImage($object->post_author);?>" />
        </div>
        <div class="ulp-questionsandanswers-list-item-question">
        	<div class="ulp-questionsandanswers-list-item-title"><a href="<?php echo Ulp_Permalinks::getForQanda($object->ID);?>"><?php echo stripslashes($object->post_title);?></a></div>
   			<div class="ulp-questionsandanswers-list-item-content"><?php echo esc_ulp_content($object->post_content);?></div>
        </div>

        <div class="ulp-clear"></div>

    </div>
   <div class="ulp-questionsandanswers-list-item-author-name">
            <?php echo DbUlp::getUserFulltName($object->post_author);?>
            <span class="ulp-questionsandanswers-list-item-author-date"> <?php esc_html_e('asked on', 'ulp'); echo esc_html(" "); echo indeed_time_elapsed_string($object->post_date);?></span>
        </div>
    <div class="ulp-questionsandanswers-list-item-no-comments">
    <a href="<?php echo Ulp_Permalinks::getForQanda($object->ID);?>"><i class="fa-ulp fa-no-comments-ulp"></i> <?php echo esc_ulp_content($object->comment_count).' '; esc_html_e('Responses', 'ulp');?></a>
    </div>
    <div class="ulp-clear"></div>
</div>
