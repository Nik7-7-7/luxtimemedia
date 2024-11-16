<div class="ulp-page-title"><?php esc_html_e('Manage Lessons', 'ulp');?></div>
<div class="wrap">
	<a href="<?php echo admin_url('post-new.php?post_type=ulp_lesson');?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e("Add new Lesson", 'ulp');?></a>
	<form method="get" >
		<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
		<input type="hidden" name="page" value="ultimate_learning_pro" />
		<input type="hidden" name="tab" value="ulp_lesson" />
	<?php
	require_once ULP_PATH . 'classes/admin/listing_table/ListLessons.class.php';
	$ListCourses = new ListLessons();
	$ListCourses->finalOutput();
	?>
	</form>
</div>
