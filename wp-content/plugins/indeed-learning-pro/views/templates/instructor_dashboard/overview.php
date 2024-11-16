<h3><?php esc_html_e('Overview', 'ulp');?></h3>
<div class="ulp-instructor-dashboard-box-wrapper">
<?php if ($lastQandAEntries):?>
<div class="ulp-instructor-dashboard-box ulp-instructor-dashboard-left-box">
	<div class="ulp-instructor-dashboard-box-title"><?php esc_html_e('Last 5 Students Questions & Comments', 'ulp');?></div>
    <div class="ulp-instructor-dashboard-box-content">
    <?php foreach ($lastQandAEntries as $entity):?>
            <div class="ulp-instructor-dashboard-box-list-item">
                <i class="fa-ulp fa-intructor-dashbboard-list-<?php echo esc_attr($entity->entity_type);?>-ulp"></i>
                <span class="ulp-instructor-dashboard-box-list-item-author"><?php echo esc_html($entity->entity_author);?></span>
                <span><?php  echo  esc_html__(' says ', 'ulp'). '-'; ?></span>
                <a href="<?php echo \Ulp_Permalinks::getForQanda($entity->parent_entity_id);?>" target="_blank"><?php echo esc_ulp_content($entity->entity_content); ?></a>
                <span class="ulp-instructor-dashboard-box-list-item-date"><?php echo  esc_html__(' on ', 'ulp') . esc_ulp_content(ulp_print_date_like_wp($entity->entity_date));?></span>
            </div>
        <?php endforeach;?>
    </div>
</div>
<?php endif;?>
<?php if ($lastStudents):?>
<div class="ulp-instructor-dashboard-box ulp-instructor-dashboard-right-box">
	<div class="ulp-instructor-dashboard-box-title"><?php esc_html_e('Last 5 Students Enrolled', 'ulp');?></div>
    <div class="ulp-instructor-dashboard-box-content">
    <?php foreach ($lastStudents as $student):?>
            <div class="ulp-instructor-dashboard-box-list-item">
                <span class="ulp-instructor-dashboard-box-list-item-author"><?php echo esc_html($student->user_login);?></span>
                <span><?php  echo esc_html('('.$student->user_email.')'). esc_html__(' to ', 'ulp'). '-'; ?></span>
                <a href="<?php echo \Ulp_Permalinks::getForCourse($student->course_id);?>" target="_blank"><?php echo \DbUlp::getPostTitleByPostId($student->course_id);?></a>
                <span class="ulp-instructor-dashboard-box-list-item-date"><?php echo  esc_html__(' on ', 'ulp') . ulp_print_date_like_wp(date("Y-m-d"));?></span>
            </div>
        <?php endforeach;?>
    </div>
</div>
<?php endif;?>
</div>

<div class="ulp-instructor-dashboard-overivew-courseslist">
	<div class="ulp-instructor-dashboard-overivew-courseslist-title"><?php esc_html_e('Your last Courses', 'ulp');?></div>
	<?php if ($courses):?>
			<div class="ulp-list-courses-wrapp ulp-wishlist-courses-wrapp">
			<?php foreach ($courses as $object):?>
					<?php include ULP_PATH . 'views/templates/instructor_dashboard/miniatures-overview_single_course.php';?>
			<?php endforeach;?>
		</div>
	<?php endif;?>

</div>

<?php if ($lastAnnouncementComments):?>
<div class="ulp-instructor-dashboard-overivew-annlist">
	<div class="ulp-instructor-dashboard-overivew-annlist-title"><?php esc_html_e('Last Comments on your Announcements', 'ulp');?></div>
    <div class="ulp-instructor-dashboard-overivew-annlist-button">
  		<a href="<?php echo esc_url($addNewAnnouncementLink);?>" class="btn btn-primary pointer"><?php esc_html_e('Add new Announcement', 'ulp');?></a>
	</div>
    <div class="ulp-instructor-dashboard-overivew-annlist-content">
        <?php foreach ($lastAnnouncementComments as $entity):?>
            <div class="ulp-instructor-dashboard-overivew-annlist-item">
                <span class="ulp-instructor-dashboard-box-list-item-author"><?php echo esc_html($entity->comment_author); ?></span>
				<span><?php  echo  esc_html__(' says ', 'ulp'). '-'; ?></span>
				 <a href="<?php echo \Ulp_Permalinks::getForAnnouncement( $entity->comment_post_ID );?>" target="_blank"><?php echo  $entity->comment_content;?></a>
                 <span class="ulp-instructor-dashboard-box-list-item-date"><?php echo  esc_html__(' on ', 'ulp') . ulp_print_date_like_wp($entity->comment_date);?></span>
            </div>
        <?php endforeach;?>
    </div>
</div>
<?php endif;?>
