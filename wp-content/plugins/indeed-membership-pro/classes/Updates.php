<?php
namespace Indeed\Ihc;
/*
 * @since 7.4
 */

class Updates
{
    /**
     * @var string
     */
    private $optionName = 'ihc_plugin_current_version';

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_action( 'init', array( $this, 'check' ) );
    }

    /**
     * @param none
     * @return none
     */
    public function check()
    {
        $currentVersion = indeed_get_plugin_version( IHC_PATH . 'indeed-membership-pro.php' );
        $versionValueInDatabase = get_option( $this->optionName );
        if ( !$versionValueInDatabase ){
            $versionValueInDatabase = '7.3';
        }

        if ( version_compare( '8', $versionValueInDatabase )==1 ){
            $this->addIndexes();
        }

        if ( version_compare( '8.7', $versionValueInDatabase )==1 ){
            $this->removeCsvOldFiles();
            $this->removeOldExportFiles();
        }

        if ( version_compare( '9.4.2', $versionValueInDatabase )==1 ){
            \Ihc_Db::create_tables();
      			$prefixes = \Ihc_Db::get_all_prefixes();
            foreach ($prefixes as $the_table_prefix){
        			 \Indeed\Ihc\Db\Memberships::setTablePrefix( $the_table_prefix );
  			       \Indeed\Ihc\Db\Memberships::importLevels();
            }
        }

        if ( version_compare( '9.5', $versionValueInDatabase ) == 1 ){
            $this->updateStateField();
        }

        if ( version_compare( '9.6.2', $versionValueInDatabase ) == 1 ){
          $cron = get_option( 'cron' );
          if ( $cron && count($cron) < 1000 ){
              foreach ( $cron as $key => $array){
                if (isset($array['ihc_weekly_reports'])){
                    unset($cron[$key]);
                }
              }
              update_option('cron', $cron);
          }
        }

        if ( version_compare( '10.2', $versionValueInDatabase ) == 1 ){
          \Ihc_Db::create_default_pages();
        }

        if ( version_compare( '10.5.1', $versionValueInDatabase ) == 1 ){
            $this->updateCrons();
        }

        // since version 11.1
        if ( version_compare( '11.0', $versionValueInDatabase ) == 1 ){ // if the second param is lower than the first
            // remove ihc_media_hash_data used in deprecated class UploadFilesSecurity
            delete_option( 'ihc_media_hash_data' );
        }

        if ( version_compare( $currentVersion, $versionValueInDatabase ) == 1 ){
            $this->updateRegisterFields();
            update_option( $this->optionName, $currentVersion );

            // check if the crons are registered. if not register them
            $crons = new \Indeed\Ihc\Crons();
            $crons->registerCrons();
        }



    }

    /**
     * @param none
     * @return none
     */
    public function updateRegisterFields()
    {
        $data = get_option( 'ihc_user_fields' );
        if ( !$data ){
            return false;
        }
        foreach ( $data as $fieldData ){
            if ( !isset( $fieldData['display_on_modal'] ) ){
                $fieldData['display_on_modal'] = 0;
            }
        }
        ///
        require_once IHC_PATH . 'admin/includes/functions/register.php'; /// double check this

        if ( ihc_array_value_exists( $data, 'ihc_optin_accept', 'name' ) === false ){
            $fieldData = array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_optin_accept', 'label' => esc_html__( 'Accept Opt-in', 'ihc' ), 'type'=>'single_checkbox', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' );
            ihc_save_user_field($fieldData);
        }
        if ( ihc_array_value_exists( $data, 'ihc_memberlist_accept', 'name' ) === false ){
            $fieldData = array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_memberlist_accept', 'label' => esc_html__( 'Accept display on Memberlist', 'ihc' ), 'type'=>'single_checkbox', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' );
            ihc_save_user_field($fieldData);
        }
    }

    /**
     * @since version 9
     * @param none
     * @return none
     */
    public function addIndexes()
    {
        $this->userLevelsIndex();
        $this->userLogsIndex();
        $this->membersPaymentsIndex();
        $this->ordersIndex();
        $this->orderMetaIndex();
    }

    /**
     * @since version 9
     * @param none
     * @return none
     */
    private function userLevelsIndex()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query =  "SHOW INDEX FROM {$wpdb->prefix}ihc_user_levels;";
        $indexList = $wpdb->get_results( $query );
        if ( !$indexList ){
            return;
        }
        foreach ( $indexList as $indexObject ){
            if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ihc_user_levels_user_id' ){
                return;
            }
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "CREATE INDEX idx_ihc_user_levels_user_id ON {$wpdb->prefix}ihc_user_levels(user_id)";
        $wpdb->query( $query );
    }

    /**
     * @since version 9
     * @param none
     * @return none
     */
    private function userLogsIndex()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SHOW INDEX FROM {$wpdb->prefix}ihc_user_logs;";
        $indexList = $wpdb->get_results( $query );
        if ( !$indexList ){
            return;
        }
        foreach ( $indexList as $indexObject ){
            if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ihc_user_logs_uid' ){
                return;
            }
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "CREATE INDEX idx_ihc_user_logs_uid ON {$wpdb->prefix}ihc_user_logs(uid)";
        $wpdb->query( $query );
    }

    /**
     * @since version 9
     * @param none
     * @return none
     */
    private function membersPaymentsIndex()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SHOW INDEX FROM {$wpdb->prefix}indeed_members_payments;";
        $indexList = $wpdb->get_results( $query );
        if ( !$indexList ){
            return;
        }
        foreach ( $indexList as $indexObject ){
            if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_indeed_members_payments_uid' ){
                return;
            }
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "CREATE INDEX idx_indeed_members_payments_uid ON {$wpdb->prefix}indeed_members_payments(u_id)";
        $wpdb->query( $query );
    }

    /**
     * @since version 9
     * @param none
     * @return none
     */
    private function ordersIndex()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SHOW INDEX FROM {$wpdb->prefix}ihc_orders;";
        $indexList = $wpdb->get_results( $query );
        if ( !$indexList ){
            return;
        }
        foreach ( $indexList as $indexObject ){
            if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ihc_orders_uid' ){
                return;
            }
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "CREATE INDEX idx_ihc_orders_uid ON {$wpdb->prefix}ihc_orders(uid)";
        $wpdb->query( $query );
    }

    /**
     * @since version 9
     * @param none
     * @return none
     */
    private function orderMetaIndex()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SHOW INDEX FROM {$wpdb->prefix}ihc_orders_meta;";
        $indexList = $wpdb->get_results( $query );
        if ( !$indexList ){
            return;
        }
        foreach ( $indexList as $indexObject ){
            if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ihc_orders_meta_order_id' ){
                return;
            }
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "CREATE INDEX idx_ihc_orders_meta_order_id ON {$wpdb->prefix}ihc_orders_meta(order_id)";
        $wpdb->query( $query );
    }

    /**
     * @param none
     * @return none
     */
    private function removeCsvOldFiles()
    {
        $directory = IHC_PATH;
        $files = scandir( $directory );
        foreach ( $files as $file ){
            $fileFullPath = $directory . $file;
            if ( file_exists( $fileFullPath ) && filetype( $fileFullPath ) == 'file' ){
                $extension = pathinfo( $fileFullPath, PATHINFO_EXTENSION );
        				if ( $extension == 'csv' && $file == 'users.csv' ){
                    unlink( $fileFullPath );
                }
            }
        }
    }

    /**
     * @param none
     * @return none
     */
    private function removeOldExportFiles()
    {
        $directory = IHC_PATH;
        $files = scandir( $directory );
        foreach ( $files as $file ){
            $fileFullPath = $directory . $file;
            if ( file_exists( $fileFullPath ) && filetype( $fileFullPath ) == 'file' ){
                $extension = pathinfo( $fileFullPath, PATHINFO_EXTENSION );
                if ( $extension == 'xml' && $file == 'export.xml' ){
                    unlink( $fileFullPath );
                }
            }
        }
    }

    public function updateStateField()
    {
      $registerFields = get_option( 'ihc_user_fields' );
      if ( !$registerFields ){
          return;
      }
      $key = ihc_array_value_exists($registerFields, 'ihc_state', 'name');
      if ( $key === false ){
          return;
      }
      $registerFields[$key]['native_wp'] = 0;
      update_option( 'ihc_user_fields', $registerFields );
    }

    /**
     * @param none
     * @return none
     */
    public function updateCrons()
    {
        $crons = get_option( 'cron' );
        if ( !$crons ){
            return;
        }
        $i=0;
        foreach ( $crons as $timestamp => $subarray ){
            if ( isset( $subarray['ihc_weekly_reports'] ) ){
                $i++;
            }
        }
        if ( $i > 1 ){
          foreach ( $crons as $timestamp => $subarray ){
              if ( isset( $subarray['ihc_weekly_reports'] ) ){
                  unset( $crons[ $timestamp ] );
              }
          }
          update_option( 'cron', $crons );
        }
        if ( !wp_get_schedule('ihc_weekly_reports') ){
            if ( date("l") !== 'Monday' ){
                $whenToStart = strtotime("next monday");
            } else {
                $whenToStart = time();
            }
            wp_schedule_event( $whenToStart, 'weekly', 'ihc_weekly_reports' );
        }
    }

}
