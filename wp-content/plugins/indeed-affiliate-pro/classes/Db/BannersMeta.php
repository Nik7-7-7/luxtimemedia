<?php
namespace Indeed\Uap\Db;

/*
columns:
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    banner_id VARCHAR( 100 ),
    meta_name VARCHAR( 300 ),
    meta_value TEXT
*/

class BannersMeta
{
    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
      * @param array
      * @return array
      */
    public function save( $bannerId=0, $metaName='', $metaValue='' )
    {
          global $wpdb;
          if ( $bannerId === 0 ){
              return [
                        'status'        => 0,
                        'reason'        => 'Banner id not provided.',
              ];
          }
          if ( $metaName === '' ){
              return [
                        'status'        => 0,
                        'reason'        => 'Meta name not provided.',
              ];
          }
          if ( $this->getOne( $bannerId, $metaName ) === false ){
              // insert
              $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}uap_banners_meta VALUES(
                null,
                %s,
                %s,
                %s
              );",
                  $bannerId,
                  $metaName,
                  $metaValue
              );
          } else {
              $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}uap_banners_meta SET meta_value=%s WHERE meta_name=%s AND banner_id=%d;",
                  $metaValue,
                  $metaName,
                  $bannerId
              );
          }

          return $wpdb->query( $query );
    }


        /**
         * @param int
         * @param string
         * @return array
         */
        public function getAllForBanner( $id=0, $slug='' )
        {
            global $wpdb;
            if ( $id ){
                $query = $wpdb->prepare( "SELECT id, banner_id, meta_name, meta_value FROM {$wpdb->prefix}uap_banners_meta
                                              WHERE id=%d
                ", $id );
                return $wpdb->get_results( $query, ARRAY_A );
            } else if ( $slug ){
                $query = $wpdb->prepare( "SELECT id, banner_id, meta_name, meta_value FROM {$wpdb->prefix}uap_banners_meta
                                              WHERE slug=%s
                ", $slug );
                return $wpdb->get_results( $query, ARRAY_A );
            }
            return false;
        }

        /**
         * @param int
         * @return array
         */
        public function getMany( $limit=0 )
        {
            global $wpdb;
            $query = $wpdb->prepare( "SELECT id, banner_id, meta_name, meta_value
                                          FROM {$wpdb->prefix}uap_banners_meta
                                          ORDER BY id DESC LIMIT %d OFFSET 0;", $limit );
            return $wpdb->get_results( $query, ARRAY_A );
        }

    /**
     * @param int
     * @param string
     * @return array
     */
    public function getOne( $bannerId=0, $metaName='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_banners_meta
                                      WHERE
                                      banner_id=%d
                                      AND
                                      meta_name=%s
        ", $bannerId, $metaName );
        $data = $wpdb->get_row( $query );
        if ( !isset( $data->meta_value ) ){
            return false;
        }
        return $data->meta_value;
    }

    /**
     * @param int
     * @param string
     * @return array
     */
    public function deleteOne( $bannerId=0, $metaName='' )
    {
        global $wpdb;
        if ( $bannerId === 0 ){
            return [
                      'status'    => 0,
                      'message'   => 'Banner Id its not provided.',
            ];
        }
        if ( $metaName === '' ){
            return [
                      'status'    => 0,
                      'message'   => 'Meta Name its not provided.',
            ];
        }
        $result = $wpdb->query( "DELETE FROM {$wpdb->prefix}uap_banners_meta WHERE banner_id=%d AND meta_name=%s;", $bannerId, $metaName );

        if ( $result ){
            return [
                      'status'    => 1,
                      'message'   => 'Success.',
            ];
        }
        return [
                      'status'    => 0,
                      'message'   => 'Something went wrong.',
        ];
    }

    /**
     * @param int
     * @return array
     */
    public function deleteAllForBanner( $bannerId=0 )
    {
        global $wpdb;
        if ( $bannerId === 0 ){
            return [
                      'status'    => 0,
                      'message'   => 'Banner Id its not provided.',
            ];
        }
        $result = $wpdb->query( "DELETE FROM {$wpdb->prefix}uap_banners_meta WHERE banner_id=%d;", $bannerId );

        if ( $result ){
            return [
                      'status'    => 1,
                      'message'   => 'Success.',
            ];
        }
        return [
                      'status'    => 0,
                      'message'   => 'Something went wrong.',
        ];
    }


}
