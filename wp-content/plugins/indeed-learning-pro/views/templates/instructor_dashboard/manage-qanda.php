<?php wp_enqueue_script( 'ulp_public_instructor_actions', ULP_URL . 'assets/js/instructor_actions.js', ['jquery'], 3.6, false );?>
<h3><?php esc_html_e('Q&A', 'ulp');?></h3>

<div class="ulp-instructor-dashboard-bttn">
    <a href="<?php echo esc_url($addNewLink);?>" class="btn btn-primary pointer"><?php esc_html_e('Add New Question', 'ulp');?></a>
</div>

<?php if ($items):?>
   <div class="ulp-instructor-dashboard-qanda-list ulp-instructor-dashboard-list">
    <div class="ulp-instructor-item-list-head">
     <div class="ulp-instructor-item-list-row">
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-title"><?php esc_html_e('Question Title', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-course"><?php esc_html_e('Target Course', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-comment"><?php esc_html_e('Comments', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-status"><?php esc_html_e('Status', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-date"><?php esc_html_e('Date', 'ulp');?></div>
     </div>
    </div>
     <div class="ulp-instructor-item-list-content">
        <?php foreach ($items as $key=>$post):?>
            <?php $object = [];?>
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
                    <?php echo esc_ulp_content($post->post_title);?>
                     <div  class="ulp-instructor-item-list-options">
                        <span><a href="<?php echo add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_qanda', 'postId' => $post->ID], $baseUri);?>"><?php esc_html_e('Edit', 'ulp');?></a></span> |
                        <span class="js-ulp-do-delete-post ulp-delete-link" data-post="<?php echo esc_attr($post->ID);?>"><?php esc_html_e('Delete', 'ulp');?></span>
                    </div>
             </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-course">
                    <?php if ($courseId):?>
                        <?php echo esc_ulp_content('<div class="ulp-instructor-item-list-course-name">' . \DbUlp::getPostTitleByPostId($courseId) . '</div>');?>
                    <?php else :?>
                        <?php $temporaryCourseId = $qandaDbObject->getCourseIdByQanda($post->ID);?>
                        <?php echo esc_ulp_content('<div class="ulp-instructor-item-list-course-name">' . \DbUlp::getPostTitleByPostId($temporaryCourseId) . '</div>');?>
                    <?php endif;?>
                </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-comment">
                    <?php
                        echo esc_ulp_content('<div class="ulp-post-no-comments" >' . \DbUlp::countPostComments($post->ID, false) .' '. esc_html__('comments', 'ulp'). '</div>');
              					echo esc_ulp_content('<div class="ulp-post-no-comments-pending" > (' . \DbUlp::countPostComments($post->ID, 0) .' '. esc_html__('pending', 'ulp'). ')</div>');
                    ?>
              </div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-status">
                     <div  class="ulp-instructor-item-list-status-text  ulp-<?php echo esc_attr($post->post_status);?>"><?php echo esc_html(ucfirst($post->post_status));?></div>
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
