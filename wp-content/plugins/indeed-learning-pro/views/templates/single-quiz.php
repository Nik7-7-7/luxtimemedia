<?php
/**
 * Template Quizes
 */
get_header();
?>
<div class="page-content ulp-single-quiz">
	<div class="ulp-quiz-main-title"><?php echo get_the_title();?></div>
	<?php $UlpPublicQuiz = new UlpPublicQuiz();?>
	<?php if($UlpPublicQuiz->is_main_section()): ?>
	<div class="ul-quiz-main-details">
    	<div class="ul-quiz-time">
			<i class="fa-ulp fa-quiz_time"></i>
			<?php
            echo esc_html_e('Time ','ulp'); ?>
             <span class="ulp-quiz-counts">
            <?php
            echo  $UlpPublicQuiz->getQuizTime();
            echo esc_html_e(' minute(s)','ulp');  ?>
            </span>
        </div>
        <div class="ul-quiz-questions-count">
			 <i class="fa-ulp fa-quiz_questions"></i>
    		 <span class="ulp-quiz-counts">
			<?php echo  $UlpPublicQuiz->getQuizQuestionsCount(); ?>
            </span>
            <?php echo esc_html_e(' Questions','ulp'); ?>
        </div>
		<?php if ($UlpPublicQuiz->getRetakeMeta() > 1):?>
        <div class="ul-quiz-retake">
			<i class="fa-ulp fa-quiz_retake"></i>
    		<?php echo esc_html_e('Retake  ','ulp');  ?>
            <span class="ulp-quiz-counts">
			<?php
            echo esc_ulp_content($UlpPublicQuiz->getRetakeAttempts());
			echo esc_ulp_content('/'). esc_ulp_content($UlpPublicQuiz->getRetakeMeta());
			?>
            </span>
        </div>
		<?php endif;?>

        <?php if ($UlpPublicQuiz->getRewardPoints() > 0):?>
        <div class="ul-quiz-rewardpoints">
			<i class="fa-ulp fa-quiz_reward"></i>
    		<?php echo esc_html_e('Reward Points  ','ulp');  ?>
            <span class="ulp-quiz-counts">
			<?php
            echo esc_ulp_content($UlpPublicQuiz->getRewardPoints());
			?>
            </span>
        </div>
		<?php endif;?>
        <div class="ul-quiz-grade">
    		<?php
			echo esc_html_e('Requires ','ulp');
			echo esc_ulp_content($UlpPublicQuiz->getGradeValue()) . esc_ulp_content($UlpPublicQuiz->getGradeType());
			echo esc_html_e(' to pass the Quiz','ulp'); ?>
        </div>
   </div>
  <?php endif; ?>
	<?php if ($UlpPublicQuiz->getTheContent()):?>
		<div class="ul-quiz-main-summary">
		<?php echo esc_ulp_content($UlpPublicQuiz->getTheContent());?>
		</div>
	<?php endif;?>

	<?php if($UlpPublicQuiz->is_main_section()): ?>
	<div class="ulp-lesson-navigation-wrapper">
		<?php echo esc_ulp_content($UlpPublicQuiz->Navigation());?>
	</div>
   <?php endif;?>

	<?php if ($UlpPublicQuiz->getPagination()):?>
		<div class="ulp-pagination"><?php echo esc_ulp_content($UlpPublicQuiz->getPagination());?></div>
	<?php endif;?>
    <?php if($UlpPublicQuiz->is_main_section()): ?>
	  <?php if ($UlpPublicQuiz->CoursePermalink()):?>
          <div class="ulp-quiz-course-link">
          <div><?php esc_html_e('Back to', 'ulp');?></div>
          <?php echo esc_ulp_content($UlpPublicQuiz->CoursePermalink());?>
          </div>
      <?php endif; ?>
   <?php endif;?>
</div>
<?php
do_action( 'ulp_course_curriculum_item', $UlpPublicQuiz->getCourseId(), $UlpPublicQuiz->getQuizId() );

global $post;

get_footer();
