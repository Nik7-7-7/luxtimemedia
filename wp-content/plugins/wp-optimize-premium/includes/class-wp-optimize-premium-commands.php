<?php

if (!defined('WPO_PLUGIN_MAIN_PATH')) die('No direct access allowed');

/**
 * All commands that are intended to be available for calling from any sort of control interface (e.g. wp-admin, UpdraftCentral) go in here. All public methods should either return the data to be returned, or a WP_Error with associated error code, message and error data.
 */
class WP_Optimize_Premium_Commands extends WP_Optimize_Commands {

	/**
	 * Saves DB Analysis setting values
	 *
	 * @param  array $settings An array of data that includes true or false for click option.
	 * @return array
	 */
	public function save_table_analysis_options($settings) {
		if (isset($settings['is_enabled']) && 'true' === $settings['is_enabled']) {
			$this->options->update_option(WPO_DB_Table_Analysis::ENABLED_SETTING_NAME, 1);
		} else {
			$this->options->update_option(WPO_DB_Table_Analysis::ENABLED_SETTING_NAME, 0);
		}
		
		return array('message' => __('DB Analysis option updated.', 'wp-optimize'));
	}

	/**
	 * Wipe table usage data
	 *
	 * @return array
	 */
	public function wipe_table_analysis_data() {
		WPO_DB_Table_Analysis::wipe_usage_data();

		return array('message' => __('All data was deleted', 'wp-optimize'));
	}

	/**
	 * Get the contents of the table analysis data
	 *
	 * @param  array $data An array of data that includes the selected plugin if any
	 * @return array
	 */
	public function get_table_analysis_data($data) {
		$dashboard = WP_Optimize_Premium()->get_db_table_analysis_dashboard();

		$is_enabled = $this->options->get_option(WPO_DB_Table_Analysis::ENABLED_SETTING_NAME);

		ob_start();
		$dashboard->print_report_ui($is_enabled, $data['selected-plugin']);
		$html = ob_get_clean();
		
		return array('html' => $html);
	}
}
