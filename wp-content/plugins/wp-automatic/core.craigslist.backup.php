<?php

// Main Class
require_once 'core.php';
class WpAutomaticCraigslist extends wp_automatic {
	
	/*
	 * ---* youtube get links ---
	 */
	function craigslist_fetch_items($keyword, $camp) {
		echo "<br>So I should now get some items from craigslist";
		
		// ini options
		$camp_opt = unserialize ( $camp->camp_options );
		if (stristr ( $camp->camp_general, 'a:' ))
			$camp->camp_general = base64_encode ( $camp->camp_general );
		$camp_general = unserialize ( base64_decode ( $camp->camp_general ) );
		$camp_general = array_map ( 'wp_automatic_stripslashes', $camp_general );
		
		// items url
		$cg_cl_page = wp_automatic_trim( $camp_general ['cg_cl_page'] );
		
		// verify valid link
		if (! (stristr ( $cg_cl_page, 'http' ) && stristr ( $cg_cl_page, 'craigslist.org' ))) {
			echo '<br>Provided craigslist link is not valid please visit craigslist.org and get a correct one';
			return false;
		}
		
		// get start-index for this keyword
		$query = "select keyword_start ,keyword_id from {$this->wp_prefix}automatic_keywords where keyword_name='$keyword' and keyword_camp={$camp->camp_id}";
		$rows = $this->db->get_results ( $query );
		@$row = $rows [0];
		
		// If no rows add a keyword record
		if (count ( $rows ) == 0) {
			$query = "insert into {$this->wp_prefix}automatic_keywords(keyword_name,keyword_camp,keyword_start) values ('$keyword','{$camp->camp_id}',1)";
			$this->db->query ( $query );
			$kid = $this->db->insert_id;
			$start = 0;
		} else {
			$kid = $row->keyword_id;
			$start = $row->keyword_start;
		}
		
		if ($start == - 1) {
			echo '<- exhausted link';
			
			if (! in_array ( 'OPT_CL_CACHE', $camp_opt )) {
				$start = 0;
				echo '<br>Cache disabled resetting index to 0';
			} else {
				
				// check if it is reactivated or still deactivated
				if ($this->is_deactivated ( $camp->camp_id, $keyword )) {
					$start = 0;
				} else {
					// still deactivated
					return false;
				}
			}
		}
		
		// start
		if ($start == 1)
			$start = 0;
		
		if ($start == 0) {
		} elseif (stristr ( $cg_cl_page, '?' )) {
			$cg_cl_page .= '&s=' . $start;
		} else {
			$cg_cl_page .= '?s=' . $start;
		}
		
		echo '<br>Craigslist items url:' . $cg_cl_page;
		
		// extracting search category search/cta from https://newyork.craigslist.org/search/ata
		$api_url = $this->get_api_url ( $cg_cl_page );
		echo '<br>API URL:' . $api_url;
		echo ' index:' . $start;
		
	 
		// update start index to start+1
		$nextstart = $start + 120;
		
		$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $nextstart where keyword_id=$kid ";
		$this->db->query ( $query );
		
		// get items
		// curl get
		$x = 'error';
		$url = $api_url;
		curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
		curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $url ) );
		$exec = curl_exec ( $this->ch );
		$x = curl_error ( $this->ch );
		
		 
		
		// error check
		if (wp_automatic_trim( $x ) != '') {
			echo '<br>Curl error:' . $x;
			return false;
		}
		
		// validate reply
		if (! stristr ( $exec, '{' )) {
			echo '<br>Not expected response from Craigslist';
			
			if (stristr ( $exec, 'IP has been automatically blocked' )) {
				echo '<br>Your server IP is blocked from Craigslist, you will need to use proxies on the plugin settings page';
			}
		}
		
		//decode json
		$json = json_decode($exec);
		echo '<pre>';
		print_r($json->data->items);
		exit;
		
		//items data.items
		
		// load items from feed txt
		// Matching items <a href="https://denver.craigslist.org/clt/d/littleton-rattan-handmade-tray/7354151791.html" data-id="7354151791" class="result-title hdrl
		
		preg_match_all ( '!<li class="result-row"(.*?)</li>!s', $exec, $itmsMatchs );
		
		$allItms = $itmsMatchs [0];
		
		// Check returned items count
		if (count ( $allItms ) > 0) {
			
			echo '<br>Valid reply returned with ' . count ( $allItms ) . ' item';
		} else {
			
			echo '<br>No items found';
			
			echo '<br>Keyword have no more items deactivating...';
			$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
			$this->db->query ( $query );
			
			if (! in_array ( 'OPT_NO_DEACTIVATE', $camp_opt ))
				$this->deactivate_key ( $camp->camp_id, $keyword );
		}
		
		echo '<ol>';
		
		$i = 0;
		foreach ( $allItms as $itemTxt ) {
			
			// get link,title
			preg_match ( '{<a href="(https[^"]*?)" data-id="(\d*?)" class="result-title.*?>(.*?)</a>}', $itemTxt, $s_itmsMatchs );
			
			$item ['item_title'] = $s_itmsMatchs [3];
			$item ['item_description'] = $s_itmsMatchs [3];
			$item_link = $item ['item_link'] = $s_itmsMatchs [1];
			
			// match date
			preg_match ( '{datetime\="(.*?)"}s', $itemTxt, $lnkMatchs );
			$item ['item_date'] = $lnkMatchs [1];
			
			// match img class="result-image gallery" data-ids="3:00S0S_ab4BTXotjaDz_0ak07K,3:00t0t_3slUtPhwD4Pz_0ak07K,3:00B0B_cTk1cEIBxSLz_0ai07I,3:00i0i_5seooMVgQhlz_0ak07K,3:00a0a_4QmgpcccJZiz_0ak07K"
			preg_match ( '{result-image gallery" data-ids\="(.*?)"}s', $itemTxt, $ImgMatchs );
			$ImgMatchs_arr = @explode ( ',', $ImgMatchs [1] );
			$ImgMatchs_arr = preg_replace ( '!^\d\:!', '', $ImgMatchs_arr );
			
			$item ['item_img'] = '';
			$item ['item_imgs'] = '';
			$imgs_arr = array ();
			if (isset ( $ImgMatchs_arr [0] ) && wp_automatic_trim( $ImgMatchs_arr [0] ) != '') {
				$item ['item_img'] = $this->craigslist_get_img_url ( $ImgMatchs_arr [0] );
				
				foreach ( $ImgMatchs_arr as $ImgMatchs_arr_s ) {
					$imgs_arr [] = $this->craigslist_get_img_url ( $ImgMatchs_arr_s );
				}
				
				$item ['item_imgs'] = implode ( ',', $imgs_arr );
			}
			
			// get id
			$ex = preg_match ( '{(\d*?)\.html}', $item ['item_link'], $allMatchs );
			$id = $allMatchs [1];
			
			// get price <span class="result-price">$20</span>
			preg_match ( '!class="result-price">(.*?)<!', $itemTxt, $priceMatchs );
			$item ['item_price'] = $priceMatchs [1];
			
			// <span class="result-hood"> (Broomfield )</span>
			preg_match ( '!class="result-hood">(.*?)<!', $itemTxt, $hoodMatchs );
			$item ['item_hood'] = wp_automatic_trim( $hoodMatchs [1] ) != '(  )' ? $hoodMatchs [1] : '';
			
			print_r ( $item );
			exit ();
			
			$data = (base64_encode ( serialize ( $item ) ));
			
			echo '<li> Link:' . $item_link;
			
			// No image skip
			if (wp_automatic_trim( $item ['item_img'] ) == '' && in_array ( 'OPT_CL_IMG', $camp_opt )) {
				echo '<- No image skip';
				continue;
			}
			
			if ($this->is_execluded ( $camp->camp_id, $item_link )) {
				echo '<-- Execluded';
				continue;
			}
			
			if (! $this->is_duplicate ( $item_link )) {
				$query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (  '$id', '0', '$data' ,'cl_{$camp->camp_id}_$keyword')  ";
				$this->db->query ( $query );
			} else {
				echo ' <- duplicated <a href="' . get_edit_post_link ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
			}
			
			$i ++;
		}
		
		echo '</ol>';
	}
	
	/*
	 * ---* craigslist post ---
	 */
	function craigslist_get_post($camp) {
		
		// Campaign options
		$camp_opt = unserialize ( $camp->camp_options );
		
		if (stristr ( $camp->camp_general, 'a:' ))
			$camp->camp_general = base64_encode ( $camp->camp_general );
		$camp_general = unserialize ( base64_decode ( $camp->camp_general ) );
		$camp_general = array_map ( 'wp_automatic_stripslashes', $camp_general );
		
		$keywords = array (
				'*' 
		);
		
		foreach ( $keywords as $keyword ) {
			
			$keyword = wp_automatic_trim( $keyword );
			
			// update last keyword
			update_post_meta ( $camp->camp_id, 'last_keyword', wp_automatic_trim( $keyword ) );
			
			if (wp_automatic_trim( $keyword ) != '') {
				
				// getting links from the db for that keyword
				$query = "select * from {$this->wp_prefix}automatic_general where item_type=  'cl_{$camp->camp_id}_$keyword' ";
				$this->used_keyword = $keyword;
				$res = $this->db->get_results ( $query );
				
				// when no links lets get new links
				if (count ( $res ) == 0) {
					
					// clean any old cache for this keyword
					$query_delete = "delete from {$this->wp_prefix}automatic_general where item_type='cl_{$camp->camp_id}_$keyword' ";
					$this->db->query ( $query_delete );
					
					// get new fresh items
					$this->craigslist_fetch_items ( $keyword, $camp );
					
					// getting links from the db for that keyword
					$res = $this->db->get_results ( $query );
				}
				
				// check if already duplicated
				// deleting duplicated items
				$res_count = count ( $res );
				for($i = 0; $i < $res_count; $i ++) {
					
					$t_row = $res [$i];
					
					$t_data = unserialize ( base64_decode ( $t_row->item_data ) );
					
					$t_link_url = $t_data ['item_link'];
					
					if ($this->is_duplicate ( $t_link_url )) {
						
						// duplicated item let's delete
						unset ( $res [$i] );
						
						echo '<br>craigslist item (' . $t_data ['item_title'] . ') found cached but duplicated <a href="' . get_permalink ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
						
						// delete the item
						$query = "delete from {$this->wp_prefix}automatic_general where id={$t_row->id} ";
						$this->db->query ( $query );
					} else {
						break;
					}
				}
				
				// check again if valid links found for that keyword otherwise skip it
				if (count ( $res ) > 0) {
					
					// lets process that link
					$ret = $res [$i];
					
					$data = unserialize ( base64_decode ( $ret->item_data ) );
					
					$temp = $data;
					
					echo '<br>Found Link:' . $temp ['item_link'];
					
					// clean show content
					if (stristr ( $temp ['item_description'], 'showcontact' )) {
						echo '<br>Removing contact link';
						$temp ['item_description'] = preg_replace ( '{<a.*?/a>}s', '', $temp ['item_description'] );
					}
					
					// getting full description
					
					// getting full image
					if (wp_automatic_trim( $temp ['item_img'] ) != '') {
						
						$fullImg =wp_automatic_str_replace( '300x300', '600x450', $temp ['item_img'] );
						echo '<br>Full Image:' . $fullImg;
						
						$temp ['item_img'] = $fullImg;
					}
					
					// Img shortcode
					$temp ['item_img_html'] = '';
					if (wp_automatic_trim( $temp ['item_img'] ) != '') {
						$temp ['item_img_html'] = '<img src="' . $temp ['item_img'] . '" />';
					}
					
					// update the link status to 1
					$query = "delete from {$this->wp_prefix}automatic_general where id={$ret->id}";
					$this->db->query ( $query );
					
					// if cache not active let's delete the cached videos and reset indexes
					if (! in_array ( 'OPT_CL_CACHE', $camp_opt )) {
						
						echo '<br>Cache disabled claring cache ...';
						$query = "delete from {$this->wp_prefix}automatic_general where item_type='cl_{$camp->camp_id}_$keyword' ";
						$this->db->query ( $query );
						
						// reset index
						$query = "update {$this->wp_prefix}automatic_keywords set keyword_start =1 where keyword_camp={$camp->camp_id}";
						$this->db->query ( $query );
					}
					
					// remove after price
					$item_title = $temp ['item_title'];
					
					// full item details
					
					// curl get
					$x = 'error';
					$url = $temp ['item_link'];
					
					echo '<br>Loading original post to get full content...';
					
					// curl get
					$x = 'error';
					curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
					curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $url ) );
					$exec = curl_exec ( $this->ch );
					$x = curl_error ( $this->ch );
					
					// verify full content
					$temp ['item_address'] = '';
					$temp ['item_location_latitude'] = '';
					$temp ['item_location_longitude'] = '';
					$temp ['item_location'] = '';
					$temp ['item_attributes'] = '';
					
					if (stristr ( $exec, 'postingbody' )) {
						echo '<-- postingbody found, content seems to be correct';
						
						echo '<br>Finding full content..';
						
						// fullContent <section id="postingbody">
						preg_match ( '!<section id="postingbody">(.*?)</section!s', $exec, $bodyMatchs );
						$bodyMatchs = $bodyMatchs [1];
						$bodyMatchs = preg_replace ( '!<div class="print-qrcode.*?div>!s', '', $bodyMatchs );
						$bodyMatchs = preg_replace ( '!<div class="print-information.*?div>!s', '', $bodyMatchs );
						
						if (wp_automatic_trim( $bodyMatchs ) != '') {
							echo '<-- Found with length of:' . strlen ( $bodyMatchs );
							$temp ['item_description'] = wp_automatic_trim( $bodyMatchs );
							
							// remove show contact info
							$temp ['item_description'] =wp_automatic_str_replace( 'show contact info', '', $temp ['item_description'] );
						} else {
							echo '<-- not able to find the description';
						}
						
						// adress <div class="mapaddress">11111 W. 6th Ave Unit E</div>
						preg_match ( '!class="mapaddress">(.*?)</div>!', $exec, $AddrMatchs );
						if (isset ( $AddrMatchs [1] ))
							$temp ['item_address'] = $AddrMatchs [1];
						
						// get lat and lang data-latitude="39.725770" data-longitude="-105.121685" data-accuracy="10"
						preg_match ( '!data-latitude="(.*?)" data-longitude="(.*?)"!', $exec, $latMatchs );
						$temp ['item_location_latitude'] = $latMatchs [1];
						$temp ['item_location_longitude'] = $latMatchs [2];
						$temp ['item_location'] = $latMatchs [1] . ',' . $latMatchs [2];
						
						// attributes <p class="attrgroup">\s*<span
						preg_match_all ( '!<p class="attrgroup">\s*(<span.*?)</p>!s', $exec, $attrMatchs );
						$attrMatchs = $attrMatchs [1];
						
						// remove other listings by this author
						$u = 0;
						foreach ( $attrMatchs as $attrMatch ) {
							if (stristr ( $attrMatch, 'class=' ))
								unset ( $attrMatchs [$u] );
							$u ++;
						}
						
						$temp ['item_attributes'] = implode ( '', $attrMatchs );
					}
					
					// gallery html
					$cg_cl_full_img_t = @$camp_general ['cg_cl_full_img_t'];
					if (wp_automatic_trim( $cg_cl_full_img_t ) == '') {
						$cg_cl_full_img_t = '<img src="[img_src]" class="wp_automatic_gallery" />';
					}
					
					$product_imgs_html = '';
					
					$allImages = explode ( ',', $temp ['item_imgs'] );
					$temp ['item_images'] = $allImages;
					
					$allImages_html = '';
					
					foreach ( $allImages as $singleImage ) {
						
						$singleImageHtml = $cg_cl_full_img_t;
						$singleImageHtml =wp_automatic_str_replace( '[img_src]', $singleImage, $singleImageHtml );
						$allImages_html .= $singleImageHtml;
					}
					
					$temp ['item_imgs_html'] = $allImages_html;
					
					// map
					$temp ['item_map'] = '<iframe src = "https://maps.google.com/maps?q=' . $temp ['item_location_latitude'] . ',' . $temp ['item_location_longitude'] . '&hl=es;z=14&amp;output=embed"></iframe>';
					
					// numeric price
					$price_raw = $temp ['item_price'];
					
					$price_raw =wp_automatic_str_replace( ',', '', $price_raw );
					
					// numeric price
					preg_match ( '{\d[.*\d]*}is', $price_raw, $price_matches );
					$temp ['item_price_numeric'] = $price_matches [0];
					
					return $temp;
				} else {
					
					echo '<br>No links found for this keyword';
				}
			} // if trim
		} // foreach keyword
	}
	function craigslist_get_img_url($img_id) {
		return 'https://images.craigslist.org/' . $img_id . '_600x450.jpg';
	}
	
	/**
	 * Function to extract search category //extracting search category search/cta from https://newyork.craigslist.org/search/ata
	 * case:https://newyork.craigslist.org/search/great-neck-ny/cta?lat=40.8491&lon=-73.7485&search_distance=46&srchType=T#search=1~gallery~0~0
	 * 
	 * @param string $url
	 * @return string
	 */
	function get_api_url($url) {
		
		// if no search string found
		if (! stristr ( $url, '/search/' )) {
			throw new Error ( 'Search URL is not correct, please add a correct CL search URL like this one https://newyork.craigslist.org/search/ata' );
		}
		
		// remove #search=1~gallery~0~0
		$url = preg_replace ( '!#.*!', '', $url );
		
		// split /search/
		$url_parts = explode ( '/search/', $url );
		$domain = $url_parts [0];
		$domain = wp_automatic_str_replace(array('https://','http://','www.'), '', $domain);
		
		$path_plus_params = $url_parts [1];
		
		// split path from params
		$path_plus_params_parts = explode ( '?', $path_plus_params );
		$path = $path_plus_params_parts [0];
		$path_encoded = urlencode($path);
		$params =  $path_plus_params_parts [1];
		$area_code = $this->domain_to_area_code ( $domain );
		
		echo '<br>Domain:' . $domain . ' path:' . $path . ' Params:' . $params . ' Area Code:' . $area_code ;
		 
		
		// api url https://sapi.craigslist.org/web/v7/postings/search/full?batch=3-0-360-0-0&cc=US&lang=en&query=painting&searchPath=great-neck-ny%2Fbka
		$api_url = "https://sapi.craigslist.org/web/v7/postings/search/full?batch={$area_code}-0-360-0-0&searchPath={$path_encoded}&{$params}&cc=US&lang=en";
		
		return $api_url;
	}
	
	/**
	 * Function to convert domain name to area code example cairo.craigslist.com to 162
	 * @param unknown $domain
	 * @return integer area ID
	 */
	function domain_to_area_code($domain) {
		$domains = 'annarbor.craigslist.org,abbotsford.craigslist.ca,abilene.craigslist.org,albuquerque.craigslist.org,nesd.craigslist.org,albanyga.craigslist.org,aberdeen.craigslist.co.uk,acapulco.craigslist.com.mx,accra.craigslist.org,addisababa.craigslist.org,adelaide.craigslist.com.au,cenla.craigslist.org,malaga.craigslist.es,athensga.craigslist.org,auckland.craigslist.org,albany.craigslist.org,alicante.craigslist.es,allentown.craigslist.org,amarillo.craigslist.org,ahmedabad.craigslist.co.in,ames.craigslist.org,amsterdam.craigslist.org,gadsden.craigslist.org,anchorage.craigslist.org,annapolis.craigslist.org,altoona.craigslist.org,appleton.craigslist.org,asheville.craigslist.org,athens.craigslist.gr,atlanta.craigslist.org,auburn.craigslist.org,augusta.craigslist.org,austin.craigslist.org,scranton.craigslist.org,bakersfield.craigslist.org,baltimore.craigslist.org,barcelona.craigslist.es,bhubaneswar.craigslist.co.in,bacolod.craigslist.com.ph,bajasur.craigslist.com.mx,belleville.craigslist.ca,berlin.craigslist.de,beirut.craigslist.org,brantford.craigslist.ca,scottsbluff.craigslist.org,baghdad.craigslist.org,bangladesh.craigslist.org,binghamton.craigslist.org,bham.craigslist.org,birmingham.craigslist.co.uk,billings.craigslist.org,bilbao.craigslist.es,bismarck.craigslist.org,bemidji.craigslist.org,guanajuato.craigslist.com.mx,bangkok.craigslist.co.th,belfast.craigslist.co.uk,bologna.craigslist.it,bgky.craigslist.org,bellingham.craigslist.org,bn.craigslist.org,bloomington.craigslist.org,boone.craigslist.org,bend.craigslist.org,brisbane.craigslist.com.au,bangalore.craigslist.co.in,bordeaux.craigslist.org,colombia.craigslist.org,boise.craigslist.org,boston.craigslist.org,boulder.craigslist.org,beaumont.craigslist.org,brainerd.craigslist.org,brighton.craigslist.co.uk,burlington.craigslist.org,bremen.craigslist.de,bern.craigslist.ch,brownsville.craigslist.org,barrie.craigslist.ca,bristol.craigslist.co.uk,brussels.craigslist.org,brasilia.craigslist.org,basel.craigslist.ch,battlecreek.craigslist.org,bath.craigslist.co.uk,butte.craigslist.org,batonrouge.craigslist.org,budapest.craigslist.org,buenosaires.craigslist.org,buffalo.craigslist.org,bucharest.craigslist.org,brunswick.craigslist.org,bozeman.craigslist.org,columbia.craigslist.org,cairo.craigslist.org,akroncanton.craigslist.org,cambridge.craigslist.co.uk,guangzhou.craigslist.com.cn,capecod.craigslist.org,casablanca.craigslist.org,catskills.craigslist.org,carbondale.craigslist.org,chambersburg.craigslist.org,cariboo.craigslist.ca,canberra.craigslist.com.au,caracas.craigslist.org,cdo.craigslist.com.ph,cadiz.craigslist.es,cebu.craigslist.com.ph,cedarrapids.craigslist.org,cfl.craigslist.org,cologne.craigslist.de,charlotte.craigslist.org,chico.craigslist.org,chennai.craigslist.co.in,chihuahua.craigslist.com.mx,chicago.craigslist.org,chatham.craigslist.ca,chillicothe.craigslist.org,chambana.craigslist.org,chautauqua.craigslist.org,christchurch.craigslist.org,charleston.craigslist.org,chattanooga.craigslist.org,cincinnati.craigslist.org,juarez.craigslist.com.mx,chongqing.craigslist.com.cn,clarksville.craigslist.org,cleveland.craigslist.org,calgary.craigslist.ca,centralmich.craigslist.org,comoxvalley.craigslist.ca,belohorizonte.craigslist.org,cnj.craigslist.org,cairns.craigslist.com.au,kerala.craigslist.co.in,columbus.craigslist.org,cookeville.craigslist.org,copenhagen.craigslist.org,oregoncoast.craigslist.org,cosprings.craigslist.org,columbiamo.craigslist.org,coventry.craigslist.co.uk,capetown.craigslist.co.za,caribbean.craigslist.org,costarica.craigslist.org,pampanga.craigslist.com.ph,corpuschristi.craigslist.org,corvallis.craigslist.org,charlestonwv.craigslist.org,csd.craigslist.org,columbusga.craigslist.org,collegestation.craigslist.org,chengdu.craigslist.com.cn,clovis.craigslist.org,curitiba.craigslist.org,cardiff.craigslist.co.uk,daytona.craigslist.org,dallas.craigslist.org,dayton.craigslist.org,dubuque.craigslist.org,derby.craigslist.co.uk,delhi.craigslist.co.in,denver.craigslist.org,detroit.craigslist.org,dothan.craigslist.org,decatur.craigslist.org,dalian.craigslist.com.cn,duluth.craigslist.org,delaware.craigslist.org,dundee.craigslist.co.uk,danville.craigslist.org,dresden.craigslist.de,delrio.craigslist.org,darwin.craigslist.com.au,desmoines.craigslist.org,dublin.craigslist.org,dunedin.craigslist.co.nz,durban.craigslist.co.za,dusseldorf.craigslist.de,devon.craigslist.co.uk,davaocity.craigslist.com.ph,eauclaire.craigslist.org,eastco.craigslist.org,edinburgh.craigslist.co.uk,edmonton.craigslist.ca,eastidaho.craigslist.org,eastky.craigslist.org,elko.craigslist.org,elmira.craigslist.org,elpaso.craigslist.org,eastmids.craigslist.co.uk,kenai.craigslist.org,eastnc.craigslist.org,enid.craigslist.org,eastoregon.craigslist.org,erie.craigslist.org,easternshore.craigslist.org,essen.craigslist.de,essex.craigslist.co.uk,easttexas.craigslist.org,eugene.craigslist.org,evansville.craigslist.org,martinsburg.craigslist.org,fredericksburg.craigslist.org,fairbanks.craigslist.org,fargo.craigslist.org,fayetteville.craigslist.org,kalispell.craigslist.org,frederick.craigslist.org,fingerlakes.craigslist.org,sierravista.craigslist.org,flagstaff.craigslist.org,florencesc.craigslist.org,florence.craigslist.it,ftmcmurray.craigslist.ca,fortmyers.craigslist.org,farmington.craigslist.org,flint.craigslist.org,frankfurt.craigslist.de,fresno.craigslist.org,faro.craigslist.pt,siouxfalls.craigslist.org,fortsmith.craigslist.org,fortcollins.craigslist.org,fortdodge.craigslist.org,fortaleza.craigslist.org,fukuoka.craigslist.jp,fortwayne.craigslist.org,fayar.craigslist.org,greensboro.craigslist.org,guadalajara.craigslist.com.mx,genoa.craigslist.it,grandforks.craigslist.org,glensfalls.craigslist.org,grandisland.craigslist.org,westslope.craigslist.org,glasgow.craigslist.co.uk,goldcountry.craigslist.org,galveston.craigslist.org,grenoble.craigslist.org,gainesville.craigslist.org,goa.craigslist.co.in,guelph.craigslist.ca,gulfport.craigslist.org,greenbay.craigslist.org,killeen.craigslist.org,grandrapids.craigslist.org,granada.craigslist.es,greenville.craigslist.org,greatfalls.craigslist.org,guatemala.craigslist.org,micronesia.craigslist.org,micronesia.craigslist.org,geneva.craigslist.ch,hannover.craigslist.de,hamburg.craigslist.de,hat.craigslist.ca,hobart.craigslist.com.au,heidelberg.craigslist.de,helsinki.craigslist.fi,natchez.craigslist.org,haifa.craigslist.org,halifax.craigslist.ca,hangzhou.craigslist.com.cn,hiltonhead.craigslist.org,hiroshima.craigslist.jp,hickory.craigslist.org,hongkong.craigslist.hk,holland.craigslist.org,helena.craigslist.org,humboldt.craigslist.org,hamilton.craigslist.ca,hanford.craigslist.org,honolulu.craigslist.org,houston.craigslist.org,pretoria.craigslist.co.za,hermosillo.craigslist.com.mx,harrisburg.craigslist.org,huntsville.craigslist.org,hartford.craigslist.org,huntington.craigslist.org,hudsonvalley.craigslist.org,houma.craigslist.org,newhaven.craigslist.org,hyderabad.craigslist.co.in,iowacity.craigslist.org,baleares.craigslist.es,indore.craigslist.co.in,iloilo.craigslist.com.ph,imperial.craigslist.org,indianapolis.craigslist.org,inlandempire.craigslist.org,longisland.craigslist.org,istanbul.craigslist.com.tr,ithaca.craigslist.org,chandigarh.craigslist.co.in,jaipur.craigslist.co.in,jackson.craigslist.org,jacksonville.craigslist.org,jonesboro.craigslist.org,ashtabula.craigslist.org,jakarta.craigslist.org,joplin.craigslist.org,johannesburg.craigslist.co.za,juneau.craigslist.org,jerusalem.craigslist.org,janesville.craigslist.org,jxn.craigslist.org,jacksontn.craigslist.org,jerseyshore.craigslist.org,ukraine.craigslist.org,kitchener.craigslist.ca,kelowna.craigslist.ca,kent.craigslist.co.uk,keys.craigslist.org,klamath.craigslist.org,kaiserslautern.craigslist.de,kamloops.craigslist.ca,kingston.craigslist.ca,knoxville.craigslist.org,kolkata.craigslist.co.in,cranbrook.craigslist.ca,kpr.craigslist.org,kirksville.craigslist.org,kansascity.craigslist.org,kuwait.craigslist.org,kalamazoo.craigslist.org,tippecanoe.craigslist.org,lakeland.craigslist.org,lansing.craigslist.org,lausanne.craigslist.ch,lawton.craigslist.org,losangeles.craigslist.org,lubbock.craigslist.org,northplatte.craigslist.org,lakecity.craigslist.org,lascruces.craigslist.org,london.craigslist.co.uk,leeds.craigslist.co.uk,leipzig.craigslist.de,lexington.craigslist.org,lafayette.craigslist.org,logan.craigslist.org,lille.craigslist.org,lima.craigslist.org,lisbon.craigslist.pt,littlerock.craigslist.org,liverpool.craigslist.co.uk,lakecharles.craigslist.org,lucknow.craigslist.co.in,limaohio.craigslist.org,lincoln.craigslist.org,lancaster.craigslist.org,londonon.craigslist.ca,louisville.craigslist.org,loz.craigslist.org,lapaz.craigslist.org,laredo.craigslist.org,lacrosse.craigslist.org,lasalle.craigslist.org,lethbridge.craigslist.ca,luxembourg.craigslist.org,lasvegas.craigslist.org,lawrence.craigslist.org,lewiston.craigslist.org,lynchburg.craigslist.org,lyon.craigslist.org,madison.craigslist.org,manchester.craigslist.co.uk,saginaw.craigslist.org,mcallen.craigslist.org,macon.craigslist.org,madrid.craigslist.es,mendocino.craigslist.org,meadville.craigslist.org,meridian.craigslist.org,melbourne.craigslist.com.au,memphis.craigslist.org,merced.craigslist.org,mexicocity.craigslist.com.mx,mansfield.craigslist.org,medford.craigslist.org,managua.craigslist.org,montgomery.craigslist.org,ksu.craigslist.org,mohave.craigslist.org,fortlauderdale.craigslist.org,miami.craigslist.org,westpalmbeach.craigslist.org,milwaukee.craigslist.org,minneapolis.craigslist.org,muskegon.craigslist.org,mankato.craigslist.org,spacecoast.craigslist.org,quadcities.craigslist.org,moseslake.craigslist.org,monroemi.craigslist.org,malaysia.craigslist.org,marshall.craigslist.org,maine.craigslist.org,manila.craigslist.com.ph,monroe.craigslist.org,montana.craigslist.org,mobile.craigslist.org,modesto.craigslist.org,montreal.craigslist.ca,moscow.craigslist.org,montpellier.craigslist.org,marseilles.craigslist.org,masoncity.craigslist.org,shoals.craigslist.org,missoula.craigslist.org,monterey.craigslist.org,mattoon.craigslist.org,monterrey.craigslist.com.mx,munich.craigslist.de,mumbai.craigslist.co.in,muncie.craigslist.org,montevideo.craigslist.org,skagit.craigslist.org,milan.craigslist.it,myrtlebeach.craigslist.org,mazatlan.craigslist.com.mx,naples.craigslist.it,kenya.craigslist.org,newbrunswick.craigslist.ca,cotedazur.craigslist.org,newcastle.craigslist.co.uk,nwct.craigslist.org,nd.craigslist.org,norfolk.craigslist.org,newfoundland.craigslist.ca,nagoya.craigslist.jp,nh.craigslist.org,newjersey.craigslist.org,nanjing.craigslist.com.cn,newlondon.craigslist.org,nmi.craigslist.org,nanaimo.craigslist.ca,northmiss.craigslist.org,neworleans.craigslist.org,nottingham.craigslist.co.uk,tuscarawas.craigslist.org,niagara.craigslist.ca,nashville.craigslist.org,loire.craigslist.org,ntl.craigslist.com.au,nuremberg.craigslist.de,nwga.craigslist.org,norwich.craigslist.co.uk,northernwi.craigslist.org,nwks.craigslist.org,newyork.craigslist.org,onslow.craigslist.org,oaxaca.craigslist.com.mx,outerbanks.craigslist.org,ocala.craigslist.org,nacogdoches.craigslist.org,odessa.craigslist.org,ogden.craigslist.org,athensohio.craigslist.org,okinawa.craigslist.jp,oklahomacity.craigslist.org,kokomo.craigslist.org,winchester.craigslist.org,olympic.craigslist.org,omaha.craigslist.org,oneonta.craigslist.org,goldcoast.craigslist.com.au,orangecounty.craigslist.org,orlando.craigslist.org,osaka.craigslist.jp,oslo.craigslist.org,stillwater.craigslist.org,ottawa.craigslist.ca,ottumwa.craigslist.org,oxford.craigslist.co.uk,owensboro.craigslist.org,owensound.craigslist.ca,ventura.craigslist.org,pakistan.craigslist.org,ramallah.craigslist.org,panama.craigslist.org,paris.craigslist.org,peace.craigslist.ca,puebla.craigslist.com.mx,portland.craigslist.org,perugia.craigslist.it,pei.craigslist.ca,beijing.craigslist.com.cn,perth.craigslist.com.au,panamacity.craigslist.org,portoalegre.craigslist.org,philadelphia.craigslist.org,porthuron.craigslist.org,phoenix.craigslist.org,peoria.craigslist.org,pittsburgh.craigslist.org,parkersburg.craigslist.org,plattsburgh.craigslist.org,pullman.craigslist.org,pune.craigslist.co.in,pensacola.craigslist.org,poconos.craigslist.org,prescott.craigslist.org,prague.craigslist.cz,puertorico.craigslist.org,puertorico.craigslist.org,providence.craigslist.org,treasure.craigslist.org,palmsprings.craigslist.org,pennstate.craigslist.org,potsdam.craigslist.org,porto.craigslist.pt,pueblo.craigslist.org,pv.craigslist.com.mx,provo.craigslist.org,quebec.craigslist.ca,quincy.craigslist.org,quito.craigslist.org,racine.craigslist.org,raleigh.craigslist.org,rapidcity.craigslist.org,roseburg.craigslist.org,rockies.craigslist.org,rochester.craigslist.org,redding.craigslist.org,reading.craigslist.org,recife.craigslist.org,reddeer.craigslist.ca,regina.craigslist.ca,rockford.craigslist.org,richmond.craigslist.org,richmondin.craigslist.org,rio.craigslist.org,reykjavik.craigslist.org,rmn.craigslist.org,reno.craigslist.org,rennes.craigslist.org,roanoke.craigslist.org,rome.craigslist.it,rouen.craigslist.org,roswell.craigslist.org,sacramento.craigslist.org,santafe.craigslist.org,elsalvador.craigslist.org,sanantonio.craigslist.org,savannah.craigslist.org,santabarbara.craigslist.org,sheboygan.craigslist.org,southbend.craigslist.org,santiago.craigslist.org,sendai.craigslist.jp,sd.craigslist.org,sandiego.craigslist.org,santodomingo.craigslist.org,seattle.craigslist.org,seks.craigslist.org,seoul.craigslist.co.kr,sevilla.craigslist.es,sfbay.craigslist.org,springfield.craigslist.org,saguenay.craigslist.ca,shanghai.craigslist.com.cn,sherbrooke.craigslist.ca,harrisonburg.craigslist.org,shenyang.craigslist.com.cn,sheffield.craigslist.co.uk,shreveport.craigslist.org,sicily.craigslist.it,sanangelo.craigslist.org,skeena.craigslist.ca,saskatoon.craigslist.ca,sandusky.craigslist.org,saltlakecity.craigslist.org,salem.craigslist.org,slo.craigslist.org,southcoast.craigslist.org,smd.craigslist.org,semo.craigslist.org,santamaria.craigslist.org,singapore.craigslist.com.sg,southjersey.craigslist.org,salina.craigslist.org,bulgaria.craigslist.org,soo.craigslist.ca,hampshire.craigslist.co.uk,showlow.craigslist.org,springfieldil.craigslist.org,spokane.craigslist.org,saopaulo.craigslist.org,sapporo.craigslist.jp,sardinia.craigslist.it,sarnia.craigslist.ca,sarasota.craigslist.org,salvador.craigslist.org,siskiyou.craigslist.org,susanville.craigslist.org,stcloud.craigslist.org,stgeorge.craigslist.org,stockholm.craigslist.se,stjoseph.craigslist.org,stockton.craigslist.org,stlouis.craigslist.org,stpetersburg.craigslist.org,stuttgart.craigslist.de,sudbury.craigslist.ca,sunshine.craigslist.ca,siouxcity.craigslist.org,surat.craigslist.co.in,swks.craigslist.org,swmi.craigslist.org,swv.craigslist.org,strasbourg.craigslist.org,sydney.craigslist.com.au,syracuse.craigslist.org,shenzhen.craigslist.com.cn,tallahassee.craigslist.org,statesboro.craigslist.org,thunderbay.craigslist.ca,canarias.craigslist.es,terrehaute.craigslist.org,thumb.craigslist.org,tijuana.craigslist.com.mx,toulouse.craigslist.org,telaviv.craigslist.org,territories.craigslist.ca,tokyo.craigslist.jp,toledo.craigslist.org,toronto.craigslist.ca,tampa.craigslist.org,topeka.craigslist.org,tehran.craigslist.org,tricities.craigslist.org,torino.craigslist.it,troisrivieres.craigslist.ca,tuscaloosa.craigslist.org,sanmarcos.craigslist.org,twintiers.craigslist.org,tulsa.craigslist.org,tunis.craigslist.org,tucson.craigslist.org,taipei.craigslist.com.tw,twinfalls.craigslist.org,texarkana.craigslist.org,texoma.craigslist.org,dubai.craigslist.org,hattiesburg.craigslist.org,staugustine.craigslist.org,utica.craigslist.org,charlottesville.craigslist.org,valencia.craigslist.es,vancouver.craigslist.ca,swva.craigslist.org,venice.craigslist.it,veracruz.craigslist.com.mx,victoria.craigslist.ca,vienna.craigslist.at,visalia.craigslist.org,valdosta.craigslist.org,blacksburg.craigslist.org,virgin.craigslist.org,virgin.craigslist.org,vietnam.craigslist.org,victoriatx.craigslist.org,wausau.craigslist.org,warsaw.craigslist.pl,waco.craigslist.org,washingtondc.craigslist.org,wenatchee.craigslist.org,whitehorse.craigslist.ca,whistler.craigslist.ca,wheeling.craigslist.org,wichita.craigslist.org,winnipeg.craigslist.ca,westky.craigslist.org,wellington.craigslist.org,waterloo.craigslist.org,westernmass.craigslist.org,westmd.craigslist.org,wilmington.craigslist.org,naga.craigslist.com.ph,wollongong.craigslist.com.au,worcester.craigslist.org,williamsport.craigslist.org,winstonsalem.craigslist.org,windsor.craigslist.ca,wichitafalls.craigslist.org,watertown.craigslist.org,bigbend.craigslist.org,wuhan.craigslist.com.cn,wv.craigslist.org,morgantown.craigslist.org,wyoming.craigslist.org,xian.craigslist.com.cn,yakima.craigslist.org",yubasutter.craigslist.org,cornwall.craigslist.c",yellowknife.craigslist.ca,youngstown.craigslist.org,peterborough.craigslist.ca,okaloosa.craigslist.org,york.craigslist.org,yucatan.craigslist.com.mx,yuma.craigslist.org,up.craigslist.org,princegeorge.craigslist.ca,zagreb.craigslist.org,zamboanga.craigslist.com.ph,zurich.craigslist.ch,zanesville.craigslist.org';
		$areas = '172,471,364,50,682,637,318,512,575,576,68,644,539,258,69,59,533,167,269,450,445,82,559,51,460,355,243,171,144,14,372,256,15,276,63,34,83,612,606,406,483,108,296,626,669,588,295,248,127,72,657,535,666,663,431,156,115,396,342,217,344,229,446,233,66,84,412,393,52,4,319,264,664,398,93,522,529,266,389,117,109,514,528,628,494,661,199,153,114,40,574,570,658,101,162,251,312,409,239,580,451,345,705,621,489,178,604,536,548,340,639,313,41,187,182,505,11,484,701,190,452,301,128,220,35,511,601,465,27,77,434,473,513,349,592,410,42,670,107,321,210,222,495,136,299,179,608,265,350,439,681,343,326,602,653,517,116,238,21,131,362,496,86,13,22,467,569,600,255,193,498,367,521,647,491,98,74,594,303,418,399,547,242,713,75,78,424,674,652,453,132,400,678,335,650,322,275,328,523,497,308,94,227,444,457,677,435,273,662,633,685,468,244,464,152,477,125,568,259,141,43,542,679,358,287,693,518,503,226,293,61,404,531,667,686,432,320,73,373,470,525,219,430,482,230,241,327,129,538,253,660,585,245,245,146,417,140,619,490,519,145,642,391,174,500,353,504,462,87,630,659,189,213,709,28,23,595,506,166,231,44,442,249,643,168,183,339,534,549,605,455,45,104,250,148,201,610,550,134,80,425,700,157,423,185,676,161,553,426,558,561,583,214,380,493,330,675,618,381,385,202,184,474,324,696,30,577,261,360,376,212,615,422,7,267,668,638,334,24,123,520,133,283,448,413,159,540,100,118,284,611,437,282,279,234,58,695,578,271,363,698,476,544,26,347,654,366,150,165,71,260,263,257,110,454,706,641,65,46,285,91,436,216,586,207,428,565,20,20,20,47,19,554,421,331,307,655,563,297,665,169,90,629,192,200,96,49,137,524,149,692,560,656,102,699,408,142,85,361,543,461,111,254,509,151,582,379,306,163,354,196,48,305,501,198,170,599,281,309,382,375,31,492,703,386,32,415,591,614,636,402,631,688,3,634,510,336,333,645,268,351,438,429,54,672,711,466,55,684,590,103,39,120,105,433,76,691,211,673,487,208,294,551,298,81,620,508,9,530,304,154,67,562,515,17,555,18,224,33,441,338,368,317,203,356,419,138,180,180,38,332,209,277,683,541,315,407,292,175,697,545,552,36,680,459,288,126,188,278,516,475,478,223,60,671,139,579,316,92,526,289,121,527,420,12,218,587,53,205,62,571,228,158,596,195,8,617,2,689,119,395,1,221,480,135,390,447,598,401,206,311,646,623,176,573,56,232,191,378,556,566,710,89,286,690,584,485,403,651,225,95,113,502,532,486,237,392,708,707,369,352,106,694,97,29,143,416,384,622,341,613,687,572,632,414,64,130,499,186,635,387,537,348,627,181,411,160,488,88,204,25,37,280,589,323,397,479,371,449,704,70,581,57,155,469,359,649,215,374,557,247,290,394,16,712,310,507,177,122,346,427,291,616,616,314,564,458,147,270,10,325,625,472,443,99,79,377,302,567,173,329,274,609,593,240,463,272,235,365,337,648,597,194,440,197,603,246,"456,481",624,252,388,640,357,405,370,262,383,546,607,112,702';
		
		$domains_arr = explode ( ',', $domains );
		$areas_arr = explode ( ',', $areas );
		
		$key = array_search ( $domain, $domains_arr );
		
		return ($areas_arr [$key]);
	}
}