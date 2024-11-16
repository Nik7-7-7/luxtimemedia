<?php if (!defined('WPO_VERSION')) die('No direct access allowed');

add_thickbox();

if (false === isset($selected_plugin)) {
	$selected_plugin = null;
}

?>

<div id="wp-optimize-database-table-analysis" class="wpo_section wpo_group">
    <div class="notice notice-warning wpo-warning is-dismissible">
        <p>
            <span class="dashicons dashicons-shield"></span>
            <strong><?php esc_html_e('This is an advanced feature; keeping it turned on continuously may lead to performance issues.', 'wp-optimize'); ?></strong><br>
			<?php esc_html_e('Use this feature only for debugging performance problems in SQL queries.', 'wp-optimize'); ?>
        </p>
    </div>
	<div class="wpo-fieldgroup">
		<div class="switch-container">
			<label class="switch">
				<input id="enable-db-analysis" name="enable-db-analysis" type="checkbox" value="true"<?php checked($is_enabled); ?>>
				<span class="slider round"></span>
			</label>
			<label for="enable-db-analysis">
				<?php echo esc_html__('Run the query analysis for all executed queries in this site', 'wp-optimize'); ?>
			</label>
		</div>

		<button id="wpo-wipe-table-usage-data" class="button"><?php echo esc_html__('Wipe usage information', 'wp-optimize'); ?></button>
		
		<label style="margin-top: 5px; display: block;">
			<?php echo esc_html__('Delete all the information about queries that are currently stored in the database', 'wp-optimize'); ?>
		</label>
		
		<div id="wpo-save-options-response" class="hidden"></div>
	
	</div>
	<?php
if (defined('SAVEQUERIES') && !SAVEQUERIES) {

?>
<div class="notice notice-warning">
    <p><span class="dashicons dashicons-info"></span> <?php echo sprintf(esc_html__('Query times are not being recorded because `%s` is set to `false` in your `%s`.', 'wp-optimize'), 'SAVEQUERIES', 'wp-config.php') . ' ' . sprintf(esc_html__('To troubleshoot query performance issues, consider setting `%s` to `true` temporarily to enable query time logging.', 'wp-optimize'), 'SAVEQUERIES'); ?></p>
</div>
<?php
}
?>
	<div style="height: 20px; text-align: right;" id="wpo_loading_spinner">
		<span class="hidden"><?php echo esc_html__('Refreshing...', 'wp-optimize'); ?></span> <img class="wpo_spinner hidden" src="<?php echo esc_url(admin_url('images/spinner-2x.gif')); ?>" alt="...">
	</div>
	<div id="wpo-table-analysis-report-container">
	<?php
	$dashboard->print_report_ui($is_enabled, $selected_plugin);
	?>
	</div>
</div><!-- end #wp-optimize-database-table-analysis -->
