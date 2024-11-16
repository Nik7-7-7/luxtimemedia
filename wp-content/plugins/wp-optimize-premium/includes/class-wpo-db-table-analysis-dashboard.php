<?php

if (!defined('WPO_VERSION')) die('No direct access allowed');

if (!class_exists('WPO_DB_Table_Analysis_Dashboard')) :

class WPO_DB_Table_Analysis_Dashboard {

	/**
	 * Store information about all the installed plugins
	 *
	 * @var array
	 */
	private $plugins_data = array();
	
	/**
	 * Populate $plugins_data
	 *
	 * @return void
	 */
	public function __construct() {
		$usage = $this->get_usage();

		foreach ($usage as $plugin => $plugin_tables) {
			if (WPO_DB_Table_Analysis::WP_INTERNALS_NAME === $plugin) {
				$this->plugins_data[$plugin] = array(
					'Name' => esc_html__('WordPress Internals', 'wp-optimize'),
					'Root' => $plugin
				);
			} elseif (file_exists(WP_PLUGIN_DIR . '/' . $plugin) && (false === strpos(__DIR__, dirname($plugin)))) {
				$this->plugins_data[$plugin] = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
				list($plugin_root) = explode("/", $plugin);
				$this->plugins_data[$plugin]['Root'] = $plugin_root;
			}
		}
	}

	/**
	 * Return how many plugins we have information about
	 *
	 * @return int
	 */
	private function get_plugin_count() {
		return count($this->plugins_data);
	}

	/**
	 * Generate the HTML to show a dropbox with the list of plugins to select from
	 *
	 * @param string $selected_plugin Pre selected plugin
	 * @return string
	 */
	private function get_plugins_dropbox_html($selected_plugin = null) {
		$usage = $this->get_usage();

		$plugins_dropbox = array();

		foreach ($usage as $plugin => $plugin_tables) {
			if (empty($this->plugins_data[$plugin])) {
				continue;
			}
			
			$plugin_root = $this->plugins_data[$plugin]['Root'];
			$plugins_dropbox[$plugin_root] = '<option value="' . esc_attr($plugin_root) . '" ' . selected($selected_plugin, $plugin_root, false) . '>' . esc_html($this->plugins_data[$plugin]['Name']) . '</option>';
		}

		ksort($plugins_dropbox);

		$plugins_dropbox = implode('', $plugins_dropbox);

		return '<select id="wpo-plugin-usage-select"><option selected="selected" value="">' . esc_html__('Select one', 'wp-optimize') . '</option>' . $plugins_dropbox . '</select>'; // phpcs:ignore WordPress.Security.EscapeOutput -- Output is already escaped
	}

	/**
	 * Print the table if there is data to be shown
	 *
	 * @param string $show_plugin Plugin to show by default, if exists
	 * @return void
	 */
	private function display_table($show_plugin = null) {
		$usage = $this->get_usage();

		foreach ($usage as $plugin => $plugin_tables) {
			if (empty($this->plugins_data[$plugin])) {
				continue;
			}

			$value = $this->plugins_data[$plugin]['Root'];
			echo '<div class="wpo-plugin-tables-usage ' . ($show_plugin === $value ? '' : 'hidden') . '" id="wpo-plugin-tables-usage-' . esc_attr($value) . '">';

			$table = new WP_Optimize_Queries_List_Table();
			$table->prepare_items();

			$sortable_rows = array();

			foreach ($plugin_tables as $table_name => $data) {
				$sortable_rows[] = array(
					'table_name' => $table_name,
					'last_used' => $data['last_used'],
					'total_queries' => count($data['per_query']),
					'avg_per_min' => WPO_DB_Table_Analysis::report_query_usage_per_minute($data['per_thread']),
					'avg_time' => WPO_DB_Table_Analysis::report_query_time_average($data['per_query']),
					'slow_queries_count' => count($data['slow_query_stack']),
				);
			}
			
			usort($sortable_rows, array($this, 'sort_by_slow_queries_count'));
			foreach ($sortable_rows as $row) {
				$thickbox_title = $this->maybe_print_thickbox_content($plugin, $plugin_tables, $row);

				$row["slow_queries_detail"] = $thickbox_title;
				$table->add_item($row);
			}

			$table->display();

			echo "</div>";
		}
	}

	/**
	 * Echo the contents of the report to STDOUT
	 *
	 * @param string $selected_plugin Optional Select a plugin to be shown by default
	 * @return void
	 */
	private function print_report($selected_plugin = null) {
		echo $this->get_report($selected_plugin); // phpcs:ignore WordPress.Security.EscapeOutput -- Output is already escaped
	}

	/**
	 * Echo the report container with auto-refresh in 5 seconds if it is enabled but has no information to show yet
	 *
	 * @param bool   $is_enabled      Is the feature setting enabled
	 * @param string $selected_plugin Optional Select a plugin to be shown by default
	 * @return void
	 */
	public function print_report_ui($is_enabled, $selected_plugin = null) {
		if ($is_enabled) {
			if (0 === $this->get_plugin_count()) {
				echo esc_html__('No information available yet', 'wp-optimize'). '. ' . esc_html__('Please wait a few seconds while we refresh...', 'wp-optimize');
			
			?>
			<script>
				setTimeout(WP_Optimize_Premium.reload_table_analysis_report, 5000);
			</script>
			<?php
			}
		} else {
			echo esc_html__('Please enable this feature to see the report', 'wp-optimize');
		}
		?>
		<div id="table-analysis-report" class="<?php echo ($is_enabled && (0 < $this->get_plugin_count())) ? '' : 'hidden'; ?>">
		<?php
			$this->print_report($selected_plugin);
		?>
		</div>
		<?php
	}
	
	/**
	 * Build the report
	 *
	 * @param string $selected_plugin Optional Select a plugin to be shown by default
	 * @return string
	 */
	private function get_report($selected_plugin = null) {
		$response = '<h2>' . esc_html__('Tables Usage Report', 'wp-optimize') . '</h2>
			' . esc_html__('Select plugin', 'wp-optimize') . ': ' . $this->get_plugins_dropbox_html($selected_plugin) . '
			<input id="wpo-db-analysis-table-search" class="' . (null != $selected_plugin ? '' : 'hidden') . '" placeholder="' . esc_attr__('Search table', 'wp-optimize') . '...">
			<label id="wpo-table-analysis-reload" class="' . (null != $selected_plugin ? '' : 'hidden') . '">' . esc_html__('Reload', 'wp-optimize') . ' <span class="dashicons dashicons-update"></span></label><hr class="wpo-thin-bar">';

		ob_start();
		$this->display_table($selected_plugin);
		$response .= ob_get_clean();

		return $response;
	}


	/**
	 * If there are slow queries information, then add the thickbox HTML so user can obtain extended information.
	 * If there is a thickbox, return the box title
	 *
	 * @param string $plugin        The plugin slug
	 * @param array  $plugin_tables Usage data specific to this table
	 * @param array  $row           The table row data
	 * @return string
	 */
	private function maybe_print_thickbox_content($plugin, $plugin_tables, $row) {
		$thickbox_title = '';

		if ((0 < $row['slow_queries_count']) && !empty($this->plugins_data[$plugin])) {
			// translators: 1: Table name, 2: Number of slow queries
			$thickbox_title = sprintf(__('Total slow queries in table `%1$s`: %2$s', 'wp-optimize'), esc_html($row['table_name']), esc_html($row['slow_queries_count']));
			$thickbox_title = '<a title="' . esc_attr($thickbox_title) . '" href="#TB_inline?&width=600&height=550&inlineId=slow-queries-detail-' . esc_attr($this->plugins_data[$plugin]['Root']) . '-' . esc_attr($row['table_name']) . '" class="thickbox">...</a>';

			echo '<div id="slow-queries-detail-' . esc_attr($this->plugins_data[$plugin]['Root']) . '-' . esc_attr($row['table_name']) . '" class="hidden"><p>';
			
			$slow_queries = array();

			foreach ($plugin_tables[$row['table_name']]['slow_query_stack'] as $query_id => $stack) {
				$query_time = $plugin_tables[$row['table_name']]['per_query'][$query_id];
				$stack = explode(', ', $stack);

				foreach ($stack as &$stack_item) {
					$stack_item = esc_html($stack_item);
				}

				$slow_queries[] = array(
					'time' => $query_time,
					'detail' => '<h4><!--nthquery--> ' . esc_html__('Query time', 'wp-optimize') . ': ' . round($query_time / 1000, 2) . ' ' . esc_html__('milliseconds', 'wp-optimize') . '</h4>
							<h5>' . esc_html__('Call Stack', 'wp-optimize') . '</h5><ul><li>' . implode('</li><li>', $stack) . '</li></ul><hr>' // phpcs:ignore WordPress.Security.EscapeOutput -- Output is already escaped
				);

				unset($stack_item);
			}
			
			usort($slow_queries, array($this, 'sort_by_slow_queries_time'));
			foreach ($slow_queries as $pos => $sq) {
				echo htmlspecialchars_decode(esc_html(str_replace('<!--nthquery-->', '#' . ($pos + 1), $sq['detail'])));
			}

			echo '</p></div>';
		}

		return $thickbox_title;
	}

	/**
	 * Sort by total count in DESC order
	 *
	 * @param array $a First item
	 * @param array $b Second item
	 * @return int
	 */
	private function sort_by_slow_queries_count($a, $b) {
		return $a['slow_queries_count'] < $b['slow_queries_count'] ? 1 : -1;
	}

	/**
	 * Sort by execution time in DESC order
	 *
	 * @param array $a First item
	 * @param array $b Second item
	 * @return int
	 */
	private function sort_by_slow_queries_time($a, $b) {
		return $a['time'] < $b['time'] ? 1 : -1;
	}

	/**
	 * Grab usage data from DB, use static data to prevent multiple calls
	 *
	 * @return array
	 */
	private function get_usage() {
		static $data;

		if (!isset($data)) {
			$data = get_option(WPO_DB_Table_Analysis::TABLE_USAGE_OPTION_NAME);
		}
		
		return is_array($data) ? $data : array();
	}
}

endif;
