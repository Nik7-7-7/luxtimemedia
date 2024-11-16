<?php
global $indeed_db;
if ( !isset( $affiliate_id ) ){
		$affiliate_id = $data['affiliate_id'];
}
if ( !isset( $affiliate_avatar ) ){
	  $affiliateuid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
		$affiliate_avatar = uap_get_avatar_for_uid( $affiliateuid );
}
if ( !isset( $affiliate_full_name ) ){
		$affiliate_full_name = $indeed_db->get_full_name_of_user($affiliate_id);
}
?>
<div class="uap-stuffbox">
	<h3 class="uap-h3"><?php echo esc_html__('Display MLM Matrix');?></h3>
	<div class="inside">

	<?php if (!empty($data['items']) || !empty($data['parent'])):?>
		<?php
				if ( !isset( $data['parent_id'] ) ){
						$data['parent_id'] = '';
				}
				if ( !isset( $data['parent_avatar'] ) ){
						$data['parent_avatar'] = '';
				}
				if ( !isset( $data['parent_full_name'] ) ){
						$data['parent_full_name'] = '';
				}
				wp_enqueue_script( 'gstatic-loader', 'https://www.gstatic.com/charts/loader.js', ['jquery'], false );
		?>

				<span class="uap-js-mlm-view-affiliate-children-parent-data"
							data-parent_id='<?php echo esc_attr($data['parent_id']);?>'
							data-parent_avatar="<?php echo esc_uap_content($data['parent_avatar']);?>"
							data-parent_full_name="<?php echo esc_attr($data['parent_full_name']);?>"
							data-parent="<?php echo esc_attr($data['parent']);?>"
				></span>

				<span class="uap-js-mlm-view-affiliate-data"
							data-affiliate_id='<?php echo esc_attr($affiliate_id);?>'
							data-affiliate_avatar="<?php echo esc_attr($affiliate_avatar);?>"
							data-parent_full_name="<?php echo esc_attr($affiliate_full_name);?>"
				></span>


		<?php if ( !empty( $data['items'] ) ):?>
			<?php foreach ( $data['items'] as $item ):?>
					<span class="uap-js-mlm-view-affiliate-children-data"
								data-avatar="<?php echo esc_uap_content($item['avatar']);?>"
								data-full_name="<?php echo esc_attr($item['full_name']);?>"
								data-amount="<?php echo esc_attr($item['amount_value']) . esc_html__(' rewards', 'uap');?>"
								data-id="<?php echo esc_attr($item['id']);?>"
								data-parent_id="<?php echo esc_attr($item['parent_id']);?>"
					></span>
			<?php endforeach;?>
		<?php endif;?>

   <div id="uap_mlm_chart"></div>

			<table class="uap-dashboard-inside-table">
				<tbody>
					<tr>
						<th><?php esc_html_e('Subaffiliate', 'uap');?></th>
						<th><?php esc_html_e('Level', 'uap');?></th>
						<th><?php esc_html_e('Amount', 'uap');?></th>
					</tr>
					<?php foreach ($data['items'] as $item):?>
					<tr>
						<td><?php echo esc_html($item['username']);?></td>
						<td><?php echo esc_html($item['level']);?></td>
						<td><?php echo esc_html($item['amount_value']);?></td>
					</tr>
					<?php endforeach;?>
				</tbody>
			</table>
		<?php else : ?>
			<?php esc_html_e('Current Affiliate user has no other sub-affiliates into his MLM Matrix on this moment', 'uap');?>
		<?php endif;?>

	</div>
</div>
