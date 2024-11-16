<div class="uap-wrapper">
<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Achievements', 'uap');?></span></div>
<div class="uap-special-box">
	<form  method="post">

		<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

		<?php esc_html_e('Affiliate Username: ', 'uap');?> <input type="text" name="affiliate_username" value="<?php echo isset($_POST['affiliate_username']) ? $_POST['affiliate_username'] : '';?>" class="uap-achievement-aff"/>
		<input type="submit" value="<?php esc_html_e('Search', 'uap');?>" name="search" class="button button-primary button-large" />
	</form>
</div>


<div class="uap-stuffbox">
	<h3 class="uap-h3"><?php esc_html_e('Last 50 Achievements', 'uap');?></h3>
	<div class="inside">
	<?php
		if (!empty($data['history'])):
			foreach ($data['history'] as $item):
				$current = (empty($item['current_rank'])) ? esc_html__('None', 'uap') : $item['current_rank'];
				$prev = (empty($item['prev_rank'])) ? esc_html__('None', 'uap') : $item['prev_rank'];
				?>
				<div class="uap-achievement"><?php echo esc_html__('On', 'uap') . ' ' . $item['add_date'] . ' <b>' . $item['username'] . '</b> ' . esc_html__('has moved from ', 'uap') . $prev . ' ' . esc_html__('to', 'uap') . ' ' . $current;?>.</div>
				<?php
			endforeach;
		endif;
	?>
	</div>
</div>
</div>
