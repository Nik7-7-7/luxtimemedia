<?php wp_enqueue_script( 'ulp_public_instructor_actions', ULP_URL . 'assets/js/instructor_actions.js', ['jquery'], 3.6, false );?>
<h3><?php esc_html_e('Questions', 'ulp');?></h3>

<div class="ulp-instructor-dashboard-bttn">
    <a href="<?php echo esc_url($addNewLink);?>" class="btn btn-primary pointer"><?php esc_html_e('Add New Question', 'ulp');?></a>
</div>

<?php if ($items):?>
    <div class="ulp-instructor-dashboard-question-list ulp-instructor-dashboard-list">
    <div class="ulp-instructor-item-list-head">
     <div class="ulp-instructor-item-list-row">
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-title"><?php esc_html_e('Title', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-quiz"><?php esc_html_e('Quiz', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-type"><?php esc_html_e('Type', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-points"><?php esc_html_e('Points', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-date"><?php esc_html_e('Date', 'ulp');?></div>
     </div>
    </div>
     <div class="ulp-instructor-item-list-content">
        <?php foreach ($items as $key=>$post):?>
		<?php
				$row_class ='';
        if($key%2 == 0){
           $row_class = 'even';
        }else{
             $row_class = 'odd';
        }
			?>
         <div class="ulp-instructor-item-list-row <?php echo esc_attr($row_class);?>">
            <div class="ulp-instructor-item-list-col ulp-instructor-item-list-title">
                  <i class="fa-ulp fa-ulp_question-ulp"></i><span class="ulp-instructor-item-list-name"><a href="<?php echo \Ulp_Permalinks::getForQuestion($post->ID);?>" target="_blank">
                  <?php
                    $limited_content = mb_substr( strip_tags(stripslashes($post->post_content)), 0 , 120);
                    echo esc_ulp_content($limited_content);?></a></span>
                     —
                    <span class="ulp-instructor-item-list-status-<?php echo esc_attr($post->post_status);?>"><?php echo esc_ulp_content(ucfirst($post->post_status));?></span>
                    <div  class="ulp-instructor-item-list-options">
                      <span><a href="<?php echo add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_question', 'postId' => $post->ID], $baseUri);?>"><?php esc_html_e('Edit', 'ulp');?></a></span> |
                      <span><a href="<?php echo add_query_arg(['tab' => 'special-settings', 'type' => 'ulp_question', 'postId' => $post->ID], $baseUri);?>"><?php esc_html_e('Special settings', 'ulp');?></a></span> |
                      <span class="js-ulp-do-delete-post ulp-delete-link" data-post="<?php echo esc_attr($post->ID);?>"><?php esc_html_e('Delete', 'ulp');?></span> |
                      <span><a href="<?php echo \Ulp_Permalinks::getForQuestion($post->ID);?>" target="_blank"><?php esc_html_e('View', 'ulp');?></a></span>
                  </div>
              </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-quiz">
                  <?php
                  $items = DbUlp::getQuizesForQuestionId($post->ID);
      						$str = '';
      						if ($items){
      							foreach ($items as $item){
      								echo esc_ulp_content('<div class="ulp-instructor-item-list-quiz-name">' . DbUlp::getPostTitleByPostId($item['quiz_id']) . '</div>');
      							}
      						}
                  ?>
              </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-type">
                  <?php
                      $opt = array(
          									1 => esc_html__('Free choice', 'ulp'),
          									7 => esc_html__('Fill In', 'ulp'),
          									2 => esc_html__('One Choice', 'ulp'),
          									3 => esc_html__('Multi Choice', 'ulp'),
          									4 => esc_html__('True or False', 'ulp'),
          									5 => esc_html__('Essay', 'ulp'),
          									6 => esc_html__('Sorting answers', 'ulp'),
          									8 => esc_html__('Choose image - single choice', 'ulp'),
          									9 => esc_html__('Choose image - multiple choice', 'ulp'),
          									10 => esc_html__('Matching', 'ulp'),
          						);
          						$type = get_post_meta($post->ID, 'answer_type', true);
          						if ($type){
          							echo esc_html($opt[$type]);
          						}
                  ?>
              </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-points">
                <?php
                  $points = get_post_meta($post->ID, 'ulp_question_points', true);
      						if ($points){
      							echo esc_html($points);
      						}
                ?>
              </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-date">
                  <?php echo ulp_print_date_like_wp($post->post_date, false);?>
             </div>
		 </div>
        <?php endforeach;?>
     </div>
 </div>
<?php endif;?>

<?php if ($pagination):?>
    <?php echo esc_ulp_content($pagination);?>
<?php endif;?>
