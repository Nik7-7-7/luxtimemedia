	<?php
	
	// Main Class
	require_once 'core.php';
	class WpAutomaticYoutube extends wp_automatic {
		
		/*
		 * ---* youtube get links ---
		 */
		function youtube_fetch_links($keyword, $camp) {
			echo "<br>so I should now get some links from youtube for keyword :" . $keyword;
			
			// check if there is an api key added
			$wp_automatic_yt_tocken = wp_automatic_trim( wp_automatic_single_item ( 'wp_automatic_yt_tocken', '' ) );
			
			if (wp_automatic_trim( $wp_automatic_yt_tocken ) == '') {
				echo '<br>Youtube API key is required, please visit settings page and add it';
				return false;
			}
			
			// ini options
			$camp_opt = unserialize ( $camp->camp_options );
			if (stristr ( $camp->camp_general, 'a:' ))
				$camp->camp_general = base64_encode ( $camp->camp_general );
			$camp_general = unserialize ( base64_decode ( $camp->camp_general ) );
			$camp_general = array_map ( 'wp_automatic_stripslashes', $camp_general );
			
			$sortby = $camp->camp_youtube_order;
			$camp_youtube_category = $camp->camp_youtube_cat;
			
			// get start-index for this keyword
			$query = "select keyword_start ,keyword_id from {$this->wp_prefix}automatic_keywords where keyword_name='$keyword' and keyword_camp={$camp->camp_id}";
			$rows = $this->db->get_results ( $query );
			$row = $rows [0];
			$kid = $row->keyword_id;
			$start = $row->keyword_start;
			if ($start == 0)
				$start = 1;
			
			if ($start == - 1) {
				echo '<- exhausted keyword';
				
				// check if it is reactivated or still deactivated
				if ($this->is_deactivated ( $camp->camp_id, $keyword )) {
					$start = 1;
				} else {
					// still deactivated
					return false;
				}
			}
			
			// limit check
			$this->is_allowed_to_call ();
			
			echo ' index:' . $start;
			
			// update start index to start+50
			if (! in_array ( 'OPT_YT_CACHE', $camp_opt )) {
				echo '<br>Caching is not enabled setting youtube page to query to 1';
				$nextstart = 1;
			} else {
				$nextstart = $start + 50;
			}
			
			$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $nextstart where keyword_id=$kid ";
			$this->db->query ( $query );
			
			// get items
			$orderby = $camp->camp_youtube_order;
			$cat = $camp->camp_youtube_cat;
			
			// base url
			$search_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&key=" . wp_automatic_trim( $wp_automatic_yt_tocken ) . "&maxResults=50";
			
			$naked_search_url = $search_url; // minimal version of the search query
			                                 
			// keyword add
			if (wp_automatic_trim( $keyword ) != '*') {
				$search_url = $search_url . '&q=' . urlencode ( wp_automatic_trim( $keyword ) );
			}
			
			if (in_array ( 'OPT_YT_DATE', $camp_opt )) {
				
				//if OPT_YT_DATE_T is set then generate a date based on the current date minus the minutes otherwise, use the date set 
				
				if(in_array ( 'OPT_YT_DATE_T', $camp_opt )){
					
					$minutes = $camp_general ['cg_yt_dte_minutes'];
					$minutes = intval($minutes);
					
					 
					$now = time();
					$now = $now - ($minutes * 60);
					
					//format the date to  RFC 3339 format
					$formatedDate = date('Y-m-d\TH:i:s\Z',$now);

					$beforeDate = $date = $formatedDate;

					//report date
					echo '<br>Using date: '.$date;

				}else{
					$date = $camp_general ['cg_yt_dte_year'] . "-" . $camp_general ['cg_yt_dte_month'] . "-" . $camp_general ['cg_yt_dte_day'];
					$beforeDate =  $date . 'T00:00:00Z';
				}
				
				
				
				$search_url .= "&publishedAfter=" . $beforeDate;
			}
			
			// published before
			if (in_array ( 'OPT_YT_BEFORE', $camp_opt )) {
				if (stristr ( $camp_general ['cg_yt_before'], '-' )) {
					$search_url .= "&publishedBefore=" . wp_automatic_trim( $camp_general ['cg_yt_before'] ) . 'T00:00:00Z';
				}
			}
			
			// OPT_YT_LIMIT_EMBED
			if (in_array ( 'OPT_YT_LIMIT_EMBED', $camp_opt )) {
				$search_url .= "&videoEmbeddable=true";
			}
			
			// license
			$cg_yt_license = $camp_general ['cg_yt_license'];
			if (wp_automatic_trim( $cg_yt_license ) != '' && $cg_yt_license != 'any') {
				$search_url .= "&videoLicense=" . $cg_yt_license;
			}
			
			// cg_yt_type
			$cg_yt_type = $camp_general ['cg_yt_type'];
			if (wp_automatic_trim( $cg_yt_type ) != '' && $cg_yt_type != 'any') {
				$search_url .= "&videoType=" . $cg_yt_type;
			}
			
			// videoDuration
			$cg_yt_duration = $camp_general ['cg_yt_duration'];
			if (wp_automatic_trim( $cg_yt_duration ) != '' && $cg_yt_duration != 'any') {
				$search_url .= "&videoDuration=" . $cg_yt_duration;
			}
			
			// videoDefinition
			$cg_yt_definition = $camp_general ['cg_yt_definition'];
			if (wp_automatic_trim( $cg_yt_definition ) != '' && $cg_yt_definition != 'any') {
				$search_url .= "&videoDefinition=" . $cg_yt_definition;
			}
			
			// safeSearch
			$cg_yt_safe = isset($camp_general ['cg_yt_safe']) ? $camp_general ['cg_yt_safe'] : '';
			if (wp_automatic_trim( $cg_yt_safe ) != '' && $cg_yt_safe != 'moderate') {
				$search_url .= "&safeSearch=" . $cg_yt_safe;
			}
			
			// order
			$camp_youtube_order = $camp->camp_youtube_order;
			if (wp_automatic_trim( $camp_youtube_order ) == 'published')
				$camp_youtube_order = 'date';
			
			if ($camp_youtube_order != 'relevance')
				$search_url .= "&order=" . $camp_youtube_order;
			
			// videoCategoryId
			$videoCategoryId = $camp->camp_youtube_cat;
			if (wp_automatic_trim( $videoCategoryId ) != 'All' && is_numeric ( $videoCategoryId )) {
				$search_url .= "&videoCategoryId=" . $videoCategoryId;
			}
			
			// regionCode
			if (in_array ( 'OPT_YT_LIMIT_CTRY', $camp_opt ) && wp_automatic_trim( $camp_general ['cg_yt_ctr'] ) != '') {
				$search_url .= "&regionCode=" . wp_automatic_trim( $camp_general ['cg_yt_ctr'] );
			}
			
			// relevanceLanguage
			if (in_array ( 'OPT_YT_LIMIT_LANG', $camp_opt ) && wp_automatic_trim( $camp_general ['cg_yt_lang'] ) != '') {
				$search_url .= "&relevanceLanguage=" . wp_automatic_trim( $camp_general ['cg_yt_lang'] );
			}
			
			if (in_array ( 'OPT_YT_USER', $camp_opt )) {
				echo '<br>Fetching vids for specific User/Channel ' . $camp->camp_yt_user;
				
				$camp_yt_user = wp_automatic_trim( $camp->camp_yt_user );
				
				//https://www.youtube.com/channel/UCRrW0ddrbFnJCbyZqHHv4KQ
				if(  stristr( $camp_yt_user , 'https' ) && stristr($camp_yt_user, '/channel') ){
					$camp_yt_user = wp_automatic_trim(wp_automatic_str_replace('https://www.youtube.com/channel/', '', $camp_yt_user));
					$camp_yt_user = wp_automatic_str_replace('/' , '' , $camp_yt_user ) ;
				}
				
				//https://www.youtube.com/c/HolyCulture
				if(  stristr( $camp_yt_user , 'https' )  ){
					
					$camp_yt_user = $this->get_channel_id_foruser($camp_yt_user , $wp_automatic_yt_tocken);
				}
				 
				 
			 
				// playlistify it to decrease used quote as normal search quote is 100 but playlist is 2 or
				if (  ! in_array ( 'OPT_YT_LIVE_ONLY', $camp_opt ) && ! in_array ( 'OPT_YT_LIVE_SKIP', $camp_opt ) && ! in_array ( 'OPT_YT_PLAYLIST', $camp_opt ) && ($search_url == $naked_search_url || $search_url == $naked_search_url . '&order=date')) {
					// lets playlistify
					
					echo '<br>Playlistifying....';
					
					$channel_playlist_key_name = 'wp_automatic_playlist_id_' . md5 ( wp_automatic_trim( $camp_yt_user ) );
					$channel_playlist_id = get_post_meta ( $camp->camp_id, $channel_playlist_key_name, true );
					
					// grab playlist ID for firt time
					if (wp_automatic_trim( $channel_playlist_id ) == '') {
						
						// get the ID from YT for first time
						$playlist_api_url = "https://www.googleapis.com/youtube/v3/channels?part=snippet%2CcontentDetails%2Cstatistics&id=" . $camp_yt_user . "&key=" . $wp_automatic_yt_tocken;
						
						echo '<br>Getting Playlist ID of this channel...';
						
						// curl get
						curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
						curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $playlist_api_url ) );
						$exec = curl_exec ( $this->ch );
						
						if (stristr ( $exec, '{' )) {
							$json_reply = json_decode ( $exec );
							
							if (isset ( $json_reply->items ) && isset ( $json_reply->items [0] )) {
								$channel_playlist_id = $json_reply->items [0]->contentDetails->relatedPlaylists->uploads;
								echo '<- found:' . $channel_playlist_id;
								
								update_post_meta ( $camp->camp_id, $channel_playlist_key_name, $channel_playlist_id );
							}
						}
					} // first playlist
					
					if (wp_automatic_trim( $channel_playlist_id ) != '') {
						// nice we have the uploads playlist lets playlistify now
						$camp_opt [] = 'OPT_YT_PLAYLIST';
						$camp_general ['cg_yt_playlist'] = $channel_playlist_id;
					}
				}
				
				 
					
				$search_url .= "&channelId=" . ($camp_yt_user);
				 
			} elseif (in_array ( 'YT_ID', $camp_opt )) {
				
				// post by ID
				$search_url = 'https://www.googleapis.com/youtube/v3/videos?key=' . $wp_automatic_yt_tocken . '&part=snippet&id=' . urlencode ( wp_automatic_trim( $keyword ) );
			} else {
				// no user just search
			}

			// check if playlist
			if (in_array ( 'OPT_YT_PLAYLIST', $camp_opt )) {
				echo '<br>Specific Playlist:' . $camp_general ['cg_yt_playlist'];
				
				$part = "snippet";
				
				if (in_array ( 'OPT_YT_DATE', $camp_opt )) {
					$part = "snippet,contentDetails";
				}

				//playlist id 
				$playlistID = wp_automatic_trim( $camp_general ['cg_yt_playlist'] );

				//if playlistID contains a playlist url, convert it to a playlist id
				if( stristr($playlistID , 'https://www.youtube.com/playlist?list=') ){
					$playlistID = wp_automatic_str_replace('https://www.youtube.com/playlist?list=' , '' , $playlistID);

					//remove any other params
					$playlistID = explode('&' , $playlistID);
					$playlistID = $playlistID[0];

					echo '<br>Converted playlist url to id:' . $playlistID;


				}
				
				$search_url = "https://www.googleapis.com/youtube/v3/playlistItems?part={$part}&playlistId=" . $playlistID . "&key=" . wp_automatic_trim( $wp_automatic_yt_tocken ) . "&maxResults=50";
			}

			//Trending youtube videos OPT_YT_TRENDING  chart=mostPopular&regionCode=US
			if (in_array ( 'OPT_YT_TRENDING', $camp_opt )) {

				//get region code cg_yt_trending_region
				$regionCode = wp_automatic_trim( $camp_general ['cg_yt_trending_region'] );

				if(wp_automatic_trim( $regionCode ) == ''){
					$regionCode = 'US';
				}

				echo '<br>Trending videos for region: ' . $regionCode;


				$search_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&chart=mostPopular&regionCode={$regionCode}&key=" . wp_automatic_trim( $wp_automatic_yt_tocken ) . "&maxResults=50";
				
				//videoCategoryId
				$videoCategoryId = $camp->camp_youtube_cat;

				if (wp_automatic_trim( $videoCategoryId ) != 'All' && is_numeric ( $videoCategoryId )) {
					$search_url .= "&videoCategoryId=" . $videoCategoryId;
				}
			
			}
			
			// check nextpagetoken 
			$tokenName = 'wp_automatic_yt_nt_' . md5 ( $keyword );
			$nextPageToken = get_post_meta ( $camp->camp_id, 'wp_automatic_yt_nt_' . md5 ( $keyword ), true );
			
			if (in_array ( 'OPT_YT_CACHE', $camp_opt )) {
				
				if (wp_automatic_trim( $nextPageToken ) != '') {
					echo '<br>nextPageToken:' . $nextPageToken;
					$search_url .= '&pageToken=' . $nextPageToken;
				} else {
					echo '<br>No page token let it the first page';
				}
			}
			
			//live only OPT_YT_LIVE_ONLY
			if (in_array ( 'OPT_YT_LIVE_ONLY', $camp_opt )) {
				$search_url .= '&eventType=live';
			}
			
			//CC videoCaption only
			if (in_array ( 'OPT_YT_CC', $camp_opt )) {
				$search_url .= '&videoCaption=closedCaption';
			}
			
			echo '<br>Search URL:' . $search_url;
			
			// process request
			curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
			curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $search_url ) );
			$exec = curl_exec ( $this->ch );
			
			$x = curl_error ( $this->ch );
			
			// verify reply
			if (! stristr ( $exec, '"kind"' )) {
			
				
				//remove broken token
				if( stristr($exec, 'location": "pageToken') ){
					echo '<br>Token seems to be defected, removing it....';
					delete_post_meta ( $camp->camp_id, $tokenName );
				}

				//if { "error exists, decode json and display error in red 
				if(stristr($exec, '"error"')){
					$json_error = json_decode($exec);
					echo '<br><span style="color:red">Youtube Error: '.$json_error->error->message.'</span>';
				}else{
					echo '<br>Not valid reply from Youtube:' . $exec . $x;
				}

				
				
				return false;
			}
			
			$json_exec = json_decode ( $exec );
			
	
			// check nextpage token
			if (isset ( $json_exec->nextPageToken ) && wp_automatic_trim( $json_exec->nextPageToken ) != '') {
				$newnextPageToken = $json_exec->nextPageToken;
				echo '<br>New page token:' . $newnextPageToken;
				update_post_meta ( $camp->camp_id, 'wp_automatic_yt_nt_' . md5 ( $keyword ), $newnextPageToken );
			} else {
				// delete the token
				echo '<br>No next page token';
				delete_post_meta ( $camp->camp_id, 'wp_automatic_yt_nt_' . md5 ( $keyword ) );
				
				// set start to -1 exhausted
				$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid";
				$this->db->query ( $query );
				
				// deactivate for 60 minutes
				if (! in_array ( 'OPT_NO_DEACTIVATE', $camp_opt )) {
					
					if (! in_array ( 'YT_ID', $camp_opt )) {
						$this->deactivate_key ( $camp->camp_id, $keyword );
					} else {
						$this->deactivate_key ( $camp->camp_id, $keyword, 0 );
					}
				}
			}
			
			// get items
			$search = array ();
			$search = $json_exec->items;
			
			// disable keyword if no new items
			if (count ( $search ) == 0) {
				echo '<br>No more vids for this keyword deactivating it ..';
				$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid";
				$this->db->query ( $query );
				
				// deleting nextpage token
				delete_post_meta ( $camp->camp_id, 'wp_automatic_yt_nt_' . md5 ( $keyword ) );
				
				// deactivate for 60 minutes
				if (! in_array ( 'OPT_NO_DEACTIVATE', $camp_opt ))
					$this->deactivate_key ( $camp->camp_id, $keyword );
				
				return;
			}
			
			echo '<ol>';
			
			// reversing?
			if (in_array ( 'OPT_YT_REVERSE', $camp_opt )) {
				echo '<br>Reversing vids list order.';
				$search = array_reverse ( $search );
			}
			
			foreach ( $search as $itm ) {
				
				// general added details
				$general = array ();
 
				// get vid id from response
				if (stristr ( $search_url, 'playlistItems' )) {
					$vid_id = $itm->snippet->resourceId->videoId;
				
				//if OPT_YT_TRENDING
				}elseif (in_array ( 'OPT_YT_TRENDING', $camp_opt )) {
					$vid_id = $itm->id;	
				
				} elseif (in_array ( 'YT_ID', $camp_opt )) {
					$vid_id = $itm->id;
				} else {
					$vid_id = $itm->id->videoId;
				}
				
				// vid url
				$link_url = 'https://www.youtube.com/watch?v=' . $vid_id;
				$httplink_url = 'http://www.youtube.com/watch?v=' . $vid_id;
				
				// vid thumbnail
				if(isset($itm->snippet ) && isset($itm->snippet->thumbnails ) && isset($itm->snippet->thumbnails->high ) && isset($itm->snippet->thumbnails->high->url ) ){
					$link_img = $itm->snippet->thumbnails->high->url;
				}else{
					$link_img = '';
				}

				// get largest size
				// $link_img = wp_automatic_str_replace('hqdefault', 'hqdefault', $link_img);
				
				// get item title
				$link_title = addslashes ( wp_automatic_htmlspecialchars_decode($itm->snippet->title , ENT_QUOTES) );
 
				
				// Skip private videos
				if ($link_title == 'Private video') {
					continue;
				}
				
				// Skip premiere
				if (isset ( $itm->snippet->liveBroadcastContent ) && $itm->snippet->liveBroadcastContent == 'upcoming') {
					continue;
				}
				
				// Skip premiere
				if ( in_array( 'OPT_YT_LIVE_SKIP' , $camp_opt ) && isset ( $itm->snippet->liveBroadcastContent ) && $itm->snippet->liveBroadcastContent == 'live') {
					echo '<li>' . $link_title . '<-- Live video, skipping...' . '</li>';
					continue;
				}

				
				
				// get item description
				$link_desc = addslashes ( $itm->snippet->description );
				
				// report link
				echo '<li><a href="'. $link_url .'">' . $link_title . '</a></li>';
				
				// validate exact and banned
				if (! $this->validate_exacts ( $link_desc, $link_title, $camp_opt, $camp )) {
					continue;
				}
				
				// channel title
				$general ['vid_author_title'] = $itm->snippet->channelTitle;
				
				// channel id
				$author = addslashes ( $itm->snippet->channelId );
				
				// link time
				if (isset ( $itm->contentDetails ) && in_array ( 'OPT_YT_PLAYLIST', $camp_opt )) {
					$link_time = strtotime ( $itm->contentDetails->videoPublishedAt );
					
					echo ' Published:' . $itm->contentDetails->videoPublishedAt;
				} else {
					$link_time = strtotime ( $itm->snippet->publishedAt );
				}
				
				// Clear these values and generate at runtime to save costs of api requests
				$link_player = '';
				
				// needs a separate request with v3 api
				$link_views = '';
				$link_rating = '';
				$link_duration = '';
				
				// echo 'Published:'. date('Y-m-d',$itm['time']).' ';
				if ($this->is_execluded ( $camp->camp_id, $link_url )) {
					echo '<-- Execluded';
					continue;
				}

				//validate shorts 
				if( in_array( 'OPT_YT_SHORT_SKIP' , $camp_opt )  ){
					
					echo '<br>Checking if short video: ' . $vid_id;

					$is_short = $this->is_short($vid_id); 					 

					if($is_short){
						echo '<-- Short video, skipping...';
						
						//exclude
						$this->link_execlude($camp->camp_id, $link_url);
						
						continue;
					}

 					 
				}

				//only import shorts OPT_YT_SHORT_ONLY
				if( in_array( 'OPT_YT_SHORT_ONLY' , $camp_opt )  ){
					
					echo '<br>Checking if short video: ' . $vid_id;

					$is_short = $this->is_short($vid_id); 					 

					if(!$is_short){
						echo '<-- Not a short video, skipping...';
						
						//exclude
						$this->link_execlude($camp->camp_id, $link_url);
						
						continue;
					}

 					 
				}
				
				// check if older than minimum date
				if ((in_array ( 'OPT_YT_DATE', $camp_opt ) && in_array ( 'OPT_YT_PLAYLIST', $camp_opt )) || in_array ( 'OPT_YT_DATE_T', $camp_opt )) {
					
					if ($this->is_link_old ( $camp->camp_id, $link_time )) {
						echo '<--old post execluding...';
						continue;
					}
				}
				
				// serializing general
				$general = base64_encode ( serialize ( $general ) );
				
				// $link_title =addslashes($link_title);
				
		 
				 
				if (! $this->is_duplicate ( $link_url )) {
					$query = "INSERT INTO {$this->wp_prefix}automatic_youtube_links ( link_url , link_title , link_keyword  , link_status , link_desc ,link_time,link_rating ,link_views,link_player,link_img,link_author,link_duration, link_general ) VALUES ( '$link_url', '$link_title', '{$camp->camp_id}_$keyword', '0' ,'$link_desc','$link_time','$link_rating','$link_views','$link_player','$link_img','$author','$link_duration','$general')";
					$ins = $this->db->query ( $query );
				 
			 
					
					if($ins){
						echo '<-- inserted';
					}else{
						echo '<-- not inserted ' . $this->db->last_error;
					}
					
				} else {
					echo ' <- duplicated <a href="' . get_edit_post_link ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
				}
			}
			echo '</ol>';
		}
		
		/*
		 * ---* youtube post ---
		 */
		function youtube_get_post($camp) {
			$camp_opt = unserialize ( $camp->camp_options );
			$keywords = explode ( ',', $camp->camp_keywords );
			if (stristr ( $camp->camp_general, 'a:' ))
				$camp->camp_general = base64_encode ( $camp->camp_general );
			$camp_general = unserialize ( base64_decode ( $camp->camp_general ) );
			$camp_general = array_map ( 'wp_automatic_stripslashes', $camp_general );
			$camp_post_content = $camp->camp_post_content;
			$camp_post_custom_v = implode ( ',', unserialize ( $camp->camp_post_custom_v ) );
			$camp_post_title = $camp->camp_post_title;
			
			foreach ( $keywords as $keyword ) {
				
				$keyword = wp_automatic_trim( $keyword );
				
				if (wp_automatic_trim( $keyword ) != '') {
					
					echo '<br>Keyword:' . $keyword;
					
					// update last keyword
					update_post_meta ( $camp->camp_id, 'last_keyword', wp_automatic_trim( $keyword ) );
					
					// getting links from the db for that keyword
					$query = "select * from {$this->wp_prefix}automatic_youtube_links where link_keyword='{$camp->camp_id}_$keyword' ";
					$res = $this->db->get_results ( $query );
					
				 
					// when no links lets get new links
					if (count ( $res ) == 0) {
						
						// clean any old cache for this keyword
						$query_delete = "delete from {$this->wp_prefix}automatic_youtube_links where link_keyword='{$camp->camp_id}_$keyword' ";
						$this->db->query ( $query_delete );
						
						$this->youtube_fetch_links ( $keyword, $camp );
						// getting links from the db for that keyword
						$res = $this->db->get_results ( $query );
					}
					
					// deleting duplicated items
					$res_count = count ( $res );
					for($i = 0; $i < $res_count; $i ++) {
						
						$t_row = $res [$i];
						$t_link_url = $t_row->link_url;
						$t_link_url_http =wp_automatic_str_replace( 'https', 'http', $t_link_url );
						
						if ($this->is_duplicate ( $t_link_url ) || $this->is_duplicate ( $t_link_url_http )) {
							
							// duplicated item let's delete
							unset ( $res [$i] );
							
							echo '<br>Vid (' . $t_row->link_title . ') found cached but duplicated <a href="' . get_permalink ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
							
							// delete the item
							$query = "delete from {$this->wp_prefix}automatic_youtube_links where link_id='{$t_row->link_id}'";
							$this->db->query ( $query );
						} else {
							break;
						}
					}
					
					// check again if valid links found for that keyword otherwise skip it
					if (count ( $res ) > 0) {
						
						// lets process that link
						$ret = $res [$i];
						
						echo '<br>Link:' . $ret->link_url . '('.$ret->link_title.')';
						
						// extract video id
						$temp_ex = explode ( 'v=', $ret->link_url );
						$vid_id = $temp_ex [1];
						
						// set used url
						$this->used_link = wp_automatic_trim( $ret->link_url );
						
						$temp ['vid_title'] = wp_automatic_trim( $ret->link_title );
						$temp ['vid_url'] = wp_automatic_trim( $ret->link_url );
						$temp ['source_link'] = wp_automatic_trim( $ret->link_url );
						$temp ['vid_time'] = wp_automatic_trim( $ret->link_time );
						
						$temp ['vid_author'] = wp_automatic_trim( $ret->link_author );
						
						// generate player
						$width = $camp_general ['cg_yt_width'];
						$height = $camp_general ['cg_yt_height'];
						if (wp_automatic_trim( $width ) == '')
							$width = 580;
						if (wp_automatic_trim( $height ) == '')
							$height = 385;
						
						$embedsrc = "https://www.youtube.com/embed/" . $vid_id;
						
						if (in_array ( 'OPT_YT_SUGGESTED', $camp_opt ) && in_array ( 'OPT_YT_AUTO', $camp_opt )) {
							
							$embedsrc .= '?rel=0&autoplay=1';
						} elseif (in_array ( 'OPT_YT_SUGGESTED', $camp_opt )) {
							
							$embedsrc .= '?rel=0';
						}
						
						if (in_array ( 'OPT_YT_AUTO', $camp_opt )) {
							
							if (stristr ( $embedsrc, '?' )) {
								$embedsrc .= '&autoplay=1';
							} else {
								$embedsrc .= '?autoplay=1';
							}
						}
						
						if (in_array ( 'OPT_YT_CAPTION', $camp_opt )) {
							
							if (stristr ( $embedsrc, '?' )) {
								$embedsrc .= '&cc_load_policy=1';
							} else {
								$embedsrc .= '?cc_load_policy=1';
							}
						}
						
						// lang
						if (in_array ( 'OPT_YT_PLAYER_LANG', $camp_opt )) {
							
							$plang = wp_automatic_trim( $camp_general ['cg_yt_plang'] );
							
							if (stristr ( $embedsrc, '?' )) {
								$embedsrc .= '&hl=' . $plang;
							} else {
								$embedsrc .= '?hl=' . $plang;
							}
						}
						
						// yt logo
						if (in_array ( 'OPT_YT_LOGO', $camp_opt )) {
							
							if (stristr ( $embedsrc, '?' )) {
								
								$embedsrc .= '&modestbranding=1';
							} else {
								
								$embedsrc .= '?modestbranding=1';
							}
						}
						
						// title tag
						if (in_array ( 'OPT_YT_F_TITLE', $camp_opt )) {
							
							$title_part = 'title = "' . esc_attr ( $temp ['vid_title'] ) . '"  ';
						} else {
							$title_part = '';
						}
						
						$temp ['vid_player'] = '<iframe ' . $title_part . ' width="' . $width . '" height="' . $height . '" src="' . $embedsrc . '" frameborder="0" allowfullscreen></iframe>';
						
						// ini get video details flag if true will request yt again for new data
						$get_vid_details = false;
						$get_vid_details_parts = array ();
						
						// statistics part
						$temp ['vid_views'] = wp_automatic_trim( $ret->link_views );
						$temp ['vid_rating'] = wp_automatic_trim( $ret->link_rating );
						
						// general
						$general = unserialize ( base64_decode ( $ret->link_general ) );
						$temp ['vid_author_title'] = $general ['vid_author_title'];
						
						// merging post content with custom fields values to check what tags
						$camp_post_content_original = $camp_post_content;
						$camp_post_content = $camp_post_custom_v . $camp_post_content . ' ' . $camp_post_title;
						
						if (stristr ( $camp_post_content, 'vid_views' ) || stristr ( $camp_post_content, 'vid_rating' ) || stristr ( $camp_post_content, 'vid_likes' ) || stristr ( $camp_post_content, 'vid_dislikes' )) {
							
							$get_vid_details = true;
							$get_vid_details_parts [] = 'statistics';
						} elseif (defined ( 'PARENT_THEME' )) {
							if (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube') {
								
								$get_vid_details = true;
								$get_vid_details_parts [] = 'statistics';
							}
						}
						
						// contentdetails part
						$temp ['vid_duration'] = wp_automatic_trim( $ret->link_duration );
						
						if (stristr ( $camp_post_content, 'vid_duration' ) || class_exists ( 'Cactus_video' )) {
							$get_vid_details = true;
							$get_vid_details_parts [] = 'contentDetails';
						}
						
						// snippet part full content
						$temp ['vid_desc'] = wp_automatic_trim( $ret->link_desc );
						
						// if full description from youtube or tags let's get them
						if (in_array ( 'OPT_YT_FULL_CNT', $camp_opt ) || (in_array ( 'OPT_YT_PLAYLIST', $camp_opt )) || in_array ( 'OPT_YT_TAG', $camp_opt ) ) {
							$get_vid_details = true;
							$get_vid_details_parts [] = 'snippet';
						}
						
						// restore the content
						$camp_post_content = $camp_post_content_original;
						
						// now get the video details again if active
						if ($get_vid_details) {
							
							echo '<br>Getting more details from youtube for the vid..';
							
							// token
							$wp_automatic_yt_tocken = wp_automatic_trim( wp_automatic_single_item ( 'wp_automatic_yt_tocken' ) );
							
							// curl get
							$x = 'error';
							$ccurl = 'https://www.googleapis.com/youtube/v3/videos?key=' . $wp_automatic_yt_tocken . '&part=' . implode ( ',', $get_vid_details_parts ) . '&id=' . $vid_id;
							
							echo '<br>yt link:' . $ccurl;
							
							curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
							curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $ccurl ) );
							$exec = curl_exec ( $this->ch );
							$x = curl_error ( $this->ch );
							
							if (stristr ( $exec, 'kind' )) {
								
								$json_exec = json_decode ( $exec );
								$theItem = $json_exec->items [0];
								
								// check snippet
								if (isset ( $theItem->snippet )) {
									
									// playlist get correct author and author id
									if (in_array ( 'OPT_YT_PLAYLIST', $camp_opt )) {
										
										$temp ['vid_author_title'] = $theItem->snippet->channelTitle;
										
										// channel id
										$temp ['vid_author'] = addslashes ( $theItem->snippet->channelId );
									}
									
									// setting full content
									if (in_array ( 'OPT_YT_FULL_CNT', $camp_opt )) {
										$temp ['vid_desc'] = $theItem->snippet->description;
										echo '<br>Full description set ';
									}
									
									$temp ['vid_time'] = strtotime ( $theItem->snippet->publishedAt );
								}
								
								// check contentdetails details
								if (isset ( $theItem->contentDetails )) {
									
									$youtube_time = $theItem->contentDetails->duration;
									
									$DTClass = new DateTime ( '@0' ); // Unix epoch
									$DTClass->add ( new DateInterval ( $youtube_time ) );
									$temp ['vid_duration'] = $DTClass->format ( 'H:i:s' );
								}
								
								// check statistics details
								if (isset ( $theItem->statistics )) {
									$temp ['vid_views'] = $theItem->statistics->viewCount;
									
									$likeCount = $theItem->statistics->likeCount;
								  
									$temp ['vid_rating'] = '';
									$temp ['vid_likes'] = $theItem->statistics->likeCount;
									 
								}
								
								//vid_tags
								if(isset($theItem->snippet->tags) && count($theItem->snippet->tags) > 0 ){
									echo '<br>'.count($theItem->snippet->tags) . ' tags found';
									$temp ['vid_tags'] = implode(',' , $theItem->snippet->tags);
									$this->used_tags = $temp ['vid_tags'];
									
								}
							} else {
								echo '<br>no valid reply from youtube ';
							}
						}
						
						$temp ['vid_img'] = wp_automatic_trim( $ret->link_img );
						
						$temp ['vid_id'] = wp_automatic_trim( $vid_id );
						$this->used_keyword = $ret->link_keyword;
						
						// if vid_image contains markup extract the source only
						if (stristr ( $temp ['vid_img'], '<img' )) {
							preg_match_all ( '/src\="(.*?)"/', $temp ['vid_img'], $matches );
							$temp ['vid_img'] = $matches [1] [0];
						}
						
						 
						
						// update the link status to 1
						$query = "delete from {$this->wp_prefix}automatic_youtube_links where link_id={$ret->link_id}";
						$this->db->query ( $query );
						
						// if cache not active let's delete the cached videos and reset indexes
						if (! in_array ( 'OPT_YT_CACHE', $camp_opt )) {
							echo '<br>Cache disabled claring cache ...';
							$query = "delete from {$this->wp_prefix}automatic_youtube_links where link_keyword='{$camp->camp_id}_$keyword' ";
							// $query = "update {$this->wp_prefix}automatic_youtube_links set link_status ='1' where link_keyword='{$camp->camp_id}_$keyword' and link_status ='0'";
							
							$this->db->query ( $query );
							
							// reset index
							$query = "update {$this->wp_prefix}automatic_keywords set keyword_start =1 where keyword_camp={$camp->camp_id}";
							$this->db->query ( $query );
						}
						
						// Vid_date publish date
						$temp ['vid_date'] = get_date_from_gmt ( gmdate ( 'Y-m-d H:i:s', $temp ['vid_time'] ) );
						
						// OPT_YT_HYPER
						if (in_array ( 'OPT_YT_HYPER', $camp_opt )) {
							
							$temp ['vid_desc'] = $this->hyperlink_this ( $temp ['vid_desc'] );
						}
						
						// download link
						$temp ['vid_download_url'] = 'https://www.youtubepp.com/watch?v=' . $temp ['vid_id'];
						
					 
						//convert time to seconds
						$temp['vid_duration_in_seconds'] = $this->time_to_seconds($temp['vid_duration']);
					 
						// get transcript if available exists as a tag using do_tag_exists
						if($this->do_tag_exists($camp , array('[transcript]' , '[transcript_raw]'))){
						
							//report tag exists, getting transcript
							echo '<br>Transcript tag found, getting transcript ...';

							//init transcript
							$temp['transcript']	= '';
							$temp['transcript_raw'] = '';

							//get transcript using get_video_transcript function and try catch
							try{

								$result = $this->get_video_transcript($temp['vid_id']);

								
								//report success and charlength of $temp['transcript'] 
								echo '<br>Transcript got successfully with a length of ' . strlen( $result['text'] ) . ' chars';

								//if option OPT_YT_TRUNCATE is enabled, truncate the returned transcript to length of chars from cg_yt_truncate
								if(in_array('OPT_YT_TRUNCATE' , $camp_opt)){
									echo '<br>Truncating transcript to ' . $camp_general['cg_yt_truncate'] . ' chars';
									
									//if mb_substr is available use it, otherwise use substr
									if(function_exists('mb_substr')){
										$temp['transcript'] = mb_substr($result['text'] , 0 , $camp_general['cg_yt_truncate']);
									}else{
										$temp['transcript'] = substr($result['text'] , 0 , $camp_general['cg_yt_truncate']);
									}
									
									
								
								
								}else{
									$temp['transcript'] = $result['text'];
								}
 

								//$result['sentences'] contains an array of sentences and every sentence has time and text, lets iterate the sentences, concatinate time to the text and add a new line
								foreach($result['sentences'] as $sentence){
									$temp['transcript_raw'] .= $sentence['time'] . ' ' . $sentence['text'] . "\n";
								}

								 
							
							}catch(Exception $e){

								//error in red color
								echo '<br><span style="color:red">Transcript error: '.$e->getMessage().'</span>';

							}
 
						 
						}

						//if tag used channel_country exists, get it from the channel id
						//https://www.googleapis.com/youtube/v3/channels?key={key}&part=snippet&id=UC_x5XG1OV2P6uZZ5FSM9Ttw
						
						if($this->do_tag_exists($camp , array('[channel_country]','[channel_country_name]'))){
							
							//report tag exists, getting channel country
							echo '<br>Channel country tag found, getting channel country ...';

							//channel id is the temp['vid_author']
							$channel_id = $temp['vid_author'];
							 
							//get channel details
							$ccurl = 'https://www.googleapis.com/youtube/v3/channels?key=' . $wp_automatic_yt_tocken . '&part=snippet&id=' . $channel_id;
							
							echo '<br>yt link:' . $ccurl;
							
							curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
							curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $ccurl ) );
							$exec = curl_exec ( $this->ch );
							$x = curl_error ( $this->ch );
							
							if (stristr ( $exec, 'kind' )) {
								
								$json_exec = json_decode ( $exec );
								$theItem = $json_exec->items [0];
								
								// check snippet
								if (isset ( $theItem->snippet )) {
									
									// setting full content
									$temp ['channel_country'] = $theItem->snippet->country;
									echo '<br>Channel country set to ' . $temp ['channel_country'];


									//snippet-country now contains the country code like US, lets get the full name
									$country_name = $this->get_country_name($temp ['channel_country']);
									$temp ['channel_country_name'] = $country_name;

								}
							} else {
								echo '<br>no valid reply from youtube ';
							}
							
						}
						
						return $temp;
					} else {
						
						echo '<br>No links found for this keyword';
					}
				} // if trim
			} // foreach keyword
		}
		
		function get_channel_id_foruser($user , $wp_automatic_yt_tocken){
			
			// $user contains a channel URL like https://www.youtube.com/c/HolyCulture
			
			echo '<br>Getting channel ID from URL:'.$user;
			
			$md5 = md5($user);
			
			//cached?
			$cached = get_post_meta($this->currentCampID , $md5 , true);
			
			if(wp_automatic_trim($cached) != '') {
				
				echo '<--cached:' . $cached;
				
				return $cached;
			}
			
			
			
			//first time,lets get and cache  
			
			//curl get
			$x='error';
			$url=wp_automatic_trim($user);
			curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
			curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
			
			//set SOCS cookie for youtube ask for cookies
			$cookies = 'SOCS=CAISNQgDEitib3FfaWRlbnRpdHlmcm9udGVuZHVpc2VydmVyXzIwMjMwMjI4LjA2X3AwGgJlbiACGgYIgJSKoAY;';
			curl_setopt($this->ch, CURLOPT_COOKIE, $cookies);
			
			$exec=curl_exec($this->ch);
			$x=curl_error($this->ch);
			$cuinfo = curl_getinfo($this->ch);
			
			
			echo '<-- returned ' . $cuinfo['size_download'] . ' request code: '. $cuinfo['http_code'];
			

			preg_match('!"externalId":"(.*?)"!', $exec , $id_matches);
			
			if(isset($id_matches[1]) && wp_automatic_trim($id_matches[1]) != '' ) {
				
				//cache and return
				update_post_meta($this->currentCampID , $md5  , $id_matches[1] ); 
				
				echo '<-- found:' . $id_matches[1];
				
				return $id_matches[1];
				
				
			}else{
				
				echo '<-- not found';
				 
			}
			 
			
			return $user;
		}

		//function that takes the video ID and returns the video transcript in text format using Youtube API
		function get_video_transcript($video_id){
			 
			echo '<br>Getting transcript for video ID: '.$video_id;
			
			 //build video URL 
			 $video_url = 'https://www.youtube.com/watch?v='.$video_id;

			 //get the video page
			 echo '<br>Getting video page to grab transcript URL: '.$video_url;
			 curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
			 curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $video_url ) );
			 $exec = curl_exec ( $this->ch );

			 //report response code and page size 
			 $cuinfo = curl_getinfo($this->ch);
			 echo '<br>Video page returned ' . $cuinfo['size_download'] . ' request code: '. $cuinfo['http_code'];
				 

			 //check if captionTracks":[{"baseUrl":" exists and if not throw an exception
			 if(!stristr($exec, 'captionTracks":[{"baseUrl":"')){

				//if contains LOGIN_REQUIRED then throw an exception that login is required
				if(stristr($exec, 'LOGIN_REQUIRED')){
					throw new Exception('Login required to get transcript, either the video is private or your server IP is suspected and at this case, using private proxy may be a solution.');
				}

			 	throw new Exception('Did not find caption URL inside returned page content');
			 }

			 //extract the caption URL
			 preg_match('!"captionTracks":\[\{"baseUrl":"(.*?)"!', $exec , $matches);

			//if no match throw an exception
			if(!isset($matches[1]) || wp_automatic_trim($matches[1]) == ''){
				throw new Exception('Did not find caption URL inside returned page content case #2');
			}

			//decode the URL
			$json = '{"caption_url":"'.$matches[1].'"}';

			//decode the json
			$caption = json_decode($json , true);

			//get the caption URL
			$caption_url = $caption['caption_url'];

			//report the caption URL
			echo '<br>Found caption URL: '.$caption_url;

			//get the caption content
			curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
			curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $caption_url ) );

			$exec = curl_exec ( $this->ch );

			//report response code and page size
			$cuinfo = curl_getinfo($this->ch);

			echo '<br>Caption returned ' . $cuinfo['size_download'] . ' request code: '. $cuinfo['http_code'];

			
			//check if the returned content is a valid transcript
			if(!stristr($exec, '</text>')){
				throw new Exception('Did not find valid transcript inside returned caption content');
			}

			 
			//extract the transcript
			preg_match_all('!<text start="(.*?)" dur="(.*?)">(.*?)</text>!s', $exec , $matches);

			//if no matches throw an exception
			if(!isset($matches[3]) || count($matches[3]) == 0){
				throw new Exception('Could not extract transcript from caption content');
			}

			//init the transcript
			$transcript = '';
			$sentences = array();

			//iterate the matches and build the transcript
			foreach($matches[3] as $text){
				
				//add the text to the transcript
				$transcript .= $text . "\n";

				//init the sentence
				$sentence['text'] = $text;
				$sentence['time'] = $matches[1][0];

				//add the sentence to the sentences array
				$sentences[] = $sentence;

			}

			//decode the transcript
			$transcript = html_entity_decode($transcript, ENT_QUOTES);

			//replace &#39; with '
			$transcript = str_replace('&#39;', "'", $transcript);

			//Fix the transcript by replacing new lines with spaces
			$transcript = str_replace("\n", " ", $transcript); 
			$transcript = preg_replace('/\xA0/u', ' ', $transcript); 
			$transcript = preg_replace('/\s+/u', ' ', $transcript); 
 
			//return the transcript
			$result['text'] = $transcript;

			//return the sentences
			$result['sentences'] = $sentences;

			//return the transcript
			return $result;
			
		}

		//is short a function to take the video ID and check if this is a short or not 
		//it does a HEAD request to the short URL https://www.youtube.com/shorts/v8VW1n1dtFU
		function is_short($video_id){
			
			//short url
			$short_url = 'https://www.youtube.com/shorts/'.$video_id;
			
			 //cu ini 
			 $ch = curl_init();

			 //set url
			 curl_setopt($ch, CURLOPT_URL, $short_url);

			 //return the transfer as a string
			 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			 //set request type to HEAD
			 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');

			 //set timeout
			 curl_setopt($ch, CURLOPT_TIMEOUT, 10);

			 //execute curl
			 $result = curl_exec($ch);

			 //get info
			 $info = curl_getinfo($ch);

			 

			 //close curl
			 curl_close($ch);

			 //if http_code is 200 then its a short
			 if($info['http_code'] == 200){
			 	echo '<br>Short video found';
			 	return true;
			 }else{
				echo '<br>Status code: '.$info['http_code'] . ' not a short';
				return false;
			 }

		}

		//function get_country_name to take the country code and return the country name
		function get_country_name($country_code){
			
			//country codes
			$country_codes = array(
				'AF' => 'Afghanistan',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'AS' => 'American Samoa',
				'AD' => 'Andorra',
				'AO' => 'Angola',
				'AI' => 'Anguilla',
				'AQ' => 'Antarctica',
				'AG' => 'Antigua and Barbuda',
				'AR' => 'Argentina',
				'AM' => 'Armenia',
				'AW' => 'Aruba',
				'AU' => 'Australia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'BS' => 'Bahamas',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BB' => 'Barbados',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BZ' => 'Belize',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BT' => 'Bhutan',
				'BO' => 'Bolivia',
				'BA' => 'Bosnia and Herzegovina',
				'BW' => 'Botswana',
				'BR' => 'Brazil',
				'IO' => 'British Indian Ocean Territory',
				'VG' => 'British Virgin Islands',
				'BN' => 'Brunei',
				'BG' => 'Bulgaria',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'KH' => 'Cambodia',
				'CM' => 'Cameroon',
				'CA' => 'Canada',
				'CV' => 'Cape Verde',
				'KY' => 'Cayman Islands',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'CL' => 'Chile',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos Islands',
				'CO' => 'Colombia',
				'KM' => 'Comoros',
				'CK' => 'Cook Islands',
				'CR' => 'Costa Rica',
				'HR' => 'Croatia',
				'CU' => 'Cuba',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'CD' => 'Democratic Republic of the Congo',
				'DK' => 'Denmark',
				'DJ' => 'Djibouti',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'TL' => 'East Timor',
				'EC' => 'Ecuador',
				'EG' => 'Egypt',
				'SV' => 'El Salvador',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'EE' => 'Estonia',
				'ET' => 'Ethiopia',
				'FK' => 'Falkland Islands',
				'FO' => 'Faroe Islands',
				'FJ' => 'Fiji',
				'FI' => 'Finland',
				'FR' => 'France',
				'PF' => 'French Polynesia',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GU' => 'Guam',
				'GT' => 'Guatemala',
				'GG' => 'Guernsey',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HN' => 'Honduras',
				'HK' => 'Hong Kong',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran',
				'IQ' => 'Iraq',
				'IE' => 'Ireland',
				'IM' => 'Isle of Man',
				'IL' => 'Israel',
				'IT' => 'Italy',
				'CI' => 'Ivory Coast',
				'JM' => 'Jamaica',
				'JP' => 'Japan',
				'JE' => 'Jersey',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KE' => 'Kenya',
				'KI' => 'Kiribati',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => 'Laos',
				'LV' => 'Latvia',
				'LB' => 'Lebanon',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libya',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MO' => 'Macao',
				'MK' => 'Macedonia',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'ML' => 'Mali',
				'MT' => 'Malta',
				'MH' => 'Marshall Islands',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'YT' => 'Mayotte',
				'MX' => 'Mexico',
				'FM' => 'Micronesia',
				'MD' => 'Moldova',
				'MC' => 'Monaco',
				'MN' => 'Mongolia',
				'ME' => 'Montenegro',
				'MS' => 'Montserrat',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'MM' => 'Myanmar',
				'NA' => 'Namibia',
				'NR' => 'Nauru',
				'NP' => 'Nepal',
				'NL' => 'Netherlands',
				'AN' => 'Netherlands Antilles',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NI' => 'Nicaragua',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'NU' => 'Niue',
				'KP' => 'North Korea',
				'MP' => 'Northern Mariana Islands',
				'NO' => 'Norway',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PW' => 'Palau',
				'PS' => 'Palestine',
				'PA' => 'Panama',
				'PG' => 'Papua New Guinea',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PH' => 'Philippines',
				'PN' => 'Pitcairn',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'PR' => 'Puerto Rico',
				'QA' => 'Qatar',
				'CG' => 'Republic of the Congo',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RU' => 'Russia',
				'RW' => 'Rwanda',
				'BL' => 'Saint Barthelemy',
				'SH' => 'Saint Helena',
				'KN' => 'Saint Kitts and Nevis',
				'LC' => 'Saint Lucia',
				'MF' => 'Saint Martin',
				'PM' => 'Saint Pierre and Miquelon',
				'VC' => 'Saint Vincent and the Grenadines',
				'WS' => 'Samoa',
				'SM' => 'San Marino',
				'ST' => 'Sao Tome and Principe',
				'SA' => 'Saudi Arabia',
				'SN' => 'Senegal',
				'RS' => 'Serbia',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SG' => 'Singapore',
				'SK' => 'Slovakia',
				'SI' => 'Slovenia',
				'SB' => 'Solomon Islands',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'KR' => 'South Korea',
				'ES' => 'Spain',
				'LK' => 'Sri Lanka',
				'SD' => 'Sudan',
				'SR' => 'Suriname',
				'SJ' => 'Svalbard and Jan Mayen',
				'SZ' => 'Swaziland',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'SY' => 'Syria',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TZ' => 'Tanzania',
				'TH' => 'Thailand',
				'TG' => 'Togo',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TT' => 'Trinidad and Tobago',
				'TN' => 'Tunisia',
				'TR' => 'Turkey',
				'TM' => 'Turkmenistan',
				'TC' => 'Turks and Caicos Islands',
				'TV' => 'Tuvalu',
				'VI' => 'U.S. Virgin Islands',
				'UG' => 'Uganda',
				'UA' => 'Ukraine',
				'AE' => 'United Arab Emirates',
				'GB' => 'United Kingdom',
				'US' => 'United States',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VU' => 'Vanuatu',
				'VA' => 'Vatican',
				'VE' => 'Venezuela',
				'VN' => 'Vietnam',
				'WF' => 'Wallis and Futuna',
				'EH' => 'Western Sahara',
				'YE' => 'Yemen',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe'
			);

			//if the country code exists in the array return the country name
			if(isset($country_codes[$country_code])){
				return $country_codes[$country_code];
			}

			//return the country code if not found
			return $country_code;
		}
		
	}