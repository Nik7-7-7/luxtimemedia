<?php wp_enqueue_script( 'ulp_public_instructor_actions', ULP_URL . 'assets/js/instructor_actions.js', ['jquery'], 3.6, false );?>
<h3><?php esc_html_e('Your Courses', 'ulp');?></h3>

<div class="ulp-instructor-dashboard-bttn">
    <a href="<?php echo esc_url($addNewLink);?>" class="btn btn-primary pointer"><?php esc_html_e('Add New Course', 'ulp');?></a>
</div>

<?php if ($items):?>
<div class="ulp-instructor-dashboard-course-list  ulp-instructor-dashboard-list">
        <div class="ulp-instructor-item-list-head">
         <div class="ulp-instructor-item-list-row">
        	<div class="ulp-instructor-item-list-col ulp-instructor-item-list-feat-img-wrapp">
            	<div class="ulp-feat-image">
					<?php esc_html_e('Course Image', 'ulp');?>
                </div>
        	</div>
        	<div class="ulp-course-additional-content">
            	<div class="ulp-instructor-item-list-col ulp-instructor-item-list-title"><?php esc_html_e('Title', 'ulp');?></div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-modules"><?php esc_html_e('Curriculum', 'ulp');?></div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-announcement"><?php esc_html_e('Announcements', 'ulp');?></div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-qanda"><?php esc_html_e('Q&A', 'ulp');?></div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-students"><?php esc_html_e('Students', 'ulp');?></div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-points"><?php esc_html_e('Points', 'ulp');?></div>
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-price"><?php esc_html_e('Price', 'ulp');?></div>
            </div>
          </div>
        </div>
        <div class="ulp-instructor-item-list-content">
        <?php foreach ($items as $key=>$post):?>
          <?php $object = new UlpCourse($post->ID, true);?>
          <?php
    				$row_class ='';
            if($key%2 == 0){
               $row_class = 'even';
            }else{
                 $row_class = 'odd';
            }
    			?>
         <div class="ulp-instructor-item-list-row <?php echo esc_attr($row_class);?>">


                  <div class="ulp-instructor-item-list-col ulp-instructor-item-list-feat-img-wrapp">
                  <?php if ($object->FeatureImage()):?>
                      <div class="ulp-feat-image" style= " background-image:url('<?php echo esc_url($object->FeatureImage());?>');"></div>
                  <?php else:?>
                      <div class="ulp-feat-image"></div>
                  <?php endif; ?>
                  </div>

              <div class="ulp-course-additional-content">
                <div class="ulp-instructor-item-list-col ulp-instructor-item-list-title">
                    <span class="ulp-instructor-item-list-name"><a href="<?php echo \Ulp_Permalinks::getForCourse($post->ID);?>" target="_blank"><?php echo esc_html($post->post_title);?></a></span>
                     —
                    <span class="ulp-instructor-item-list-status-<?php echo esc_attr($post->post_status);?>"><?php echo esc_html(ucfirst($post->post_status));?></span>
                     <div  class="ulp-instructor-item-list-options">
                        <span><a href="<?php echo add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_course', 'postId' => $post->ID], $baseUri);?>"><?php esc_html_e('Edit', 'ulp');?></a></span> |
                        <span><a href="<?php echo add_query_arg(['tab' => 'special-settings', 'type' => 'ulp_course', 'postId' => $post->ID], $baseUri);?>"><?php esc_html_e('Special settings', 'ulp');?></a></span> |
                        <span class="js-ulp-do-delete-post ulp-delete-link" data-post="<?php echo esc_attr($post->ID);?>"><?php esc_html_e('Delete', 'ulp');?></span> |
                        <span><a href="<?php echo \Ulp_Permalinks::getForCourse($post->ID);?>" target="_blank"><?php esc_html_e('View', 'ulp');?></a></span>

                    </div>
                    <div class="ulp-course-last-updated"><?php echo esc_html__('Last update: ', 'ulp') . indeed_time_elapsed_string($post->post_modified);?></div>
                </div>
                      <div class="ulp-instructor-item-list-col ulp-instructor-item-list-modules">
                   			 <?php
              					$total_quizes = $object->TotalQuizes();
              					$total_lessons = $object->TotalLessons();
              					if ($total_lessons){?>
              							<div class="ulp-instructor-lesson-box"><?php echo esc_html($total_lessons); ?><span> <?php echo  esc_html__(' Lessons', 'ulp'); ?></span></div>
              					<?php
                             	 }
              					if ($total_quizes){?>
              							<div class="ulp-instructor-quiz-box"><?php echo esc_html($total_quizes); ?><span> <?php echo  esc_html__(' Quizzes', 'ulp'); ?></span></div>
              					<?php
              					}
                   			 ?>
                        </div>
                    <div class="ulp-instructor-item-list-col ulp-instructor-item-list-announcement">
                    	<?php if ($show_announcements):?>

                        <?php
                            $objectAnnouncements = new \Indeed\Ulp\Db\Announcements();
                  					$count = $objectAnnouncements->countAllByCourse($post->ID);
                            echo esc_ulp_content("<a href='" . add_query_arg(['tab' => 'manage', 'type' => 'ulp_announcement', 'courseId' => $post->ID], $baseUri) . "'>" . $count."</a>");
                        ?>
                <?php endif;?>
                    </div>
                    <div class="ulp-instructor-item-list-col ulp-instructor-item-list-qanda">
                     <?php if ($show_qanda):?>
                        <?php
                            $objectQandA = new \Indeed\Ulp\Db\QandA();
                  					$count = $objectQandA->countAllByCourse($post->ID);
                            echo esc_ulp_content("<a href='" . add_query_arg(['tab' => 'manage', 'type' => 'ulp_qanda', 'courseId' => $post->ID], $baseUri) . "'>" . $count."</a>");
                        ?>

                	<?php endif;?>
                    </div>
                    <div class="ulp-instructor-item-list-col ulp-instructor-item-list-students">
                     <?php
                        $countStudents = $object->TotalStudents();
						  if ($countStudents){
								  echo esc_ulp_content(' <i class="fa-ulp fa-users-ulp"></i> ');
								  echo esc_ulp_content("<a href='" . add_query_arg(['tab' => 'list-students', 'courseId' => $post->ID], $baseUri) . "'>" . $countStudents . '/' . $object->MaxEnrolledStudents() . ' '. esc_html__('Students ', 'ulp') . '</a>');
						  } else {
                            esc_html_e('No students yet', 'ulp');
                        }
                    ?>
                    </div>
                    <div class="ulp-instructor-item-list-col ulp-instructor-item-list-points">
                    	<?php
                        $points  = $object->RewardPoints();
              					if ($points){
              						echo esc_html($points) . esc_html__(' points', 'ulp');
              					}
                    ?>
                    </div>
                    <div class="ulp-instructor-item-list-col ulp-instructor-item-list-price">
                    	<span class="price-box">
						<?php
							  $price = $object->Price(true);
							  if ($object->IsFree() == 0){
								  esc_html_e('Free', 'ulp');
							  } else {
								  echo esc_html($price . $currency);
							  }
						  ?>
                  		</span>
                    </div>
              </div>
            </div>
        <?php endforeach;?>
 	</div>
 </div>
<?php endif;?>

<?php if ($pagination):?>
    <?php echo esc_ulp_content($pagination);?>
<?php endif;?>
