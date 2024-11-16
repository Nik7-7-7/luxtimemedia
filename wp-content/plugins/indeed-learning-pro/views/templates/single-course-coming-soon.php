<?php
/**
 * Template Coming Soon Course
 */
get_header();
global $post;
$course = new UlpCourse($post->ID);
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
                      <label><?php echo esc_html__('Category ', 'ulp'); ?></label>
                      <strong><?php  echo  $course->Categories(TRUE);?></strong>
                  </li>
                  <li>
                      <label><?php echo esc_html($course->TotalModules()) .esc_html__(' Modules ', 'ulp') ;?></label>
                      <strong><?php echo esc_html($course->TotalLessons()) . esc_html__(' Lessons ', 'ulp') . '/'. $course->TotalQuizes() . esc_html__(' Quizes ', 'ulp');?></strong>
                  </li>
                  <div class="ulp-clear"></div>
              </ul>

        <div class="ulp-course-descrption-comingsoon">
            <?php echo esc_ulp_content($course->ComingSoonDescription());?>
        </div>
        <div class="ulp-course-comingsoon-time">
            <?php
			// Available data about when the course will become public.
			//echo  $course->comingSoonEndTime();?>
        </div>

        <?php if ($course->comingSoonShowCountdown()):?>
          <div class="ulp-countdown">
            <div class="ulp-col-sm-3 ulp-text-center">
                <div class="ulp-count-container">
                    <span class="ulp-days"></span>
                    <p class="ulp-days_ref"></p>
                </div>
            </div>
            <div class="ulp-col-sm-3 ulp-text-center">
                <div class="ulp-count-container">
                    <span class="ulp-hours"></span>
                    <p class="ulp-hours_ref"></p>
                </div>
            </div>
            <div class="ulp-col-sm-3 ulp-text-center">
                <div class="ulp-count-container">
                    <span class="ulp-minutes"></span>
                    <p class="ulp-minutes_ref"></p>
                </div>
            </div>
            <div class="ulp-col-sm-3 ulp-text-center">
                <div class="ulp-count-container">
                    <span class="ulp-seconds"></span>
                    <p class="ulp-seconds_ref"></p>
                </div>
            </div>
            <div class="ulp-clear"></div>
          </div>
          <?php wp_enqueue_script('ulp.jquery.countdown', ULP_URL . 'assets/js/jquery.countdown.js', array('jquery'), 3.5 );?>
					<span class="ulp-js-single-course-coming-soon"
									data-date="<?php echo strtotime($course->comingSoonEndTime());?>"
									data-until_timestamp="<?php echo strtotime($course->comingSoonEndTime());?>"></span>

        <?php endif;?>

		<div class="ulp-short-description"><?php  echo esc_ulp_content($course->Excerpt());?></div>
    		</div>
            <div class="ulp-right-sidebar">
            	<div class="price-box"><?php echo esc_html($course->Price());?></div>
            	<div class="ulp-course-enroll-section">

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


</div>
<?php //do_action('ulp_sidebars', $post->ID);?>
<?php get_footer();?>
