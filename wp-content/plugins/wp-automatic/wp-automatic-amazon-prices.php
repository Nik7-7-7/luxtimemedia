<?php
/**
 * Finds a product deserver price update
 */
function wp_automatic_amazon_prices_update($using_api = true) {
	
	// get a product to update SELECT * FROM `wp_postmeta` WHERE `meta_key` LIKE '%product_price_updated%'
	global $wpdb;
	$prefix = $wpdb->prefix;
	
	$query = "SELECT * FROM `{$prefix}postmeta` WHERE `meta_key` = 'product_price_updated' ORDER BY `meta_value` ASC limit 1 ";
	$rows = $wpdb->get_results ( $query );
	
	if (count ( $rows ) > 0) {
		
		$time = time ();
		$yesterday = $time - 86400;
		
		$row = $rows [0];
		
		if ($row->meta_value < $yesterday) {
			$pid = $row->post_id;
			
			echo '<br>Updating an amazon product price at post:' . $pid;
			
			wp_automatic_log_new('Amazon Price update', 'Updating an amazon product price at post:' . $pid);
			wp_automatic_amazon_price_update ( $pid, $using_api );
		}
	}
}

/**
 * Updates a specific post product price
 *
 * @param integer $pid
 */
function wp_automatic_amazon_price_update($pid, $using_api) {
	
	// get old price,asin,and more
	global $wpdb;
	$prefix = $wpdb->prefix;
	$price = ''; //ini
	
	$query = "SELECT * FROM `{$prefix}postmeta` WHERE `post_id` = '$pid' ";
	$rows = $wpdb->get_results ( $query );
	
	$isWooProduct = false;
	
	foreach ( $rows as $row ) {
		
		if ($row->meta_key == 'product_asin') {
			$product_asin = $row->meta_value;
		} elseif ($row->meta_key == 'product_price') {
			$product_price = $row->meta_value;
		} elseif ($row->meta_key == 'product_list_price') {
			$product_list_price = $row->meta_value;
		} elseif ($row->meta_key == 'original_link') {
			
			// find region
			preg_match ( '{amazon.(.*?)/}', $row->meta_value, $matchs );
			$region = ($matchs [1]);
		} elseif ($row->meta_key == '_price') {
			$isWooProduct = true;
		}
	}
	
	// getting details from amazon
	echo ' ASIN:' . $product_asin;

	// echo if is woo product
	if ($isWooProduct) {
		echo ' - Woo Product';
	}
	
	// curl ini
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
	curl_setopt ( $ch, CURLOPT_REFERER, 'http://www.bing.com/' );
	curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8' );
	curl_setopt ( $ch, CURLOPT_MAXREDIRS, 5 ); // Good leeway for redirections.
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 ); // Many login forms redirect at least once.
	
	if ($using_api) {
		
		// API method
		$amazonPublic = get_option ( 'wp_amazonpin_abk', '' );
		$amazonSecret = get_option ( 'wp_amazonpin_apvtk', '' );
		$amazonAid = get_option ( 'wp_amazonpin_aaid', '' );
		
		try {
			
			$obj = new wp_automatic_AmazonProductAPI ( wp_automatic_trim( $amazonPublic ), wp_automatic_trim( $amazonSecret ), wp_automatic_trim( $amazonAid ), $region );
			$obj->ch = $ch;
			
			$result = $obj->getItemByAsin ( $product_asin );
			
		} catch ( Exception $e ) {
			echo '<br>Exception:' . $e->getMessage ();
			
			// not found InvalidParameterValue:The ItemId B01BJ5G33C provided in the request is invalid.
			if(stristr( $e->getMessage () , 'InvalidParameterValue:The ItemId' )   ){
				$not_found_product = true;
			}
			
			//when TooManyRequests , the api did not really work, just return 
			if(stristr( $e->getMessage () , 'TooManyRequests' )  ){
				echo '<br>API did not work, got TooManyRequests message';
				return;
			}
		}
		
		if ( is_array($result) && count ( $result ) > 0) {
			$Item = $result [0];
			 
			if (! isset ( $Item->Offers->Listings [0]->Price )) {
				echo '<-- no price found';
				 
			}
			
			// current price
			$price = '';
			$price = $Item->Offers->Listings [0]->Price->DisplayAmount;
			$price = wp_automatic_trim( preg_replace ( '{\(.*?\)}', '', $price ) );
			$price_numeric = $Item->Offers->Listings [0]->Price->Amount;
			
			// list price
			$ListPrice = '';
			
			if (isset ( $Item->Offers->Listings [0]->Price->Savings )) {
				$ListPrice = $Item->Offers->Listings [0]->Price->Savings->Amount + $price_numeric;
				$ListPrice =wp_automatic_str_replace( $price_numeric, $ListPrice, $price );
			}
			
			if (wp_automatic_trim( $ListPrice ) == '') {
				$ListPrice = $price;
			}
			
			//out of stock?
			if(wp_automatic_trim($price) == ''){
				echo '<br>We got the product but not the price, obviousely out of stock';
				$out_of_stock = true;
			}
			
		} 
	} else {
		
		// no API method
		$amazonAid = get_option ( 'wp_amazonpin_aaid', '' );
		
		require_once (dirname ( __FILE__ ) . '/inc/class.amazon.api.less.php');
		$obj = new wp_automatic_amazon_api_less ( $ch, $region );
		
		try {
			
			$agent = get_option ( 'wp_automatic_amazon_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.163 Safari/537.36' );
			curl_setopt ( $ch, CURLOPT_USERAGENT, $agent );
			
			$item = $obj->getItemByAsin ( $product_asin );
			 
			
			if( isset($item['item_price']) ){
				
				$price = $item['item_price'];
				$ListPrice = $item['item_pre_price'];
			}
			
			//out of stock
			if(isset($item['item_out_of_stock']) && $item['item_out_of_stock'] == 'yes' ){
				$out_of_stock = true;
			}
			
		 
		} catch ( Exception $e ) {
			echo '<br>Amazon error:' . $e->getMessage ();
			
			if(stristr( $e->getMessage () , '404 Not founnd' )){
				$not_found_product = true;
				 
			}
			
		}
	}
	
	
	// update price
	if (wp_automatic_trim( $price ) != '') {
		
		//nice, we got a price from amazon which means this product is online and is in stock already, if it was out of stock, return it to stock 
		
		$wp_automatic_out_of_stock = get_post_meta($pid , 'wp_automatic_out_of_stock' , true);
		
		if($wp_automatic_out_of_stock == 1){
			echo '<br>This product was out of stock and now returned in stock, lets get it back published';
			
			//delete the out of stock custom field 
			delete_post_meta($pid, 'wp_automatic_out_of_stock' );		
			
			//publish the post
			wp_publish_post($pid);
			
		}
		
		if ($price != $product_price || $ListPrice != $product_list_price) {
			
			echo '<-- Price changed from '  . $product_price . ' to ' .  $price   .  ' updating...';
			
			update_post_meta ( $pid, 'product_price', ( string ) $price );
			update_post_meta ( $pid, 'product_list_price', ( string ) $ListPrice );
			
			if ($isWooProduct) {
				
				$thousandSeparator = ',';
				
				//if $region is es or de or fr or it, set the thousand separator to .
				if ($region == 'es' || $region == 'de' || $region == 'fr' || $region == 'it') {
					$thousandSeparator = '.';
				}
 
				// woo sousands separator
				if (class_exists ( 'WooCommerce' )) {
					$woocommerce_price_thousand_sep = get_option ( 'woocommerce_price_thousand_sep', '' );
					
					if ($woocommerce_price_thousand_sep == '.' || $woocommerce_price_thousand_sep == ',') {
						$thousandSeparator = $woocommerce_price_thousand_sep;
						echo '<br>Woo Thusand separator:' . $woocommerce_price_thousand_sep;
					}
				}
				
				// fixing listPrice
				$price_no_commas =wp_automatic_str_replace( $thousandSeparator, '', $ListPrice );
				preg_match ( '{\d.*\d}is', ($price_no_commas), $price_matches );
				update_post_meta ( $pid, '_regular_price', $price_matches [0] );
				;
				
				// fixing sell price
				$price_no_commas =wp_automatic_str_replace( $thousandSeparator, '', $price );
				preg_match ( '{\d.*\d}is', ($price_no_commas), $price_matches );
				update_post_meta ( $pid, '_price', $price_matches [0] );
				update_post_meta ( $pid, '_sale_price', $price_matches [0] );
				
				// get _sale_price and _regular_price and if they are equal, delete the _sale_price fix ticket:23246
				$regular_price = get_post_meta( $pid, '_regular_price', true );
				$sale_price = get_post_meta( $pid, '_sale_price', true );
				if( wp_automatic_trim($regular_price) != '' && wp_automatic_trim($sale_price) != '' && $regular_price == $sale_price ){
					echo '<br>deleting _sale_price as it is equal to _regular_price';
					delete_post_meta( $pid, '_sale_price' );
				}


			}
		} else {
			
			echo '<-- Price is up-to-date';
		}
	}else{
		echo '<-- Did not get a price';
		
		//deleted product action
		
		if( isset($not_found_product) && $not_found_product ){
			
			echo '<-- not a valid item no more, should be removed...';
			
			//delete if OPT_AMAZON_DELETE is enabled, otherwise, delete the price updated so we no more check it for price updates
			
			if( ! isset($wp_automatic_options) ) $wp_automatic_options = get_option ( 'wp_automatic_options' , array() );
			
			if(in_array('OPT_AMAZON_DELETE' , $wp_automatic_options  )){
				
				//completely delete
				echo '<br>Deleting this post (' .  $pid  . ') now.... ';
				wp_delete_post($pid , true);
				
			}else{
				
				// remove update meta tag
				delete_post_meta($pid, 'product_price_updated');
				echo '<br>Deleting 404 products option is not enabled, marking this product as deleted so we do not check it again';
			}
		}
		
		
		//out of stock action
		if( isset($out_of_stock) && $out_of_stock ){
			echo '<br>This item is out of stock already ....';
		 
			//set as pending
			if( ! isset($wp_automatic_options) ) $wp_automatic_options = get_option ( 'wp_automatic_options' , array() );
			
			if( in_array('OPT_AMAZON_PENDING', $wp_automatic_options) ){
				
				echo '<-- setting the post status to pending...';
				
				//1 set the post status to pending
				wp_update_post(array(
						'ID'    =>  $pid,
						'post_status'   =>  'pending'
				));
				
				//2 set the custom field wp_automatic_out_of_stock to 1
			 	update_post_meta($pid, 'wp_automatic_out_of_stock' , '1');
				
			}
		
		}
		
		
		
	}
	
	if(! isset($not_found_product))
	update_post_meta ( $pid, 'product_price_updated', time () );
}

//function wp_automatic_ebay_prices_update
function wp_automatic_ebay_prices_update() {

	echo '<br><br>Updating ebay prices for a single product';
	
	// get a product to update SELECT * FROM `wp_postmeta` WHERE `meta_key` LIKE '%product_price_updated%'
	global $wpdb;
	$prefix = $wpdb->prefix;
	
	$query = "SELECT * FROM `{$prefix}postmeta` WHERE `meta_key` = 'product_price_updated_ebay' ORDER BY `meta_value` ASC limit 1 ";
	$rows = $wpdb->get_results ( $query );
	
	if (count ( $rows ) > 0) {
		
		$time = time ();
		$yesterday = $time - 86400;
		
		$row = $rows [0];
		$pid = $row->post_id;

		if ($row->meta_value < $yesterday) {
			
			
			echo '<br> - Updating an ebay product price at post:' . $pid;
			
			wp_automatic_log_new('Ebay Price update', 'Updating an ebay product price at post:' . $pid);

			//set the last update time
			update_post_meta ( $pid, 'product_price_updated_ebay', time () );

			wp_automatic_ebay_price_update ( $pid );
		}else{
			echo '<br> - last update for post ' . $pid . ' was less than 24 hours ago';
		}
	}else{
		echo '<br> - No product to update';
	}
}

//function product_price_updated_ebay 
//1- get the product id from the custom field original_link https://www.ebay.com/itm/135205563701
//2- use the eBay browse API to get the price of the product and check if it is in stock
//3- update the price if it is different
function wp_automatic_ebay_price_update($pid) {
	
	// get old price,asin,and more
	global $wpdb;
	$prefix = $wpdb->prefix;
	$price = ''; //ini
	
	$query = "SELECT * FROM `{$prefix}postmeta` WHERE `post_id` = '$pid' ";
	$rows = $wpdb->get_results ( $query );
	
	$isWooProduct = false;
	
	foreach ( $rows as $row ) {
		
		if ($row->meta_key == 'product_price') {
			$product_price = $row->meta_value;
		} elseif ($row->meta_key == 'product_list_price') {
			$product_list_price = $row->meta_value;
		} elseif ($row->meta_key == 'original_link') {

			echo '<br> - Item link:' . $row->meta_value;
			$link = $row->meta_value;
		
		}elseif ($row->meta_key == 'item_api_id') {
			$ebay_item_id = $row->meta_value;

		} elseif ($row->meta_key == '_price') {
			$isWooProduct = true;
		}
	}
	
	// getting details from ebay
	echo '<br> - Ebay Item ID:' . $ebay_item_id;

	//current price
	echo '<br> - Last price:' . $product_price;
	echo '<br> - Last list price:' . $product_list_price;

	// echo if is woo product
	if ($isWooProduct) {
		echo ' <- Woo Product';
	}

	//require core.ebay.php
	require_once (dirname ( __FILE__ ) . '/core.ebay.php');

	//new instance of ebay
	$WpAutomatic = new WpAutomaticeBay();

	 //get_single_product
	 try {
		$single_product = $WpAutomatic->get_single_product($ebay_item_id,$link);
	} catch (Exception $e) {
		echo '<br> - Exception:' . $e->getMessage();

		//if not found and OPT_EBAY_DELETE is enabled, delete the post
		if(stristr( $e->getMessage () , 'not found' )){
			
			if( ! isset($wp_automatic_options) ) $wp_automatic_options = get_option ( 'wp_automatic_options' , array() );
			
			if(in_array('OPT_EBAY_DELETE' , $wp_automatic_options  )){
				
				//completely delete
				echo '<br> - Deleting this post (' .  $pid  . ') now.... ';
				wp_delete_post($pid , true);
				
			}else{
				
				// remove update meta tag
				delete_post_meta($pid, 'product_price_updated_ebay');
				echo '<br> - Deleting 404 products option is not enabled, marking this product as deleted so we do not check it again';
			}
			
			return;

		}
	}

	//print
	echo '<br> - Current price:' . $single_product['item_marketing_price'];
	echo '<br> - Current listing price:' . $single_product['item_price'];

	//update price
	if (wp_automatic_trim( $single_product['item_marketing_price'] ) != '') {
		
		if ($single_product['item_marketing_price'] != $product_price || $single_product['item_price'] != $product_list_price) {
			
			echo '<-- Price changed from '  . $product_price . ' to ' .  $single_product['item_marketing_price']   .  ' updating...';
			
			update_post_meta ( $pid, 'product_price', ( string ) $single_product['item_marketing_price'] );
			update_post_meta ( $pid, 'product_list_price', ( string ) $single_product['item_price'] );
			
			if ($isWooProduct) {
				
				$thousandSeparator = ',';
				
				//if $region is es or de or fr or it, set the thousand separator to .
				/*
				if ($region == 'es' || $region == 'de' || $region == 'fr' || $region == 'it') {
					$thousandSeparator = '.';
				}*/
 
				// woo sousands separator
				if (class_exists ( 'WooCommerce' )) {
					$woocommerce_price_thousand_sep = get_option ( 'woocommerce_price_thousand_sep', '' );
					
					if ($woocommerce_price_thousand_sep == '.' || $woocommerce_price_thousand_sep == ',') {
						$thousandSeparator = $woocommerce_price_thousand_sep;
						echo '<br>Woo Thusand separator:' . $woocommerce_price_thousand_sep;
					}
				}
				
				// fixing listPrice
				$price_no_commas =wp_automatic_str_replace( $thousandSeparator, '', $single_product['item_price'] );
				preg_match ( '{\d.*\d}is', ($price_no_commas), $price_matches );
				update_post_meta ( $pid, '_regular_price', $price_matches [0] );
				;
				
				// fixing sell price
				$price_no_commas =wp_automatic_str_replace( $thousandSeparator, '', $single_product['item_marketing_price'] );
				preg_match ( '{\d.*\d}is', ($price_no_commas), $price_matches );
				update_post_meta ( $pid, '_price', $price_matches [0] );
				update_post_meta ( $pid, '_sale_price', $price_matches [0] );

				// get _sale_price and _regular_price and if they are equal, delete the _sale_price fix ticket:23246
				$regular_price = get_post_meta( $pid, '_regular_price', true );
				$sale_price = get_post_meta( $pid, '_sale_price', true );
				if( wp_automatic_trim($regular_price) != '' && wp_automatic_trim($sale_price) != '' && $regular_price == $sale_price ){
					echo '<br>deleting _sale_price as it is equal to _regular_price';
					delete_post_meta( $pid, '_sale_price' );
				}


			}

		} else {
			
			echo '<-- Price is up-to-date';
		}
	}else{
		echo '<-- Did not get a price';
	}
	
}

 