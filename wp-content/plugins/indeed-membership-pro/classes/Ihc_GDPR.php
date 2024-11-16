<?php
namespace Indeed\Ihc;

class Ihc_GDPR
{
    private $pluginName = 'Ultimate Membership Pro';
    private $uid = 0;

    public function __construct()
    {
        add_action('admin_init', array($this, 'privacyPolicy'));
        add_filter('wp_privacy_personal_data_exporters', array($this, 'registerExport') );
			  add_filter('wp_privacy_personal_data_erasers', array($this, 'registerErase') );
    }

    public function privacyPolicy()
    {
        if (!function_exists('wp_add_privacy_policy_content')){
           return;
        }
        $policyText = '';
        wp_add_privacy_policy_content($this->pluginName, $policyText);
    }

    public function registerExport($exporters=array())
    {
        $exporters['ump-exporter'] = array(
            'exporter_friendly_name'	=> $this->pluginName,
            'callback'		          	=> array($this, 'doExport'),
        );
        return $exporters;
    }

    public function registerErase($erasers=array())
    {
        $erasers['ump-eraser'] = array(
        		'eraser_friendly_name'    => $this->pluginName,
        		'callback'                => array($this, 'doErase'),
      	);

      	return $erasers;
    }

    /*
      Export data from the following tables:
        usermeta (all ump usermetas)
        ihc_user_levels
        ihc_user_logs
        ihc_security_login
        ihc_download_monitor_limit
        ihc_cheat_off
        indeed_members_payments
        ihc_user_sites
        ihc_orders
        ihc_orders_meta
    */
    public function doExport($emailAddress='', $page=1)
    {
        $user = get_user_by( 'email', $emailAddress );
        $this->uid = $user->ID;

        $done = false;
        $userMetas = $this->getUserMetas();
        if ($userMetas && count($userMetas)){
            $done = true;
        }
        $userLevels = $this->getUserLevels();
        if ($userLevels && count($userLevels)){
            $done = true;
        }
        $userLogs = $this->getUserLogs();
        if ($userLogs && count($userLogs)){
            $done = true;
        }
        $securityLogin = $this->getSecurityLogin();
        if ($securityLogin && count($securityLogin)){
            $done = true;
        }
        $downloadMonitorLimit = $this->getDownloadMonitorLimit();
        if ($downloadMonitorLimit && count($downloadMonitorLimit)){
            $done = true;
        }
        $cheatOff = $this->getCheatOff();
        if ($cheatOff && count($cheatOff)){
            $done = true;
        }
        $payments = $this->getPayments();
        if ($payments && count($payments)){
            $done = true;
        }
        $userSites = $this->getUserSites();
        if ($userSites && count($userSites)){
            $done = true;
        }
        $orders = $this->getOrders();
        if ($orders && count($orders)){
            $done = true;
        }
        $ordersMeta = $this->getOrderMetas();
        if ($ordersMeta && count($ordersMeta)){
            $done = true;
        }

        $exportData = array(
            $userMetas,
            $userLevels,
            $userLogs,
            $securityLogin,
            $downloadMonitorLimit,
            $cheatOff,
            $payments,
            $userSites,
            $orders,
            $ordersMeta
        );

        return array(
          'data' => $exportData,
        	'done' => 1
        );
    }

    /*
      Delete data from the following tables:
        usermeta (all ump usermetas)
        ihc_user_levels
        ihc_user_logs
        ihc_security_login
        ihc_download_monitor_limit
        ihc_cheat_off
        ihc_user_sites
    */
    public function doErase($emailAddress='', $page=1)
    {
        $user = get_user_by('email', $emailAddress );
        $this->uid = $user->ID;
        global $wpdb;
        /// usermeta
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->usermeta}
                                      WHERE
                                      user_id=%d
                                      ", $this->uid );
        $query .= " AND (meta_key LIKE '%ihc_%' OR meta_key LIKE '%user_levels%')";
        $wpdb->query( $query );
        /// ihc_user_levels
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_levels
                                      WHERE user_id=%d;", $this->uid );
        $wpdb->query( $query );
        /// ihc_user_logs
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_logs
                                    WHERE uid=%d", $this->uid );
        $wpdb->query( $query );
        /// ihc_security_login
        $username = \Ihc_Db::get_username_by_wpuid($this->uid);
        $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}ihc_security_login
                          WHERE username=%s ;", $username );
        $wpdb->query( $query );
        /// ihc_download_monitor_limit
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_download_monitor_limit
                          WHERE uid=%d ;", $this->uid );
        $wpdb->query( $query );
        /// ihc_cheat_off
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_cheat_off
                          WHERE uid=%d;", $this->uid );
        $wpdb->query( $query );
        /// ihc_user_sites
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_sites
                          WHERE uid=%d;", $this->uid );
        $wpdb->query( $query );
        return array(
            				'items_removed' => true,
            				'items_retained' => false,
            				'messages' => array( '' ),
            				'done' => 1,
  			);
    }

    private function getUserMetas()
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT meta_key, meta_value
                        FROM {$wpdb->usermeta}
                        WHERE
                        (
                          meta_key LIKE '%ihc_%'
                          OR
                          meta_key LIKE '%user_levels%'
                        )
                        AND
                        user_id=%d ", $this->uid );
        $dataDb = $wpdb->get_results($query);
        if (!$dataDb){
            return false;
        }
        $data = array();
        foreach ($dataDb as $object){
          $data[] = array(
                'name'  => $object->meta_key,
                'value' => $object->meta_value,
          );
        }

        return array(
        			'group_id'    => 'ihc_usermetas',
        			'group_label' => esc_html__('UMP user metas'),
        			'item_id'     => 'ihc_usermeta_' . $this->uid,
        			'data'        => $data,
    		);
    }

    private function getUserLevels()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT level_id, start_time, update_time, expire_time, notification, status
                                    FROM {$wpdb->prefix}ihc_user_levels
                                    WHERE user_id=%d
        ", $this->uid);
        $dataDb = $wpdb->get_results($query);
        if (!$dataDb){
            return false;
        }
        foreach ($dataDb as $object){
          $data[] = array(
                'name'  => 'Entry',
                'value' => $object->level_id . '(' . \Ihc_Db::get_level_name_by_lid($object->level_id) . ')' . ', start time: ' . $object->start_time .
                            ', update time: ' . $object->update_time . ', expire time: ' . $object->expire_time .
                            ', notification : ' . $object->update_time . ', status: ' . $object->status,
          );
        }
        return array(
              'group_id'    => 'ihc_user_levels',
              'group_label' => esc_html__('UMP user levels'),
              'item_id'     => 'ihc_user_levels_' . $this->uid,
              'data'        => $data,
        );
    }

    private function getUserLogs()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT lid, log_type, log_content FROM {$wpdb->prefix}ihc_user_logs
                                    WHERE uid=%d ;
        ", $this->uid);
        $dataDb = $wpdb->get_results($query);
        if (!$dataDb){
            return false;
        }
        foreach ($dataDb as $object){
          $data[] = array(
                'name'  => 'Entry',
                'value' => 'On level ' . $object->level_id . '(' . \Ihc_Db::get_level_name_by_lid($object->level_id) . ')'
                            . '. Log type: ' . $object->log_type . '. Log content: ' . $object->log_content . '. Create date: '
                            . date('Y-m-d H:i:s', $object->create_date)
          );
        }
        return array(
              'group_id'    => 'ihc_user_logs',
              'group_label' => esc_html__('UMP user levels'),
              'item_id'     => 'ihc_user_logs_' . $this->uid,
              'data'        => $data,
        );
    }

    private function getSecurityLogin()
    {
        global $wpdb;
        $username = \Ihc_Db::get_username_by_wpuid($this->uid);
        $query = $wpdb->prepare( "SELECT ip, log_time, attempts_count, locked
                                      FROM {$wpdb->prefix}ihc_security_login
                                      WHERE username=%s;
                                ", $username );
        $dataDb = $wpdb->get_results($query);
        if (!$dataDb){
            return false;
        }
        foreach ($dataDb as $object){
          $data[] = array(
                'name'  => 'Entry',
                'value' => 'Ip: ' . $object->ip . '. Log time : ' . date('Y-m-d H:i:s', $object->log_time)
                            . '. Attempts count: ' . $object->attempts_count . '. Locked: ' . $object->locked
          );
        }
        return array(
              'group_id'    => 'ihc_security_login',
              'group_label' => esc_html__('UMP security login'),
              'item_id'     => 'ihc_security_login' . $this->uid,
              'data'        => $data,
        );
    }

    private function getDownloadMonitorLimit()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT lid, download_limit
                                    FROM {$wpdb->prefix}ihc_download_monitor_limit
                                    WHERE uid=%d ;
        ", $this->uid );
        $dataDb = $wpdb->get_results($query);
        if (!$dataDb){
            return false;
        }
        foreach ($dataDb as $object){
          $data[] = array(
                'name'  => 'Entry',
                'value' => 'Level: ' . $object->lid . '(' . \Ihc_Db::get_level_name_by_lid($object->level_id) . ')'
                            . '. Download limit : ' . $object->download_limit
          );
        }
        return array(
              'group_id'    => 'ihc_download_monitor_limit',
              'group_label' => esc_html__('UMP donwload monitor limits'),
              'item_id'     => 'ihc_download_monitor_limit' . $this->uid,
              'data'        => $data,
        );
    }

    private function getCheatOff()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT hash
                                    FROM {$wpdb->prefix}ihc_cheat_off
                                    WHERE uid=%d;
        ", $this->uid );
        $dataDb = $wpdb->get_results($query);
        if (!$dataDb){
            return false;
        }
        foreach ($dataDb as $object){
          $data[] = array(
                'name'  => 'Entry',
                'value' => 'Hash: ' . $object->hash
          );
        }
        return array(
              'group_id'    => 'ihc_cheat_off',
              'group_label' => esc_html__('UMP cheat off'),
              'item_id'     => 'ihc_cheat_off' . $this->uid,
              'data'        => $data,
        );
    }

    private function getPayments()
    {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT txn_id, payment_data, history, paydate
                                  FROM {$wpdb->prefix}indeed_members_payments
                                  WHERE u_id=%d;
      ", $this->uid );
      $dataDb = $wpdb->get_results($query);
      if (!$dataDb){
          return false;
      }
      foreach ($dataDb as $object){
        $data[] = array(
              'name'  => 'Entry',
              'value' => 'Transaction id: ' . $object->txn_id . '. Pay date: ' . $object->paydate
                          . '. Payment data: ' . $object->payment_data . '. Payment history: ' . json_encode(unserialize($object->history))
        );
      }
      return array(
            'group_id'    => 'indeed_members_payments',
            'group_label' => esc_html__('UMP payments'),
            'item_id'     => 'indeed_members_payments_' . $this->uid,
            'data'        => $data,
      );
    }

    private function getUserSites()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT lid, site_id
                                    FROM {$wpdb->prefix}ihc_user_sites
                                    WHERE uid=%d;
        ", $this->uid );
        $dataDb = $wpdb->get_results($query);
        if (!$dataDb){
            return false;
        }
        foreach ($dataDb as $object){
          $data[] = array(
                'name'  => 'Entry',
                'value' => 'Level: ' . $object->lid . ' (' . \Ihc_Db::get_level_name_by_lid($object->level_id) . ')'
                            . '. Site : ' . $object->site_id . ' (' . get_site_url($object->site_id) . ')'
          );
        }
        return array(
              'group_id'    => 'ihc_user_sites',
              'group_label' => esc_html__('UMP user sites'),
              'item_id'     => 'ihc_user_sites' . $this->uid,
              'data'        => $data,
        );
    }

    private function getOrders()
    {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT lid, amount_type, amount_value, automated_payment, status, create_date
                                    FROM {$wpdb->prefix}ihc_orders
                                    WHERE uid=%d;
      ", $this->uid );
      $dataDb = $wpdb->get_results($query);
      if (!$dataDb){
          return false;
      }
      foreach ($dataDb as $object){
        $data[] = array(
              'name'  => 'Entry',
              'value' => 'Level: ' . $object->lid . ' (' . \Ihc_Db::get_level_name_by_lid($object->lid) . ')'
                          . '. Amount type : ' . $object->amount_type
                          . '. Amount value : ' . $object->amount_value
                          . '. Automated payment : ' . $object->automated_payment
                          . '. Status : ' . $object->status
                          . '. Create date : ' . $object->create_date
        );
      }
      return array(
            'group_id'    => 'ihc_orders',
            'group_label' => esc_html__('UMP orders'),
            'item_id'     => 'ihc_orders' . $this->uid,
            'data'        => $data,
      );
    }

    private function getOrderMetas()
    {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT a.order_id, a.meta_key, a.meta_value
                                    FROM {$wpdb->prefix}ihc_orders_meta a
                                    INNER JOIN {$wpdb->prefix}ihc_orders b
                                    ON a.order_id=b.id
                                    WHERE b.uid=%d;
      ", $this->uid );
      $dataDb = $wpdb->get_results( $query );
      if (!$dataDb){
          return false;
      }
      foreach ($dataDb as $object){
        $data[] = array(
              'name'  => 'Entry',
              'value' => 'Order Id : ' . $object->order_id
                          . '. ' . $object->meta_key
                          . ' : ' . $object->meta_value
        );
      }
      return array(
            'group_id'    => 'ihc_orders_meta',
            'group_label' => esc_html__('UMP orders meta'),
            'item_id'     => 'ihc_orders_meta' . $this->uid,
            'data'        => $data,
      );
    }

}
