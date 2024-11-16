<?php
if (!defined('WPO_VERSION')) die('No direct access allowed');

require_once __DIR__ . '/../vendor/autoload.php';

use PHPSQLParser\PHPSQLParser;

if (!class_exists('WPO_DB_Table_Analysis')) :

class WPO_DB_Table_Analysis {

	/**
	 * Name of the option to enable/disable the analysis
	 *
	 * @var string
	 */
	const ENABLED_SETTING_NAME = 'enable-db-analysis';

	/**
	 * DB queries stack
	 *
	 * @var array
	 */
	private $queries = array();

	/**
	 * Time-to-live for query report data
	 *
	 * @var int
	 */
	const REPORT_DATA_TTL_IN_SECONDS = 3600;

	/**
	 * How many PIDs at least to keep, so we can build statistics, ignoring TTL
	 *
	 * @var int
	 */
	const REPORT_PID_MINIMUM_COUNT_KEEP = 10;

	/**
	 * How many PIDs at most to keep, so we can build statistics, to avoid large report data
	 *
	 * @var int
	 */
	const REPORT_PID_MAXIMUM_COUNT_KEEP = 50;

	/**
	 * How many query snapshots (query per N-seconds grouping count) we want to keep
	 *
	 * @var int
	 */
	const REPORT_SNAPSHOTS_MAXIMUM_COUNT_KEEP = 50;

	/**
	 * How many query execution times we want to keep, sorted from slowest to fastest
	 *
	 * @var int
	 */
	const REPORT_QUERY_MAXIMUM_COUNT_KEEP = 15;

	/**
	 * Window size for snapshots grouping, in seconds
	 *
	 * @var int
	 */
	const SNAP_SECONDS_GROUPING = 5;

	/**
	 * If a query is slower than X nanoseconds, save the call stack in the report
	 *
	 * @var int
	 */
	const QUERY_SAVE_STACK_THRESHOLD_MICROSECONDS = 1000;
	
	/**
	 * WP Option name to store usage data
	 *
	 * @var string
	 */
	const TABLE_USAGE_OPTION_NAME = 'wp-optimize-table-usage';
	
	/**
	 * Group WordPress internal queries under this name
	 *
	 * @var string
	 */
	const WP_INTERNALS_NAME = 'wordpress-internals';

	/**
	 * Register the `shutdown` event to do the analysis after user got their response, to avoid slowing down requests
	 * Also register filters to gather query data
	 *
	 * @return void
	 */
	public function __construct() {
		$handler = array($this, 'execute_query_analysis');
		if (!has_action('shutdown', $handler)) {
			add_action('shutdown', $handler);
		}
		
		if (!defined('SAVEQUERIES')) {
			define('SAVEQUERIES', true);
		}
		
		if (true === SAVEQUERIES) {
			$handler = array($this, 'save_plugin_query_filter');
			if (!has_filter('log_query_custom_data', $handler)) {
				add_filter('log_query_custom_data', $handler, 10, 4);
			}
		} else {
			// We cannot extract extended information about the queries if SAVEQUERIES is disabled
			$handler = array($this, 'save_plugin_query_raw_filter');
			if (!has_filter('query', $handler)) {
				add_filter('query', $handler);
			}
		}
	}

	/**
	 * Initialize the class
	 *
	 * @return WPO_DB_Table_Analysis
	 */
	static public function get_instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Filter only the query, without extended information
	 *
	 * @param string $query Query SQL
	 * @return string
	 */
	public function save_plugin_query_raw_filter($query) {
		$this->save_plugin_query_filter(array(), $query, 0, "");

		return $query;
	}

	/**
	 * Filter called on every DB query, saves it for later analysis. Because it is a filter, it has to return the same $query_data
	 *
	 * @param array  $query_data      Custom query data.
	 * @param string $query           The query's SQL.
	 * @param float  $query_time      Total time spent on the query, in seconds.
	 * @param string $query_callstack Comma-separated list of the calling functions.
	 * @return array
	 */
	public function save_plugin_query_filter($query_data, $query, $query_time, $query_callstack) {
		$plugin_name = self::get_plugin_path_from_debug_backtrace();

		if (!$this->is_self_reference() && ('' !== $plugin_name)) {
			$this->queries[$plugin_name][] = array(
				"query" => $query,
				"time" => $query_time,
				"stack" => $query_callstack
			);
		}

		return $query_data;
	}

	/**
	 * During `shutdown` event, after user got their response, finish the request before analyzing the query and saving report to database
	 *
	 * @return void
	 */
	public function execute_query_analysis() {
		if (ob_get_contents()) ob_end_clean();
		ignore_user_abort();
		ob_start();
		ob_end_flush();
		flush();
		if (is_callable('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}
		if (is_callable( 'litespeed_finish_request')) {
			litespeed_finish_request();
		}
		
		$script_pid = getmypid();

		$table_usage = get_option(self::TABLE_USAGE_OPTION_NAME, array());

		foreach ($this->queries as $plugin_name => $queries) {
			foreach ($queries as $query_data) {
				$query = $query_data['query'];
				$query_md5 = md5($query);

				$query_time = round($query_data['time'] * 1000000); // Convert to microseconds

				$query_call_stack = $query_data['stack'];

				$table_name = self::get_query_table($query);
		
				if ('' !== $table_name) {
					$table_usage[$plugin_name][$table_name]['last_used'] = date("Y-m-d H:i:s");

					// round data to per self::SNAP_SECONDS_GROUPING basis
					$snap_ts = round(time() / self::SNAP_SECONDS_GROUPING) * self::SNAP_SECONDS_GROUPING;
					$snap_ts_utc = date('Y-m-d H:i:s', $snap_ts);

					if (!isset($table_usage[$plugin_name][$table_name]['per_thread'])) {
						$table_usage[$plugin_name][$table_name]['per_thread'] = array();
					}

					if (!isset($table_usage[$plugin_name][$table_name]['per_query'])) {
						$table_usage[$plugin_name][$table_name]['per_query'] = array();
					}

					if (!isset($table_usage[$plugin_name][$table_name]['slow_query_stack'])) {
						$table_usage[$plugin_name][$table_name]['slow_query_stack'] = array();
					}

					if (!isset($table_usage[$plugin_name][$table_name]['per_thread'][$script_pid])) {
						$table_usage[$plugin_name][$table_name]['per_thread'][$script_pid] = array('ts' => $snap_ts_utc, 'snaps' => array());
					}

					if (!isset($table_usage[$plugin_name][$table_name]['per_thread'][$script_pid]['snaps'][$snap_ts_utc])) {
						$table_usage[$plugin_name][$table_name]['per_thread'][$script_pid]['snaps'][$snap_ts_utc] = 0;
					}

					$table_usage[$plugin_name][$table_name]['per_thread'][$script_pid]['snaps'][$snap_ts_utc]++;

					if (isset($table_usage[$plugin_name][$table_name]['per_query'][$query_md5])) {
						$table_usage[$plugin_name][$table_name]['per_query'][$query_md5] = max($table_usage[$plugin_name][$table_name]['per_query'][$query_md5], $query_time);
					} else {
						$table_usage[$plugin_name][$table_name]['per_query'][$query_md5] = $query_time;
					}

					if (self::QUERY_SAVE_STACK_THRESHOLD_MICROSECONDS < $table_usage[$plugin_name][$table_name]['per_query'][$query_md5]) {
						$table_usage[$plugin_name][$table_name]['slow_query_stack'][$query_md5] = $query_call_stack;
					}

					
					// Only keep latest data to avoid evergrowing reports
					$table_usage = $this->remove_old_threads($table_usage);

				}
			}
		}

		// Use native `update_option` so we can set autoload=false, we only use this option for query analysis
		update_option(self::TABLE_USAGE_OPTION_NAME, $table_usage, false);
	}

	/**
	 * Obtain from the stack trace which plugin executed this query
	 *
	 * @return string
	 */
	static public function get_plugin_path_from_debug_backtrace() {
		$ignored_classes = array(
			'wpdb'
		);
		
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($trace as $trc) {
			$ignore = isset($trc['class']) && in_array($trc['class'], $ignored_classes);

			if (isset($trc['file']) && (__FILE__ != $trc['file']) && (false !== strpos($trc['file'], WP_PLUGIN_DIR)) && !$ignore) {
				$aux = explode(WP_PLUGIN_DIR, $trc['file']);
				$aux = explode('/', $aux[1]);
				$plugin_name = $aux[1];
				
				// Check if get_plugins() function exists. This is required on the front end of the
				// site, since it is in a file normally only loaded in the admin.
				if (!function_exists( 'get_plugins')) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$all_plugins = get_plugins();
				
				foreach ($all_plugins as $plugin_file => $plugin_data) {
					list($plugin_folder) = explode('/', $plugin_file);

					if ($plugin_folder === $plugin_name) {
						return $plugin_file;
					}
				}
			}
		}

		// No plugin folder found in backtrace paths, must be WordPress internals
		return self::WP_INTERNALS_NAME;
	}

	/**
	 * Detect if this class executes the query
	 *
	 * @return boolean
	 */
	private function is_self_reference() {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		
		$this_class_count = 0;

		foreach ($trace as $trc) {
			if (isset($trc['class']) && ('WPO_DB_Table_Analysis' === $trc['class']) && ('analyze_plugin_query' === $trc['function'])) {
				$this_class_count++;
			}
		}

		return $this_class_count > 1;
	}

	/**
	 * Use a lightweight SQL parser to retrieve the table name, only for methods that are of our interest
	 *
	 * @param string $query The query to be parsed
	 * @return string
	 */
	static public function get_query_table($query) {
		$parseable_methods = array(
			'SELECT' => 'FROM',
			'UPDATE' => 'UPDATE',
			'DELETE' => 'FROM',
			'INSERT' => 'INSERT',
		);

		$parser = new PHPSQLParser();
		$parsed = $parser->parse($query);

		$is_parseable = false;
		$method_delimiter = "";
		foreach ($parseable_methods as $sql_command_name => $sql_command_operator) {
			if (isset($parsed[$sql_command_name])) {
				$is_parseable = true;
				$method_delimiter = $sql_command_operator;
				break;
			}
		}

		if ($is_parseable && isset($parsed[$method_delimiter])) {
			$table_name = '';

			foreach ($parsed[$method_delimiter] as $method_parts) {
				if ('table' === $method_parts['expr_type']) {
					$table_name = str_replace('`', '', $method_parts['table']);
				} elseif ('subquery' === $method_parts['expr_type']) {
					$table_name = self::get_query_table($method_parts['base_expr']);
				}
			}

			return $table_name;
		} else {
			return '';
		}
	}

	/**
	 * Get rid of old information, but always leave at least self::REPORT_PID_MINIMUM_COUNT_KEEP pids for statistical purposes
	 *
	 * @param array $usage Array of pid table usage
	 * @return array
	 */
	public function remove_old_threads($usage) {
		$ttl = self::REPORT_DATA_TTL_IN_SECONDS;
		$pid_count_to_keep = self::REPORT_PID_MINIMUM_COUNT_KEEP;
		$pid_max_count_to_keep = self::REPORT_PID_MAXIMUM_COUNT_KEEP;

		$dates_max_count_to_keep = self::REPORT_SNAPSHOTS_MAXIMUM_COUNT_KEEP;

		$remove_from = date("Y-m-d H:i:s", time() - $ttl);

		$pid_keep = array();
		foreach ($usage as $plugin => $tables) {
			foreach ($tables as $table => $pids) {
				
				uasort($pids['per_thread'], array($this, 'sort_pids_by_ts'));
				foreach ($pids['per_thread'] as $pid => $dates) {
					if ($dates['ts'] > $remove_from || count($pid_keep) < $pid_count_to_keep) {
						uksort($dates['snaps'], array($this, 'sort_snaps_by_ts'));

						$keep_snaps = array();
						foreach ($dates['snaps'] as $date => $count) {
							if (count($keep_snaps) < $dates_max_count_to_keep) {
								$keep_snaps[$date] = $count;
							}
						}
						$dates['snaps'] = $keep_snaps;

						$pid_keep[$pid] = $dates;
		
						if (count($pid_keep) >= $pid_max_count_to_keep) {
							break;
						}
					}
				}

				arsort($pids['per_query']);
				$pids['per_query'] = array_slice($pids['per_query'], 0, self::REPORT_QUERY_MAXIMUM_COUNT_KEEP);

				$usage[$plugin][$table]['per_thread'] = $pid_keep;
				$usage[$plugin][$table]['per_query'] = $pids['per_query'];

				foreach ($usage[$plugin][$table]['slow_query_stack'] as $slow_key => $slow_stack) {
					if (!array_key_exists($slow_key, $usage[$plugin][$table]['per_query'])) {
						unset($usage[$plugin][$table]['slow_query_stack'][$slow_key]);
					}
				}
				
			}
		}

		return $usage;
		
	}

	/**
	 * Sort array by timestamp
	 *
	 * @param array $a PID date element
	 * @param array $b PID date element
	 * @return int
	 */
	public function sort_pids_by_ts($a, $b) {
		return $a['ts'] < $b['ts'] ? 1 : ($a['ts'] === $b['ts'] ? 0 : -1);
	}

	/**
	 * Sort snapshot array by ts
	 *
	 * @param string $a UTC date
	 * @param string $b UTC date
	 * @return int
	 */
	public function sort_snaps_by_ts($a, $b) {
		return $a < $b ? 1 : ($a === $b ? 0 : -1);
	}

	/**
	 * Accumulate total queries from per thread summary
	 *
	 * @param array $threads Query total per minute per thread
	 * @return int
	 */
	static public function report_query_usage_per_minute($threads) {
		$per_minute = array();
		foreach ($threads as $info) {
			foreach ($info['snaps'] as $ts => $count) {
				if (!isset($per_minute[$ts])) {
					$per_minute[$ts] = 0;
				}
				$per_minute[$ts] += $count;
			}
		}

		if (0 === count($per_minute)) {
			return 0;
		} else {
			return round(array_sum($per_minute) / count($per_minute), 2);
		}
	}

	/**
	 * Calculate average time for all queries
	 *
	 * @param array $data Query time data
	 * @return string
	 */
	static public function report_query_time_average($data) {
		if (0 === count($data)) {
			return "N/A";
		} else {
			return round(array_sum($data) / count($data) / 1000, 2) . ' ms';
		}
	}

	/**
	 * Remove usage data from the DB
	 *
	 * @return void
	 */
	static public function wipe_usage_data() {
		delete_option(self::TABLE_USAGE_OPTION_NAME);
	}
}

endif;
