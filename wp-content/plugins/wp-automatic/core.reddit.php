<?php

// Main Class
require_once 'core.php';
class WpAutomaticReddit extends wp_automatic {
	
	/*
	 * ---* youtube get links ---
	 */
	function reddit_fetch_items($keyword, $camp) {
		echo "<br>so I should now get some items from Reddit";

		//reddit session cookie wp_automatic_reddit_session
		$wp_automatic_reddit_session =  get_option('wp_automatic_reddit_sess');

		//trim
		$wp_automatic_reddit_session = wp_automatic_trim($wp_automatic_reddit_session);

		//if no session cookie
		if(($wp_automatic_reddit_session) == ''){
			
			//echo error message in a span with red color and ask the user to visit the plugin settings page and add the required session cookie
			echo '<br><span style="color:red">Reddit session cookie is missing please visit the plugin settings page and add the required session cookie</span>';
			

			return false;
		}

		
		// ini options
		$camp_opt = unserialize ( $camp->camp_options );
		if (stristr ( $camp->camp_general, 'a:' ))
			$camp->camp_general = base64_encode ( $camp->camp_general );
		$camp_general = unserialize ( base64_decode ( $camp->camp_general ) );
		$camp_general = array_map ( 'wp_automatic_stripslashes', $camp_general );
		
		// items url
		$cg_rd_page = wp_automatic_trim( $camp_general ['cg_rd_page'] );
		$cg_rd_page_md = md5 ( $cg_rd_page );
		
		// verify valid link
		if (! (stristr ( $cg_rd_page, 'http' ) && stristr ( $cg_rd_page, 'reddit.com' ))) {
			echo '<br>Provided reddit link is not valid please visit reddit.com and get a correct one';
			return false;
		}
		
		// https://www.reddit.com/r/Jokes/top/?t=month
		$additional_query = ''; // ini
		
		if (stristr ( $cg_rd_page, '?' )) {
			$url_pts = explode ( '?', $cg_rd_page );
			$additional_query = '?' . $url_pts [1];
			$cg_rd_page = $url_pts [0];
		}
		
		// .json
		if (! preg_match ( '{/$}', $cg_rd_page )) {
			$cg_rd_page .= '/';
		}
		
		$cg_rd_page .= '.json' . $additional_query;
		
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
			
			if (! in_array ( 'OPT_RD_CACHE', $camp_opt )) {
				$start = 1;
				echo '<br>Cache disabled resetting index to 1';
			} else {
				
				// check if it is reactivated or still deactivated
				if ($this->is_deactivated ( $camp->camp_id, $keyword )) {
					$start = 1;
				} else {
					// still deactivated
					return false;
				}
			}
		}
		
		// page tag
		if (in_array ( 'OPT_RD_CACHE', $camp_opt )) {
			
			$after_tag = get_post_meta ( $camp->camp_id, 'after_tag', 1 );
			$after_md = get_post_meta ( $camp->camp_id, 'after_md', 1 );
			
			echo '<br>AFter tag:' . $after_tag;
			echo '<br>md:' . $after_md;
			echo '<br>current md:' . $cg_rd_page_md;
			
			if (wp_automatic_trim( $after_tag ) != '') {
				
				if ($after_md == $cg_rd_page_md) {
					
					$cg_rd_page .= stristr ( $cg_rd_page, '?' ) ? '&' : '?';
					$cg_rd_page .= 'after=' . $after_tag;
				}
			}
		}
		
		echo '<br>Reddit items url:' . $cg_rd_page;
		
		echo ' index:' . $start;
		
		// update start index to start+1
		$nextstart = $start + 1;
		
		$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $nextstart where keyword_id=$kid ";
		$this->db->query ( $query );
		
		// get items
		// curl get
		$x = 'error';
		$url = $cg_rd_page;
		curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
		curl_setopt ( $this->ch, CURLOPT_URL, wp_automatic_trim( $url ) );

		//set the reddit session cookie reddit_session=
		//removed to fix #36
		
 
		
		curl_setopt($this->ch, CURLOPT_COOKIE, "reddit_session=$wp_automatic_reddit_session");

		$exec = curl_exec ( $this->ch );
		$x = curl_error ( $this->ch );
 
 		
		// error check
		if (wp_automatic_trim( $x ) != '') {
			echo '<br>Curl error:' . $x;
			return false;
		}
		
		// validate reply
		if (! stristr ( $exec, '{"' )) {
			echo '<br>Not expected response from reddit';
		}
		
		// decode json
		$jsonReply = json_decode ( $exec );
		
		$allItms = array ();
		if (isset ( $jsonReply->data->children )) {
			$allItms = $jsonReply->data->children;
		}
		
		// Check returned items count
		if (count ( $allItms ) > 0) {
			
			echo '<br>Valid reply returned with ' . count ( $allItms ) . ' item';
			
			$after = '';
			$after = $jsonReply->data->after;
			echo '<br>Next page tag:' . $after;
			
			if (wp_automatic_trim( $after ) == '') {
				delete_post_meta ( $camp->camp_id, 'after_tag' );
			} else {
				update_post_meta ( $camp->camp_id, 'after_tag', $after );
				update_post_meta ( $camp->camp_id, 'after_md', ($cg_rd_page_md) );
			}
		} else {
			
			echo '<br>No items found';
			delete_post_meta ( $camp->camp_id, 'after_tag' );
			
			echo '<br>Keyword have no more images deactivating...';
			$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
			$this->db->query ( $query );
			
			if (! in_array ( 'OPT_NO_DEACTIVATE', $camp_opt ))
				$this->deactivate_key ( $camp->camp_id, $keyword );
		}
		
		echo '<ol>';
		
		foreach ( $allItms as $itemTxt ) {
			
			$item = array ();
			
			// match title
			$item ['item_title'] = '';
			
			if (isset ( $itemTxt->data->title ))
				$item ['item_title'] = $itemTxt->data->title;
			
			if (wp_automatic_trim( $item ['item_title'] ) == '' && isset ( $itemTxt->data->link_title )) {
				$item ['item_title'] = $itemTxt->data->link_title;
			}
			
			// match description
			$item ['item_description'] = $itemTxt->data->selftext;

			//if $itemTxt->data->selftext_html is not empty use it
			if(wp_automatic_trim($itemTxt->data->selftext_html) != ''){
				$item ['item_description'] = $itemTxt->data->selftext_html;

				//decode html entities
				$item ['item_description'] = html_entity_decode($item ['item_description']);

			}
						
			// match link
			$item_link = $item ['item_url'] = $itemTxt->data->url;
			
			if (wp_automatic_trim( $item ['item_url'] ) == '' && isset ( $itemTxt->data->link_url )) {
				$item_link = $item ['item_url'] = $itemTxt->data->link_url;
			}
			
			// permalink
			$item ['item_link'] = $itemTxt->data->permalink;
			
			// match date
			$item ['item_date'] = $itemTxt->data->created_utc;
			
			// match img
			$item ['item_img'] = '';
			$item ['item_img'] = @$itemTxt->data->preview->images [0]->source->url;
			
			// link_url contains https://i.redd.it/vfzv4pwyi9j91.jpg
			if ($item ['item_img'] == '' && isset ( $itemTxt->data->link_url ) && stristr ( $itemTxt->data->link_url, '.jpg' )) {
				$item ['item_img'] = $itemTxt->data->link_url;
			}
			
			$id = $item ['item_id'] = $itemTxt->data->id;
			$item ['item_domain'] = $itemTxt->data->domain;
			$item ['item_score'] = $itemTxt->data->score;
			$item ['item_author'] = $itemTxt->data->author;
			
			$item ['item_gif'] = '';
			$item ['item_gif'] = @$itemTxt->data->preview->images [0]->variants->gif->source->url;
			
			$item ['item_mp4'] = '';
			$item ['item_mp4'] = @$itemTxt->data->preview->images [0]->variants->mp4->source->url;
			
			// reddit gifs
			if (wp_automatic_trim( $item ['item_gif'] ) == '' && wp_automatic_trim( $item ['item_mp4'] ) == '') {
				if (isset ( $itemTxt->data->media->reddit_video->fallback_url )) {
					$item ['item_mp4'] = $itemTxt->data->media->reddit_video->fallback_url;
				} elseif (isset ( $itemTxt->data->preview->reddit_video_preview->fallback_url )) {
					$item ['item_mp4'] = $itemTxt->data->preview->reddit_video_preview->fallback_url;
				}
			}
			
			// media external embed
			$html = '';
			$html = @$itemTxt->data->media->oembed->html;
			
			if (wp_automatic_trim( $html ) != '' && ! stristr ( $html, 'embedly-embed' ))
				$item ['item_embed'] = html_entity_decode ( $html );
			
			// gfycat better mp4
			$gyf_thumb = '';
			if (stristr ( $item ['item_url'], 'gfycat.com' ) && wp_automatic_trim( $item ['item_mp4'] ) != '') {
				
				$gyf_thumb = $itemTxt->data->secure_media->oembed->thumbnail_url;
				
				if (wp_automatic_trim( $gyf_thumb ) != '') {
					$gyf_thumb = preg_replace ( '{-.*}', '-mobile.mp4', $gyf_thumb );
					$item ['item_mp4'] = $gyf_thumb;
				}
			}
			
			// nsfw
			$item ['item_nsfw'] = 'no';
			if ($itemTxt->data->over_18 == 1) {
				$item ['item_nsfw'] = 'yes';
			}
			
			// gallery sample https://pastebin.com/HEmG45dG
			$gallery_imgs = array ();
			if (isset ( $itemTxt->data->gallery_data->items )) {
				
				echo ' <-- this is a gallery';
				
				foreach ( $itemTxt->data->gallery_data->items as $gallery_item ) {
					
					$media_id = $gallery_item->media_id;
					$gallery_img_url = end ( $itemTxt->data->media_metadata->{"$media_id"}->p );
					$gallery_img_url = $gallery_img_url->u;
					
					$gallery_imgs [] = $gallery_img_url;
				}
				
				$item ['item_gallery_imgs'] = implode ( ',', $gallery_imgs );
				
				// when no featured image, pick the first from the gallery
				if ($item ['item_img'] == '' && count ( $gallery_imgs ) > 0) {
					$item ['item_img'] = $gallery_imgs [0];
				}
			}
			
			// link_flair_richtext flairs
			$item_flairs = '';
			$item_flairs_arr = array ();
		 
			
			if (isset ( $itemTxt->data->link_flair_richtext )) {
				
				$link_flair_richtext = is_array ( $itemTxt->data->link_flair_richtext ) ? $itemTxt->data->link_flair_richtext : array ();
				
				if(count($link_flair_richtext) == 0 && isset($itemTxt->data->link_flair_text) && wp_automatic_trim($itemTxt->data->link_flair_text) != '' ){
					$link_flair_richtext = array($itemTxt->data->link_flair_text);
				}
				
			 
 				
				foreach ( $link_flair_richtext as $link_flair_richtext_s ) {
				
					if(isset($link_flair_richtext_s->t)){
						$item_flairs_arr [] = $link_flair_richtext_s->t;
					}else{
						
						//check if link_flair_richtext_s is a string
						if(is_string($link_flair_richtext_s))
						$item_flairs_arr [] = $link_flair_richtext_s;
					}
				
				}
				 

				$item_flairs = implode ( ',', $item_flairs_arr );
			}
			
			$item ['item_flairs'] = $item_flairs;
			
			$data = (base64_encode ( serialize ( $item ) ));
			
			// post_hint
			$post_hint = '';
			$post_hint = @$itemTxt->data->post_hint;
			
			echo '<li> Link:' . $item_link;
			
			// No image skip
			if (wp_automatic_trim( $item ['item_img'] ) == '' && in_array ( 'OPT_RD_IMG', $camp_opt )) {
				echo '<- No image skip';
				continue;
			}
			
			// Filter type
			if (in_array ( 'OPT_RD_POST_FILTER', $camp_opt )) {
				
				// gifs
				if (wp_automatic_trim( $item ['item_mp4'] ) != '' || isset ( $item ['item_embed'] )) {
					
					if (in_array ( 'OPT_RD_POST_VIDS', $camp_opt ) && isset ( $itemTxt->data->is_video ) && $itemTxt->data->is_video == 1) {
						
						echo '<-- Video ...';
					} elseif (in_array ( 'OPT_RD_POST_GIFS', $camp_opt ) && isset ( $itemTxt->data->is_video ) && $itemTxt->data->is_video == '') {
						echo '<-- GIF ...';
						
						// video & gif
					} elseif (! in_array ( 'OPT_RD_POST_VID', $camp_opt )) {
						
						// backward compitablity
						echo '<-- Gif/Vid skipping...';
						continue;
					}
				} elseif (wp_automatic_trim( $post_hint ) == '' || $post_hint == 'self') {
					
					// text
					if (! in_array ( 'OPT_RD_POST_TXT', $camp_opt )) {
						echo '<-- Text post skipping...';
						continue;
					}
				} elseif ($post_hint == 'image') {
					
					// Image
					if (! in_array ( 'OPT_RD_POST_IMAGE', $camp_opt )) {
						echo '<-- Image post skipping...';
						continue;
					}
				} elseif ($post_hint == 'link') {
					
					// Image
					if (! in_array ( 'OPT_RD_POST_LINK', $camp_opt )) {
						echo '<-- Link post skipping...';
						continue;
					}
				}
			}
			
			if ($this->is_execluded ( $camp->camp_id, $item_link )) {
				echo '<-- Execluded';
				continue;
			}
			
			if (! $this->is_duplicate ( $item_link )) {
				$query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (  '$id', '0', '$data' ,'rd_{$camp->camp_id}_$keyword')  ";
				$this->db->query ( $query );
			} else {
				echo ' <- duplicated <a href="' . get_edit_post_link ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
			}
		}
		
		echo '</ol>';
	}
	
	/*
	 * ---* reddit post ---
	 */
	function reddit_get_post($camp) {
		
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
				$query = "select * from {$this->wp_prefix}automatic_general where item_type=  'rd_{$camp->camp_id}_$keyword' ";
				$this->used_keyword = $keyword;
				$res = $this->db->get_results ( $query );
				
				// when no links lets get new links
				if (count ( $res ) == 0) {
					
					// clean any old cache for this keyword
					$query_delete = "delete from {$this->wp_prefix}automatic_general where item_type='rd_{$camp->camp_id}_$keyword' ";
					$this->db->query ( $query_delete );
					
					// get new fresh items
					$this->reddit_fetch_items ( $keyword, $camp );
					
					// getting links from the db for that keyword
					$res = $this->db->get_results ( $query );
				}
				
				// check if already duplicated
				// deleting duplicated items
				$res_count = count ( $res );
				for($i = 0; $i < $res_count; $i ++) {
					
					$t_row = $res [$i];
					
					$t_data = unserialize ( base64_decode ( $t_row->item_data ) );
					
					$t_link_url = $t_data ['item_url'];
					
					if ($this->is_duplicate ( $t_link_url )) {
						
						// duplicated item let's delete
						unset ( $res [$i] );
						
						echo '<br>reddit item (' . $t_data ['item_title'] . ') found cached but duplicated <a href="' . get_permalink ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
						
						// delete the item
						$query = "delete from {$this->wp_prefix}automatic_general where id='{$t_row->id}' ";
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
					
					// empty item_description fix
					if (wp_automatic_trim( $temp ['item_description'] ) == '')
						$temp ['item_description'] = $temp ['item_title'];
					
					// Item img html
					if (isset ( $temp ['item_gallery_imgs'] ) && in_array ( 'OPT_RD_SLIDER', $camp_opt )) {
						echo '<br>Slider images exist...';
						
						// template for img html
						$cg_rd_full_img_t = $camp_general ['cg_rd_full_img_t'];
						if (wp_automatic_trim( $cg_rd_full_img_t ) == '')
							$cg_rd_full_img_t = '<img src="[img_src]" />';
						
						// build the html
						$item_gallery_imgs = explode ( ',', $temp ['item_gallery_imgs'] );
						
						$gallery_html = '';
						foreach ( $item_gallery_imgs as $single_img_src ) {
							$gallery_html .=wp_automatic_str_replace( '[img_src]', $single_img_src, $cg_rd_full_img_t );
						}
						
						$temp ['item_img_html'] = $gallery_html;
					} elseif (wp_automatic_trim( $temp ['item_img'] ) != '') {
						$temp ['item_img_html'] = '<img src="' . $temp ['item_img'] . '" />';
					} else {
						$temp ['item_img_html'] = '';
					}
					
					// Yt embed
					if (! isset ( $temp ['item_embed'] ))
						$temp ['item_embed'] = '';
					
					if (stristr ( $temp ['item_url'], 'youtu.be' ) || stristr ( $temp ['item_url'], 'youtube.com' )) {
						$temp ['item_embed'] = '[embed]' . $temp ['item_url'] . '[/embed]';
					}
					
					// Gif embed
					$temp ['item_gif_embed'] = '';
					if (wp_automatic_trim( $temp ['item_gif'] ) != '') {
						$temp ['item_embed'] = $temp ['item_gif_embed'] = '<img src="' . $temp ['item_gif'] . '"/>';
					}
					
					// mp4 embed
					$temp ['item_mp4_embed'] = '';
					
					if (wp_automatic_trim( $temp ['item_mp4'] ) != '') {
						
						$loop = (in_array ( 'OPT_RD_LOOP', $camp_opt )) ? ' loop="on" ' : '';
						$autoPlay = (in_array ( 'OPT_RD_AUTO', $camp_opt )) ? ' autoplay="on" ' : '';
						
						$temp ['item_embed'] = $temp ['item_mp4_embed'] = '<video ' . $loop . ' ' . $autoPlay . '    controls="controls"><source src="' . $temp ['item_mp4'] . '" type="video/mp4"></video>';
						
						// official embed
						if (in_array ( 'OPT_RD_OFFICIAL_EMBED', $camp_opt )) {
							$temp ['item_embed'] = '[embed]https://reddit.com' . $temp ['item_link'] . '[/embed]';
						}
					}
					
					// author link
					$temp ['item_author_link'] = '<a href="' . $temp ['item_author'] . '">' . $temp ['item_author'] . '</a>';
					
					// Item link prefix
					$temp ['item_link'] = 'https://reddit.com' . $temp ['item_link'];
					
					// update the link status to 1
					$query = "delete from {$this->wp_prefix}automatic_general where id={$ret->id}";
					$this->db->query ( $query );
					
					// if cache not active let's delete the cached videos and reset indexes
					if (! in_array ( 'OPT_RD_CACHE', $camp_opt )) {
						echo '<br>Cache disabled claring cache ...';
						$query = "delete from {$this->wp_prefix}automatic_general where item_type='rd_{$camp->camp_id}_$keyword' ";
						$this->db->query ( $query );
						
						// reset index
						$query = "update {$this->wp_prefix}automatic_keywords set keyword_start =1 where keyword_camp={$camp->camp_id}";
						$this->db->query ( $query );
					}
					
					// item_date timestamp to date
					$temp ['item_date_formated'] = get_date_from_gmt ( gmdate ( 'Y-m-d H:i:s', ($temp ['item_date']) ) );
					
					// categories to set if enabled
					if (in_array ( 'OPT_RD_CAT', $camp_opt ) && wp_automatic_trim( $temp ['item_flairs'] ) != '')
						$temp ['categories_to_set'] = $temp ['item_flairs'];
					
					// tags to set if enabled
					if (in_array ( 'OPT_RD_TAG', $camp_opt ) && wp_automatic_trim( $temp ['item_flairs'] ) != '')
						$temp ['tags_to_set'] = $temp ['item_flairs'];
					
					return $temp;
				} else {
					
					echo '<br>No links found for this keyword';
				}
			} // if trim
		} // foreach keyword
	}
}