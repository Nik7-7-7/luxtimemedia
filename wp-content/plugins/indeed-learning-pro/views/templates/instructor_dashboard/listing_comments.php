<span class="ulp-js-instructor-listing-comments" ></span>
<h3><?php esc_html_e('Comments', 'ulp');?></h3>

<div class="ulp-comment-box">
    <div>
        <div class="btn btn-primary pointer ulp-js-add-new-comment"><?php esc_html_e('Add comment', 'ulp');?></div>
    </div>
    <div class="ulp-display-none ulp-comment-form" data-post-id="<?php echo esc_attr($postId);?>" data-hash="<?php echo md5($postId . 'ulp_secret');?>">
        <div>
            <label><?php esc_html_e('Add new comment', 'ulp');?></label>
            <textarea name="content" class="ulp-instrutor-ann-post-content" id="add_new_comment"></textarea>
        </div>
        <div class="btn btn-primary pointer ulp-js-submit-comment"><?php esc_html_e('Submit', 'ulp');?></div>
        <div class="btn btn-primary pointer ulp-js-hide-comment-form"><?php esc_html_e('Cancel', 'ulp');?></div>
    </div>
</div>

<div class="ulp-list-comments">
    <?php if ($comments):?>
        <?php foreach ($comments as $comment):?>
            <?php include ULP_PATH . 'views/templates/instructor_dashboard/miniatures-single_comment.php';?>
        <?php endforeach;?>
    <?php else :?>
        <h3><?php esc_html_e('No comments yet!', 'ulp');?></h3>
    <?php endif;?>
</div>
<?php if ($showLoadMoreBttn):?>
  <div class="ulp-load-more-bttn-wrapp">
    <div class="js-ulp-load-more-comments ulp-load-more-bttn"
        data-limit="<?php echo esc_attr($limit);?>"
    ><?php esc_html_e('Show More', 'ulp');?></div>
  </div>
<?php endif;?>
