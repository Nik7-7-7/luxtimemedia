<div class="ulp-page-title"><?php esc_html_e('Manage Courses', 'ulp');?></div>
<div class="wrap">
	<a href="<?php echo admin_url('post-new.php?post_type=ulp_course');?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e("Add new Course", 'ulp');?></a>
	<form method="get" >
		<input type="hidden" name="page" value="ultimate_learning_pro" />
		<input type="hidden" name="tab" value="ulp_course" />
	<?php
	require_once ULP_PATH . 'classes/admin/listing_table/ListCourses.class.php';
	$ListCourses = new ListCourses();
	$ListCourses->finalOutput();
	?>
	</form>
</div>
