<?php
/**
 * Template Course
 */
get_header();
global $post;
$course = new UlpCourse($post->ID);
?>

<div class="page-content ulp-course-wrapp ulp-course-enrolled">
	 <h1><?php the_title();?></h1>
        <div class="ulp-top-section">
        <?php if( $course->IsFeatured()): ?>
            <span class="ulp-sale"><span class="ulp-text-sale"><?php esc_html_e('NEW', 'ulp');?></span></span>
        <?php endif;?>
		<?php if ($course->FeatureImage()):?>
				<div class="ulp-feat-img-wrapp">
                	<div class="ultp-feat-img-single-course" style= " min-width:<?php echo Ulp_Global_Settings::get('ulp_singlecourse_imagesize_width');?>px; height:<?php echo Ulp_Global_Settings::get('ulp_singlecourse_imagesize_height');?>px; background-image:url('<?php echo esc_url($course->FeatureImage());?>');">
                    </div>
				</div>
		<?php endif;?>
        </div>
	<div class="ulp-main-section">
			<div class="ulp-left-content">
            	<div class="ulp-short-description"><?php  echo esc_ulp_content($course->Excerpt());?></div>
            	<div class="ulp-course-progress-wrapp">
                	<h2><?php echo esc_html__('You are Enrolled ', 'ulp'); ?><span><?php echo esc_html__('starting with ', 'ulp'); ?><?php echo esc_html($course->EntrolledDate(false));?></span></h2>
                    <div class="ulp-progress-details"><?php echo esc_html__('In Progress ', 'ulp'); ?><?php echo esc_ulp_content($course->Progress());?><span><?php echo esc_html(' (') . esc_html__(' you must complete the course until ', 'ulp').$course->ExpireDate(false).' )';?></span></div>
            		<div class="ulp-progress-bar">
									<div class="ulp-progress-completed" style= " width:<?php echo esc_ulp_content($course->Progress());?>;"></div>
                </div>
				<?php echo do_shortcode("[ulp-finish-course-bttn course_id={$post->ID}]");?>

                	<div class="ulp-course-results">
					<?php $course_results = $course->CourseResult();
                        if($course_results['grade'] > 0){
                            //echo esc_html__('Your course grade is: ', 'ulp').'<strong>'.$course_results['grade'].'/'.$course->PassingValue().'</strong>.';
							if($course_results['course_passed'] == 1){
						 		echo esc_html__(' You have Passed the Course!', 'ulp');
						 	}elseif($course_results['course_passed'] == 0){
						 		echo esc_html__(' You have not passed the Course!', 'ulp');
						 	}
                        }

                    /*if ($course->IsEntrolled() && $course->IsCompleted()):?>
                            <h3><?php esc_html_e('Course Completed', 'ulp');?></h3>
                    <?php endif; */ ?>
					</div>

            	</div>
  			 <?php echo do_shortcode("[ulp_watch_list_bttn course_id={$post->ID} show='button']");?>
			  <?php echo do_shortcode("[ulp_notes_form course_id={$post->ID}]");?>
            </div>
            <div class="ulp-right-sidebar">
            <ul class="ulp-top-details">
                  <li>
                      <div class="ulp-avatar">
                          <img src="<?php echo esc_url($course->AuthorImage()); ?>" />
                      </div>
                      <label><?php echo esc_html__('Teacher ', 'ulp'); ?></label>
                      <strong><?php  echo esc_html($course->Author(FALSE));?></strong>
                  </li>
                  <li>
                      <label><?php echo esc_html__('Students ', 'ulp'); ?></label>
                      <strong><?php  echo esc_html($course->TotalStudents()) . '/'.esc_html($course->MaxEnrolledStudents()).' '. esc_html__('Students ', 'ulp');?></strong>
                  </li>
                  <li>
                      <label><?php echo esc_html__('Category ', 'ulp'); ?></label>
                      <strong><?php  echo  $course->Categories(TRUE);?></strong>
                  </li>
                  <li>
                      <label><?php echo esc_html($course->TotalModules()) . esc_html__(' Modules ', 'ulp') ;?></label>
                      <strong><?php echo esc_html($course->TotalLessons()) . esc_html__(' Lessons ', 'ulp') . '/'. esc_html($course->TotalQuizes()) . esc_html__(' Quizes ', 'ulp');?></strong>
                  </li>
                  <?php if($course->Difficulty() != ''): ?>
                  <li>
                      <label><?php echo esc_html__('Difficulty ', 'ulp'); ?></label>
                      <strong><?php  echo  $course->Difficulty();?></strong>
                  </li>
                  <?php endif; ?>
                  <?php if($course->CourseTimePeriod() != ''): ?>
                  <li>
                      <label><?php echo esc_html__('Estimated Time ', 'ulp'); ?></label>
                      <strong><?php  echo  $course->CourseTimePeriod();?></strong>
                  </li>
                  <?php endif; ?>
                  <?php if($course->RewardPoints()): ?>
                  <li>
                      <label><?php echo esc_html__('Reward Points', 'ulp'); ?></label>
                      <strong><?php  echo  $course->RewardPoints(). esc_html__(' points', 'ulp');?></strong>
                  </li>
                  <?php endif; ?>
              </ul>
            </div>
     </div>

     <div class="ulp-single-course-menu-wrapper">
     	<?php do_action('ulp_before_print_single_course', $post->ID);?>
     </div>

     <div class="ulp-single-course-content-wrapper" id="ulp_single_course_overview_page" >

      <div class="ulp-single-course-content-description">
		<?php while (have_posts()):?>
            <?php the_post();?>
            <?php the_content();?>
        <?php endwhile;?>
	   </div>

		 <?php echo do_shortcode( "[ulp-course-curriculum course_id={$post->ID}]" );?>

    <div class="ulp-single-course-alsobought">
		<?php echo do_shortcode("[ulp_students_also_bought course_id='{$post->ID}']");?>
	</div>

    <div class="ulp-single-course-instructor">
		<?php echo do_shortcode("[ulp_about_the_instructor instructor_id='{$course->AuthorID()}']");?>
	</div>

	<?php if($course->Additional_Instructors()): ?>
    	<div class="ulp-single-course-instructors">
        	<h2><?php esc_html_e("Course Instructors ", 'ulp'); ?></h2>
            <div class="ulp-course-instructors-list">
            	<?php foreach($course->Additional_Instructors() as $object){
					$user_info = get_userdata($object);
					?>
                <div class="ulp-course-instructor">
                  <img src="<?php echo esc_url( get_avatar_url( $object )); ?>"/>
                  <div class="ulp-instructor-name"><?php
										if ( isset( $user_info->last_name ) ){
												echo esc_html($user_info->last_name) .  " ";
										}
										if ( isset( $user_info->first_name ) ){
												echo esc_html($user_info->first_name);
										}
  								?></div>
                </div>
				<?php } ?>
                <div class="ulp-clear"></div>
            </div>
        </div>
    <?php endif; ?>

    <div class="ulp-single-course-reviews">
		<?php echo do_shortcode("[ulp_review_form course_id={$post->ID}]");?>
		<?php echo do_shortcode("[ulp_reviews_awesome_box course_id={$post->ID}]");?>
		<?php echo do_shortcode("[ulp_list_reviews course_id={$post->ID}]");?>
        <div class="ulp-course ulp-course-the-rating-wrapp">
			<div class="<?php echo esc_attr('ulp-rating-') . $course->Rating();?>"></div>
		</div>
    </div>

    <div class="ulp-single-course-more-courses-by">
		<?php echo do_shortcode("[ulp-more-courses-by instructor_id={$course->AuthorID()} course_id={$post->ID} limit=3]"); ?>
    </div>

	</div>
	<?php do_action('ulp_after_single_course', '', true);?>


    <div class="ulp-single-course-notes ulp-display-none" id="ulp_single_course_notes_page">
        <h2><?php esc_html_e("Your Course Notes", 'ulp'); ?></h2>
    	<?php echo do_shortcode("[ulp_list_notes course_id={$post->ID}]");?>
	</div>

    <?php do_action('ulp_single_course_after_overview_content', $post->ID);?>

</div>



<?php //do_action('ulp_sidebars', $post->ID);?>
<?php get_footer();?>
