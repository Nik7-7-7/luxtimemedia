<div class="ulp-single-comment">
    <div class="ulp-single-comment-avatar"><?php
        $avatar = \DbUlp::getAuthorImage(\DbUlp::getUidByUsername($comment->comment_author));
        echo esc_ulp_content("<img src='{$avatar}' width='32' height='32' />");
    ?></div>
    <div class="ulp-single-comment-user-info">
        <?php echo esc_html($comment->comment_author_email);?>
        <div>
            <span class="js-ulp-do-delete-comment ulp-delete-link" data-comment="<?php echo esc_html($comment->comment_ID);?>"><?php esc_html_e('Delete', 'ulp');?></span>
        </div>
    </div>
    <div class="ulp-single-comment-content"><?php echo esc_ulp_content($comment->comment_content);?></div>
    <div class="ulp-single-comment-status"><?php
      if ($comment->comment_approved){
          esc_html_e('Approved', 'ulp');
      } else {
          esc_html_e('Pending', 'ulp');
      }
    ?></div>
</div>
