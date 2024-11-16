<div  class="ulp-qanda-list-wrapper"
      data-slug="<?php echo esc_attr($data['post_slug']);?>"
      data-hash="<?php echo md5($data['post_slug'] . 'ulp_secret');?>"
      data-limit="<?php echo esc_attr($data['limit']);?>"
>
<?php if ($data['items']):?>
    <?php foreach ($data['items'] as $object):?>
        <?php include ULP_PATH . 'views/templates/course/miniatures-single_qanda.php';?>
    <?php endforeach;?>
<?php else :?>
  <div class="ulp-additional-message"><?php esc_html_e('No questions available. Be the first one who post the first question.', 'ulp');?></div>
<?php endif;?>
</div>

<?php if ($data['showMore']):?>
    <?php wp_enqueue_script('ulp-load_more_qanda', ULP_URL . 'assets/js/load_more_qanda.js', ['jquery'], '3.7' );?>
    <div class="ulp-load-more-bttn-wrapp">
      <div class="ulp-load-more-bttn" id="ulp_course_qanda_load_more" ><?php esc_html_e('Show More', 'ulp');?></div>
    </div>
<?php endif;?>
