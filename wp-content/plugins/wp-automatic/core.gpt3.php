<?php

// Main Class
require_once 'core.php';
class WpAutomaticgpt3 extends wp_automatic {
	
	
	/**
	 * Get gpt3 new post for a title and a keyword and a campaign id 
	 * @param unknown $camp
	 * @return string[]|Boolean
	 */
	 
	function gpt3_get_post($camp) {
		
  
		// ini keywords and options
		$camp_opt = $this->camp_opt;

		// get keywords
		$keywords = explode ( ',', $camp->camp_keywords );
		
		// get general options
		$camp_general = $this->camp_general;
		
		// looping keywords
		foreach ( $keywords as $keyword ) {
			
			// trim keyword
			$keyword = wp_automatic_trim( $keyword );
			
			// update last keyword
			update_post_meta ( $camp->camp_id, 'last_keyword', wp_automatic_trim( $keyword ) );
			
			// when valid keyword
			if (wp_automatic_trim( $keyword ) != '') {

				// log processed keyword
				wp_automatic_log_new ( 'Processing keyword:' , $keyword );
				
				// record current used keyword
				$this->used_keyword = $keyword;
				
				// getting links from the db for that keyword
				$query = "select * from {$this->wp_prefix}automatic_general where item_type=  'gp_{$camp->camp_id}_$keyword' ";
				$res = $this->db->get_results ( $query );
				
				// when no links lets get new links
				if (count ( $res ) == 0) {
					
					// clean any old cache for this keyword
					$query_delete = "delete from {$this->wp_prefix}automatic_general where item_type='gp_{$camp->camp_id}_$keyword' ";
					$this->db->query ( $query_delete );
					
					// get new links
					$this->gpt3_fetch_items ( $keyword, $camp );
					
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
						
						echo '<br>GPT item  (' . $t_data ['item_title'] . ') found cached but duplicated <a href="' . get_permalink ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
						
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
					
					$temp = unserialize ( base64_decode ( $ret->item_data ) );
					 
					// report link
					echo '<br>Found Title:' . $temp ['item_title'];
					 
					  
					// get prompt
					$cg_gp_prompt = $camp_general ['cg_gp_prompt'];

					// default prompt if empty
					if (wp_automatic_trim( $cg_gp_prompt ) == '') {
						$cg_gp_prompt = 'Write an article about [article_title]';
					}

					// replace [article_title] with the title
					$cg_gp_prompt =wp_automatic_str_replace( '[article_title]', $temp ['item_title'], $cg_gp_prompt );
					 
					// replace [keyword] with the keyword
					$cg_gp_prompt =wp_automatic_str_replace( '[keyword]', $keyword, $cg_gp_prompt );
					
					//if OPT_GP_NO_CONTENT exists on camp_opt then skip openAI call
					if(in_array('OPT_GP_NO_CONTENT', $camp_opt)){
						echo '<br>Content generation prompt disabled, skipping OpenAI API call';
						
						//default content
						$temp['item_content'] = '';
						
						 
					}else{

						//Generate the content using OpenAI API
						try{

							// report
							echo '<br>Calling OpenAI API:' . $cg_gp_prompt;

							// call the api
							$result = $this->openai_gpt3($cg_gp_prompt);

							// report result char length
							echo '<br>Result length:' . strlen($result);

							// nl to br
							$result = nl2br($result);

							// if contains html and body, only get the body
							if(strpos($result, '<body') !== false){
								$result = $this->grab_body($result);
								echo '<br>Result contains html, only getting the body';
							}

							// if option OPT_GP_REMOVE_H1 is enabled, remove h1 tags
							if(in_array('OPT_GP_REMOVE_H1', $camp_opt)){
								$result = preg_replace('/<h1[^>]*>(.*?)<\/h1>/is', '', $result);
								echo '<br>H1 tag removal option is enabled, removing h1 tags';
							}

							//echo $result;exit;

 							// add result to the temp array to be used later
							$temp['item_content'] = $result;

						}catch(Exception $e){
							echo '<br><span style="color:red">OpenAI API call failed:'  . $e->getMessage() . '</span>';
							return;
						}
						
					}

					// return the temp array containing the item data including the content, title, url
					return $temp;
				
				} else {
					
					echo '<br>No links found for this keyword';
				}
			} // if trim
		} // foreach keyword
	}
	
	/**
	 * function gpt3_fetch_items to get items from gpt3 api for a keyword and camp id and save them in the db for later use 
	 * @param string $keyword
	 * @param unknown $camp
	 * @return boolean
	 * @since 1.0
	 * @version 1.0
	 * @updated 00.00.13
	 * @access public
	 * @category gpt3
	 */
	function gpt3_fetch_items($keyword, $camp) {
		
		// report
		echo "<br>So I should now get some articles from OpenAI gpt3 for keyword :" . $keyword;
		
		//api key
		$wp_automatic_openai_key = wp_automatic_single_item('wp_automatic_openai_key','');

		//openrouter api key
		$wp_automatic_openrouter_key = wp_automatic_single_item('wp_automatic_openrouter_key','');

		// check if api key is set
		if(wp_automatic_trim($wp_automatic_openai_key) == '' && wp_automatic_trim($wp_automatic_openrouter_key) == ''){
			echo '<br><span style="color:red">OpenAI API key not set, Please visit the plugin settings page and add it</span>';
			return;
		}

		// ini options
		$camp_opt = $this->camp_opt;
		$camp_general = $this->camp_general;
		
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
		
		// gpt3 offset parameter starts from zero, 50 , 100
 
		
		echo ' index:' . $start;
		
		// update start index to start+1
		$nextstart = $start + 1;
		$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $nextstart where keyword_id=$kid ";
		$this->db->query ( $query );

		// prompt generation
		$cg_gp_prompt_titles = $camp_general['cg_gp_prompt_titles'];
		
		// if empty use default
		if(wp_automatic_trim($cg_gp_prompt_titles) == ''){
			$cg_gp_prompt_titles = 'suggest headlines for articles about [keyword]';
		}

		// replace keyword
		$cg_gp_prompt_titles = wp_automatic_str_replace('[keyword]', $keyword, $cg_gp_prompt_titles);

		// report prompt
		echo '<br>GPT prompt:' . $cg_gp_prompt_titles;

		// get the gpt3 call results
		try{ 
			 
			// if checkbox with value = OPT_GP_NO_TITLES is not checked, do api_call, otherwise, set lines array to contain the keyword only
			if(!in_array('OPT_GP_NO_TITLES', $camp_opt)){
				
				// call the api
				$result = $this->openai_gpt3($cg_gp_prompt_titles);


				//  remove ol and ul tags if exists
				 $result = str_replace(array('<ol>', '</ol>', '<ul>', '</ul>','<li>'), '', $result);

				 // replace </li> with new line
				 $result = str_replace('</li>', "\n", $result);

				 //decode &quot; and similar
				 $result = html_entity_decode($result);
  
				//split the results by new line
				$lines = explode("\n", $result);

				//remove any line that contains "headline" or "title"
				$lines = array_filter($lines, function($line){
					return strpos($line, 'headline') === false && strpos($line, 'title') === false;
				});


			} else {
				// set lines array to contain the keyword only
				$lines = array($keyword);
			}
			 
			//filter lines array to remove empty lines
			$lines = array_filter($lines);

		  
			// map lines array to remove the number followed by dot from the start of the line
			$lines = array_map(function($line){
				return wp_automatic_trim( preg_replace('/^\d+\./', '', $line));
			}, $lines);
 
			// report
			echo '<br>GPT results count:' . count($lines);

			// if option OPT_GP_ONE_TITLES is checked, set lines array to contain only the first line
			if(in_array('OPT_GP_ONE_TITLES', $camp_opt)){
				
				echo '<br>One title option is checked, so I will only use the first title';
				
				$lines = array_slice($lines, 0, 1);
			}

			// loop on lines
			echo '<ol>';
			$i=0; // init counter
			foreach($lines as $line){

				//remove html tags
				$line = strip_tags($line);

				// report
				echo '<li>GPT suggested title :' . $line . '</li>';

				// remove quotes from the line
				$line = wp_automatic_str_replace('"', '', $line);
				
				// line md5
				$md5 = md5 ( $line );

				//generate url by appending open.ai to the md5
				$url = 'https://open.ai/' . $md5;

				$itm ['item_title'] = $line;
				$itm ['item_url'] = $url;

				$data = base64_encode ( serialize ( $itm ) );
				
				if ($this->is_execluded ( $camp->camp_id, $itm ['item_url'] )) {
					echo '<-- Execluded';
					continue;
				}
				
				if (! $this->is_duplicate ( $itm ['item_url'] )) {
					$query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (    '{$md5}', '0', '$data' ,'gp_{$camp->camp_id}_$keyword')  ";
					$this->db->query ( $query );
				} else {
					echo ' <- duplicated <a href="' . get_edit_post_link ( $this->duplicate_id ) . '">#' . $this->duplicate_id . '</a>';
				}
				
			 
				$i ++;

			
			}

			echo '</ol>';

			echo '<br>Total ' . $i . ' items found & cached';
			
			
			// check if nothing found so deactivate
			if ($i == 0) {
				echo '<br>No new articles found for this keyword, deactivating it permanently ';
				
				//deactivate this keyword permanently
				$this->deactivate_key ( $camp->camp_id, $keyword , 0);

				// set start index to -1 to indicate it is deactivated
				$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
				$this->db->query ( $query );
				
			}

			 
		
		
		} catch (Exception $e){
			echo '<br>OpenAI API error: ' . $e->getMessage();
			return false;
		}
 
		// check if reached call end limit cg_gp_prompt_count 
		$cg_gp_prompt_count = $camp_general['cg_gp_prompt_count'];

		// if empty use default
		if(wp_automatic_trim($cg_gp_prompt_count) == ''){
			$cg_gp_prompt_count = 1;
		}

		//  if start index == cg_gp_prompt_count then deactivate
		if($start >= $cg_gp_prompt_count){
			echo '<br>Reached call limit , deactivating this keyword permanently for further titles generation';
			
			//deactivate this keyword permanently
			$this->deactivate_key ( $camp->camp_id, $keyword , 0);

			// set the start index to -1 to indicate exhausted
			$query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
			$this->db->query ( $query );
		}
		
		return true;
	}

	/**
	 * function grab_body to get the body of a html page
	 * Check if the content contains html & body tags, if yes, return the body only
	 * @param string $content
	 * @return string 
	 */
	function grab_body($content){
		
		 if(strpos($content, '<body') !== false && strpos($content, '</body>') !== false){

			 //math body content using regex
			 preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $matches);

			 // if matches found
			 if(count($matches) > 0){

				 // return the body
				 return $matches[1];
			 }

		 }

		 
		return $content;
	}
}