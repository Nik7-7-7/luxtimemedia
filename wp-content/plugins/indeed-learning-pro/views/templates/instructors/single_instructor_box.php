<div class="ulp-about-the-instructor-box">
    <h2><?php esc_html_e('About the Instructor', 'ulp');?></h2>
    <div class="ulp-about-the-instructor-box-content">

        <div class="ulp-about-the-instructor-left">
            <div class="ulp-instructor-avatar">
                <?php if (!empty($data['avatar'])):?>
                    <img src="<?php echo esc_url($data['avatar']);?>"  />
                <?php else :?>

                <?php endif;?>
            </div>
            <div class="ulp-instructor-details">
              <?php if (!empty($data['number_of_courses'])):?>
                  <div >
                      <i class="fa-ulp fa-book-ulp"></i><strong><?php echo esc_html($data['number_of_courses']);?></strong> <span><?php esc_html_e('Courses', 'ulp');?></span>
                  </div>
              <?php endif;?>
              <?php if (!empty($data['number_of_students'])):?>
                  <div >
                      <i class="fa-ulp fa-users-ulp"></i><strong><?php echo esc_html($data['number_of_students']);?></strong> <span><?php esc_html_e('Students', 'ulp');?></span>
                  </div>
              <?php endif;?>
              <?php if (!empty($data['average_rating'])):?>
                  <div >
                      <i class="fa-ulp fa-full-star-ulp"></i><strong><?php echo esc_html($data['average_rating']);?></strong> <span><?php esc_html_e('Average Rating', 'ulp');?></span>
                  </div>
              <?php endif;?>
              <?php if (!empty($data['number_of_reviews'])):?>
                  <div >
                      <i class="fa-ulp fa-comments-ulp"></i><strong><?php echo esc_html($data['number_of_reviews']);?></strong> <span><?php esc_html_e('Reviews', 'ulp');?></span>
                  </div>
              <?php endif;?>
            </div>
        </div>
        <div class="ulp-about-the-instructor-right">
            <?php if (!empty($data['instructorName'])): ?>
                <div class="ulp-instructor-name"><a href="<?php echo esc_url($data['permalink']);?>"><?php echo stripslashes($data['instructorName']);?></a></div>
            <?php endif;?>
            <?php if (!empty($data['biography'])): ?>
                <div class="ulp-instructor-biography"><?php echo stripslashes($data['biography']);?></div>
            <?php endif;?>
        </div>

    </div>
</div>
