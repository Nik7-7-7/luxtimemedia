<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('WooCommerce Payment Integration', 'ulp');?></h3>
		<div class="inside">

			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate/Hold WooCommerce Payment Integration', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_woocommerce_payment_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_woocommerce_payment_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_woocommerce_payment_enable" value="<?php echo esc_attr($data['metas']['ulp_woocommerce_payment_enable']);?>" id="ulp_woocommerce_payment_enable" />
			</div>

			<div class="ulp-form-line">
				<div><?php esc_html_e('By activating this option you have the possibility to link a course to a WooCommerce product.', 'ulp');?></div>
				<div><?php esc_html_e('By doing this, your users can use the payment methods integrated in WooCommerce.', 'ulp');?></div>
				<div><?php esc_html_e('In WooCommerce when creating or editing a product, you will find a new section for Ultimate Learning Pro where you can link your course to the current product.', 'ulp');?></div>
			</div>

			<div class="ulp-form-line">
					<?php ulp_default_payment_type();?>
			</div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>

		</div>
	</div>

		<?php if (!empty($data['items'])):?>
			<div class="ulp-stuffbox">
				<table class="wp-list-table widefat fixed tags ulp-special-table">
					<thead>
						<tr>
							<td><?php esc_html_e('Ultimate Learning Pro Course', 'ulp');?></td>
							<td><?php esc_html_e('WooCommerce Product', 'ulp');?></td>
						</tr>
					</thead>
					<tbody class="uap-alternate">
					<?php foreach ($data['items'] as $array):?>
					<tr>
						<td><span  class="uap-list-affiliates-name-label"><a href="<?php echo admin_url('post.php?post=' . $array['course_id'] . '&action=edit');?>" target="_blank"><?php echo esc_html($array['course_label']);?></a></span></td>
						<td><a href="<?php echo admin_url('post.php?post=' . $array['product_id'] . '&action=edit');?>" target="_blank"><?php echo esc_html($array['product_label']);?></a></td>
					</tr>
					<?php endforeach;?>
					</tbody>
				</table>
			</div>
		<?php endif;?>

</form>
