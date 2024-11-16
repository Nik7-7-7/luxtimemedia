<?php
$course = new UlpCourse($object->post_id);
?>
<div class="ulp-list-courses-item-wrapp ulp-watch-list-item">
  <?php if ($course->FeatureImage()):?>
      <div class="ulp-feat-img-wrapp">
          <div class="ulp-feat-img-single-course" style= " min-width:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_width');?>px; height:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_height');?>px; background-image:url('<?php echo esc_url($course->FeatureImage());?>');">
          </div>
          <div class="ulp-course-price"><?php echo esc_ulp_content($course->Price());?></div>
      </div>
  <?php endif;?>
  <div class="ulp-list-courses-item-wrapp-content">
  	<div class="ulp-list-courses-item-title">
    	<div class="ulp-course-title"><a href="<?php echo Ulp_Permalinks::getForCourse($object->post_id);?>"><?php echo esc_ulp_content($object->post_title);?></a>
        <?php echo do_shortcode("[ulp-course-list-tags course_id={$object->post_id}]");?>
        </div>

    </div>
    <div class="ulp-list-courses-item-wrapp-second-content">
    	<div class="ulp-course-last-updated"><?php echo esc_html__('Last update: ', 'ulp') . indeed_time_elapsed_string($object->post_modified);?></div>
    </div>
        <ul class="ulp-list-courses-item-wrapp-third-content">
        <?php if ($course->Rating()):?>
        	<li>
            	<label><i class="fa-ulp fa-full-star-ulp"></i> <?php echo esc_html__('Rating ', 'ulp'); ?></label>
                <strong><?php echo esc_ulp_content($course->Rating());?></strong>
            </li>
        <?php endif;?>
        	<li>
            	<label><i class="fa-ulp fa-users-ulp"></i> <?php echo esc_html__('Students ', 'ulp'); ?></label>
                <strong><?php  echo esc_html($course->TotalStudents()) . '/' . esc_html($course->MaxEnrolledStudents()) . ' '. esc_html__('Students ', 'ulp');?></strong>
        	</li>
        </ul>
    <div>
        <?php echo ucfirst($course->PostStatus());?>
    </div>
  </div>
</div>
