<?php
//upload hander plugin to handle uploaded files, like export campaigns then display a message to the user or wp_die with an error message

add_action( 'admin_init', 'wp_automatic_handle_file_upload' );

function wp_automatic_handle_file_upload()
{

	if ( isset( $_POST['wp_automatic_upload_file'] ) && isset( $_FILES['wp_automatic_upload_file'] ) ) {
		 
		//variable to hold count of available campaigns 
		$available_campaigns = 0;
		
		//variable to hold successfully imported campaigns
		$imported_campaigns = 0;

		//validate if user is allowed to upload files 
		if(! ( is_user_logged_in() && current_user_can('upload_files')  )  ){
			wp_die('You are not allowed to upload files');
			return;
		}


		$errors= array();
		$file_name = $_FILES['wp_automatic_upload_file']['name'];
		$file_size =$_FILES['wp_automatic_upload_file']['size'];
		$file_tmp =$_FILES['wp_automatic_upload_file']['tmp_name'];
		$file_type=$_FILES['wp_automatic_upload_file']['type'];

		$file_ext_parts = explode('.', $file_name);
		$file_ext = end($file_ext_parts);
		$file_ext=strtolower($file_ext);
		
		$expensions= array("json"); 
		
		if(in_array($file_ext,$expensions)=== false){
			$errors[]="extension not allowed, please choose a .json file";
		}
		
		if($file_size > 2097152 * 2){
			$errors[]='File size must be excately 4 MB or less';
		}
		
		if(empty($errors)==true){
			 
			 
			//get file content
			$file_content = file_get_contents($file_tmp);
			
			//verify if file content contains camp_id string 
			if( strpos($file_content, 'camp_id') === false ){
				wp_die('The file you uploaded is not a valid wp automatic campaign file');
				return;
			}

			//verify valid json
			$json = json_decode($file_content);
 
			if( $json == null ){
				wp_die('The file you uploaded is not a valid wp automatic campaign file');
				return;
			}

			
			//load camps from the json file 
			$camps = $json;

			//set count of available campaigns
			$available_campaigns = count($camps);
			
			//loop on camps and insert them to the database table wp_automatic_camps
			global $wpdb;
			$table_name = $wpdb->prefix . 'automatic_camps';

			foreach($camps as $camp){

				 //insert a new post with type wp_automatic
				 $post_id = wp_insert_post( array(
					'post_title' => $camp->camp_name,
					'post_type' => 'wp_automatic',
					'post_status' => 'publish'
				) );
				
				//build insert array by looping on the camp object keys 
				$insert_array = array();
				foreach($camp as $key => $value){
					$insert_array[$key] = $value;
				}

				//insert the camp to the database
				$insert = $wpdb->insert( $table_name, array(
					'camp_id' => $post_id,
					'camp_name' => $camp->camp_name,
					'camp_keywords' => $camp->camp_keywords,
					'camp_post_title' => $camp->camp_post_title,
					'camp_post_content' => $camp->camp_post_content,
					'camp_cb_category' => $camp->camp_cb_category,
					'camp_replace_link' => $camp->camp_replace_link,
					'camp_post_status' => $camp->camp_post_status,
					'camp_post_every' => $camp->camp_post_every,
					'camp_add_star' => $camp->camp_add_star,
					'camp_post_category' => $camp->camp_post_category,
					'camp_options' => $camp->camp_options,
					'feeds' => $camp->feeds,
					'camp_type' => $camp->camp_type,
					'camp_search_order' => $camp->camp_search_order,
					'camp_amazon_cat' => $camp->camp_amazon_cat,
					'camp_youtube_cat' => $camp->camp_youtube_cat,
					'camp_youtube_order' => $camp->camp_youtube_order,
					'camp_amazon_region' => $camp->camp_amazon_region,
					'camp_post_author' => $camp->camp_post_author,
					'camp_post_custom_k' => $camp->camp_post_custom_k,
					'camp_post_custom_v' => $camp->camp_post_custom_v,
					'camp_post_exact' => $camp->camp_post_exact,
					'camp_general' => $camp->camp_general,
					'camp_post_execlude' => $camp->camp_post_execlude,
					'camp_yt_user' => $camp->camp_yt_user,
					'camp_translate_to' => $camp->camp_translate_to,
					'camp_translate_from' => $camp->camp_translate_from,
					'camp_translate_to_2' => $camp->camp_translate_to_2,
					'camp_post_type' => $camp->camp_post_type
				) );

				//if the insert is successful, get keywords and add them to the database table wp_automatic_keywords
				if($insert){

					//increment successifull campaigns counter
					$imported_campaigns++;

					//table name for keywords
					$table_name_keywords = $wpdb->prefix . 'automatic_keywords';
					$table_name_articles_keys = $wpdb->prefix . 'automatic_articles_keys';

					//get the keywords from the camp object
					$keywords = $camp->camp_keywords;
					
					//explode the keywords by comma
					$keywords = explode(',', $keywords);
					
					//loop on the keywords keyword_name,keyword_camp
					foreach($keywords as $keyword){
						
						//insert IGNORE the keyword to the database
						$insert = $wpdb->insert( $table_name_keywords, array(
							'keyword_name' => $keyword,
							'keyword_camp' => $post_id
						) );

						//insert IGNORE the keyword to the database table automatic_articles_keys
						$insert = $wpdb->insert( $table_name_articles_keys, array(
							'keyword' => $keyword,
							'camp_id' => $post_id
						) );
					}
				}
				 

			}
 

			 //build the success message showing number of campaigns and number of successful campaigns imported
			$success_message = $imported_campaigns . ' of ' . $available_campaigns . ' campaigns imported successfully';

			//add the message to be displayed in the admin area 
			add_settings_error( 'wp_automatic_messages', 'wp_automatic_message', __( $success_message, 'wp-automatic' ), 'updated' );


		}else{
			wp_die( 'There was an error uploading your file. The error is: ' .$errors[0] );
		}
		 
	}

}