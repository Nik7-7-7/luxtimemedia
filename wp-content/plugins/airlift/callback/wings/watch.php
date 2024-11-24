<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVWatchCallback')) :

class BVWatchCallback extends BVCallbackBase {
	public $db;
	public $settings;

	const WATCH_WING_VERSION = 1.5;

	public function __construct($callback_handler) {
		$this->db = $callback_handler->db;
		$this->settings = $callback_handler->settings;
	}

	public function getData($table, $limit = 0, $filter = "") {
		$result = array();
		$data = array();
		$rows = $this->db->getTableContent($table, '*', $filter, $limit);
		$last_id = 0;
		foreach ($rows as $row) {
			$result[] = $row;
			$last_id = $row['id'];
		}
		$data['last_id'] = $last_id;
		$data['rows'] = $result;
		return $data;
	}

	public function deleteBvDynamicEvents($filter = "") {
		$name = BVWPDynSync::$dynsync_table;
		return $this->db->deleteBVTableContent($name, $filter);
	}

	public function setWatchTime() {
		return $this->settings->updateOption('bvwatchtime', time());
	}

	public function getFWPrependLog($params) {
		$result = array();
		$fname = $params['fname'];
		$limit = intval($params['limit']);

		if (file_exists($fname)) {

			$result['exists'] = true;
			$tmpfname = $fname."tmp";

			if (!@rename($fname, $tmpfname)) {

				$result = array('status' => 'Error', 'message' => 'UNABLE_TO_RENAME_LOGFILE');

			} else {

				if (file_exists($tmpfname)) {

					$fsize = filesize($tmpfname);
					$result["size"] = $fsize;

					if ($fsize <= $limit) {

						$result['content'] = file_get_contents($tmpfname);

					} else {
						$handle = fopen($tmpfname, "rb");
						$result['content'] = fread($handle, $limit);
						$result['incomplete'] = true;
						fclose($handle);
					}

					$result['tmpfile'] = unlink($tmpfname);
				} else {
					$result['tmpfile'] = 'DOES_NOT_EXISTS';
				}

			}
		}

		return $result;
	}

	public function getDomainInfo($params) {
		$domain = $params["domain"];
		if (empty($domain)) {
			return array('domainData' => "DOMAIN_NOT_PRESENT");
		}

		$whoisServer = $params["host"];
		if (empty($whoisServer)) {
			return array('domainData' => "WHOIS_SERVER_NOT_PRESENT : $domain");
		}

		$conn = @fsockopen($whoisServer, $params["port"], $errno, $errstr, $params["timeout"]);
		if (!$conn) {
			return array('domainData' => "UNABLE_TO_CONNECT_TO_WHOIS_SERVER : $whoisServer : DOMAIN : $domain : ERROR : $errstr ($errno)");
		}

		try {
			fwrite($conn, "$domain\r\n");

			$response = '';
			while (!feof($conn)) {
				$response .= fgets($conn, 1024);
			}

			fclose($conn);
		} catch (Exception $e) {
			fclose($conn);
			return array('domainData' => "ERROR_WHILE_FETCHING_DATA : " . $e->getMessage());
		}

		return array('domainData' => $response);
	}

	public function process($request) {
		$db = $this->db;
		$settings = $this->settings;
		$this->setWatchTime();
		$params = $request->params;

		switch ($request->method) {
		case "getdata":
			$resp = array();

			if (isset($params['domain_params']) && is_array($params['domain_params'])) {
				$resp = array_merge($resp, $this->getDomainInfo($params['domain_params']));
			}

			if (array_key_exists('lp', $params)) {
				require_once dirname( __FILE__ ) . '/../../protect/lp.php';
				$lp_params = $params['lp'];
				if (!isset($lp_params['bv_check_table']) || $db->isTablePresent($db->getBVTable(ALProtectLP_V582::TABLE_NAME))) {
					$limit = intval($lp_params['limit']);
					$filter = $lp_params['filter'];
					$offset = isset($lp_params['offset']) ? intval($lp_params['offset']) : 0;
					$table = $db->getBVTable(ALProtectLP_V582::TABLE_NAME);
					$auto_increment = $db->getAutoIncrement($table);


					if ($auto_increment && ($auto_increment < $offset) && $lp_params['enforce_auto_incr_check']) {
						$resp["lplogs"] = array("auto_increment" => $auto_increment, "offset_reset_required" => true);
					} else {
						$db->deleteBVTableContent(ALProtectLP_V582::TABLE_NAME, $lp_params['rmfilter']);
						$resp["lplogs"] = $this->getData($table, $limit, $filter);
					}
				} else {
					$resp["lplogs"] = array("status" => "TABLE_NOT_PRESENT");
				}
			}

			if (array_key_exists('prelog', $params)) {
				$prelog_params = $params['prelog'];
				$resp["prelog"] = $this->getFWPrependLog($prelog_params);
			}

			if (array_key_exists('fw', $params)) {
				require_once dirname( __FILE__ ) . '/../../protect/fw.php';
				$fw_params = $params['fw'];
				if (!isset($fw_params['bv_check_table']) || $db->isTablePresent($db->getBVTable(ALProtectFW_V582::TABLE_NAME))) {
					$limit = intval($fw_params['limit']);
					$filter = $fw_params['filter'];
					$offset = isset($fw_params['offset']) ? intval($fw_params['offset']) : 0;
					$table = $db->getBVTable(ALProtectFW_V582::TABLE_NAME);
					$auto_increment = $db->getAutoIncrement($table);

					if ($auto_increment && ($auto_increment < $offset) && $fw_params['enforce_auto_incr_check']) {
						$resp["fwlogs"] = array("auto_increment" => $auto_increment, "offset_reset_required" => true);
					} else {
						$db->deleteBVTableContent(ALProtectFW_V582::TABLE_NAME, $fw_params['rmfilter']);
						$resp["fwlogs"] = $this->getData($table, $limit, $filter);
					}

				} else {
					$resp["fwlogs"] = array("status" => "TABLE_NOT_PRESENT");
				}
			}

			if (array_key_exists('dynevent', $params)) {
				require_once dirname( __FILE__ ) . '/../../wp_dynsync.php';
				$isdynsyncactive = $settings->getOption('bvDynSyncActive');
				if ($isdynsyncactive == 'yes') {
					if (!isset($params['bv_check_table']) || $db->isTablePresent($db->getBVTable(BVWPDynSync::$dynsync_table))) {
						$limit = intval($params['limit']);
						$filter = $params['filter'];
						$offset = isset($params['offset']) ? intval($params['offset']) : 0;
						$table = $db->getBVTable(BVWPDynSync::$dynsync_table);
						$auto_increment = $db->getAutoIncrement($table);

						if ($auto_increment && ($auto_increment < $offset) && $params['enforce_auto_incr_check']) {
							$resp = array("auto_increment" => $auto_increment, "offset_reset_required" => true);
						} else {
							$this->deleteBvDynamicEvents($params['rmfilter']);
							$data = $this->getData($table, $limit, $filter);

							$resp['last_id'] = $data['last_id'];
							$resp['events'] = $data['rows'];
							$resp['timestamp'] = time();
							$resp["status"] = true;
						}
					}
				}
			}

			if (array_key_exists('actlog', $params)) {
				require_once dirname( __FILE__ ) . '/../../wp_actlog.php';
				$actlog_params = $params['actlog'];
				if (!isset($actlog_params['bv_check_table']) || $db->isTablePresent($db->getBVTable(BVWPActLog::$actlog_table))) {
					$table = $db->getBVTable(BVWPActLog::$actlog_table);
					$limit = intval($actlog_params['limit']);
					$filter = $actlog_params['filter'];
					$offset = isset($actlog_params['offset']) ? intval($actlog_params['offset']) : 0;
					$auto_increment = $db->getAutoIncrement($table);

					if ($auto_increment && ($auto_increment < $offset) && $actlog_params['enforce_auto_incr_check']) {
						$resp["actlogs"] = array("auto_increment" => $auto_increment, "offset_reset_required" => true);
					} else {
						$db->deleteBVTableContent(BVWPActLog::$actlog_table, $actlog_params['rmfilter']);
						$resp["actlogs"] = $this->getData($table, $limit, $filter);
					}
				} else {
					$resp["actlogs"] = array("status" => "TABLE_NOT_PRESENT");
				}
			}

			if (array_key_exists('airlift_stats', $params)) {
				$airlift_stats_table = "airlift_stats";
				$airlift_stats_params = $params['airlift_stats'];
				$table = $db->getBVTable($airlift_stats_table);
				if (!isset($airlift_stats_params['bv_check_table']) || $db->isTablePresent($table)) {
					$limit = intval($airlift_stats_params['limit']);
					$filter = $airlift_stats_params['filter'];
					$offset = isset($airlift_stats_params['offset']) ? intval($airlift_stats_params['offset']) : 0;
					$auto_increment = $db->getAutoIncrement($table);

					if ($auto_increment && ($auto_increment < $offset) && $airlift_stats_params['enforce_auto_incr_check']) {
						$resp["airlift_stats"] = array("auto_increment" => $auto_increment, "offset_reset_required" => true);
					} else {
						$db->deleteBVTableContent($airlift_stats_table, $airlift_stats_params['rmfilter']);
						$resp["airlift_stats"] = $this->getData($table, $limit, $filter);
					}
				} else {
					$resp["airlift_stats"] = array("status" => "TABLE_NOT_PRESENT");
				}
			}

			if (array_key_exists('php_error_monitoring', $params)) {
				require_once dirname( __FILE__ ) . '/../../php_error_monitoring/monitoring.php';
				$php_error_monit_params = $params['php_error_monitoring'];
				$table = $db->getBVTable(ALWPPHPErrorMonitoring::ERROR_TABLE);
				if (!isset($php_error_monit_params['bv_check_table']) || $db->isTablePresent($table)) {
					$limit = intval($php_error_monit_params['limit']);
					$filter = $php_error_monit_params['filter'];
					$offset = isset($php_error_monit_params['offset']) ? intval($php_error_monit_params['offset']) : 0;
					$auto_increment = $db->getAutoIncrement($table);

					if ($auto_increment && ($auto_increment < $offset) && $php_error_monit_params['enforce_auto_incr_check']) {
						$resp["php_error_monitoring"] = array("auto_increment" => $auto_increment, "offset_reset_required" => true);
					} else {
						$db->deleteBVTableContent(ALWPPHPErrorMonitoring::ERROR_TABLE, $php_error_monit_params['rmfilter']);
						$resp["php_error_monitoring"] = $this->getData($table, $limit, $filter);
					}
				} else {
					$resp["php_error_monitoring"] = array("status" => "TABLE_NOT_PRESENT");
				}
			}

			$resp["status"] = "done";
			break;
		case "rmdata":
			require_once dirname( __FILE__ ) . '/../../wp_dynsync.php';
			$filter = $params['filter'];
			$resp = array("status" => $this->deleteBvDynamicEvents($filter));
			break;
		default:
			$resp = false;
		}
		return $resp;
	}
}
endif;