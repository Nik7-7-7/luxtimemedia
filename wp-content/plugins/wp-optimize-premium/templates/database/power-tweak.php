<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="wpo-power-tweak <?php echo sanitize_html_class($action_type); ?>" data-tweak="<?php echo esc_attr($tweak_name); ?>">
	<h4><?php echo esc_html($title); ?></h4>
	<div class="actions">

		<?php if ($is_available) : ?>
			<?php if ('activate' === $action_type) : ?>

				<div class="switch-container">
					<label class="switch">
						<input id="tweak-<?php echo esc_attr($tweak_name); ?>" name="tweak[<?php echo esc_attr($tweak_name); ?>]" class="enable-tweak" type="checkbox" value="true"<?php checked($is_active); ?>>
						<span class="slider round"></span>
					</label>
					<label for="tweak-<?php echo esc_attr($tweak_name); ?>">
						<?php echo esc_html($toggle_label); ?>
					</label>
				</div>
			
			<?php elseif('run' === $action_type) : ?>

				<button class="button button-primary run-tweak" type="button"><?php echo esc_html($run); ?></button>

			<?php endif; ?>
			
			<?php do_action('wpo_power_tweak_actions', $tweak_name, $is_available); ?>

			<?php do_action('wpo_power_tweak_actions_'.$tweak_name, $is_available); ?>

		<?php else: ?>
			<?php if ('run' === $action_type && false !== $last_run) : ?>
				<div class="notice"><p class="wpo-tweak-unavailable"><?php esc_html_e('The tweak has been performed.', 'wp-optimize'); ?></p></div>
			<?php else: ?>
				<div class="notice"><p class="wpo-tweak-unavailable"><?php esc_html_e('Not available on your site', 'wp-optimize'); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ('run' === $action_type) : ?>
			<div class="last-updated<?php echo $last_run ? '' : ' hidden'; ?>"><?php esc_html_e('Last run:', 'wp-optimize'); ?> <span class="date"><?php echo esc_html(WP_Optimize()->format_date_time($last_run)); ?></span></div>
		<?php endif; ?>

		<?php if ('activate' === $action_type) : ?>
			<div class="last-updated<?php echo $updated_status ? '' : ' hidden'; ?>"><?php esc_html_e('Last time the status was changed:', 'wp-optimize'); ?> <span class="date"><?php echo esc_html(WP_Optimize()->format_date_time($updated_status)); ?></span></div>
		<?php endif; ?>

	</div>
	<div class="description"><?php echo esc_html($description); ?></div>
	<?php if ($details) : ?>
		<a href="#" class="show-details"><?php esc_html_e('Show more details', 'wp-optimize'); ?></a>
		<div class="details hidden"><?php echo esc_html($details); ?></div>
	<?php endif; ?>
	<?php if ($faq_link) : ?>
		<p><?php $wp_optimize->wp_optimize_url($faq_link, '', esc_html__('Read more on getwpo.com', 'wp-optimize').'<span class="dashicons dashicons-external"></span>', 'class="show-faqs"'); ?></p>
	<?php endif; ?>
	<div class="tweak-is-running">
		<?php esc_html_e('Running...', 'wp-optimize'); ?>
		<span class="spinner"></span>
	</div>
</div>