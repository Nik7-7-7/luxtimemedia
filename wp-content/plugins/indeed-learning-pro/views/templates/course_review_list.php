<div class="ulp-course-review-list-wrapp">
    <?php if (!empty($data['items'])):?>
        <h2><?php esc_html_e('Course Reviews', 'ulp');?></h2>
        <div class="ulp-course-review-list">
        <?php foreach ($data['items'] as $object):?>
            <?php
                $fullName = $object->full_name;
                $authorImage = $object->authorImage;
                $stars = $object->stars;
                $createdTime = $object->created_time;
                $title = $object->title;
                $content = $object->content;
            ?>
            <?php include $data['single_course_review_template'];?>
        <?php endforeach;?>
      </div>
      <?php if ($data['showMore']):?>
           <div class="ulp-load-more-bttn-wrapp">
          	<div class="ulp-load-more-bttn" id="ulp_course_reviews_load_more" ><?php esc_html_e('Show More', 'ulp');?></div>
      		</div>
	  <?php endif;?>
    <?php else:?>
        <div class="ulp-additional-message"><?php esc_html_e('No Reviews avaialable for this course', 'ulp');?></div>
    <?php endif;?>
</div>
<?php if ($data['showMore']):?>

  <?php wp_enqueue_script( 'ulp-course-reviews-js', ULP_URL . 'assets/js/course_reviews.js', array('jquery'), 3.6, false );?>
  <span class="ulp-js-course-review-list-init" data-slug="<?php echo esc_attr($data['post_slug']);?>" data-hash="<?php echo md5($data['post_slug'] . 'ulp_secret');?>" ></span>

<?php endif;?>
