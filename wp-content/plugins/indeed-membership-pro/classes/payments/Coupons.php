<?php
namespace Indeed\Ihc\Payments;

class Coupons
{
    /**
     * @param int
     */
    private $id             = 0;
    /**
     * @param string
     */
    private $code           = '';
    /**
     * @param int
     */
    private $lid            = 0;
    /**
     * @param int
     */
    private $uid            = 0;
    /**
     * @param array
     */
    private $couponData     = [];

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {}

    /**
     * @param none
     * @return array
     */
    public function getAll()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE status=1;";
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return [];
        }
        foreach ( $data as $object ){
            $returnData[ $object->id ]['code'] = $object->code;
            $returnData[ $object->id ]['settings'] = maybe_unserialize( $object->settings );
            $returnData[ $object->id ]['submited_coupons_count'] = $object->submited_coupons_count;
        }
        return $returnData;
    }

    /**
     * @param none
     * @return array
     */
    public function getDefaultValueForNewCoupon()
    {
        return array(
    						"code"            => "",
    						"discount_type"   => "percentage",
    						"discount_value"  => '10',
    						"period_type"     => "unlimited",
    						"repeat"          => "10",
    						"target_level"    => "",
    						"reccuring"       => "1",
    						"start_time"      => '',
    						"end_time"        => '',
    						"box_color"       => ihc_generate_color_hex(),
    						"description"     => "",
    		);
    }

    /**
     * @param none
     * @return bool
     */
    public function delete()
    {
        global $wpdb;
        if ( !$this->code && !$this->id ){
            return false;
        }
        if ( !$this->couponData ){
            $this->getData();
        }
        if ( !$this->couponData ){
            return false;
        }
        $queryString = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_coupons WHERE id=%d;", $this->id );
        return $wpdb->query( $queryString );
    }

    public function ihc_create_coupon($post_data=array()){
    	/*
    	 * @param post_data (array)
    	 * @return boolean
    	 */
    	if ($post_data){
    		global $wpdb;
    		if (!empty($post_data['how_many_codes'])){
    			// ============== MULTIPLE COUPONS ===============//
    			$settings = serialize($post_data);
    			$prefix = $post_data['code_prefix'];
    			$prefix_length = strlen($post_data['code_prefix']);
    			$length = $post_data['code_length'] - $prefix_length;
    			$limit = $post_data['how_many_codes'];
    			unset($post_data['how_many_codes']);
    			unset($post_data['code_prefix']);
    			unset($post_data['code_length']);
    			if (empty($post_data['discount_value'])){
    				return;
    			}
    			while ($limit){
    				$code = ihc_random_str($length);
    				$code = $prefix . $code;
    				$code = str_replace(' ', '', $code);
    				$code = ihc_make_string_simple($code);
            $query = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE code=%s ;", $code );
    				$data = $wpdb->get_row( $query );
    				if ( $data ){
    					continue;
    				}
            $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_coupons VALUES( '', %s, %s, 0, 1);", $code, $settings );
    				$wpdb->query( $query );
    				$limit--;
    			}
    		} else {
    			//============== SINGLE COUPON ==================//
    			if (empty($post_data['code']) || empty($post_data['discount_value'])){
    				return FALSE;
    			}
    			//check if this code already exists
          $query = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE code=%s;", $post_data['code'] );
    			$data = $wpdb->get_row( $query );
    			if ($data){
    				return FALSE;
    			}
    			$code = str_replace(' ', '', $post_data['code']);
    			$code = ihc_make_string_simple($code);
    			unset($post_data['code']);
    			if (isset($post_data['special_status'])){
    				$status = $post_data['special_status'];
    				unset($post_data['special_status']);
    			} else {
    				$status = 1;
    			}
    			$settings = serialize($post_data);
          $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_coupons VALUES( '', %s, %s, 0, %s );", $code, $settings, $status );
    			$wpdb->query( $query );
    			return TRUE;
    		}
    	}
    }

    public function insert( $postData=[] )
    {
        global $wpdb;
        if ( !$postData ){
            return false;
        }
        if (!empty($postData['how_many_codes'])){
            // ============== MULTIPLE COUPONS ===============//
            $settings = serialize($postData);
            $prefix = $postData['code_prefix'];
            $prefixLength = strlen($postData['code_prefix']);
            $length = $postData['code_length'] - $prefixLength;
            $limit = $postData['how_many_codes'];
            unset($postData['how_many_codes']);
            unset($postData['code_prefix']);
            unset($postData['code_length']);
            if (empty($postData['discount_value'])){
                return;
            }
            while ($limit){
              $code = ihc_random_str($length);
              $code = $prefix . $code;
              $code = str_replace(' ', '', $code);
              $code = ihc_make_string_simple($code);
              $queryString = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE code=%s;", $code);
              $data = $wpdb->get_row( $queryString );
              if ( $data ){
                  continue;
              }
              $queryString = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_coupons VALUES( '', %s, %s, 0, 1);", $code, $settings );
              $wpdb->query( $queryString );
              $limit--;
            }
        } else {
            //============== SINGLE COUPON ==================//
            if (empty($postData['code']) || empty($postData['discount_value'])){
              return false;
            }
            //check if this code already exists
            $queryString = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE code=%s;", $postData['code'] );
            $data = $wpdb->get_row( $queryString );
            if ( $data ){
                return false;
            }
            $code = str_replace(' ', '', $postData['code']);
            $code = ihc_make_string_simple($code);
            unset($postData['code']);
            if (isset($postData['special_status'])){
              $status = $postData['special_status'];
              unset($postData['special_status']);
            } else {
              $status = 1;
            }
            $settings = serialize($postData);
            $queryString = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_coupons VALUES( '', %s, %s, 0, %s);" , $code, $settings, $status );
            return $wpdb->query( $queryString );
        }
    }

    /**
     * @param array
     * @return bool
     */
    public function update( $postData=[] )
    {
        global $wpdb;
        if ( !$postData || empty($postData['code']) || empty($postData['discount_value']) ){
            return false;
        }
        $this->id = sanitize_text_field($postData['id']);
        unset($postData['id']);
        $this->getData();
        if ( !$this->couponData ){
            return false;
        }
        $code = str_replace(' ', '', $postData['code'] );
        $code = ihc_make_string_simple( $postData['code'] );
        unset($postData['code']);
        unset($postData['id']);
        $settings = serialize( $postData );
        $queryString = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_coupons
                                            SET code=%s, settings=%s
                                            WHERE id=%d;", $code, $settings, $id );
        return $wpdb->query( $queryString );
    }

    /**
     * @param string
     * @return object
     */
    public function setCode( $code='' )
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param int
     * @return object
     */
    public function setId( $id=0 )
    {
        if ( !$id ){
            return;
        }
        $this->id = $id;
        return $this;
    }

    /**
     * @param int
     * @return object
     */
    public function setLid( $lid=0 )
    {
        $this->lid = $lid;
        return $this;
    }

    /**
     * @param int
     * @return object
     */
    public function setUid( $uid=0 )
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @param none
     * @return array
     */
    public function getData()
    {
        global $wpdb;
        if ( !$this->code && !$this->id ){
            return [];
        }

        if ( $this->code ){
            $this->code = str_replace( ' ', '', $this->code );
            $queryString = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons	WHERE code=%s ;", $this->code );
        } else {
            $queryString = $wpdb->prepare( "SELECT code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons	WHERE id=%d ;", $this->id );
        }

        $object = $wpdb->get_row( $queryString );
        if ( !$object ){
            return [];
        }
        $this->couponData = array_merge( maybe_unserialize( $object->settings ), [
                  'id'                      => $object->id,
                  'code'                    => $object->code,
                  'submited_coupons_count'  => $object->submited_coupons_count,
                  'status'                  => $object->status,
        ]);
        if ( !$this->code ){
            $this->code = $object->code;
        } else if ( !$this->id) {
            $this->id = $object->id;
        }
        return $this->couponData;
    }

    /**
     * @param none
     * @return bool
     */
    public function isValid()
    {
      if ( !$this->code || $this->lid ==-1 ){
          return false;
      }
      if ( empty( $this->couponData ) ){
          $this->getData();
      }
      if ( empty( $this->couponData ) ){
          return false;
      }

      if ( !empty( $this->couponData['repeat'] ) && ( $this->couponData['repeat']<=$this->couponData['submited_coupons_count'] ) ){
          return false;
      }

      if ( $this->couponData['period_type']=='date_range' && !empty( $this->couponData['start_time'] ) && !empty( $this->couponData['end_time'] ) ){
            //we must check the time
            $startTime = strtotime($this->couponData['start_time']);
            $endTime = strtotime($this->couponData['end_time']);
            $currentTime = indeed_get_unixtimestamp_with_timezone();
            if ( $startTime > $currentTime ){
                //not begin coupon time
                return false;
            }
            if ( $currentTime > $endTime ){
                //out of date
                return false;
            }
        }
        if ( $this->couponData['target_level'] == -1 ){
            // -1 means all
            return true;
        } else if ( strpos( $this->couponData['target_level'], ',') !== false ){
            // multiple
            $this->couponData['target_level'] = explode( ',', $this->couponData['target_level'] );
            if ( !in_array( $this->lid, $this->couponData['target_level'] ) ){
                return false;
            }
        } else {
            // single membership
            if ( $this->couponData['target_level']!= $this->lid ) {
                //it's not the target membership
                return false;
            }
        }

        return true;
    }

    /**
     * @param float
     * @return float
     */
    public function getDiscountValue( $price=0 )
    {
        if ( !$price && !$this->couponData ){
            return 0;
        }
        if ( $this->couponData['discount_type'] == 'percentage' ){
            return ( $price * $this->couponData['discount_value'] / 100 );
        } else {
            return $this->couponData['discount_value'];
        }
    }

    /**
     * @param float
     * @return float
     */
    public function getPriceAfterDiscount( $price=0 )
    {
        if ( !$price && !$this->couponData ){
            return $price;
        }
        if ( $this->couponData['discount_type']=='percentage'){
    			$price = $price - ($price*$this->couponData['discount_value']/100);
    		} else {
    			$price = $price - $this->couponData['discount_value'];
    		}
        $decimals = get_option( 'ihc_num_of_decimals' );
        if ( $decimals === false || $decimals == '' ){
            $decimals = 2;
        }
    		return round( $price, $decimals );
    }

    /**
     * @param none
     * @return bool
     */
    public function submitCode()
    {
        global $wpdb;
        if ( empty( $this->code ) && empty( $object->id ) ){
            return false;
        }
        if ( !isset( $this->couponData['submited_coupons_count'] ) ){
            $this->getData();
        }
        if ( !isset( $this->couponData['submited_coupons_count'] ) ){
            return false;
        }
        $submited = (int)$this->couponData['submited_coupons_count'];
        $submited++;
        $queryString = $wpdb->prepare("UPDATE {$wpdb->prefix}ihc_coupons
                                          SET submited_coupons_count=%d
                                          WHERE code=%s;", $submited, $this->code );
        $wpdb->query( $queryString );

        do_action('ump_coupon_code_submited', $this->code, $this->uid, $this->lid );
        // @description Run after coupon code was submited. @param coupon code, user id, level id.

        return true;
    }
}
