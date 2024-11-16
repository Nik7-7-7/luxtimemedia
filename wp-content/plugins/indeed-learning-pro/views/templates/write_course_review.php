<div class="ulp-write-course-review-wrapp" id="ulp_write_review">
    <?php if (isset($data['review_inserted'])):?>
        <?php if ($data['review_inserted']):?>
            <div class="ulp-success-message"><?php esc_html_e('Completed!', 'ulp');?></div>
        <?php else :?>
            <div class="ulp-danger-message"><?php esc_html_e('Error', 'ulp');?></div>
        <?php endif;?>
    <?php endif;?>
    <form method="post" >
        <input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />

        <h2><?php esc_html_e('Add new Review', 'ulp');?></h2>
        <div >
            <label><?php esc_html_e('Review Title', 'ulp');?></label>
            <input type="text" name="title" value="" />
        </div>
        <div  class="ulp-write-course-message">
            <label><?php esc_html_e('Message', 'ulp');?></label>
            <textarea name="message"></textarea>
        </div>
        <div class="ulp-write-course-review-stars">
          <div class="<?php echo esc_attr('review_for_' . $data['course_id']);?>">
              <?php for ($i=1; $i<6; $i++):?>
                  <span class="ulp-star-item <?php echo esc_attr('i_' . $i);?>" data-star_num="<?php echo esc_attr($i);?>"><i class="fa-ulp fa-star-o-ulp ulp-star-unselected" ></i></span>
              <?php endfor;?>
          </div>
          <input type="hidden" name="rating" value="" class="js-ulp-course-rating" />
        </div>
        <div >
            <button type="submit" class="ulp-add-new-review-bttn"><?php esc_html_e('Add Review', 'ulp');?></button>
        </div>
    </form>
</div>
