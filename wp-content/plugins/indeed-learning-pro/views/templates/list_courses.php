<?php if ($data['courses']):?>
	<div class="ulp-list-courses-wrapp">
		<?php foreach ($data['courses'] as $course_data):?>
				<?php $course = new UlpCourse($course_data['ID']);?>
				<div class="ulp-list-courses-item-wrapp">
					<?php if ($course->FeatureImage()):?>
							<div class="ulp-feat-img-wrapp">
                            	<?php if( $course->IsFeatured()): ?>
                                	<span class="ulp-sale"><span class="ulp-text-sale"><?php esc_html_e('Sale', 'ulp');?></span></span>
								<?php endif;?>
                                <a href="<?php echo Ulp_Permalinks::getForCourse($course_data['ID']);?>" class="ulp-feat-img-single-course" style= " min-width:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_width');?>px; height:<?php echo Ulp_Global_Settings::get('ulp_multiplecourses_imagesize_height');?>px; background-image:url('<?php echo esc_url($course->FeatureImage());?>');">
                                </a>
								<div class="ulp-course-price"><?php echo esc_ulp_content($course->Price());?></div>
                            </div>
					<?php endif;?>
					<div class="ulp-list-courses-item-wrapp-content">
						<div class="ulp-list-courses-item-title">
                        	<a href="<?php echo esc_url(Ulp_Permalinks::getForCourse($course_data['ID']));?>"><?php echo esc_html($course->Title());?></a>
                        	<?php echo do_shortcode("[ulp-course-list-tags course_id={$course_data['ID']}]");?>
                        </div>
						<div class="ulp-list-courses-item-wrapp-second-content">
                        	<div><?php echo esc_html($course->CreateDate());?> / <?php echo  $course->TotalModules(). " ". esc_html__('Modules', 'ulp');?></div>
						</div>
                        <div class="ulp-list-courses-item-wrapp-excerpt">
                        	<?php  echo esc_html($course->Excerpt());?>
                        </div>
                        <ul class="ulp-list-courses-item-wrapp-third-content">
                        	<li>
                            	<div class="ulp-avatar">
									<img src="<?php echo esc_url($course->AuthorImage()); ?>" />
                                </div>
                                <label><?php echo esc_html__('Teacher ', 'ulp'); ?></label>
                                <strong><?php  echo esc_html($course->Author(FALSE));?></strong>
                            </li>
                            <li>
                            	<label><?php echo esc_html__('Students ', 'ulp'); ?></label>
                                <strong><?php  echo esc_html($course->TotalStudents()) . ' '. esc_html__('Students ', 'ulp');?></strong>
                            </li>
                            <li>
                            	<label><?php echo esc_html__('Category ', 'ulp'); ?></label>
                                <strong><?php  echo  $course->Categories(TRUE);?></strong>
                            </li>
                        </ul>
                         <div class="ulp-list-courses-item-wrapp-hidden-content">
                        	<div class="ulp-list-courses-item-id">id: <?php echo esc_html($course_data['ID']);?></div>
							<div><?php echo esc_html__('Total lessons: ', 'ulp') . $course->TotalLessons();?></div>
							<div><?php echo esc_html__('Total quizzes: ', 'ulp') . $course->TotalQuizes();?></div>
						</div>
						<?php if ($course->user_can_entroll()):?>
							<?php //echo do_shortcode("[enroll-course id={$course_data['ID']}]");?>
						<?php endif;?>
						<div class="ulp-progress-wrapp">
							<?php if ($course->IsEntrolled()):?>
								<div class="ulp-float-left"><?php echo esc_html($course->EntrolledDate());?></div>
								<div  class="ulp-float-right"><?php echo esc_html__('Expires on: ', 'ulp') . $course->ExpireDate();?></div>
								<div class="clear"></div>
								<div class="ulp-progress-bar">
									<div class="ulp-progress-completed" style= " width:<?php echo esc_attr($course->Progress());?>;"></div>
                                </div>
							<?php else:?>
								<div><?php echo Ulp_Global_Settings::get('ulp_messages_list_courses_not_enrolled');?></div>
							<?php endif;?>
						</div>
					</div>
				</div>
		<?php endforeach;?>
		<?php if ($data['pagination']):?>
				<?php echo esc_ulp_content($data['pagination']);?>
		<?php endif;?>
	</div>
<?php endif;?>
