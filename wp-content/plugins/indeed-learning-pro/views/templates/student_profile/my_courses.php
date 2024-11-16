<div class="ulp-student-profile-tab-the-title"><?php echo esc_ulp_content($data ['title']);?></div>
<div class="ulp-student-profile-tab-the-content"><?php echo esc_ulp_content($data ['content']);?></div>
<div>
    <?php if ($Student->StudentHasCourse()):
	?>
    <div class="ulp-list-courses-wrapp ulp-my-courses-wrapp">
     <?php foreach ($Student->student_courses as $course_id => $course_details): ?>
    	<div class="ulp-list-courses-item-wrapp">
					<?php if($course_details['feature_image']):?>
							<div class="ulp-feat-img-wrapp">
                            	<?php if( $course_details['is_featured']): ?>
                                	<span class="ulp-sale"><span class="ulp-text-sale"><?php esc_html_e('Sale', 'ulp');?></span></span>
								<?php endif;?>
                                <a href="<?php  echo esc_url($course_details['permalink']);?>" target="_blank" class="ulp-feat-img-single-course" style= " min-width:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_width');?>px; height:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_height');?>px; background-image:url('<?php echo esc_url($course_details['feature_image']);?>');">
                                </a>

								<!--div class="ulp-course-price"></div-->
                            </div>
					<?php endif;?>
					<div class="ulp-list-courses-item-wrapp-content">
						<div class="ulp-list-courses-item-title">
                        	<a href="<?php  echo esc_url($course_details['permalink']);?>" target="_blank"><?php  echo esc_ulp_content($course_details['title']);?></a>
                            <?php echo do_shortcode("[ulp-course-list-tags course_id={$course_id}]");?>
                        </div>
						<div class="ulp-list-courses-item-wrapp-second-content">
                        	<div><?php echo  ulp_print_date_like_wp($course_details['create_date']);?> / <?php echo  $course_details['total_modules']. " ". esc_html__('Modules', 'ulp');?></div>
						</div>
                        <div class="ulp-list-courses-item-wrapp-excerpt">
                        	<?php  echo esc_ulp_content($course_details['excerpt']);?>
                        </div>
                        <ul class="ulp-list-courses-item-wrapp-third-content">
                        	<li>
                            	<div class="ulp-avatar">
									<img src="<?php echo esc_url($course_details['author_image']); ?>" />
                                </div>
                                <label><?php echo esc_html__('Teacher ', 'ulp'); ?></label>
                                <strong><?php  echo esc_html($course_details['author_name']);?></strong>
                            </li>
                            <li>
                            	<label><?php echo esc_html__('Students ', 'ulp'); ?></label>
                                <strong><?php  echo esc_html($course_details['number_of_students']) . ' '. esc_html__('Students ', 'ulp');?></strong>
                            </li>
                            <li>
                            	<label><?php echo esc_html__('Category ', 'ulp'); ?></label>
                                <strong><?php  echo  $course_details['category'];?></strong>
                            </li>
                        </ul>
						<div class="ulp-progress-wrapp">
								<div class="ulp-float-left"><?php echo  ulp_print_date_like_wp($course_details['start_time']);?></div>
								<div  class="ulp-float-right"><?php echo esc_html__('Expires on: ', 'ulp') .  ulp_print_date_like_wp($course_details['end_time']);?></div>
								<div class="clear"></div>
								<div class="ulp-progress-bar">
									<div class="ulp-progress-completed" style= " width:<?php echo  $course_details['progress'];?>;"></div>
                                </div>
						</div>
					</div>
				</div>
        <?php endforeach;?>
    </div>
    <?php else:?>
    <div class="ulp-additional-message"><?php esc_html_e('You have no enrolled Courses yet!', 'ulp');?></div>
    <?php endif;?>
</div>
