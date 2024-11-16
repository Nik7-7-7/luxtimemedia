<?php
$ModuleItems = new UlpModuleItems($courseId);
$course = new UlpCourse($courseId);
?>
<div <?php echo  ($isTab) ? 'class="ulp-display-none"  id="ulp_single_course_curriculum_page" ' : ''; ?> >
<div class="ulp-singl-course-content-curriculum">
<!-- LIST OF MODULES -->
<?php if ($ModuleItems->has_modules()):?>
<div class="ulp-public-the-modules-wrapper">
  <div class="ulp-h3-title"><?php esc_html_e('Course Curriculum', 'ulp');?></div>
<?php while ($ModuleItems->have_modules()):?>
<div class="ulp-public-the-module">
     <?php if ($ModuleItems->has_children()):?>
 <div class="ulp-public-the-module-title">
         <h3><?php echo esc_ulp_content($ModuleItems->Name());?></h3>
         <span class="ulp-module-details"> <span><?php echo esc_html($ModuleItems->countLessons());?></span><?php esc_html_e(' Lessons', 'ulp');?> / <span><?php echo esc_html($ModuleItems->countQuizes());?></span><?php esc_html_e(' Quizes', 'ulp');?></span>
         </div>
 <div class="ulp-public-the-module-content">
     <?php while ($ModuleItems->have_children()):?>
       <div  class="ulp-public-the-module-content-element">
         <?php if ($ModuleItems->ChildType()=='ulp_lesson'):?>
           <!-- LESSON -->
           <!-- Title + Permalink -->
           <?php $lesson = new UlpLesson($ModuleItems->ChildId(), true, $courseId);?>
           <?php if ($course->IsEntrolled()):?>
               <!-- ENROLLED STUDENTS -->
               <?php if (!$course->can_access_any_item() && !$lesson->is_completed() && $ModuleItems->ChildId()!=$ModuleItems->FirstChildId()):?>
                   <span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>">
                      <?php $is_video = $lesson->isVideo(); if ( $is_video && $is_video[0] != 0):?>
                        <i class="fa-ulp fa-video-ulp"></i>
                     <?php else :?>
                     <i class="fa-ulp fa-curr_lesson"></i>
                     <?php endif;?>
                     <?php echo esc_ulp_content($lesson->Title());?></span>
               <?php else:?>
                   <a href="<?php echo esc_url($lesson->Permalink());?>" <?php  echo($lesson->is_completed() ? 'class="ulp-lesson-link-completed"' : '')?>>
                     <?php $is_video = $lesson->isVideo(); if ( $is_video && $is_video[0] != 0):?>
                        <i class="fa-ulp  fa-video-ulp"></i>
                     <?php else :?>
                     <i class="fa-ulp fa-curr_lesson"></i>
                     <?php endif;?>
                     <?php echo esc_ulp_content($lesson->Title());?></a>
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
                           <span class="ulp-module-content-points"><?php echo esc_html($lesson->RewardPoints()). ' '.esc_html__('points','ulp');?></span>
                         <?php endif; ?>
                         <?php if($lesson->Duration()): ?>
                           <span class="ulp-module-content-time"><?php echo esc_html($lesson->Duration()).esc_html($lesson->DurationType());?></span>
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
                   <a href="<?php echo esc_url($quiz->Permalink());?>" <?php echo ($quiz->has_grade() ? 'class="ulp-quiz-link-completed"' : '');?>><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></a>
               <?php endif;?>
               <!-- /Title + Permalink -->
           <?php else:?>
               <span title="<?php echo esc_html__(' (Not available yet)', 'ulp');?>"><i class="fa-ulp fa-curr_quiz"></i><?php echo esc_ulp_content($quiz->Title());?></span>
           <?php endif;?>
             <?php if ($quiz->has_grade()!==FALSE):?>
                 <?php if ($quiz->is_passed()):?>
                   <span class="ulp-quiz-status-passed"><?php esc_html_e("Passed ", 'ulp'); echo esc_html('(' . $quiz->Grade() . ')' );?></span>
                 <?php else:?>
                   <span class="ulp-quiz-status-failed"><?php esc_html_e("Failed ", 'ulp'); echo esc_html('(' . $quiz->Grade() . ')' );?></span>
                 <?php endif;?>
             <?php endif;?>
                                 <span class="ulp-public-the-module-content-element-rightside">
                <?php if($quiz->RewardPoints()): ?>
                                       <span class="ulp-module-content-points"><?php echo esc_html($quiz->RewardPoints()). ' '.esc_html__('points','ulp');?></span>
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
 <?php endif;?>
</div>
<?php endwhile;?>
<?php if ($ModuleItems->hasPagination()):?>
<?php echo esc_ulp_content($ModuleItems->Pagination());?>
<?php endif;?>
</div>
<?php endif;?>
<!-- LIST OF MODULES -->
</div>
</div>
