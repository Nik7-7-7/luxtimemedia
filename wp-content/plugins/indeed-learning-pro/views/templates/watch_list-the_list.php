<div class="ulp-watch-list-items-wrapp">
    <?php if ($data ['items']):?>
    <div class="ulp-list-courses-wrapp ulp-wishlist-courses-wrapp">
        <?php foreach ($data['items'] as $course_id => $course_data):?>
            <div class="ulp-list-courses-item-wrapp ulp-watch-list-item ulp-remove-<?php echo esc_attr($course_id);?>">
					<?php if($course_data['feature_image']):?>
							<div class="ulp-feat-img-wrapp">
                            	<?php if( $course_data['is_featured']): ?>
                                	<span class="ulp-sale"><span class="ulp-text-sale"><?php esc_html_e('Sale', 'ulp');?></span></span>
								<?php endif;?>
                                <div class="ulp-feat-img-single-course" style= " min-width:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_width');?>px; height:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_height');?>px; background-image:url('<?php echo esc_url($course_data['feature_image']);?>');">
                                <div class="ulp-list-course-wishlist-wrapper"></div>
                                <span class="ulp-list-course-wishlist"><i class="fa-ulp fa-watch_list-ulp ulp-pointer js-remove-watch-list" data-course_id="<?php echo esc_attr($course_id);?>"></i></span>
                                </div>

								<!--div class="ulp-course-price"></div-->
                            </div>
					<?php endif;?>
					<div class="ulp-list-courses-item-wrapp-content">
						<div class="ulp-list-courses-item-title">
                        	<a href="<?php echo Ulp_Permalinks::getForCourse($course_id);?>" target="_blank"><?php  echo esc_ulp_content($course_data['title']);?></a>
                        	<?php echo do_shortcode("[ulp-course-list-tags course_id={$course_id}]");?>
                        </div>
						<div class="ulp-list-courses-item-wrapp-second-content">
                        	<div><?php echo  ulp_print_date_like_wp($course_data['create_date']);?> / <?php echo  $course_data['total_modules']. " ". esc_html__('Modules', 'ulp');?></div>
						</div>
                        <div class="ulp-list-courses-item-wrapp-excerpt">
                        	<?php  echo esc_ulp_content($course_data['excerpt']);?>
                        </div>
                        <ul class="ulp-list-courses-item-wrapp-third-content">
                        	<li>
                            	<div class="ulp-avatar">
									<img src="<?php echo esc_url($course_data['author_image']); ?>" />
                                </div>
                                <label><?php echo esc_html__('Teacher ', 'ulp'); ?></label>
                                <strong><?php  echo$course_data['author_name'];?></strong>
                            </li>
                            <li>
                            	<label><?php echo esc_html__('Students ', 'ulp'); ?></label>
                                <strong><?php  echo esc_html($course_data['number_of_students']) . ' '. esc_html__('Students ', 'ulp');?></strong>
                            </li>
                            <li>
                            	<label><?php echo esc_html__('Category ', 'ulp'); ?></label>
                                <strong><?php  echo  $course_data['category'];?></strong>
                            </li>
                        </ul>

					</div>
				</div>
        <?php endforeach;?>
    </div>
	<?php else :?>
        <div class="ulp-additional-message"><?php esc_html_e('You have no Course saved in your Wish List', 'ulp');?></div>
    <?php endif;?>
</div>
