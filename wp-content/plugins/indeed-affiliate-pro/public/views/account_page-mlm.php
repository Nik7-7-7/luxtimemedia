<?php
global $indeed_db;
if ( !isset( $affiliate_id ) ){
		$affiliate_id = $data['id'];
}
if ( !isset( $affiliate_avatar ) ){
			$affiliateuid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
			$affiliate_avatar = uap_get_avatar_for_uid( $affiliateuid );
}
if ( !isset( $affiliate_full_name ) ){
		$affiliate_full_name = $indeed_db->get_full_name_of_user($affiliate_id);
}
?>
<div class="uap-ap-wrap">

	<?php if (!empty($data['title'])):?>
		<h3><?php echo esc_uap_content($data['title']);?></h3>
	<?php endif;?>
	<?php if (!empty($data['message'])):?>
		<p><?php echo do_shortcode($data['message']);?></p>
	<?php endif;?>

		<?php if (!empty($data['items'])):?>

			<?php wp_enqueue_script( 'uap-gstatic-charts', 'https://www.gstatic.com/charts/loader.js', ['jquery'], 8.6 );?>
			<?php wp_enqueue_script( 'uap-public-mlm', UAP_URL . 'assets/js/public-mlm.js', ['jquery'], 8.6 );?>

			<span class="uap-js-mlm-view-affiliate-children-parent-data"
						data-parent_id='<?php echo esc_attr($data['parent_id']);?>'
						data-parent_avatar="<?php echo isset( $data['parent_avatar'] ) ? $data['parent_avatar'] : '';?>"
						data-parent_full_name="<?php echo isset( $data['parent_full_name'] ) ? esc_attr($data['parent_full_name']) : '';?>"
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
									data-avatar="<?php echo esc_url($item['avatar']);?>"
									data-full_name="<?php echo esc_attr($item['full_name']);?>"
									data-amount="<?php echo esc_attr($item['amount_value'] ) . esc_html__(' rewards', 'uap');?>"
									data-id="<?php echo esc_attr($item['id']);?>"
									data-parent_id="<?php echo esc_attr($item['parent_id']);?>"
						></span>
				<?php endforeach;?>
			<?php endif;?>

<div id="uap_mlm_chart"></div>

			<table class="uap-account-table">
				<tbody>
					<thead>
						<tr>
							<th><?php esc_html_e('Affiliate', 'uap');?></th>
							<th><?php esc_html_e('Email Address', 'uap');?></th>
							<th><?php esc_html_e('MLM Level', 'uap');?></th>
							<th><?php esc_html_e('Amount', 'uap');?></th>
						</tr>
					</thead>
					<?php foreach ($data['items'] as $item):?>
					<tr>
						<td><?php echo esc_html($item['username']);?></td>
						<td><?php echo esc_html($item['email']);?></td>
						<td><?php echo esc_html($item['level']);?></td>
						<td><?php echo esc_html($item['amount_value']);?></td>
					</tr>
					<?php endforeach;?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="uap-account-detault-message">
              <div><?php esc_html_e('In order to have affiliates inside your MLM Matrix just promote the affiliate program and bring new affiliates registered with your Affiliate Link.', 'uap');?></div>
          </div>
		<?php endif;?>

</div>
