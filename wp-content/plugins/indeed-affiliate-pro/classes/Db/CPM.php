<?php
namespace Indeed\Uap\Db;

class CPM
{
    private $affiliateId = 0;

    public function __construct($affiliateId=0)
    {
        $this->affiliateId = $affiliateId;
    }

    public function save()
    {
        global $wpdb;
        if (empty($this->affiliateId)){
            return false;
        }
        $oldValue = $this->get();
        if ($oldValue){
            /// update
            $count = $oldValue->count_number + 1;
            $time = date("Y-m-d H:i:s");
            $query = $wpdb->prepare("UPDATE {$wpdb->prefix}uap_cpm SET count_number=%d, update_time=%s
                                        WHERE affiliate_id=%d ;", $count, $time, $this->affiliateId );
        } else {
            /// insert
            $count = 1;
            $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}uap_cpm VALUES(NULL, %d, %d, NULL);", $this->affiliateId, $count );
        }
        $inserted = $wpdb->query($query);
        if ($inserted)
            return $count;
        else
            return 0;
    }

    public function reset()
    {
        global $wpdb;
        $time = date("Y-m-d H:i:s");
        $query = $wpdb->prepare("UPDATE {$wpdb->prefix}uap_cpm SET count_number=0, update_time=%s
                                    WHERE affiliate_id=%d ;", $time, $this->affiliateId );
        return $wpdb->query($query);
    }

    public function get()
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id,affiliate_id,count_number,update_time FROM {$wpdb->prefix}uap_cpm WHERE affiliate_id=%d ", $this->affiliateId );
        return $wpdb->get_row( $query );
    }

    public function delete()
    {
      global $wpdb;
      $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}uap_cpm WHERE affiliate_id=%d;", $this->affiliateId );
      return $wpdb->query( $query );
    }

}
