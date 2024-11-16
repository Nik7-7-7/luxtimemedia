<?php
namespace Indeed\Uap\Db;

class Products
{

    private $limit          = 30;
    private $offset         = 0;
    private $searchPhrase   = '';
    private $type           = '';
    private $affiliateId    = 0;
    private $category       = 0;
    private $orderBy        = '';
    private $wooPriceFormat = '';

    public function __construct(){}

    public function setSearchPhrase( $searchPhrase='' )
    {
        $this->searchPhrase = $searchPhrase;
        return $this;
    }

    public function setLimit( $limit=0 )
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset( $offset=0 )
    {
        $this->offset = $offset;
        return $this;
    }

    public function setType( $type=0 )
    {
        $this->type = $type;
        return $this;
    }

    public function setAffiliateId( $affiliateId=0 )
    {
        $this->affiliateId = $affiliateId;
        return $this;
    }

    public function setProductCategory( $category=0 )
    {
        $this->category = $category;
        return $this;
    }

    public function setOrderBy( $orderBy='' )
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getResults( $search='', $limit=0, $offset=0 )
    {
        switch ( $this->type ){
            case 'woo':
              return $this->searchIntoWoo();
              break;
            case 'edd':
              return $this->searchIntoEdd();
              break;
            case 'ulp':
              return $this->searchIntoUlp();
              break;
        }
        return [];
    }

    public function getCount()
    {
        switch ( $this->type ){
            case 'woo':
              return $this->countsForWoo();
              break;
            case 'edd':
              return $this->countsForEdd();
              break;
            case 'ulp':
              return $this->countsForUlp();
              break;
        }

    }

    public function searchIntoWoo()
    {
        global $wpdb;
        $search = uap_sanitize_array( $this->searchPhrase );
        $query = "SELECT a.ID, a.post_title,
                          CAST(c.meta_value AS DECIMAL(10,2)) as price,
                          CAST(d.meta_value AS UNSIGNED) as total_sales,
                          c.meta_key as c_meta_key,
                          c.meta_value as c_meta_value
                          FROM {$wpdb->posts} a ";
        if ( $this->category ){
            $query .= " INNER JOIN {$wpdb->term_relationships} b ON a.ID=b.object_id  ";
        }
        $query .= " INNER JOIN {$wpdb->postmeta} c ON a.ID=c.post_id ";
        $query .= " INNER JOIN {$wpdb->postmeta} d ON a.ID=d.post_id ";
        $query .= " WHERE 1=1 ";
        if ( $this->category ){
            $query .= $wpdb->prepare( " AND b.term_taxonomy_id=%d ", $this->category );
        }
        if ( $search != '' ){
            $query .= " AND a.post_title LIKE '%$search%' ";
        }
        $query .= " AND a.post_type='product' AND a.post_status='publish' ";
        $query .= " AND (c.meta_key='_regular_price' OR c.meta_key='_price') ";
        $query .= " AND d.meta_key='total_sales' ";
        $query .= " GROUP BY a.ID ";
        if ( $this->orderBy ){
            switch ( $this->orderBy ){
                case 'popularity':
                  $query .= " ORDER BY total_sales DESC ";
                  break;
                case 'date':
                  $query .= " ORDER BY a.post_date DESC ";
                  break;
                case 'price':
                  $query .= " ORDER BY price ASC ";
                  break;
                case 'price-desc':
                  $query .= " ORDER BY price DESC ";
                  break;
            }
        }
        $query .= $wpdb->prepare( " LIMIT %d OFFSET %d;", $this->limit, $this->offset);

        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return [];
        }
        $currency = get_option('woocommerce_currency');
        if ( function_exists( 'get_woocommerce_currency_symbol' ) ){
            $currency = get_woocommerce_currency_symbol( $currency );
        }
        $return = [];

        require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';
        $do_math = new \Affiliate_Referral_Amount( $this->affiliateId, 'woo');

        $excludeTax = get_option( 'uap_exclude_tax' );

        foreach ( $data as $productData){
            $prices = [];
            $regularPrices = [];
            $product = wc_get_product( $productData->ID );


            if ( $excludeTax ){
              $price = wc_get_price_excluding_tax( $product, [] );
            } else {
              $price = wc_get_price_including_tax( $product, [] );
            }

      			$sale_price = $product->get_sale_price();
      			$regular_price = $product->get_regular_price();

      			if(!empty($sale_price)) {
      				$price = $sale_price;
      				if($regular_price == $sale_price){
                 $regular_price = FALSE;
              }
      			}

            $priceHtml = $this->formatWooPrice( $price, $currency );
            $referralAmount = $do_math->get_result( $price, $productData->ID );// input price, product id
            $referralPrice = $this->formatWooPrice( $referralAmount, $currency );

            if ( $product instanceof \WC_Product_Variable ){
                  $variablePrices = $product->get_available_variations();
                  foreach ( $variablePrices as $variablePrice ){
                  		$variableProductObject = wc_get_product( $variablePrice['variation_id'] );
                      if ( $excludeTax ){
                        $prices[] = wc_get_price_excluding_tax( $variableProductObject, [] );
                      } else {
                        $prices[] = wc_get_price_including_tax( $variableProductObject, [] );
                      }
                      /*
                      $prices[] = $variablePrice['display_price'];
                      if ( isset( $variablePrice['display_regular_price'] ) ){
                          $regularPrices[] = $variablePrice['display_regular_price'];
                      }
                      */
                  }
                  // referral amount
                  $temporaryMinReferralAmount = $do_math->get_result( min( $prices ), $productData->ID );
                  $temporaryMaxReferralAmount = $do_math->get_result( max( $prices ), $productData->ID );
                  $referralPrice = $this->formatWooPrice( $temporaryMinReferralAmount, $currency );
                  $referralPrice .= ' - ';
                  $referralPrice .= $this->formatWooPrice( $temporaryMaxReferralAmount, $currency );

                  // base price
                  $priceHtml = $this->formatWooPrice( min( $prices ), $currency );
                  $priceHtml .= ' - ';
                  $priceHtml .= $this->formatWooPrice( max( $prices ), $currency );

                  // regular price
                  if ( !empty( $regularPrices ) ){
                      $regularPriceHtml = $this->formatWooPrice( min( $regularPrices ), $currency );
                      $regularPriceHtml .= ' - ';
                      $regularPriceHtml .= $this->formatWooPrice( max( $regularPrices ), $currency );
                  }
            }

            $return[$productData->ID] = [
                  'price'                   => $priceHtml,
                  'numeric_price'           => $price,
				          'regular_price'           => isset( $regularPriceHtml ) ? $regularPriceHtml : $regular_price,
                  'numeric_regular_price'   => $regular_price,
                  'label'                   => $productData->post_title,
                  'featureImage'            => get_the_post_thumbnail_url( $productData->ID ),
                  'id'                      => $productData->ID,
                  'product_type'            => 'woo',
                  'referral_amount'         => $referralPrice,
                  'permalink'               => get_permalink( $productData->ID ),
                  'categories'              => get_the_terms( $productData->ID, 'product_cat' ),
            ];
            if ( isset( $regularPriceHtml ) ){
                unset( $regularPriceHtml );
            }
        }
        return $return;
    }

    private function formatWooPrice( $price='', $currency='' )
    {
        if ($price === FALSE){
           return FALSE;
        }

		    if ( !$this->wooPriceFormat ){
            $this->wooPriceFormat = get_option( 'woocommerce_currency_pos' );
        }
        $string = $currency . $price;
        switch ( $this->wooPriceFormat ) {
          case 'left':
            $string = $currency . $price;
            break;
          case 'right':
            $string = $price . $currency;
            break;
          case 'left_space':
            $string = $currency . ' ' . $price;
            break;
          case 'right_space':
            $string = $price . ' ' . $currency;
            break;
        }
        return $string;
    }

    private function countsForWoo()
    {
        global $wpdb;
        $search = uap_sanitize_array( $this->searchPhrase );
        $query = "SELECT COUNT( a.ID ) FROM {$wpdb->posts} a ";
        if ( $this->category ){
            $query .= " INNER JOIN {$wpdb->term_relationships} b ON a.ID=b.object_id ";
        }
        $query .= " WHERE 1=1 ";
        if ( $this->category ){
            $query .= $wpdb->prepare( " AND b.term_taxonomy_id=%d ", $this->category );
        }
        if ( $search != '' ){
            $query .= " AND a.post_title LIKE '%$search%' ";
        }
        $query .= " AND a.post_type='product' AND a.post_status='publish' ";
        return $wpdb->get_var( $query );
    }

    private function searchIntoEdd()
    {
        global $wpdb;
        $search = uap_sanitize_array( $this->searchPhrase );
        $query = "SELECT a.ID, a.post_title, CAST(c.meta_value AS DECIMAL(10,2)) as price, CAST(d.meta_value AS UNSIGNED) as total_sales FROM {$wpdb->posts} a ";
        if ( $this->category ){
            $query .= " INNER JOIN {$wpdb->term_relationships} b ON a.ID=b.object_id  ";
        }
        $query .= " INNER JOIN {$wpdb->postmeta} c ON a.ID=c.post_id ";
        $query .= " INNER JOIN {$wpdb->postmeta} d ON a.ID=d.post_id ";
        $query .= " WHERE 1=1 ";
        if ( $this->category ){
            $query .= $wpdb->prepare( " AND b.term_taxonomy_id=%d ", $this->category );
        }
        if ( $search != '' ){
            $query .= " AND a.post_title LIKE '%$search%' ";
        }
        $query .= " AND a.post_type='download' AND a.post_status='publish' ";
        $query .= " AND c.meta_key='edd_price' ";
        $query .= " AND d.meta_key='_edd_download_sales' ";
        if ( $this->orderBy ){
            switch ( $this->orderBy ){
                case 'popularity':
                  $query .= " ORDER BY total_sales DESC ";
                  break;
                case 'date':
                  $query .= " ORDER BY a.post_date DESC ";
                  break;
                case 'price':
                  $query .= " ORDER BY price ASC ";
                  break;
                case 'price-desc':
                  $query .= " ORDER BY price DESC ";
                  break;
            }
        }
        $query .= $wpdb->prepare( " LIMIT %d OFFSET %d;", $this->limit, $this->offset);

        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return [];
        }
        $return = [];

        $currency = edd_get_currency();

        require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';
        $do_math = new \Affiliate_Referral_Amount( $this->affiliateId, 'edd');

        foreach ( $data as $productData){
            $price = edd_price( $productData->ID, false );
            $referralAmount = $do_math->get_result( $price, $productData->ID );// input price, product id

            $return[$productData->ID] = [
                  'price'             => $price,
                  'label'             => $productData->post_title,
                  'featureImage'      => get_the_post_thumbnail_url( $productData->ID ),
                  'id'                => $productData->ID,
                  'product_type'      => 'edd',
                  'referral_amount'   => edd_currency_filter( $referralAmount, $currency ),
                  'permalink'         => get_permalink( $productData->ID ),
                  'categories'        => get_the_terms( $productData->ID, 'download_category' ),
            ];
        }
        return $return;
    }

    private function countsForEdd()
    {
        global $wpdb;
        $search = uap_sanitize_array( $this->searchPhrase );
        $query = "SELECT COUNT(a.ID) FROM {$wpdb->posts} a ";
        if ( $this->category ){
            $query .= " INNER JOIN {$wpdb->term_relationships} b ON a.ID=b.object_id ";
        }
        $query .= " WHERE 1=1 ";
        if ( $this->category ){
            $query .= $wpdb->prepare( " AND b.term_taxonomy_id=%d ", $this->category );
        }
        if ( $search != '' ){
            $query .= " AND a.post_title LIKE '%$search%' ";
        }
        $query .= " AND a.post_type='download' AND a.post_status='publish' ";
        return $wpdb->get_var( $query );
    }

    private function searchIntoUlp()
    {
        global $wpdb;
        $search = uap_sanitize_array( $this->searchPhrase );
        $query = "SELECT a.ID, a.post_title, CAST(c.meta_value AS DECIMAL(10,2)) as price, COUNT(d.id) as total_sales FROM {$wpdb->posts} a ";
        if ( $this->category ){
            $query .= " INNER JOIN {$wpdb->term_relationships} b ON a.ID=b.object_id  ";
        }
        $query .= " LEFT JOIN {$wpdb->postmeta} c ON a.ID=c.post_id ";
        $query .= " LEFT JOIN {$wpdb->prefix}ulp_user_entities_relations d ON a.ID=d.entity_id ";
        $query .= " WHERE 1=1 ";
        if ( $this->category ){
            $query .= $wpdb->prepare( " AND b.term_taxonomy_id=%d ", $this->category );
        }
        if ( $search != '' ){
            $query .= " AND a.post_title LIKE '%$search%' ";
        }
        $query .= " AND a.post_type='ulp_course' AND a.post_status='publish' ";
        $query .= " AND c.meta_key='ulp_course_price' ";
        $query .= " GROUP BY a.ID ";
        if ( $this->orderBy ){
            switch ( $this->orderBy ){
                case 'popularity':
                  $query .= " ORDER BY total_sales DESC ";
                  break;
                case 'date':
                  $query .= " ORDER BY a.post_date DESC ";
                  break;
                case 'price':
                  $query .= " ORDER BY price ASC ";
                  break;
                case 'price-desc':
                  $query .= " ORDER BY price DESC ";
                  break;
            }
        }
        $query .= $wpdb->prepare( " LIMIT %d OFFSET %d;", $this->limit, $this->offset);

        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return [];
        }
        $currency = get_option('ulp_currency');
        $return = [];

        require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';
        $do_math = new \Affiliate_Referral_Amount( $this->affiliateId, 'ulp');

        foreach ( $data as $productData){
            $price = get_post_meta( $productData->ID, 'ulp_course_price', true );
            if ( is_numeric($price) ){
                $price .= $currency;
            }
            $referralAmount = $do_math->get_result( $price, $productData->ID );// input price, product id

            $return[$productData->ID] = [
                  'price'             => $this->formatUlpPrice( $price, $currency ),
                  'label'             => $productData->post_title,
                  'featureImage'      => get_the_post_thumbnail_url( $productData->ID ),
                  'id'                => $productData->ID,
                  'product_type'      => 'ulp',
                  'referral_amount'   => $this->formatUlpPrice( $referralAmount, $currency ),
                  'permalink'         => get_permalink( $productData->ID ),
                  'categories'        => get_the_terms( $productData->ID, 'ulp_course_categories' ),
            ];
        }
        return $return;
    }


    private function formatUlpPrice( $price='', $currency='' )
    {
        if ( function_exists('ulp_format_price') ){
            return ulp_format_price( $price );
        }
        return $price . $currency;
    }

    private function countsForUlp()
    {
        global $wpdb;
        $search = uap_sanitize_array( $this->searchPhrase );
        $query = "SELECT COUNT(a.ID) FROM {$wpdb->posts} a ";
        if ( $this->category ){
            $query .= " INNER JOIN {$wpdb->term_relationships} b ON a.ID=b.object_id ";
        }
        $query .= " WHERE 1=1 ";
        if ( $this->category ){
            $query .= $wpdb->prepare( " AND b.term_taxonomy_id=%d ", $this->category );
        }
        if ( $search != '' ){
            $query .= " AND a.post_title LIKE '%$search%' ";
        }
        $query .= " AND a.post_type='ulp_course' AND a.post_status='publish' ";
        return $wpdb->get_var( $query );
    }

}
