<?php wp_enqueue_script( 'ulp_public_instructor_actions', ULP_URL . 'assets/js/instructor_actions.js', ['jquery'], 3.6, false );?>
<h3><?php esc_html_e('Lessons', 'ulp');?></h3>

<div class="ulp-instructor-dashboard-bttn">
    <a href="<?php echo esc_url($addNewLink);?>" class="btn btn-primary pointer"><?php esc_html_e('Add New Lesson', 'ulp');?></a>
</div>

<?php if ($items):?>

<div class="ulp-instructor-dashboard-lesson-list ulp-instructor-dashboard-list">
    <div class="ulp-instructor-item-list-head">
     <div class="ulp-instructor-item-list-row">
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-title"><?php esc_html_e('Title', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-course"><?php esc_html_e('Course', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-duration"><?php esc_html_e('Duration', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-rewards"><?php esc_html_e('Rewards', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-date"><?php esc_html_e('Date', 'ulp');?></div>
     </div>
    </div>
     <div class="ulp-instructor-item-list-content">
        <?php foreach ($items as $key=>$post):?>
            <?php $object = new UlpLesson($post->ID, TRUE);?>
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
                    <i class="fa-ulp fa-ulp_lesson-ulp"></i><span class="ulp-instructor-item-list-name"><a href="<?php echo \Ulp_Permalinks::getForLesson($post->ID);?>" target="_blank"><?php echo esc_ulp_content($post->post_title);?></a></span>
                     —
                    <span class="ulp-instructor-item-list-status-<?php echo esc_attr($post->post_status);?>"><?php echo esc_html(ucfirst($post->post_status));?></span>
                    <div  class="ulp-instructor-item-list-options">
                        <span><a href="<?php echo add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_lesson','postId' => $post->ID], $baseUri);?>"><?php esc_html_e('Edit', 'ulp');?></a></span> |
                        <span><a href="<?php echo add_query_arg(['tab' => 'special-settings', 'type' => 'ulp_lesson','postId' => $post->ID], $baseUri);?>"><?php esc_html_e('Special settings', 'ulp');?></a></span> |
                        <span class="js-ulp-do-delete-post ulp-delete-link" data-post="<?php echo esc_attr($post->ID);?>"><?php esc_html_e('Delete', 'ulp');?></span> |
						<span><a href="<?php echo \Ulp_Permalinks::getForLesson($post->ID);?>" target="_blank"><?php esc_html_e('View', 'ulp');?></a></span>
                    </div>
                </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-course">
                    <?php
                    $course_id = $object->LessonParent();
                    if ($course_id){
                      $course_label = DbUlp::getPostTitleByPostId($course_id);
                      if ($course_label){
                          echo esc_ulp_content('<div class="ulp-instructor-item-list-course-name">'.$course_label.'</div>');
                      } else {
                          echo esc_ulp_content('-');
                      }
                    } else {
                      echo esc_ulp_content('-');
                    }
                    ?>
                </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-duration">
                  <?php
                      $types = ulp_get_time_types();
                      $duration = $object->Duration();
                      if ($duration && isset($types[$object->DurationType()])){
                        echo esc_html($duration) . ' ' . esc_html($types[$object->DurationType()]);
                      } else {
                        echo  esc_html__('Not set', 'ulp');
                      }
                  ?>
                </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-rewards">
                    <?php
                				$points = $object->RewardPoints();
                				if($points > 0){
                            echo esc_html($points) . esc_html__(' points', 'ulp');
                        } else {
                            echo esc_ulp_content('-');
                        }
                    ?>
                </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-date">
                    <?php echo ulp_print_date_like_wp($post->post_date, FALSE);?>
                </div>
           </div>
        <?php endforeach;?>
     </div>
 </div>
<?php endif;?>

<?php if ($pagination):?>
    <?php echo esc_ulp_content($pagination);?>
<?php endif;?>
