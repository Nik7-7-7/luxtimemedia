<div class="ulp-page-title"><?php esc_html_e('Payment History', 'ulp');?></div>
<div class="wrap">
	<a href="<?php echo admin_url('post-new.php?post_type=ulp_order');?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e("Add new Payment", 'ulp');?></a>
	<form method="get" >
		<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
		<input type="hidden" name="page" value="ultimate_learning_pro" />
		<input type="hidden" name="tab" value="ulp_order" />
	<?php
	require_once ULP_PATH . 'classes/admin/listing_table/ListOrders.class.php';
	$ListCourses = new ListOrders();
	$ListCourses->finalOutput();
	?>
	</form>
</div>
<span class="ulp-js-init-print-this" data-load_css="<?php echo ULP_URL . 'assets/css/public.css';?>"></span>
