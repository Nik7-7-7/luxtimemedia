<?php
/**
 * Template Leasons
 */
get_header();
$UlpPublicLesson = new UlpPublicLesson();
$videoLesson = new \Indeed\Ulp\PublicSection\VideoLesson( $UlpPublicLesson->getLessonId() );
?>
<?php do_action('ulp_before_single_lesson');?>

<div class="page-content ulp-lesson-wrapp">
	<?php if ($UlpPublicLesson->FeatureImage()):?>
			<div class="ulp-lesson-main-featureimage">
					<img src="<?php echo esc_url($UlpPublicLesson->FeatureImage());?>" />
			</div>
	<?php endif;?>
	<?php while (have_posts()):?>
		<?php the_post();?>
		<div class="ulp-lesson-main-title"><?php the_title();?></div>

		  <?php if ( $videoLesson->isYoutube() ):?>
                  <div class="ulp-lesson-video-warpper">
                      <div id="ulp_youtube_player"></div>
                  </div>
          <?php elseif ( $videoLesson->isVimeo() ):?>
                  <div class="ulp-lesson-video-warpper">
                      <div id="ulp_vimeo_player" allow="autoplay"></div>
                  </div>
          <?php endif;?>

	    <div class="ulp-lesson-main-content"><?php the_content();?></div>
	<?php endwhile;?>


  <div class="ulp-lesson-navigation-wrapper">
  	<?php echo esc_ulp_content($UlpPublicLesson->Navigation());?>
  </div>
  <div class="ulp-lesson-complete-button-wrapper">
  	<?php echo esc_ulp_content($UlpPublicLesson->CompleteButton());?>
  </div>
  <?php if ($UlpPublicLesson->CoursePermalink()):?>
      <div class="ulp-lesson-course-link">
      	<div><?php esc_html_e('Back to', 'ulp');?></div>
      	<?php echo esc_ulp_content($UlpPublicLesson->CoursePermalink());?>
      </div>
  <?php endif; ?>
</div>
<?php do_action( 'ulp_after_single_lesson' );?>
<?php do_action( 'ulp_course_curriculum_item', $UlpPublicLesson->getCourseId(), $UlpPublicLesson->getLessonId() );?>
<?php
global $post;
get_footer();
