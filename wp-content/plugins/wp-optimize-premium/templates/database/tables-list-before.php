<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<p class="innodb_force_optimize--container hidden">
	<input id="innodb_force_optimize_single" type="checkbox">
	<label for="innodb_force_optimize_single"><?php esc_html_e('Optimize InnoDB tables anyway.', 'wp-optimize'); ?></label>
	<?php $wp_optimize->wp_optimize_url('https://getwpo.com/faqs/', __('Warning: you should read the FAQ on the risks of this operation first.', 'wp-optimize')); ?>
</p>