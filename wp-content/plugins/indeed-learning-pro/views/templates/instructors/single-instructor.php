<?php
/**
 * Template Course
 */
get_header();
global $post;
$UlpInstructor = new UlpInstructor();
$data = $UlpInstructor->setPostIdForInstructor($post->ID)->gettingAllInstructorData();
$instructor_id = $UlpInstructor->getInstructorID();
$showData = $UlpInstructor->getSingleInstructorPageSettings($instructor_id);
?>
<div class="page-content ulp-instructor-page-wrapp">
  <div class="row">

      <div class="ulp-instructor-page-left">
          <div class="ulp-instructor-page-avatar">
              <?php if (!empty($data['avatar']) && !empty($showData['ulp_instructor_show_avatar'])):?>
                  <img src="<?php echo esc_url($data['avatar']);?>"  />
              <?php else :?>

              <?php endif;?>
          </div>
          <div class="ulp-instructor-page-details">
            <?php if (!empty($data['average_rating']) && !empty($showData['ulp_instructor_show_average_rating']) ):?>
                <div class="ulp-instructor-page-details-item">
                    <i class="fa-ulp fa-full-star-ulp"></i>
					<span><?php echo esc_ulp_content($data['average_rating']);?></span>
                    <span><?php esc_html_e('Average Rating', 'ulp');?></span>

                </div>
            <?php endif;?>
            <?php if (!empty($data['number_of_reviews']) && !empty($showData['ulp_instructor_show_number_of_reviews']) ):?>
                <div class="ulp-instructor-page-details-item">
                    <i class="fa-ulp fa-comments-ulp"></i>
					<span><?php echo esc_ulp_content($data['number_of_reviews']);?></span>
					<span><?php esc_html_e('Reviews', 'ulp');?></span>
                </div>
            <?php endif;?>
          </div>
      </div>
      <div class="ulp-instructor-page-right">
          <?php if (!empty($data['instructorName']) && !empty($showData['ulp_instructor_show_instructor_name']) ): ?>
              <div class="ulp-instructor-page-name"><?php echo stripslashes($data['instructorName']);?></div>
          <?php endif;?>
          <?php if (!empty($data['biography']) && !empty($showData['ulp_instructor_show_biography']) ): ?>
              <div class="biography"><?php echo stripslashes($data['biography']);?></div>
          <?php endif;?>
          <div class="ulp-instructor-page-details">
            <?php if (!empty($data['number_of_courses']) && !empty($showData['ulp_instructor_show_number_of_courses']) ):?>
                <div class="ulp-instructor-page-details-item">
                    <i class="fa-ulp fa-book-ulp"></i>
					<span><?php esc_html_e('Courses', 'ulp');?></span>
					<div class="ulp-instructor-page-details-num"><?php echo esc_html($data['number_of_courses']);?></div>
                </div>
            <?php endif;?>
            <?php if (!empty($data['number_of_students']) && !empty($showData['ulp_instructor_show_number_of_students']) ):?>
                <div class="ulp-instructor-page-details-item">
                    <i class="fa-ulp fa-users-ulp"></i>
					<span><?php esc_html_e('Total Students', 'ulp');?></span>
					<div class="ulp-instructor-page-details-num"><?php echo esc_html($data['number_of_students']);?></div>
                </div>
            <?php endif;?>
      		<div class="ulp-clear"></div>
           </div>
      </div>
      <div class="ulp-clear"></div>
      <?php echo do_shortcode("[ulp-more-courses-by instructor_id={$instructor_id} limit=3]"); ?>
  </div>
</div>

<?php get_sidebar();?>
<?php get_footer();?>
