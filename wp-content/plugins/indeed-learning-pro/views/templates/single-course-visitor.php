<?php
/**
 * Template Course
 */
get_header();
global $post;
$course = new UlpCourse($post->ID);
$ModuleItems = new UlpModuleItems($post->ID);
?>
<div class="page-content ulp-course-wrapp">
	<?php do_action('ulp_before_single_course');?>
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
                      <label><?php echo esc_html( $course->TotalModules() ) . esc_html__(' Modules ', 'ulp') ;?></label>
                      <strong><?php echo esc_html( $course->TotalLessons() ) . esc_html__(' Lessons ', 'ulp') . '/'. $course->TotalQuizes() . esc_html__(' Quizes ', 'ulp');?></strong>
                  </li>
                  <div class="ulp-clear"></div>
              </ul>
			  <div class="ulp-short-description"><?php  echo esc_html($course->Excerpt());?></div>

			  <?php while (have_posts()):?>
                  <?php the_post();?>
                  <?php the_content();?>
              <?php endwhile;?>
    		</div>
            <div class="ulp-right-sidebar">
            	<div class="price-box"><?php echo esc_ulp_content($course->Price());?></div>
            	<div class="ulp-course-enroll-section">
									<?php echo do_shortcode("[ulp-enroll-course id={$post->ID}]");?>
							</div>
                <div class="ulp-wishlist-wrapper">
  					 <?php echo do_shortcode("[ulp_watch_list_bttn course_id={$post->ID} show='button']");?>
                </div>
                <div class="sub-content-details">
                	<h3><?php  esc_html_e('Additional Details', 'ulp');?></h3>
                    <ul class="ulp-right-details">
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
                      <li>
                      <label><?php echo esc_html__('Release Time', 'ulp'); ?></label>
                      <strong><?php  echo   $course->CreateDate(TRUE);?></strong>
                      </li>
                    </ul>

                </div>
            </div>
		</div>
	<!-- LIST OF MODULES -->
	<?php if ($ModuleItems->has_modules()):?>
    	<div class="ulp-public-the-modules-wrapper">
        	<div class="ulp-h3-title"><?php  esc_html_e('Course Curriculum', 'ulp');?></div>
		<?php while ($ModuleItems->have_modules()):?>
			<div class="ulp-public-the-module">
					<?php if ($ModuleItems->has_children()):?>
                    	<div class="ulp-public-the-module-title">
               				<h3><?php echo esc_html($ModuleItems->Name());?></h3>
                			<span class="ulp-module-details"> <span><?php echo esc_html($ModuleItems->countLessons());?></span><?php  esc_html_e(' Lessons', 'ulp');?> / <span><?php echo esc_html($ModuleItems->countQuizes());?></span><?php  esc_html_e(' Quizes', 'ulp');?></span>
               			</div>
                        <div class="ulp-public-the-module-content">
						<?php while ($ModuleItems->have_children()):?>
							<div  class="ulp-public-the-module-content-element">
								<?php if ($ModuleItems->ChildType()=='ulp_lesson'):?>
									<!-- LESSON -->
									<!-- Title + Permalink -->
									<?php $lesson = new UlpLesson($ModuleItems->ChildId(), true, $post->ID);?>
                                   <?php if ($course->IsEntrolled()):?>
									<?php if (!$course->can_access_any_item() && !$lesson->is_completed() && $ModuleItems->ChildId()!=$ModuleItems->FirstChildId()):?>
											<span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>"><i class="fa-ulp fa-curr_lesson"></i> <?php echo esc_ulp_content($quiz->Title());?></span>
									<?php else:?>
											<a href="<?php echo esc_url($lesson->Permalink());?>"><i class="fa-ulp fa-curr_lesson"></i><?php echo esc_ulp_content($lesson->Title());?></a>
									<?php endif;?>
									<!-- /Title + Permalink -->
									<?php if ($lesson->is_completed()):?>
                                            <span class="ulp-lesson-status-completed">
													<i class="fa-ulp fa-curr_lesson_completed"></i>
                                            </span>
									<?php endif;?>
                                  <?php else:?>
											<!-- NON ENROLLED USERS -->
											<?php if ($lesson->hasPreview()):?>
													<a href="<?php echo esc_url($lesson->Permalink());?>">
														 <?php $is_video = $lesson->isVideo(); if ( $is_video && $is_video[0] != 0):?>
			                         <i class="fa-ulp fa-video-ulp"></i>
			                      <?php else :?>
			                      <i class="fa-ulp fa-curr_lesson"></i>
			                      <?php endif;?>
														<?php echo esc_ulp_content($lesson->Title());?></a><span class="ulp-lesson-preview"><?php echo esc_html__(' Preview', 'ulp');?></span>
											<?php else:?>
													<span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>">
														 <?php $is_video = $lesson->isVideo(); if ( $is_video && $is_video[0] != 0):?>
			                         <i class="fa-ulp fa-video-ulp"></i>
			                      <?php else :?>
			                      <i class="fa-ulp fa-curr_lesson"></i>
			                      <?php endif;?>
														<?php echo esc_ulp_content($lesson->Title()); ?></span>
											<?php endif;?>
									<?php endif;?>
                                    <span class="ulp-public-the-module-content-element-rightside">
                                 <?php if($lesson->RewardPoints()): ?>
                               	 	<span class="ulp-module-content-points"><?php echo esc_ulp_content($lesson->RewardPoints()). ' '.esc_html__('points','ulp');?></span>
                                <?php endif; ?>
                                <?php if($lesson->Duration()): ?>
                                	<span class="ulp-module-content-time"><?php echo esc_ulp_content($lesson->Duration()).esc_ulp_content($lesson->DurationType());?></span>
                                <?php endif; ?>
                                </span>
								<div class="ulp-clear"></div>
									<!-- / LESSON -->
								<?php else:?>
									<!--QUIZ-->
									<?php $quiz = new UlpQuiz($ModuleItems->ChildId(), true, $post->ID);?>
                                    <?php if ($course->IsEntrolled()):?>
									<!-- Title + Permalink -->
									<?php if (!$course->can_access_any_item() && !$quiz->has_grade() && $ModuleItems->ChildId()!=$ModuleItems->FirstChildId()):?>
											<span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>"><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></span>
									<?php else:?>
											<a href="<?php echo esc_url($quiz->Permalink());?>"><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></a>
									<?php endif;?>
									<!-- /Title + Permalink -->
                                    <?php else:?>
											<span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>"><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></span>
									<?php endif;?>
										<?php if ($quiz->has_grade()):?>
												<?php if ($quiz->is_passed()):?>
													<span class="ulp-quiz-status-passed"><?php  esc_html_e("Passed ", 'ulp'); echo esc_ulp_content('(' . esc_ulp_content($quiz->Grade()) . ')');?></span>
												<?php else:?>
													<span class="ulp-quiz-status-failed"><?php  esc_html_e("Failed ", 'ulp'); echo esc_ulp_content('(' . esc_ulp_content($quiz->Grade()) . ')');?></span>
												<?php endif;?>
										<?php endif;?>

                                        <span class="ulp-public-the-module-content-element-rightside">
										   <?php if($quiz->RewardPoints()): ?>
                                              <span class="ulp-module-content-points"><?php echo esc_ulp_content($quiz->RewardPoints()) . ' ' . esc_html__('points','ulp');?></span>
                                          <?php endif; ?>
                                          <?php if($quiz->Duration()): ?>
                                              <span class="ulp-module-content-time"><?php echo esc_ulp_content($quiz->Duration()).'m';?></span>
                                          <?php endif; ?>
                                          </span>
                                          <div class="ulp-clear"></div>
										<!--/QUIZ-->
								<?php endif;?>
							</div>
						<?php endwhile;?>
                      </div>
					<?php endif;?>
			</div>
		<?php endwhile;?>
	<?php if ($ModuleItems->hasPagination()):?>
			<?php echo esc_ulp_content($ModuleItems->Pagination());?>
	<?php endif;?>
      </div>
	<?php endif;?>
	<!-- LIST OF MODULES -->

	<div class="ulp-course ulp-course-the-rating-wrapp">
		<div class="<?php echo esc_attr('ulp-rating-') . esc_ulp_content($course->Rating());?>"></div>
	</div>
	<div>
			<?php //echo esc_ulp_content($course->Difficulty());?>
	</div>
	<div>
			<?php //echo esc_ulp_content($course->CourseTimePeriod());?>
	</div>
	<?php echo do_shortcode("[ulp_students_also_bought course_id='{$post->ID}']");?>
	<?php echo do_shortcode("[ulp_about_the_instructor instructor_id='{$course->AuthorID()}']");?>
	<?php echo do_shortcode("[ulp_reviews_awesome_box course_id={$post->ID}]");?>
	<?php echo do_shortcode("[ulp_list_reviews course_id={$post->ID}]");?>
	<?php echo do_shortcode("[ulp-course-list-tags course_id={$post->ID}]");?>
	<?php echo do_shortcode("[ulp-more-courses-by course_id={$post->ID} limit=3]");?>


	<?php do_action('ulp_after_single_course', '', true);?>

</div>
<?php //do_action('ulp_sidebars', $post->ID);?>
<?php get_footer();?>
