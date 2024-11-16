<?php
$course = new UlpCourse($object->courseId);
?>
<div class="ulp-course-miniature">
  <?php if ($course->FeatureImage()):?>
      <div class="ulp-feat-img-wrapp">
          <div class="ulp-feat-image" style= " background-image:url('<?php echo esc_url($course->FeatureImage());?>');">
          </div>
      </div>
  <?php endif;?>
  <div class="ulp-course-additional-content">
  	<div class="ulp-course-title-container">
    	<div class="ulp-course-title"><a href="<?php echo esc_url(Ulp_Permalinks::getForCourse($object->courseId));?>"><?php echo esc_ulp_content($object->post_title);?></a></div>
    	<div class="ulp-course-last-updated"><?php echo esc_html__('Last update: ', 'ulp') . indeed_time_elapsed_string($object->post_modified);?></div>
    </div>
    <div class="ulp-course-content-container">
        <?php if ($course->Rating()):?>
            <div class="ulp-course-the-rating-wrapp">
                <i class="fa-ulp fa-full-star-ulp"></i> <strong><?php echo esc_ulp_content($course->Rating());?></strong>
            </div>
        <?php endif;?>
        <span class="ulp-course-students"><i class="fa-ulp fa-users-ulp"></i> <?php  echo esc_html($course->TotalStudents()) . '/' . esc_html( $course->MaxEnrolledStudents() ) . ' '. esc_html__('Students ', 'ulp');?></span>
        <span class="price-box-wrapper"><span class="price-box"><?php echo esc_ulp_content($course->Price());?></span></span>
  	</div>
  </div>
</div>
