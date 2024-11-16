<head>
<link rel="stylesheet" href="<?php echo ULP_URL . "assets/css/perfect-scrollbar.css"; ?>">
<?php wp_enqueue_script( 'ulp-perfect-scrollbar', ULP_URL . "assets/js/perfect-scrollbar.js", ['jquery'], 3.6, false );?>
</head>

<?php
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes($style) );
 ?>
<div class="ulp-course-curriculum-slider-wrapper">

    <div class="ulp-course-curriculum-content" id="ulp_course_curriculum_content" >
   		 <div class="ulp-course-curriculum-trigger" id="ulp_course_curriculum_trigger">

        	<span class="ulp-course-curriculum-trigger-label">
			<?php echo stripslashes($label);?>
    		</span>
        </div>

        <?php
            $ModuleItems = new UlpModuleItems( $courseId );
            $course = new UlpCourse( $courseId );
        ?>
        <div class="ulp-course-curriculum-title"><?php echo esc_ulp_content($courseTitle);?></div>
        <?php if ( $courseCategories ):?>
            <?php foreach ( $courseCategories as $courseCategory ):?>
                <div class="ulp-course-curriculum-category"><?php echo esc_html($courseCategory);?></div>
            <?php endforeach;?>
        <?php endif;?>
        <!-- developer work -->

          <div class="scroll-wrapper curriculum-scrollable scrollbar-light">


      <div class="curriculum-scrollable scrollbar-light scroll-content scroll-scrolly_visible">

          <!-- end developer work -->
		<?php if ($ModuleItems->has_modules()):?>
            <?php while ($ModuleItems->have_modules()):?>
                <?php if ($ModuleItems->has_children()):?>
                    <h5 class="ulp-course-curriculum-content-module-title">
                        <?php echo esc_ulp_content($ModuleItems->Name());?>
                        <!--span class="ulp-module-details"> <span><?php echo esc_html($ModuleItems->countLessons());?></span><?php esc_html_e(' Lessons', 'ulp');?> / <span><?php echo esc_html($ModuleItems->countQuizes());?></span><?php esc_html_e(' Quizes', 'ulp');?></span-->
                    </h5>
                    <div class="ulp-course-curriculum-content-module-content">
                         <?php while ($ModuleItems->have_children()):?>
                         <?php $extraClass = ( $currentEntity == $ModuleItems->ChildId() ) ? 'ulp-course-curriculum-current-entity' : '';?>
                           <div  class="ulp-course-curriculum-content-module-content-element <?php echo esc_attr($extraClass);?>">
                             <?php if ($ModuleItems->ChildType()=='ulp_lesson'):?>
                               <!-- LESSON -->
                               <!-- Title + Permalink -->
                               <?php $lesson = new UlpLesson($ModuleItems->ChildId(), true, $courseId);?>
								<?php $lesson_icon = '<i class="fa-ulp fa-curr_lesson"></i>'; ?>
								<?php if ( $lesson->isVideo() ):
                                  $lesson_icon = '<i class="fa-ulp fa-video-ulp"></i>';
                                  endif;?>
                               <?php if ($course->IsEntrolled()):?>
                                   <!-- ENROLLED STUDENTS -->
                                   <?php if (!$course->can_access_any_item() && !$lesson->is_completed() && $ModuleItems->ChildId()!=$ModuleItems->FirstChildId()):?>
                                       <span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>">
                                        <?php echo esc_ulp_content($lesson_icon) . esc_ulp_content($lesson->Title());?></span>
                                   <?php else:?>


                                       <a href="<?php echo esc_url($lesson->Permalink());?>" class=" <?php echo ($lesson->is_completed() ? 'ulp-course-curriculum-content-lesson-link-completed' : '');?>"><?php echo esc_ulp_content($lesson_icon) . esc_ulp_content($lesson->Title());?></a>
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
                                       <a href="<?php echo esc_url($lesson->Permalink());?>"><?php echo esc_ulp_content($lesson_icon) . esc_ulp_content($lesson->Title());?></a><span class="ulp-lesson-preview"><?php echo esc_html__(' Preview', 'ulp');?></span>
                                   <?php else:?>
                                       <span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>"><?php echo esc_ulp_content($lesson_icon) . esc_ulp_content($lesson->Title()); ?></span>
                                   <?php endif;?>
                               <?php endif;?>
                             <span class="ulp-course-curriculum-content-module-content-element-rightside">
                                              <?php if($lesson->RewardPoints()): ?>
                                               <span class="ulp-course-curriculum-module-content-points"><?php echo esc_ulp_content($lesson->RewardPoints()). ' '.esc_html__('points','ulp');?></span>
                                             <?php endif; ?>
                                             <?php if($lesson->Duration()): ?>
                                               <span class="ulp-module-content-time"><?php echo esc_ulp_content($lesson->Duration()) . esc_ulp_content($lesson->DurationType());?></span>
                                             <?php endif; ?>
                                             </span>
                             <div class="ulp-clear"></div>
                                               <!-- / LESSON -->
                             <?php else:?>
                               <!--QUIZ-->
                               <?php $quiz = new UlpQuiz($ModuleItems->ChildId(), true, $courseId);?>
                               <?php if ($course->IsEntrolled()):?>
                                   <!-- Title + Permalink -->
                                   <?php if (!$course->can_access_any_item() && !$quiz->has_grade() && $ModuleItems->ChildId()!=$ModuleItems->FirstChildId()):?>
                                       <span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>"><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></span>
                                   <?php else:?>

                                       <a href="<?php echo esc_url($quiz->Permalink());?>" class="<?php echo ($quiz->has_grade()) ? '.ulp-course-curriculum-content-quiz-link-completed' : '';?>"><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></a>
                                   <?php endif;?>
                                   <!-- /Title + Permalink -->
                               <?php else:?>
                                   <span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>"><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></span>
                               <?php endif;?>
                                 <?php if ($quiz->has_grade()!==FALSE):?>
                                     <?php if ($quiz->is_passed()):?>
                                       <span class="ulp-quiz-status-passed"><?php esc_html_e("Passed ", 'ulp'); echo esc_html('(' . $quiz->Grade() . ')');?></span>
                                     <?php else:?>
                                       <span class="ulp-quiz-status-failed"><?php esc_html_e("Failed ", 'ulp'); echo esc_html('(' . $quiz->Grade() . ')');?></span>
                                     <?php endif;?>
                                 <?php endif;?>
                                                     <span class="ulp-course-curriculum-content-module-content-element-rightside">
                                    <?php if($quiz->RewardPoints()): ?>
                                                           <span class="ulp-course-curriculum-module-content-points"><?php echo esc_html($quiz->RewardPoints()). ' '.esc_html__('points','ulp');?></span>
                                                       <?php endif; ?>
                                                       <?php if($quiz->Duration()): ?>
                                                           <span class="ulp-module-content-time"><?php echo esc_html($quiz->Duration()).'m';?></span>
                                                       <?php endif; ?>
                                                       </span>
                                                       <div class="ulp-clear"></div>
                                 <!--/QUIZ-->
                             <?php endif;?>
                           </div>
                         <?php endwhile;?>

                     </div>

                <?php endif; // end of has_children ?>
            <?php endwhile; /// end of have_modules ?>
        <?php endif; // has_modules ?>
        </div>
        </div>
  </div>
</div>
<span class="ulp-js-course-curriculum-slider" ></span>
