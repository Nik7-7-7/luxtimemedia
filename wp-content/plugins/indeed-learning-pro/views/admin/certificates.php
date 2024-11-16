<div class="ulp-page-title"><?php esc_html_e('Manage Certificates', 'ulp');?></div>
<div class="wrap">
	<a href="<?php echo admin_url('post-new.php?post_type=ulp_certificate');?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e("Add new Certificate", 'ulp');?></a>
	<form method="get" >
		<input type="hidden" name="page" value="ultimate_learning_pro" />
		<input type="hidden" name="tab" value="ulp_certificate" />
		<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	<?php
	require_once ULP_PATH . 'classes/admin/listing_table/ListCertificates.class.php';
	$ListCertificates = new ListCertificates();
	$ListCertificates->finalOutput();
	?>
	</form>
</div>
