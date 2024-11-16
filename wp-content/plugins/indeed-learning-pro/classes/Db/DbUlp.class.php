<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('DbUlp')){
   return;
}

class DbUlp{

	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}

	/**
	 * @param none
	 * @return array
	 */
	public static function plugin_post_types(){
			return array(
					'ulp_course',
					'ulp_quiz',
					'ulp_question',
					'ulp_lesson',
					'ulp_certificate',
					'ulp_order',
					'ulp_course_review',
					'ulp_announcement',
					'ulp_instructor',
					'ulp_qanda',
			);
	}

	/**
	 * Create Tables for plugin. If using multisite it will create a table for each site.
	 * @param none
	 * @return none
	 */
	public static function createTables(){
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $prefixes = self::get_all_prefixes();

    foreach ($prefixes as $the_table_prefix):
    		/// wp_ulp_course_modules
    		$table = $the_table_prefix . 'ulp_courses_modules';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								module_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								module_name VARCHAR(255) DEFAULT NULL,
    								course_id BIGINT(20) NOT NULL DEFAULT 0,
    								module_order INT(3),
    								status TINYINT(1),
    								INDEX idx_ulp_courses_modules_course_id (`course_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
    			";
    			dbDelta($sql);
    		}

    		/// wp_ulp_courses_modules_metas
    		$table = $the_table_prefix . 'ulp_courses_modules_metas';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								module_id INT(11) NOT NULL,
    								meta_key VARCHAR(255),
    								meta_value TEXT,
    								INDEX idx_ulp_courses_modules_metas_module_id (`module_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
    			";
    			dbDelta($sql);
    		}

    		/// wp_ulp_course_modules_items
    		$table = $the_table_prefix . 'ulp_course_modules_items';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								module_id INT(11) NOT NULL,
    								course_id BIGINT(20) NOT NULL DEFAULT 0,
    								item_id BIGINT(20),
    								item_type VARCHAR(50),
    								item_order INT(3),
    								status TINYINT(1),
    								INDEX idx_ulp_course_modules_items_course_id (`course_id`),
    								INDEX idx_ulp_course_modules_items_item_id (`item_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
          ";
    			dbDelta($sql);
    		}

    		/// wp_ulp_quizes_questions
    		$table = $the_table_prefix . 'ulp_quizes_questions';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								quiz_id BIGINT(20) NOT NULL,
    								question_id BIGINT(20),
    								item_order INT(3),
    								status TINYINT(1),
    								INDEX idx_ulp_quizes_questions_quiz_id (`quiz_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
          ";
    			dbDelta($sql);
    		}

    		/// wp_ulp_user_entities_relations
    		$table = $the_table_prefix . 'ulp_user_entities_relations';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								user_id BIGINT(20) NOT NULL,
    								entity_id BIGINT(20),
    								entity_type VARCHAR(255),
    								start_time TIMESTAMP NOT NULL DEFAULT 0,
    								end_time TIMESTAMP NOT NULL DEFAULT 0,
    								status TINYINT(1),
    								INDEX idx_ulp_user_entities_relations_user_id (`user_id`),
    								INDEX idx_ulp_user_entities_relations_entity_id (`entity_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
          ";
    			dbDelta($sql);
    		}

    		/// wp_ulp_user_entities_relations_metas
    		$table = $the_table_prefix . 'ulp_user_entities_relations_metas';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								user_entity_relation_id INT(11) NOT NULL,
    								meta_key VARCHAR(255),
    								meta_value TEXT,
    								INDEX idx_ulp_user_entities_relations_metas_user_entity_relation_id (`user_entity_relation_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
    			";
    			dbDelta($sql);
    		}

    		/// wp_ulp_activity
    		$table = $the_table_prefix . 'ulp_activity';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								uid BIGINT(20) NOT NULL,
    								entity_id BIGINT(20) DEFAULT 0,
    								entity_type VARCHAR(255),
    								action VARCHAR(255),
    								description TEXT,
    								event_time TIMESTAMP NOT NULL DEFAULT 0,
    								status TINYINT(1),
    								INDEX idx_ulp_activity_uid (`uid`),
    								INDEX idx_ulp_activity_entity_id (`entity_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
    			";
    			dbDelta($sql);
    		}

    		/// ulp_reward_points
    		$table = $the_table_prefix . 'ulp_reward_points';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								uid BIGINT(20) NOT NULL,
    								points INT(11)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
          ";
    			dbDelta($sql);
    		}

    		/// wp_ulp_reward_points_details
    		$table = $the_table_prefix . 'ulp_reward_points_details';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    			$sql = "CREATE TABLE $table(
    								id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    								uid BIGINT(20) NOT NULL,
    								points_num INT(3),
    								post_id BIGINT(20),
    								action VARCHAR(255),
    								description TEXT,
    								event_time TIMESTAMP NOT NULL DEFAULT 0,
    								INDEX idx_ulp_reward_points_details_uid (`uid`),
    								INDEX idx_ulp_reward_points_details_post_id (`post_id`)
    			)
          ENGINE=MyISAM
          CHARACTER SET utf8 COLLATE utf8_general_ci;
    			";
    			dbDelta($sql);
    		}

    		/// NOTIFICATIONS
    		$table = $the_table_prefix . 'ulp_notifications';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query ) != $table){
    			$sql = "CREATE TABLE " . $table . " (
    						id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    						type VARCHAR(255),
    						course_id BIGINT(20),
    						subject VARCHAR(255),
    						message TEXT,
    						pushover_message TEXT,
    						pushover_status TINYINT(1) DEFAULT 0,
    						status TINYINT(1) DEFAULT 0
    					)
      				ENGINE=MyISAM
      				CHARACTER SET utf8 COLLATE utf8_general_ci;
    			";
    			dbDelta($sql);
    		}

    		/// ORDERS
    		$table = $the_table_prefix . 'ulp_order_meta';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query ) != $table){
    			$sql = "CREATE TABLE " . $table . " (
    						id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    						order_id BIGINT(20),
    						meta_key VARCHAR(255),
    						meta_value LONGTEXT,
    						INDEX idx_ulp_order_meta_order_id (`order_id`)
    					)
      				ENGINE=MyISAM
      				CHARACTER SET utf8 COLLATE utf8_general_ci;
    			";
    			dbDelta($sql);
    		}

    		/// student Certificates
    		$table = $the_table_prefix . 'ulp_student_certificate';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query ) != $table){
    				$sql = "CREATE TABLE " . $table . " (
    							id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    							uid BIGINT(20) NOT NULL,
    							course_id BIGINT(20),
    							certificate_id BIGINT(20) NOT NULL,
    							grade VARCHAR(10),
    							details TEXT,
    							obtained_date TIMESTAMP NOT NULL DEFAULT 0,
    							INDEX idx_ulp_student_certificate_uid (`uid`),
    							INDEX idx_ulp_student_certificate_course_id (`course_id`)
    						)
        				ENGINE=MyISAM
        				CHARACTER SET utf8 COLLATE utf8_general_ci;
    				";
    				dbDelta($sql);
    		}


    		/// ulp_notes
    		$table = $the_table_prefix . 'ulp_notes';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    				$sql = "CREATE TABLE $table (
    							id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    							uid BIGINT(20) NOT NULL,
    							course_id BIGINT(20),
    							note_title VARCHAR (400),
    							note_content LONGTEXT,
    							obtained_date TIMESTAMP NOT NULL DEFAULT 0,
    							INDEX idx_ulp_notes_uid (`uid`),
    							INDEX idx_ulp_notes_course_id (`course_id`)
    						)
        				ENGINE=MyISAM
        				CHARACTER SET utf8 COLLATE utf8_general_ci;
                ";
    				dbDelta($sql);
    		}

    		/// ULP_DASHBOARD_NOTIFICATIONS
    		$table = $the_table_prefix . 'ulp_dashboard_notifications';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    				$sql = "CREATE TABLE $table (
    							type VARCHAR(40) NOT NULL,
    							value INT(11) DEFAULT 0
    				)
    				ENGINE=MyISAM
    				CHARACTER SET utf8 COLLATE utf8_general_ci;
            ";
    				dbDelta($sql);
    		}


    		/// ulp_student_badges
    		$table = $the_table_prefix . 'ulp_student_badges';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    				$sql = "CREATE TABLE $table (
    							id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    							uid BIGINT(20) NOT NULL,
    							badge_id BIGINT(20),
    							obtained_date TIMESTAMP NOT NULL DEFAULT 0,
    							INDEX idx_ulp_student_badges_uid (`uid`)
    						)
        				ENGINE=MyISAM
        				CHARACTER SET utf8 COLLATE utf8_general_ci;
    				";
    				dbDelta($sql);
    		}

    		/// ulp_badges
    		$table = $the_table_prefix . 'ulp_badges';
        $query = $wpdb->prepare( "show tables like %s ", $table );
    		if ($wpdb->get_var( $query )!=$table){
    				$sql = "CREATE TABLE $table (
    									id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    									badge_title VARCHAR(255),
    									badge_content LONGTEXT,
    									badge_image VARCHAR(400),
    									badge_type VARCHAR(200),
    									rule TEXT,
    									create_date TIMESTAMP NOT NULL DEFAULT 0
    						)
        				ENGINE=MyISAM
        				CHARACTER SET utf8 COLLATE utf8_general_ci;
    				";
    				dbDelta($sql);
    		}
      endforeach;
	}

  /**
   * @param none
   * @return array
   */
  public static function get_all_prefixes()
  {
      global $wpdb;
      $data[] = $wpdb->base_prefix;
      if (is_multisite() ){
          if ( is_network_admin() ){
              // activate on entire network
              $ids = self::get_all_blog_ids();
              if ($ids){
                foreach ($ids as $object){
                    if ( $object->blog_id == 1 ){
                       continue;
                    }
                    $data[] = $wpdb->base_prefix . $object->blog_id . '_';
                }
              }
          } else {
            // activate on single site on network
            $currentSite = get_current_blog_id();
            $mainSite = 1;
            if ( $currentSite == $mainSite ){
                return [ $wpdb->base_prefix ];
            }
            return [ $wpdb->base_prefix . $currentSite . '_' ];
          }
      }
      return $data;
  }

  /**
   * @param none
   * @return object
   */
  public static function get_all_blog_ids()
  {
      global $wpdb;
      //No query parameters required, Safe query. prepare() method without parameters can not be called
      $query = "SELECT blog_id FROM {$wpdb->blogs};";
      $data = $wpdb->get_results( $query );
      return $data;
  }

	public static function addCoursesCodulesIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_courses_modules;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_courses_modules_course_id' ){
							return;
					}
			}
      $query = "CREATE INDEX idx_ulp_courses_modules_course_id ON {$wpdb->prefix}ulp_courses_modules(course_id)";
			$wpdb->query( $query );
	}

	public static function addCoursesModulesMetasIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_courses_modules_metas;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_courses_modules_metas_module_id' ){
							return;
					}
			}
      $query = "CREATE INDEX idx_ulp_courses_modules_metas_module_id ON {$wpdb->prefix}ulp_courses_modules_metas(module_id)";
			$wpdb->query( $query );
	}

	public static function addCourseModulesItemsIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_course_modules_items;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_course_modules_items_course_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_course_modules_items_course_id ON {$wpdb->prefix}ulp_course_modules_items(course_id)";
					$wpdb->query( $query );
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_course_modules_items_item_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_course_modules_items_item_id ON {$wpdb->prefix}ulp_course_modules_items(item_id)";
					$wpdb->query( $query );
			}
	}

	public static function addQuizesQuestionsIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_quizes_questions;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_quizes_questions_quiz_id' ){
							return;
					}
			}
      $query = "CREATE INDEX idx_ulp_quizes_questions_quiz_id ON {$wpdb->prefix}ulp_quizes_questions(quiz_id)";
			$wpdb->query( $query );
	}

	public static function addUserEntitiesRelationsIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_user_entities_relations;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_user_entities_relations_user_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_user_entities_relations_user_id ON {$wpdb->prefix}ulp_user_entities_relations(user_id)";
					$wpdb->query( $query );
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_user_entities_relations_entity_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_user_entities_relations_entity_id ON {$wpdb->prefix}ulp_user_entities_relations(entity_id)";
					$wpdb->query( $query );
			}
	}

	public static function addUserEntitiesRelationsMetasIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_user_entities_relations_metas;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_user_entities_relations_metas_user_entity_relation_id' ){
							return;
					}
			}
      $query = "CREATE INDEX idx_ulp_user_entities_relations_metas_user_entity_relation_id ON {$wpdb->prefix}ulp_user_entities_relations_metas(user_entity_relation_id)";
			$wpdb->query( $query );
	}

	public static function addActivityIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_activity;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_activity_uid' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_activity_uid ON {$wpdb->prefix}ulp_activity(uid)";
					$wpdb->query( $query );
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_activity_entity_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_activity_entity_id ON {$wpdb->prefix}ulp_activity(entity_id)";
					$wpdb->query( $query );
			}
	}

	public static function addRewardPointsDetailsIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_reward_points_details;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_reward_points_details_uid' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_reward_points_details_uid ON {$wpdb->prefix}ulp_reward_points_details(uid)";
					$wpdb->query( $query );
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_reward_points_details_post_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_reward_points_details_post_id ON {$wpdb->prefix}ulp_reward_points_details(post_id)";
					$wpdb->query( $query );
			}
	}

	public static function addOrderMetaIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_order_meta;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_order_meta_order_id' ){
							return;
					}
			}
      $query = "CREATE INDEX idx_ulp_order_meta_order_id ON {$wpdb->prefix}ulp_order_meta(order_id)";
			$wpdb->query( $query );
	}

	public static function addStudentCertificateIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_student_certificate;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_student_certificate_uid' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_student_certificate_uid ON {$wpdb->prefix}ulp_student_certificate(uid)";
					$wpdb->query( $query );
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_student_certificate_course_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_student_certificate_course_id ON {$wpdb->prefix}ulp_student_certificate(course_id)";
					$wpdb->query( $query );
			}
	}

	public static function addNotesIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_notes;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_notes_uid' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_notes_uid ON {$wpdb->prefix}ulp_notes(uid)";
					$wpdb->query( $query );
			}
			$doId = true;
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_notes_course_id' ){
							$doId = false;
					}
			}
			if ( $doId ){
          $query = "CREATE INDEX idx_ulp_notes_course_id ON {$wpdb->prefix}ulp_notes(course_id)";
					$wpdb->query( $query );
			}
	}

	public static function addStudentBadgesIndex()
	{
			global $wpdb;
      $query = "SHOW INDEX FROM {$wpdb->prefix}ulp_student_badges;";
			$indexList = $wpdb->get_results( $query );
			if ( !$indexList ){
					return;
			}
			foreach ( $indexList as $indexObject ){
					if ( isset( $indexObject->Key_name ) && $indexObject->Key_name == 'idx_ulp_student_badges_uid' ){
							return;
					}
			}
      $query = "CREATE INDEX idx_ulp_student_badges_uid ON {$wpdb->prefix}ulp_student_badges(uid)";
			$wpdb->query( $query );
	}

	public static function create_default_pages(){
			$insert_array = array(
							'ulp_default_page_list_courses' => array(
												'title' => esc_html__('List courses', 'ulp'),
												'content' => '[ulp-list-courses]',
							),
							'ulp_default_page_student_profile' => array(
												'title' => esc_html__('Student profile', 'ulp'),
												'content' => '[ulp-student-profile]',
							),
							'ulp_default_page_become_instructor' => array(
												'title' => esc_html__('Become instructor', 'ulp'),
												'content' => '[ulp-become-instructor]',
							),
							'ulp_default_page_list_watch_list' => array(
												'title' => esc_html__('List wish list', 'ulp'),
												'content' => '[ulp_list_watch_list]',
							),
							'ulp_default_page_checkout' => array(
												'title' => esc_html__('Course Checkout', 'ulp'),
												'content' => '[ulp_checkout]',
							),
							'ulp_default_page_instructor_dashboard' => array(
												'title' => esc_html__('Instructor Dashboard', 'ulp'),
												'content' => '[ulp-instructor-dashboard]',
							),
			);

			foreach ($insert_array as $key=>$inside_arr){
					$exists = get_option($key);
					if ($exists){
							continue;
					}
					$arr = array(
									'post_content' => $inside_arr['content'],
									'post_title' => $inside_arr['title'],
									'post_type' => 'page',
									'post_status' => 'publish',
					);
					$post_id = wp_insert_post($arr);
					update_option($key, $post_id);
			}
	}

  /**
	 * @param none
	 * @return array
	 */
	public static function get_all_prefixes_for_unistall()
	{
			global $wpdb;
			$data[] = $wpdb->base_prefix;
			if (is_multisite() ){
					$ids = self::get_all_blog_ids();
					if ( $ids ){
							foreach ($ids as $object){
										$data[] = $wpdb->base_prefix . $object->blog_id . '_';
							}
					}
			}
			return $data;
	}

  /**
   * @param none
   * @return none
   */
	public static function do_uninstall()
  {
      //delete tables
      global $wpdb;
      $tables = array(
               "ulp_courses_modules",
               "ulp_courses_modules_metas",
               "ulp_course_modules_items",
               "ulp_quizes_questions",
               "ulp_user_entities_relations",
               "ulp_user_entities_relations_metas",
               "ulp_activity",
               "ulp_reward_points",
               "ulp_reward_points_details",
               'ulp_order_meta',
               'ulp_student_certificate',
               'ulp_notes',
               "ulp_notifications",
               'ulp_dashboard_notifications',
               'ulp_student_badges',
               'ulp_badges',
      );
      $prefixes = self::get_all_prefixes_for_unistall();
      foreach ($prefixes as $the_table_prefix){
          foreach ($tables as $table){
              $the_table = $the_table_prefix . $table;
              $query = "DROP TABLE IF EXISTS $the_table;";
              $wpdb->query( $query );
          }

          /// delete posts !!!!!
          $post_types = self::plugin_post_types();
          foreach ($post_types as $post_type){
            $query = $wpdb->prepare( "
              DELETE a,b,c,d,e FROM {$the_table_prefix}posts a
                LEFT JOIN {$wpdb->prefix}term_relationships b ON (a.ID=b.object_id)
                LEFT JOIN {$wpdb->prefix}term_taxonomy c ON (c.term_taxonomy_id=b.term_taxonomy_id)
                LEFT JOIN {$wpdb->prefix}terms d ON (c.term_id = d.term_id)
                LEFT JOIN {$wpdb->prefix}postmeta e ON (a.ID=e.post_id)
                WHERE a.post_type=%s;
            ", $post_type );
            $wpdb->query( $query );
          }
      }

      if ( is_multisite() ){
          // multisite
          $blogs = self::get_all_blog_ids();
          foreach ( $blogs as $blogObject ){
            /// delete options
            switch_to_blog( $blogObject->blog_id );
            $opt_group = self::option_groups();
            foreach ($opt_group as $group_name){
                $data = self::getOptionMetaGroup($group_name);
                if ($data){
                  foreach ($data as $k=>$v){
                      delete_option($k);
                  }
                }
            }
            delete_option('ulp_course_difficulty_types');
            delete_option('ulp_plugin_version');
          }
      } else {
          /// SINGLE SITE

          /// delete options
          $opt_group = self::option_groups();
          foreach ($opt_group as $group_name){
              $data = self::getOptionMetaGroup($group_name);
              if ($data){
                foreach ($data as $k=>$v){
                    delete_option($k);
                }
              }
          }

          delete_option('ulp_course_difficulty_types');
          delete_option('ulp_plugin_version');
      }// end of single site

	}

	public static function save_settings_default_values(){
		$opt_group = self::option_groups();
		foreach ($opt_group as $group_name){
				$data = self::getOptionMetaGroup($group_name);
				if ($data){
					foreach ($data as $k=>$v){
							update_option($k, $v);
					}
				}
		}
	}

	public static function option_groups()
	{
			$array = array(
					'default_pages',
					'general_settings',
					'payment_settings',
					'woocommerce_payment',
					'gradebook',
					'ump_payment',
					'admin_workflow',
					'public_workflow',
					'access',
					'notification_settings',
					'redirects',
					'public_messages',
					'multiple_instructors',
					'course_reviews',
					'lesson_drip_content',
					'notes',
					'ulp_student_badges',
					'ulp_certificates',
					'watch_list',
					'mycred',
					'edd_payment',
					'invoices',
					'buddypress',
					'bbpress',
					'showcases_account_page',
					'licensing',
					'paypal',
					'paypal_magic_feat',
					'bt',
					'stripe',
					'stripe_magic_feat',
					'pushover',
					'course_difficulty',
					'course_time_period',
					'ulp_student_account_custom_tabs',
					'ulp_about_instructor',
					'students_also_bought',
					'more_courses_by',
			);
			return apply_filters( 'ulp_addon_action_before_print_admin_settings', $array );
	}


	public static function create_plugin_custom_roles(){
		$permissions = array(
			'read' => 1,
			'edit_posts' => 1,
			'upload_files' => 1,

			'delete_published_ulp_courses' => 1,
			'edit_published_ulp_courses' => 1,
			'edit_ulp_courses' => 1,
			'delete_ulp_courses' => 1,

			'delete_published_ulp_quizs' => 1,
			'edit_published_ulp_quizs' => 1,
			'edit_ulp_quizs' => 1,
			'delete_ulp_quizs' => 1,
			'publish_ulp_quizs' => 1,

			'delete_published_ulp_questions' => 1,
			'edit_published_ulp_questions' => 1,
			'edit_ulp_questions' => 1,
			'delete_ulp_questions' => 1,
			'publish_ulp_questions' => 1,

			'delete_published_ulp_lessons' => 1,
			'edit_published_ulp_lessons' => 1,
			'edit_ulp_lessons' => 1,
			'delete_ulp_lessons' => 1,
			'publish_ulp_lessons' => 1,

			'delete_published_ulp_announcements' => 1,
			'edit_published_ulp_announcements' => 1,
			'edit_ulp_announcements' => 1,
			'delete_ulp_announcements' => 1,
			'publish_ulp_announcements' => 1,
		);
		$seniorInstructorPermissions = $permissions;
		$seniorInstructorPermissions[ 'edit_published_posts' ] = 1;
		$seniorInstructorPermissions[ 'publish_ulp_courses' ] = 1;

		if (!get_role('ulp_instructor')){
				add_role('ulp_instructor', 'Instructor (Entry)', $permissions );
		} else {
				self::updateRoleLabel('ulp_instructor', 'Instructor (Entry)');
		}
		if (!get_role('ulp_instructor_senior')){
				add_role('ulp_instructor_senior', 'Instructor (Senior)', $seniorInstructorPermissions );
		}
		if (!get_role('ulp_instructor-pending')){
				add_role('ulp_instructor-pending', 'Instructor pending', array( 'read' => false, 'level_0' => true ) );
		}

		if (is_multisite()){
			global $wpdb;
			$table = $wpdb->base_prefix . 'blogs';
      $query = "SELECT blog_id FROM $table;";
			$data = $wpdb->get_results( $query );
			if ($data){
				foreach ($data as $object){
					if (!empty($object->blog_id) && $object->blog_id>1){
						$prefix = $wpdb->base_prefix . $object->blog_id . '_' ;
						$table = $prefix . 'options';
						$option = $prefix . 'user_roles';
            $query = $wpdb->prepare( "SELECT option_value FROM $table WHERE option_name=%s ;", $option );
						$temp_data = $wpdb->get_row( $query );
						if ($temp_data && !empty($temp_data->option_value)){
							$array_unserialize = unserialize($temp_data->option_value);

							if (empty($array_unserialize['ulp_instructor-pending'])){
								$array_unserialize['ulp_instructor-pending'] = array(
																			'name' => 'Instructor pending',
																			'capabilities' => array( 'read' => false, 'level_0' => true ),
								);
							}
							if (empty($array_unserialize['ulp_instructor'])){
								$array_unserialize['ulp_instructor'] = array(
																			'name' => 'Instructor (Entry)',
																			'capabilities' => $permissions,
								);
							}
							if (empty($array_unserialize['ulp_instructor_senior'])){
								$array_unserialize['ulp_instructor_senior'] = array(
																			'name' => 'Instructor (Senior)',
																			'capabilities' => $seniorInstructorPermissions,
								);
							}

							$array_serialize = serialize($array_unserialize);
              $query = $wpdb->prepare( "UPDATE $table SET option_value=%s WHERE option_name=%s ; ", $array_serialize, $option );
							$wpdb->query( $query );

						}
					}
				}
			}

		}
	}

	public static function updateRoleLabel($slug='', $newName='')
	{
			global $wpdb;
			$prefix = $wpdb->base_prefix;
			$option = $prefix . 'user_roles';
      $query = $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name=%s ;", $option );
			$data = $wpdb->get_var( $query );
			$data = unserialize($data);
			if ($data[$slug]){
					$data[$slug]['name'] = $newName;
					$newData = serialize($data);
          $query = $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value=%s WHERE option_name=%s ;", $newData, $option );
					$wpdb->query( $query );
			}
	}


	/**
	 * @param none
	 * @return array
	 */
	public static function getAllPostTypes(){
		global $wpdb;
		$array = array();
    $query = "SELECT DISTINCT post_type FROM {$wpdb->posts} WHERE post_status='publish';";
		$data = $wpdb->get_results( $query );
		if ($data && count($data)){
			foreach ($data as $obj){
				$array[] = $obj->post_type;
			}
			$exclude = array('bp-email', 'edd_log', 'nav_menu_item', 'bp-email');
			foreach ($exclude as $e){
				$k = array_search($e, $array);
				if ($k!==FALSE){
					unset($array[$k]);
					unset($k);
				}
			}
		}
		return $array;
	}


	public static function change_post_status($post_id=0, $post_status=''){
			global $wpdb;
			$post_id = sanitize_text_field($post_id);
			$post_status = sanitize_text_field($post_status);
      $update = $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status=%s WHERE ID=%d ;", $post_status, $post_id );
			return $wpdb->query( $update );
	}


	/**
	 * @param, string
	 * @param string
	 * @return int or bool (FALSE)
	 */
	public static function getPostIdByTypeAndName($custom_post_type='', $post_name=''){
		global $wpdb;
		$custom_post_type = sanitize_text_field($custom_post_type);
		$post_name = sanitize_text_field($post_name);
		$q = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_name=%s ", $custom_post_type, $post_name);
		return $wpdb->get_var($q);
	}

	public static function getPostIdByName($post_name='')
	{
		global $wpdb;
		if (empty($post_name)){
				return 0;
		}
		$post_name = sanitize_text_field($post_name);
		$q = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s ", $post_name);
		return $wpdb->get_var($q);
	}

	/**
	 * @param int
	 * @return string
	 */
	public static function getPostTypeById($post_id=0){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
		$q = $wpdb->prepare("SELECT post_type FROM {$wpdb->posts} WHERE ID=%d ", $post_id);
		return $wpdb->get_var($q);
	}


	/**
	 * @param int
	 * @return string
	 */
	public static function getPostNameById($post_id=0){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
		$q = $wpdb->prepare("SELECT post_name FROM {$wpdb->posts} WHERE ID=%d ", $post_id);
		return $wpdb->get_var($q);
	}


	/**
	 * @param none
	 * @return array
	 */
	public static function getAllQuestions($checkUser=false){
		global $wpdb;
		$q = "SELECT ID, post_title, post_content,post_type FROM {$wpdb->posts} WHERE post_type='ulp_question' AND post_status NOT IN ('trash', 'auto-draft')";
		if ($checkUser){
			$uid = ulp_get_current_user();
			$roles = self::get_user_roles($uid);
			if (!isset($roles['administrator']) && ( isset($roles['ulp_instructor']) || isset($roles['ulp_instructor_senior']) || isset($roles['ulp_instructor-pending']) ) ){
					$q .= " AND post_author=$uid ";
			}
		}
		return indeed_convert_to_array($wpdb->get_results($q));
	}


	/**
	 * @param none
	 * @return array
	 */
	public static function getAllQuizes(){
		global $wpdb;
		$q = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='ulp_quiz' AND post_status NOT IN ('trash', 'auto-draft')";
		return indeed_convert_to_array($wpdb->get_results($q));
	}

	/**
	 * @param none
	 * @return array
	 */
	public static function getAllLessons(){
		global $wpdb;
		$q = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='ulp_lesson' AND post_status NOT IN ('trash', 'auto-draft')";
		return indeed_convert_to_array($wpdb->get_results($q));
	}


	/**
	 * @param int
	 * @return array
	 */
	public static function getAllCourses($limit=0, $offset=0){
			global $wpdb;
			$q = "SELECT ID, post_title, post_excerpt FROM {$wpdb->posts} WHERE post_type='ulp_course' AND post_status NOT IN ('trash', 'auto-draft', 'pending', 'draft')";
			if ($limit){
				$limit = sanitize_text_field($limit);
				$offset = sanitize_text_field($offset);
				$q .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
			}
			$courses = indeed_convert_to_array($wpdb->get_results($q));
			$courses = apply_filters( 'ulp_filter_select_courses', $courses, $limit, $offset );
			return $courses;
	}

	public static function getAllQuizesAndLessons($checkUser=false){
			global $wpdb;
			$q = "SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE
								(post_type='ulp_lesson' OR post_type='ulp_quiz')
								AND post_status NOT IN ('trash', 'auto-draft')
			";

			if ($checkUser){
					$uid = ulp_get_current_user();
					$roles = self::get_user_roles($uid);
					if (!isset($roles['administrator']) && (isset($roles['ulp_instructor']) || isset($roles['ulp_instructor_senior']) || isset($roles['ulp_instructor-pending']) ) ) {
							$q .= $wpdb->prepare( " AND post_author=%d ", $uid );
					}
			}

			return indeed_convert_to_array($wpdb->get_results($q));
	}

	public static function countCourses(){
			global $wpdb;
			$q = "SELECT IFNULL(COUNT(ID), 0) as c FROM {$wpdb->posts} WHERE post_type='ulp_course' AND post_status NOT IN ('trash', 'auto-draft', 'pending', 'draft')";
			$number = $wpdb->get_var($q);
			$number = apply_filters( 'ulp_filter_count_all_courses', $number );
			return $number;
	}

	public static function selectCoursesByLanguage( $lang='', $limit=0, $offset=0 )
	{
		global $wpdb;
		$q = $wpdb->prepare( "SELECT a.ID, a.post_title, a.post_excerpt
          										FROM {$wpdb->posts} a
          										INNER JOIN {$wpdb->prefix}icl_translations b
          										ON a.ID=b.element_id
          										WHERE
          										a.post_type='ulp_course'
          										AND
          										a.post_status NOT IN ('trash', 'auto-draft', 'pending')
          										AND
          										b.language_code=%s
		", $lang );
		if ($limit){
			$limit = sanitize_text_field($limit);
			$offset = sanitize_text_field($offset);
			$q .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
		}
		$courses = indeed_convert_to_array($wpdb->get_results($q));
		return $courses;
	}

	public static function countCoursesByLanguage( $lang='' )
	{
			global $wpdb;
			$q = $wpdb->prepare( "SELECT IFNULL(COUNT(a.ID), 0) as c FROM {$wpdb->posts} a
              									INNER JOIN {$wpdb->prefix}icl_translations b
              									ON a.ID=b.element_id
          											WHERE
          											a.post_type='ulp_course'
          											AND a.post_status NOT IN ('trash', 'auto-draft', 'pending')
          											AND b.language_code=%s
			", $lang );
			$number = $wpdb->get_var($q);
			return $number;
	}


	/**
	 * @param int (post id)
	 * @param string (what to select)
	 * @return array
	 */
	public static function select_post_data($post_id=0, $select=''){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
		$q = $wpdb->prepare( "SELECT $select FROM {$wpdb->posts} WHERE ID=%d ", $post_id );
		return (array)$wpdb->get_row($q);
	}


	/**
	 * @param int
	 * @return array
	 */
	public static function getQuizesForQuestionId($question_id=0){
		global $wpdb;
		$question_id = sanitize_text_field($question_id);
		$table = $wpdb->prefix . 'ulp_quizes_questions';
    $query = $wpdb->prepare( "SELECT quiz_id FROM $table WHERE question_id=%d;", $question_id );
		return indeed_convert_to_array( $wpdb->get_results( $query ) );
	}


	/**
	 * @param int
	 * @return array
	 */
	public static function getCoursesForQuizId($quiz_id=0){
		global $wpdb;
		$quiz_id = sanitize_text_field($quiz_id);
    $query = $wpdb->prepare( "SELECT ucm.course_id FROM
									{$wpdb->prefix}ulp_courses_modules ucm
									INNER JOIN {$wpdb->prefix}ulp_course_modules_items ucmi
									ON ucm.module_id=ucmi.module_id
									WHERE ucmi.item_id=%d ", $quiz_id );
		return indeed_convert_to_array( $wpdb->get_results( $query ) );
	}

	/**
	 * @param int
	 * @return string
	 */
	public static function getPostTitleByPostId($post_id=0){
		global $wpdb;
		if (!$post_id){
				return '';
		}
		$post_id = sanitize_text_field($post_id);
    $query = $wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID=%d", $post_id);
		return $wpdb->get_var( $query );
	}

	public static function getCourseNameByEntityId($entityId=0)
	{
		global $wpdb;
		$entityId = sanitize_text_field($entityId);
		$q = $wpdb->prepare( "SELECT a.post_title FROM {$wpdb->posts} a
															INNER JOIN {$wpdb->prefix}ulp_user_entities_relations b
															ON a.ID=b.entity_id
															WHERE b.id=%d ", $entityId );
		return $wpdb->get_var($q);
	}

	public static function getCourseIdByEntityId($id=0)
	{
			global $wpdb;
			$id = sanitize_text_field($id);
      $query = $wpdb->prepare( "SELECT entity_id FROM {$wpdb->prefix}ulp_user_entities_relations WHERE id=%d;", $id );
			$courseId = $wpdb->get_var( $query );
			return $courseId;
	}


	/**
	 * @param int
	 * @return string
	 */
	public static function getPostContentByPostId($post_id=0){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
    $query = $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID=%d ;", $post_id );
		return $wpdb->get_var( $query );
	}


	/**
	 * @param int (post id)
	 * @return string
	 */
	public static function getPostCreateDate($post_id=0){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
    $query = $wpdb->prepare( "SELECT post_date FROM {$wpdb->posts} WHERE ID=%d ;", $post_id );
		return $wpdb->get_var( $query );
	}

	/**
	 * @param int (post id)
	 * @return string
	 */
	public static function getPostExcerpt($post_id=0){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
    $query = $wpdb->prepare( "SELECT post_excerpt FROM {$wpdb->posts} WHERE ID=%d ;", $post_id );
		return $wpdb->get_var( $query );
	}



	public static function getAuthorImage($uid=0){
		$avatar = '';
		/// GRAVATAR
		$uid = sanitize_text_field($uid);

		$ulp_avatar = get_user_meta($uid, 'ulp_avatar', TRUE);

		if ( $ulp_avatar != ''){
		  if (strpos($ulp_avatar, "http")===0){
			  $avatar = $ulp_avatar;
		  } else {
				$avatar = self::getMediaBaseImage( $ulp_avatar );
				if ( $avatar && strpos($avatar, "http")===0 ){
						return $avatar;
				}
			  $data_ing = wp_get_attachment_image_src($ulp_avatar);
			  if (!empty($data_ing[0])){
				  $avatar = $data_ing[0];
			  }
		  }
		}

		if($avatar == ''){
			if (function_exists('get_avatar_url')){
        try {
  				$avatar = get_avatar_url($uid, ['size' => 96, 'default' => ULP_URL . 'assets/images/no-avatar.png']);
        } catch ( \Exception $e ){
            return ULP_URL . 'assets/images/no-avatar.png';
        }
			} else if (function_exists('get_avatar')){
				/// < wp 4.2
					$avatar = get_avatar($uid);
					preg_match("/src='(.*?)'/i", $avatar, $matches);
					$avatar = $matches[1];
			}
			if (empty($avatar)){
					$avatar = ULP_URL . 'assets/images/no-avatar.png';
			}
		}
		return $avatar;
	}


	/**
	 * @param int (post id)
	 * @return string
	 */
	public static function getPostAuthor($post_id=0){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
    $query = $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE ID=%d ;", $post_id );
		$uid = $wpdb->get_var( $query );
		return $uid;
	}


	/**
	 * @param int
	 * @param string
	 * @param bool
	 * @return array
	 */
	public static function getPostMetaGroup($post_id=0, $group='', $with_values=TRUE){
		$array = array();
		switch ($group){
			case 'answer_settings':
				$array = array(
							'answer_type' => 1,
							'answer_value' => 'exemple 1',
							'answer_value_required' => 'exemple 1',
							'answers_multiple_answers_possible_values' => '',
							'answers_multiple_answers_correct_answers' => '',
							'answers_single_answer_possible_values' => '',
							'answers_single_answer_correct_value' => '',
							'answer_value_for_bool' => 1,
							'answer_value_for_essay' => '',
							'answers_sorting_type' => '',

							'image_answers_single_answer_possible_values' => '',
							'image_answers_single_answer_correct_value' => '',
							'image_answers_multiple_answers_possible_values' => '',
							'image_answers_multiple_answers_correct_answers' => '',

							'matching_micro_questions' => [],
							'matching_micro_questions_answers' => [],
				);
				break;
			case 'lesson_special_settings':
				$array = array(
								'ulp_lesson_duration' => '',
								'ulp_lesson_duration_type' => '',
								'ulp_lesson_preview' => 0,
								'ulp_lesson_show_back_to_course_link' => 1,
								'ulp_post_reward_points' => 10,
 				);
				break;
			case 'questions_special_settings':
				$array = array(
								'ulp_question_hint' => '',
								'ulp_question_explanation' => '',
								'ulp_question_points' => 5,
				);
				break;
			case 'quiz_special_settings':
				$array = array(
								'retake_limit' => 3,
								'quiz_time' => 30,/// in minutes
								'quiz_workflow' => 'default',
								'enable_back_button' => 1,
								'ulp_quiz_show_explanation' => 1,
								'ulp_quiz_show_hint' => 1,
								'ulp_quiz_grade_type' => 'percentage',
								'ulp_quiz_grade_value' => 50,
								'ulp_post_reward_points' => 20,
								'ulp_quiz_display_questions_random' => 1,
								'ulp_quiz_display_answers_random' => 0,
				);
				break;
			case 'course_special_settings':
				$array = array(
								'ulp_course_duration' => 4,
								'ulp_course_duration_type' => 'w',
								'ulp_course_time_period_duration' => 4,
								'ulp_course_time_period_duration_type' => 'w',
								'ulp_course_prerequest_courses' => '',
								'ulp_course_prerequest_reward_points' => 0,
								'ulp_course_initial_price' => '',
								'ulp_course_max_students' => 100,
								'ulp_course_retake_course' => '',
								'ulp_course_featured' => '',
								'ulp_modules_order_items_by' => 'default',
								'ulp_modules_order_items_type' => 'ASC',
								'ulp_modules_per_page' => 10,
								'ulp_course_payment' => 0,
								'ulp_course_price' => 0,
								'ulp_course_assessments' => 'lessons',
								'ulp_course_assessments_passing_value' => 90,
								'ulp_course_quizes_min_grade' => 50,
								'ulp_post_reward_points' => 50,
								'ulp_course_access_item_only_if_prev' => 0,
								'ulp_course_difficulty' => '',
								/// coming soon special settings
								'ulp_course_coming_soon_enabled' => 0,
								'ulp_course_coming_soon_message' => '<h2 class="ulp-text-aling-center">This course is Coming Soon</h2>
<h4 class="ulp-text-aling-center">Please save it into your WishList and follow the updates</h4>',
								'ulp_course_coming_soon_end_time' => '',
								'ulp_course_coming_soon_show_count_down' => 0,
				);
				break;
			case 'drip_content':
				$array = array(
								'ulp_drip_content' => '',
								'ulp_drip_start_type' => 1,
								'ulp_drip_start_numeric_type' => '',
								'ulp_drip_start_numeric_value' => '',
								'ulp_drip_start_certain_date' => '',
				);
				break;
			case 'video_lesson_settings':
				$array = [
						'ulp_lesson_is_video'							=> 0,
						'ulp_lesson_video_target'					=> '',
						'ulp_lesson_video_autoplay'				=> 0,
						'ulp_lesson_video_autocomplete'		=> 0,
						'ulp_lesson_video_width'					=> 450,
						'ulp_lesson_video_height'					=> 390,
				];
				break;
		}
		if ($with_values && $array && $post_id){
			foreach ($array as $key=>$value){
				$temp = self::get_post_meta($post_id, $key, TRUE);
				$temp = maybe_unserialize($temp);

				if ($temp!==null){
						$array[$key] = $temp;
				}
			}
		}
		return $array;
	}


	public static function get_post_meta($postId=0, $metaKey='')
	{
			global $wpdb;
			$query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s ;", $postId, $metaKey );
			return $wpdb->get_var($query);
	}

	public static function postStatus($postId=0)
	{
			if (empty($postId)){
					return false;
			}
			global $wpdb;
			$query = $wpdb->prepare( "SELECT post_status FROM {$wpdb->posts} WHERE ID=%d ;", $postId );
			return $wpdb->get_var($query);
	}


	public static function update_post_meta_group( $group_name='', $post_id=0, $post_data=array() ){
			$group = self::getPostMetaGroup($post_id, $group_name);
			if ($group){
					foreach ($group as $item_name=>$item_value){
							if (isset($post_data[$item_name])){
									update_post_meta($post_id, $item_name, $post_data[$item_name]);
							}
					}
			}
	}

	/*
	 * When you add a new group into this function please add the name of group into option_groups() too.
	 */
	public static function getOptionMetaGroup($group='', $with_values=TRUE){
		$array = array();
		switch ($group){
			case 'default_pages':
				$array = array(
						'ulp_default_page_list_courses' => -1,
						'ulp_default_page_student_profile' => -1,
						'ulp_default_page_become_instructor' => -1,
						'ulp_default_page_list_watch_list' => -1,
						'ulp_default_page_checkout' => -1,
						'ulp_default_page_instructor_dashboard' => -1,
				);
				break;
			case 'general_settings':
				$array = array(
						'ulp_course_progress_type' 				=> 'completed_lessons_and_quizes',
						'ulp_course_auto_enroll' 					=> 1,
						'ulp_enable_gutenberg'						=> 0,
				);
				break;
			case 'payment_settings':
				$array = array(
						'ulp_currency' => 'USD',
						'ulp_custom_currency_code'	=> '',
						'ulp_currency_position' => 'right',
						'ulp_thousands_separator' => ',',
						'ulp_decimals_separator' => '.',
						'ulp_num_of_decimals' => 2,
						'ulp_first_priority_payment_type' => 'checkout',
						'ulp_default_payment_type' => 'checkout',
						'ulp_order_prefix_code' => 'ULP',
				);
				break;
			case 'public_messages':
				$array = array(
						/// enroll
						'ulp_messages_enroll_error_user_not_logged' => esc_html__('You cannot enroll on this course because you are not logged in.', 'ulp'),
						'ulp_messages_enroll_error_on_maximum_num_of_students' => esc_html__('You cannot enroll on this course because the maximum number of students has been reached.', 'ulp'),
						'ulp_messages_course_prerequest_courses' => esc_html__("You cannot enroll on this course because you don't have the pre-request courses.", 'ulp'),
						'ulp_messages_enroll_error_user_is_already_enroll' => esc_html__("You're already enrolled on this course", 'ulp'),
						'ulp_messages_course_prerequest_reward_points' => esc_html__("You cannot enroll on this course because you don't have the minimal number of reward points.", 'ulp'),
						'ulp_messages_enroll_error_retake_course_limit' => esc_html__('You have reach the retake course limit!', 'ulp'),

						/// become instructor
						'ulp_messages_become_instructor_user_not_logged' => esc_html__('In order to become Instructor, you must be logged.', 'ulp'),
						'ulp_messages_become_instructor_button' => esc_html__('Become instructor', 'ulp'),
						'ulp_messages_become_instructor_already_registered' => esc_html__('You are already registered as Instructor.', 'ulp'),
						'ulp_messages_become_instructor_pending' => esc_html__('Your request to become instructor is pending.', 'ulp'),

						/// checkout
						'ulp_messages_checkout_amount' => esc_html__('Amount', 'ulp'),
						'ulp_messages_checkout_payment_type' => esc_html__('Payment type', 'ulp'),
						'ulp_messages_checkout_user_not_logged' => esc_html__('In order to complete this purchase you must be logged in.', 'ulp'),

						/// badges
						'ulp_messages_list_badges_title' => '',

						/// courses
						'ulp_messages_list_courses_not_enrolled' => esc_html__("You are not Enrolled on this course", 'ulp'),
						'ulp_messages_buy_course_bttn' => esc_html__('Buy this course!', 'ulp'),

						/// list notes
						'ulp_messages_list_notes_zero' => esc_html__("You din't save any Note yet!", 'ulp'),

						/// quizes
						'ulp_messages_quiz_not_completed' => esc_html__("You haven't completed this quiz yet!", 'ulp'),
						'ulp_messages_quiz_result' => esc_html__('Your result on this quiz is:', 'ulp'),

						/// stripe
						'ulp_messages_stripe_completed' => esc_html__('Payment completed!', 'ulp'),
						'ulp_messages_stripe_not_completed' => esc_html__('An error has occurred, please try again!', 'ulp'),

				);
				break;
			case 'woocommerce_payment':
				$array = array(
						'ulp_woocommerce_payment_enable' => 0,
						'ulp_default_payment_type' => '',
				);
				break;
			case 'gradebook':
				$array = [
						'ulp_gradebook_enable' => 0,
				];
				break;
			case 'ump_payment':
				$array = array(
						'ulp_ump_payment_enable' => 0,
						'ulp_default_payment_type' => '',
				);
				break;
			case 'admin_workflow':
				$array = array(
							'ulp_dashboard_notifications' => 1,
							'ulp_show_special_settings_for_entry_instructors' => 0,
							'ulp_keep_data_after_delete'	=> 0,
				);
				break;
			case 'public_workflow':
				$array = array(
							'ulp_singlecourse_imagesize_width' => 1160,
							'ulp_singlecourse_imagesize_height' => 300,
							'ulp_multiplecourses_imagesize_width' => 430,
							'ulp_multiplecourses_imagesize_height' => 340,
							'ulp_course_custom_query_var' => 'single-course',
							'ulp_quiz_custom_query_var' => 'course-quiz',
							'ulp_lesson_custom_query_var' => 'course-lesson',
							'ulp_question_custom_query_var' => 'quiz-question',
							'ulp_announcement_custom_query_var' => 'course-announcement',
							'ulp_qanda_custom_query_var' => 'course-qanda',
							'ulp_show_curriculum_as_tab' => 1,
				);
				break;
			case 'access':
				$array = array(
							'ulp_dashboard_allowed_roles' => '',
				);
				break;
			case 'notification_settings':
				$array = array(
						'ulp_notifications_from_email_addr' => '',
						'ulp_notifications_from_name' => '',
						'ulp_notifications_admin_email' => '',
				);
				break;
			case 'redirects':
				$array =  array(
						'ulp_default_redirect' 													=> get_home_url(),
						'ulp_user_profile_redirect'											=> get_home_url(),
						'ulp_unregistered_user_try_to_buy_redirect'			=> get_home_url(),
				);
				break;
			case 'multiple_instructors':
				$array = array(
						'ulp_multiple_instructors_enable' => 1,
				);
				break;
			case 'course_reviews':
				$array = array(
						'ulp_course_reviews_enabled' => 0,
						'ulp_course_reviews_limit_one' => 1,
				);
				break;
			case 'lesson_drip_content':
				$array = array(
						'lesson_drip_content_enable' => 0,
				);
				break;
			case 'notes':
				$array = array(
						'lesson_notes_enable' => 1,
				);
				break;
			case 'ulp_student_badges':
				$array = [
						'ulp_student_badges_enable' => 1,
				];
				break;
			case 'ulp_certificates':
				$array = [
						'ulp_certificates_enable' => 0,
				];
				break;
			case 'watch_list':
				$array = [
						'ulp_watch_list_enable' => 1,
				];
				break;
			case 'mycred':
				$array = [
						'ulp_mycred_enable' => 0,
				];
				break;
			case 'edd_payment':
				$array = [
						'ulp_edd_payment_enable' => 0,
						'ulp_default_payment_type' => '',
				];
				break;
			case 'invoices':
				$array = [
						'ulp_invoices_enable' => 1,
						'ulp_invoices_only_completed_payments' => 0,
						'ulp_invoices_template' => 'ulp-invoice-template-2',
						'ulp_invoices_logo' => ULP_URL . 'assets/images/default-logo1.png',
						'ulp_invoices_title' => 'Your Order Invoice',
						'ulp_invoices_company_field' => '<div><b>Your CompanyName LLC</b></div>
<div>Unique Code: #99991239</div>
<div>Company Address: Your Email Address</div>',
						'ulp_invoices_bill_to' => '<div><b>Bill to</b></div>
<div><b>Name: </b>{first_name} {last_name} </div>
<div><b>E-mail: </b>{user_email} </div>',
						'ulp_invoices_footer' => 'If you have any questions about this Invoice, please contact us!',
						'ulp_invoices_custom_css' => '',
				];
				break;
			case 'buddypress':
				$array = [
						'ulp_buddypress_integration_enable' => 0,
						'ulp_buddypress_menu_label' => 'Ultimate Learning Pro',
						'ulp_buddypress_menu_possition' => 5,
				];
				break;
			case 'bbpress':
				$array = [
						'ulp_bbpress_integration_enable' => 0,
				];
				break;
			case 'showcases_account_page':
				$array = [
						'ulp_ap_footer_msg' => 'Ultimate Learning Pro - the best WordPress learning system',
						'ulp_account_page_custom_css' => '',
						'ulp_ap_edit_show_avatar' => 1,
						'ulp_ap_edit_show_points' => 1,
						'ulp_ap_edit_show_badges' => 1,
						'ulp_ap_top_template' => 'ulp-ap-top-theme-2',
						'ulp_ap_edit_background' => 1,
						'ulp_ap_top_background_image' => ULP_URL . 'assets/images/top_ulp_bk_4.png',
						'ulp_ap_welcome_msg' => '<span class="ulp-user-page-mess-special">Hello</span> <span class="ulp-user-page-name"> {last_name} {first_name}</span>,
														<span class="ulp-user-page-mess">you\'re logged as</span><span class="ulp-user-page-mess-special"> {username}</span>
														<div class="ulp-user-page-mess">Member since {user_registered}</div>
														',
						'ulp_ap_theme' => 'ulp-ap-theme-3',
						'ulp_ap_tabs' => 'overview,profile,my_courses,wish_list,notes,orders,help',

						'ulp_ap_overview_menu_label' => 'Dashboard',
						'ulp_ap_overview_title' => 'Dashboard',
						'ulp_ap_overview_msg' => '',
						'ulp_ap_overview_icon_class' => '',
						'ulp_ap_overview_icon_code' => 'f015',

						'ulp_ap_profile_menu_label' => 'Profile',
						'ulp_ap_profile_title' => 'My Profile',
						'ulp_ap_profile_msg' => '',
						'ulp_ap_profile_icon_class' => '',
						'ulp_ap_profile_icon_code' => 'f007',

						'ulp_ap_my_courses_menu_label' => 'My Courses',
						'ulp_ap_my_courses_title' => 'My Courses',
						'ulp_ap_my_courses_msg' => '',
						'ulp_ap_my_courses_icon_class' => '',
						'ulp_ap_my_courses_icon_code' => 'f19c',

						'ulp_ap_wish_list_menu_label' => 'Wish List',
						'ulp_ap_wish_list_title' => 'Wish List',
						'ulp_ap_wish_list_msg' => '[ulp_list_watch_list]',
						'ulp_ap_wish_list_icon_class' => '',
						'ulp_ap_wish_list_icon_code' => 'f005',

						'ulp_ap_notes_menu_label' => 'Notes',
						'ulp_ap_notes_title' => 'Notes',
						'ulp_ap_notes_msg' => '[ulp_list_notes]',
						'ulp_ap_notes_icon_class' => '',
						'ulp_ap_notes_icon_code' => 'f24a',

						'ulp_ap_orders_menu_label' => 'Orders',
						'ulp_ap_orders_title' => 'Orders',
						'ulp_ap_orders_msg' => 'Check out purchases history.',
						'ulp_ap_orders_icon_class' => '',
						'ulp_ap_orders_icon_code' => 'f09d',

						'ulp_ap_reward_points_menu_label' => 'Reward Points',
						'ulp_ap_reward_points_title' => 'Reward Points',
						'ulp_ap_reward_points_msg' => 'Hey {username}, good job!

You have a total of [ulp-reward-points] points, keep on learning!',
						'ulp_ap_reward_points_icon_class' => '',
						'ulp_ap_reward_points_icon_code' => 'f06b',

						'ulp_ap_help_menu_label' => 'Help',
						'ulp_ap_help_title' => 'Help',
						'ulp_ap_help_msg' => '<p>Thank you for using <strong>our Learning platform</strong>, this section is here to help you with the basics and also serves as an example of how a course is made.</p>

<p>If this is your first time using a Learning Management System, you can follow this section to get a basic idea of how it works.

A course is a collection of Lessons and Quizzes grouped together in Sections. A quiz is a collection of questions.</p>',
						'ulp_ap_help_icon_class' => '',
						'ulp_ap_help_icon_code' => 'f059',

						'ulp_ap_list_certificates_menu_label' => 'List certificates',
						'ulp_ap_list_certificates_title' => 'List certificates',
						'ulp_ap_list_certificates_msg' => '[ulp-list-certificates]',
						'ulp_ap_list_certificates_icon_class' => '',
						'ulp_ap_list_certificates_icon_code' => 'f0a3',
				];
				break;
			case 'licensing':
				$array = [
						'ulp_license_set' => '',
						'ulp_envato_code' => '',
				];
				break;
			case 'paypal':
				$array = [
						'ulp_paypal_sandbox' => 1,
						'ulp_paypal_email' => '',
						'ulp_paypal_label' => 'PayPal',
						'ulp_paypal_description' => 'Use your PayPal deposit or pay via Credit Card if you do not have a PayPal account',
						'ulp_paypal_multipayment_order' => 2,
				];
				break;
			case 'paypal_magic_feat':
				$array = [
						'ulp_paypal_enable' => 1,
				];
				break;
			case 'stripe_magic_feat':
				$array = array(
						'ulp_stripe_payment_enable' => 0,
				);
				break;
			case 'stripe':
				$array = array(
						'ulp_stripe_label' => 'Stripe',
						'ulp_stripe_description' => 'Pay with your Credit Card',
						'ulp_stripe_multipayment_order' => 3,
						'ulp_stripe_secret_key' => '',
						'ulp_stripe_publishable_key' => '',
				);
				break;
			case '2checkout_magic_feat':
				$array = [
						'ulp_2checkout_payment_enable' => 0,
				];
				break;
			case '2checkout':
				$array = [
						'ulp_2checkout_sandbox_on' 				 => 0,
						'ulp_2checkout_label' 						 => '2Checkout',
						'ulp_2checkout_description' 			 => 'Pay with your Credit Card',
						'ulp_2checkout_multipayment_order' => 4,
						'ulp_2checkout_api_username' 			 => '',
						'ulp_2checkout_api_password' 			 => '',
						'ulp_2checkout_api_private_key' 	 => '',
						'ulp_2checkout_account_number' 		 => '',
						'ulp_2checkout_secret_word' 			 => '',
				];
				break;
			case 'bt':
				$array = [
						'ulp_bt_enable' => 1,
						'ulp_bt_message' => '<p>Hi {username},</p>
<br/>
<p>Please proceed the bank transfer payment for: {currency}{amount}</p>

<p><strong>Payment Details:</strong> Course {course_name} for {username} with Identification: {user_id}_{course_id}</p>

<br/>

<strong>Bank Details:</strong><br/>

IBAN:xxxxxxxxxxxxxxxxxxxx<br/>

Bank Name<br/>',
						'ulp_bt_label' => 'Bank Transfer',
						'ulp_bt_description' => 'Complete payment via a Bank transfer',
						'ulp_bt_multipayment_order' => 1,
				];
				break;
			case 'pushover':
				$array = [
						'ulp_pushover_enable' => 0,
						'ulp_pushover_app_token' => '',
						'ulp_pushover_app_token' => '',
						'ulp_pushover_url' => '',
						'ulp_pushover_url_title' => '',
						'ulp_pushover_sound' => 'classical',
				];
				break;
			case 'course_difficulty':
				$array = [
						'ulp_course_difficulty_enable' => 1,
				];
				break;
			case 'course_time_period':
				$array = [
						'ulp_course_time_period_enable' => 1,
				];
				break;
			case 'ulp_student_account_custom_tabs':
				$array = [
						'ulp_student_account_custom_tabs_enabled' => 1,
						'ulp_account_page_menu_order' => [],
				];
				break;
			case 'ulp_about_instructor':
				$array = [
						'ulp_about_the_instructor_mf' => 0,
				];
				break;
			case 'students_also_bought':
				$array = [
						'ulp_student_also_bought_enable' => 0,
						'ulp_student_also_bought_limit' => 5,
						'ulp_student_also_bought_minimum_limit' => 2,
				];
				break;
			case 'more_courses_by':
				$array = [
							'ulp_more_courses_by_enabled' => 0,
				];
				break;
			case 'coming_soon':
				$array = [
							'ulp_coming_soon_enabled' => 1,
				];
				break;
			case 'announcements':
				$array = [
							'ulp_announcements_enabled' => 0,
				];
				break;
			case 'qanda':
				$array = [
							'ulp_qanda_enabled' => 0,
				];
				break;
			case 'frontendcourse':
				$array = [
							'ulp_frontendcourse_enabled' => 0,
				];
				break;
			case 'curriculum_slider':
				$array = [
							'ulp_curriculum_slider_enabled'				=> 0,
							'ulp_curriculum_slider_label'					=> esc_html__( 'Course Curriculum', 'ulp' ),
							'ulp_curriculum_slider_custom_css'		=> '',
				];
				break;
		}
		$array = apply_filters( 'ulp_default_options_group_filter', $array, $group );
		if ($with_values && $array){
			foreach ($array as $key=>$value){
				$temp = get_option($key);
				if ($temp!==FALSE){
					$array[$key] = $temp;
				}
			}
		}
		return $array;
	}

	public static function get_all_ulp_db_options(){
			$groups = self::option_groups();
			$return_data = array();
			if ($groups){
				foreach ($groups as $group){
					$data = self::getOptionMetaGroup($group, TRUE);
					$return_data = array_merge($return_data, $data);
				}
			}
			return $return_data;
	}


	public static function account_page_get_tabs($only_standard=FALSE, $only_visible=FALSE){
			$available_tabs = array(
									'overview'=> ['label' => esc_html__('Overview', 'ulp'), 'icon' => 'f015', 'icon_class' => ''],
									'profile'=> ['label' => esc_html__('Profile', 'ulp'), 'icon' => 'f007', 'icon_class' => ''],
									'my_courses' => ['label' => esc_html__('My Courses'), 'icon' => 'f19c', 'icon_class' => ''],
									'wish_list' => ['label' => esc_html__('Wish List'), 'icon' => 'f005', 'icon_class' => ''],
									'notes' => ['label' => esc_html__('Notes', 'ulp'), 'icon' => 'f24a', 'icon_class' => ''],
									'orders' => ['label' => esc_html__('Orders', 'ulp'), 'icon' => 'f09d', 'icon_class' => ''],
									'list_certificates' => ['label' => esc_html__('List certificates'), 'icon' => 'f0a3', 'icon_class' => ''],
									'help' => ['label' => esc_html__('Help', 'ulp'), 'icon' => 'f059', 'icon_class' => ''],
			);
			$tabs_to_show = get_option('ulp_ap_tabs');
			$tabs_to_show = explode(',', $tabs_to_show);
			if ($only_standard){
					return $available_tabs;
			}
			$custom_available_tabs = self::account_page_menu_get_custom_items();
			if (!empty($custom_available_tabs)){
					$available_tabs = array_merge($available_tabs, $custom_available_tabs);
			}


			foreach ($available_tabs as $slug=>$array_data){
					if ($only_visible && !in_array($slug, $tabs_to_show)) {
							unset($available_tabs [$slug]);
							continue;
					}
					$string = get_option('ulp_ap_' . $slug . '_icon_code');
					if ($string){
						$available_tabs[$slug]['icon'] = $string;
					}
					$string = get_option('ulp_ap_' . $slug . '_menu_label');
					if ($string){
							$available_tabs[$slug]['label'] = $string;
					}
			}

			/// re-order
			$available_tabs = self::reorder_menu_items(get_option('ulp_account_page_menu_order'), $available_tabs);

			return $available_tabs;
	}

	public static function account_page_get_tabs_details(){
		 $keys = self::account_page_get_tabs();
		 $return = array();
		 foreach ($keys as $key => $extra){
			 $tempkey = 'ulp_ap_' . $key . '_menu_label';
			 $return[$tempkey] = get_option($tempkey);
			 $tempkey = 'ulp_ap_' . $key . '_title';
			 $return[$tempkey] = get_option($tempkey);
			 $tempkey = 'ulp_ap_' . $key . '_msg';
			 $return[$tempkey] = get_option($tempkey);
			 $tempkey = 'ulp_ap_' . $key . '_icon_code';
			 $return[$tempkey] = get_option($tempkey);
			 $tempkey = 'ulp_ap_' . $key . '_icon_class';
			 $return[$tempkey] = get_option($tempkey);
		 }
		 return $return;
	}

	public static function account_page_save_tabs_details($array=array()){
			$keys = self::account_page_get_tabs();
			foreach ($keys as $key => $extra){
					$tempkey = 'ulp_ap_' . $key . '_menu_label';
					if (isset($array[$tempkey])){
						update_option($tempkey, $array[$tempkey]);
					}
					$tempkey = 'ulp_ap_' . $key . '_title';
					if (isset($array[$tempkey])){
						update_option($tempkey, $array[$tempkey]);
					}
					$tempkey = 'ulp_ap_' . $key . '_msg';
					if (isset($array[$tempkey])){
						update_option($tempkey, $array[$tempkey]);
					}
					$tempkey = 'ulp_ap_' . $key . '_icon_code';
					if (isset($array[$tempkey])){
						update_option($tempkey, $array[$tempkey]);
					}
					$tempkey = 'ulp_ap_' . $key . '_icon_class';
					if (isset($array[$tempkey])){
						update_option($tempkey, $array[$tempkey]);
					}
			}
	}


	public static function reorder_menu_items($order=[], $array=[]){
			if (!empty($order) && is_array($order)){
	 		 $return_array = array();
	 		 foreach ($order as $key=>$value){
	 		 	 if (isset($array[$key])){
	 		 	 	 $return_array[$key] = $array[$key];
	 				 unset($array[$key]);
	 		 	 }
	 		 }
	 		 if (!empty($array)){
	 		 	$return_array = array_merge($return_array, $array);
	 		 }
	 		 return $return_array;
	 	 }
	 	 return $array;
	}

	public static function account_page_menu_delete_custom_item($slug=''){
			if ($slug){
				$data = get_option('ulp_account_page_custom_menu_items');
				if (isset($data[$slug])){
					unset($data[$slug]);
				}
				update_option('ulp_account_page_custom_menu_items', $data);
			}
	}

	public static function account_page_menu_save_custom_item($array=array()){
		 $data = get_option('ulp_account_page_custom_menu_items');
		 $slug = $array['slug'];
		 $standardTabs = [
											 'overview',
											 'profile',
											 'my_courses',
											 'wish_list',
											 'notes',
											 'orders',
											 'list_certificates',
											 'help'
		 ];
		 if (($data && isset($data[$slug])) || in_array( $slug, $standardTabs )){
			return FALSE; /// slug already exists
		 } else {
			$data[$slug] = array(
									'label' => $array['label'],
									'url' => $array['url'],
			);
			$tempkey = 'ulp_ap_' . $slug . '_icon_code';
			update_option($tempkey, $array['ulp_account_page_menu_add_new-the_icon_code']);
			$tempkey = 'ulp_ap_' . $slug . '_icon_class';
			update_option($tempkey, $array['ulp_account_page_menu_add_new-the_icon_class']);
		 }
		 update_option('ulp_account_page_custom_menu_items', $data);
		 return TRUE;
	}

	 public static function account_page_menu_get_custom_items(){
			$data = get_option('ulp_account_page_custom_menu_items');
			if ($data){
				foreach ($data as $slug => $array){
					$tempkey = 'ulp_ap_' . $slug . '_icon_code';
					$data[$slug]['icon'] = get_option($tempkey);
					$tempkey = 'ulp_ap_' . $slug . '_icon_class';
					$data[$slug]['class'] = get_option($tempkey);
				}
			}
			return $data;
	 }


	public static function updateOptionMetaGroup($group='', $post_data=array() ){
			$group_arr = self::getOptionMetaGroup($group);
			foreach ($group_arr as $k=>$v){
					if (isset($post_data[$k])){
							update_option($k, $post_data[$k]);
					}
			}
	}


	/**
	 * @param int(user id)
	 * @return string
	 */
	public static function getUsernameByUID($uid=0){
		global $wpdb;
		if(!isset($uid)){
			return;
		}
		$table = $wpdb->base_prefix . 'users';
		$q = $wpdb->prepare("SELECT user_login FROM $table WHERE ID=%d ", $uid);
		$data = $wpdb->get_var($q);
		return $data;
	}


	public static function getUidByUsername($username=''){
			global $wpdb;
			$q = $wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE user_login=%s ", $username);
			return $wpdb->get_var($q);
	}


	public static function search_for_users($search_string=''){
			global $wpdb;
			$search_string = sanitize_textarea_field($search_string);
			$q = "SELECT ID, user_login FROM {$wpdb->users} WHERE user_login LIKE '%$search_string%';";
			$data = $wpdb->get_results($q);
			return $data;
	}


	public static function getUserFulltName($uid=0){
			if (empty($uid)){
         return '';
      }
			$uid = sanitize_text_field($uid);
			$first = get_user_meta($uid, 'first_name', TRUE);
			$last = get_user_meta($uid, 'last_name', TRUE);
			if($first != '' || $last != '')
				return $first . ' ' . $last;

			$nickname = get_user_meta($uid, 'nickname', TRUE);
			return $nickname;
	}


	public static function getCommentContent($commentId=0)
	{
			global $wpdb;
			if (!$commentId){
					return false;
			}
			$query = $wpdb->prepare("SELECT comment_content FROM {$wpdb->comments} WHERE comment_ID=%d", $commentId);
			return $wpdb->get_var($query);
	}

	public static function getUserColByUid($uid=0, $col_name=''){
		global $wpdb;
		$table = $wpdb->base_prefix . 'users';
		$uid = sanitize_text_field($uid);
		$col_name = sanitize_text_field($col_name);
    $query = $wpdb->prepare( "SELECT $col_name FROM $table WHERE ID=%d ", $uid );
		$data = $wpdb->get_var( $query );
		return $data;
	}

	public static function getPostColumnByID($ID=0, $columnName='')
	{
			global $wpdb;
			if (!$ID || !$columnName){
					return false;
			}
			$query = $wpdb->prepare("SELECT $columnName FROM {$wpdb->postmeta} WHERE ID=%d", $ID);
			return $wpdb->get_var($query);
	}


	/**
	 * @param int (post id)
	 * @param string (taxonomy name)
	 * @return array
	 */
	public static function getCategoriesForPost($post_id=0, $taxonomy_name=''){
		$cats = array();
		$post_id = sanitize_text_field($post_id);
		$taxonomy_name = sanitize_text_field($taxonomy_name);
		$data = get_the_terms($post_id, 'ulp_course_categories');
		if ($data){
			foreach ($data as $object){
				$cats[] = $object->name;
			}
		}
		return $cats;
	}


	/**
	 * @param int
	 * @param int
	 * @param int
	 * @return object
	 */
	public static function getStudents($course_id=0, $limit=20, $offset=0, $search_substring=''){
		global $wpdb;
		$table = $wpdb->prefix . 'ulp_user_entities_relations';
		$table_b = $wpdb->base_prefix . 'users';
		$table_c = $wpdb->base_prefix . 'usermeta';
		$q = "SELECT a.user_id,
								GROUP_CONCAT(a.entity_id) as courses,
								GROUP_CONCAT(a.id) as courses_entities_id,
								b.user_login,
								b.user_registered,
								b.user_email,
								CONCAT(c.meta_value, ' ', d.meta_value) as full_name,
								IFNULL(e.points, 0) as reward_points,
								a.start_time as enroll_time,
								a.end_time as expire_time
						FROM $table a
						INNER JOIN $table_b b
						ON a.user_id=b.ID
						INNER JOIN $table_c c
						ON b.ID=c.user_id
						INNER JOIN $table_c d
						ON b.ID=d.user_id
						LEFT JOIN {$wpdb->prefix}ulp_reward_points e
						ON e.uid=b.ID
						WHERE 1=1
		";
		if ($course_id){
			$course_id = sanitize_text_field($course_id);
			$q .= $wpdb->prepare(" AND a.entity_id=%d ", $course_id );
		}
		if ($search_substring){
				$search_substring = sanitize_textarea_field($search_substring);
				$q .= " AND (
									b.user_login LIKE '%$search_substring%'
									OR
									b.user_email LIKE '%$search_substring%'
									OR
									b.user_nicename LIKE '%$search_substring%'
				 ) ";
		}
		$q .= "AND a.entity_type='ulp_course' ";
		$q .= "AND c.meta_key='first_name' AND d.meta_key='last_name' ";
		$q .= "GROUP BY a.user_id ";
		$q .= "ORDER BY b.user_registered DESC ";
		if ( $limit > -1 && $offset > -1 ){
				$q .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
		}
		return $wpdb->get_results($q);
	}

	public static function getStudentIdsByCourse($courseId=0)
	{
			global $wpdb;
			if (empty($courseId)){
					return false;
			}
			$query = $wpdb->prepare("SELECT a.ID
									FROM {$wpdb->users} a
									INNER JOIN {$wpdb->prefix}ulp_user_entities_relations b
 									ON a.ID=b.user_id
									WHERE
									b.entity_id=%d
			", $courseId);
			return $wpdb->get_results($query);
	}

	/**
	 * @param none
	 * @return int
	 */
	public static function countStudents($course_id=0, $search_substring=''){
			global $wpdb;
			$table = $wpdb->prefix . 'ulp_user_entities_relations';
			$table_b = $wpdb->base_prefix . 'users';
			$q = "SELECT COUNT(DISTINCT a.user_id) as c
							FROM $table a
							INNER JOIN $table_b b
							ON a.user_id=b.ID
							WHERE 1=1
			";
			if ($course_id){
					$course_id = sanitize_text_field($course_id);
					$q .= $wpdb->prepare( "AND a.entity_id=%d ", $course_id );
			}
			if ($search_substring){
					$search_substring = sanitize_textarea_field($search_substring);
					$q .= " AND (
										b.user_login LIKE '%$search_substring%'
										OR
										b.user_email LIKE '%$search_substring%'
										OR
										b.user_nicename LIKE '%$search_substring%'
					 ) ";
			}
			return $wpdb->get_var($q);
	}


	public static function countInstructors($search_substring=''){
		global $wpdb;
		$role_key = $wpdb->prefix . 'capabilities';
		$q = "SELECT COUNT(DISTINCT(user_id)) as c
						FROM {$wpdb->usermeta} a
						INNER JOIN {$wpdb->users} b
						ON a.user_id=b.ID
						WHERE
						a.meta_key='$role_key'
						AND (
							a.meta_value LIKE '%ulp_instructor%'
							OR
							a.meta_value LIKE '%admin%'
						) ";
		if ($search_substring){
			$search_substring = sanitize_textarea_field($search_substring);
			$q .= " AND (
								b.user_login LIKE '%$search_substring%'
								OR
								b.user_email LIKE '%$search_substring%'
								OR
								b.user_nicename LIKE '%$search_substring%'
			 ) ";
		}
		return $wpdb->get_var($q);
	}


	/**
	 * @param array
	 * @return array
	 */
	public static function getCoursesDetails($ids_in=array()){
		global $wpdb;
		$in = implode(',', $ids_in);
		$in = sanitize_textarea_field($in);
		$q = "SELECT ID, post_title, post_excerpt FROM {$wpdb->posts} WHERE post_type='ulp_course' AND post_status NOT IN ('trash', 'auto-draft', 'pending') AND ID IN($in);";
		return indeed_convert_to_array($wpdb->get_results($q));
	}


	/**
	 * @param int (user id)
	 * @param int (quiz id)
	 * @return stirng
	 */
	public static function userGetQuizGrade($uid=0, $quiz_id=0){
		require_once ULP_PATH . 'classes/public/UlpQuizActions.class.php';
		$UlpQuizActions = new UlpQuizActions();
		$UlpQuizActions->setUID($uid);
		$UlpQuizActions->setQID($quiz_id);
		$UlpQuizActions->setMetas();
		return $UlpQuizActions->getGrade();
	}


	/**
	 * @param int
	 * @return int
	 */
	public static function courseGetLastQuiz($course_id=0){
		$last_quiz = 0;
		require_once ULP_PATH . 'classes/Db/DbCoursesModulesUlp.class.php';
		$DbCoursesModulesUlp = new DbCoursesModulesUlp();
		$module_id = $DbCoursesModulesUlp->getLastModuleForCourse($course_id);
		if ($module_id){
			require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
			$DbModuleItems = new DbModuleItems();
			$last_quiz = $DbModuleItems->getLastQuizForCourse($module_id);
		}
		return $last_quiz;
	}


	/**
	 * @param int (course id)
	 * @param string (ulp_lesson, ulp_quiz, empty for all)
	 * @return array
	 */
	public static function getAllCourseItems($course_id=0, $type=''){
		global $wpdb;
		$course_id = sanitize_text_field($course_id);
		$type = sanitize_text_field($type);
		$table = $wpdb->prefix . 'ulp_course_modules_items';
		$table_b = $wpdb->prefix . 'ulp_courses_modules';
		$q = $wpdb->prepare("
			SELECT item_id FROM $table a
				INNER JOIN $table_b b
				ON b.module_id=a.module_id
				WHERE b.course_id=%d
		", $course_id );
		if ($type){
			$q .= $wpdb->prepare( "AND a.item_type=%s ", $type );
		}
		return indeed_convert_to_array($wpdb->get_results($q));
	}


	/**
	 * @param int (user id)
	 * @param int (lesson)
	 * @return boolean
	 */
	public static function isLessonCompletedForUID($uid=0, $lesson_id=0){
		///// dont forget to update ......

		require_once ULP_PATH . 'classes/Entity/UlpLesson.class.php';
		$object = new UlpLesson($lesson_id, FALSE);
		$object->setUID($uid);
		return $object->is_completed();
	}


	/**
	 * Return course id for a lesson/quiz/question id
	 * @param int
	 * @return int
	 */
	public static function getCourseForItem($post_id=0){
		///// dont forget to update ......
		require_once ULP_PATH . 'classes/Entity/UlpLesson.class.php';
		$object = new UlpLesson($post_id, FALSE);
		return $object->LessonParent();
	}


	public static function getCourseNameForAnnouncement($announcementId=0)
	{
			if (empty($announcementId)){
					return '';
			}
			$courseId = get_post_meta($announcementId, 'ulp_course_id', true);
			return self::getPostTitleByPostId($courseId);
	}

	public static function getCoursePermalinkForAnnouncement($announcementId=0)
	{
			if (empty($announcementId)){
					return '';
			}
			$courseId = get_post_meta($announcementId, 'ulp_course_id', true);
			return Ulp_Permalinks::getForCourse($courseId);
	}

	public static function getCourseNameForQanda($announcementId=0)
	{
			if (empty($announcementId)){
					return '';
			}
			$courseId = get_post_meta($announcementId, 'ulp_qanda_course_id', true);
			return self::getPostTitleByPostId($courseId);
	}

	public static function getCoursePermalinkForQanda($announcementId=0)
	{
			if (empty($announcementId)){
					return '';
			}
			$courseId = get_post_meta($announcementId, 'ulp_qanda_course_id', true);
			return Ulp_Permalinks::getForCourse($courseId);
	}

	public static function countPostComments($postId=0, $commentStatus=false)
	{
			global $wpdb;
			if (empty($postId)){
					return 0;
			}
			$query = $wpdb->prepare( "SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_post_ID=%d", $postId );
			if ($commentStatus!==false){
					$query .= $wpdb->prepare(" AND comment_approved=%d", $commentStatus);
			}
			return $wpdb->get_var($query);
	}


	/**
	 * @param string
	 * @param string
	 * @param array
	 * @return array
	 */
	public static function reorderPostList($order_by='', $order_type='', $posts_in=array()){
			global $wpdb;
			$posts_list = implode(',', $posts_in);
			$order_by = sanitize_text_field($order_by);
			$order_type = sanitize_text_field($order_type);
			$posts_list = sanitize_textarea_field($posts_list);
			$q = "SELECT ID FROM {$wpdb->posts} WHERE ID in ($posts_list) ORDER BY $order_by $order_type;";
			$children = indeed_convert_to_array($wpdb->get_results($q));
			return ulp_array_value_of_child_become_value($children, 'ID');
	}


	/**
	 * @param string
	 * @param string
	 * @param array
	 * @return array
	 */
	public static function reorderPostListByPostMeta($order_by='', $order_type='', $posts_in=array()){
			global $wpdb;
			$posts_list = implode(',', $posts_in);
			$order_by = sanitize_text_field($order_by);
			$order_type = sanitize_text_field($order_type);
			$posts_list = sanitize_textarea_field($posts_list);
			$q = $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE post_id IN ($posts_list) AND meta_key=%s ORDER BY meta_value $order_type;", $order_by );
			$children = indeed_convert_to_array($wpdb->get_results($q));
			if (!empty($children)){
					$children = ulp_array_value_of_child_become_value($children, 'post_id');
					return $children;
			} else {
					return $posts_in;
			}
	}

	/**
	* wrapp function to get_standard_by_type from DbNotificationsUlp class
	* @param string
	* @return array
	*/
	public static function getStandardNotificationForType($type=''){
			require_once ULP_PATH . 'classes/Db/DbNotificationsUlp.class.php';
			$DbNotificationsUlp = new DbNotificationsUlp();
			return $DbNotificationsUlp->get_standard_by_type($type);
	}

	public static function default_pages_get_current_page_type($id=0){
		if ($id){
			$data = self::getOptionMetaGroup('default_pages');
			if ($key=array_search($id, $data)){
				return $key;
			}
		}
		return '';
	}

	public static function default_pages_get_default_unset_pages(){
		$unset = array();
		$arr = array(
				'ulp_default_page_list_courses' => esc_html__('List courses', 'ulp'),
				'ulp_default_page_student_profile' => esc_html__('Student profile', 'ulp'),
				'ulp_default_page_become_instructor' => esc_html__('Become instructor', 'ulp'),
				'ulp_default_page_list_watch_list' => esc_html__('Wish list', 'ulp'),
				'ulp_default_page_checkout' => esc_html__('Checkout', 'ulp'),
				'ulp_default_page_instructor_dashboard' => esc_html__('Instructor Dashboard', 'ulp'),
		);
		$values = self::getOptionMetaGroup('default_pages');
		foreach ($arr as $name=>$label){
			if (empty($values[$name]) || $values[$name]==-1){
				$unset[] = $label;
			}
		}
		return $unset;
	}


	public static function getFeatImage($post_id=0){
		global $wpdb;
		$post_id = sanitize_text_field($post_id);
    $query = $wpdb->prepare( "SELECT p.guid FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm on p.ID=pm.meta_value WHERE pm.meta_key='_thumbnail_id' AND pm.post_id=%d ;", $post_id );
		return $wpdb->get_var( $query );
	}

	public static function getAllItemsForCourse($course_id=0){
		require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
		require_once ULP_PATH . 'classes/Db/DbCoursesModulesUlp.class.php';
		$course_id = sanitize_text_field($course_id);
		$reorder_type = get_post_meta($course_id, 'ulp_modules_order_items_by', true);
		$reorder_by = get_post_meta($course_id, 'ulp_modules_order_items_type', true);

		$DbCoursesModulesUlp = new DbCoursesModulesUlp();
		$modules = $DbCoursesModulesUlp->getAllModulesForCourse($course_id, 0, 0); ///getting the modules
		$items = array();
		foreach ($modules as $t){
			$DbModuleItems = new DbModuleItems();
			$children = $DbModuleItems->getAllModuleItemsByModuleId($t['module_id']);
			$children = ulp_array_value_of_child_become_value($children, 'item_id');
			$children = DbUlp::course_reorder_items($children, $reorder_type, $reorder_by);
			foreach ($children as $k=>$v){
					$items [] = $v;
			}
		}
		return $items;
	}

	public static function course_reorder_items($items=array(), $reorder_type='', $reorder_by=''){
		if ($reorder_by!='default'){
				switch ($reorder_by){
					case 'post_title':
					case 'post_date':
						$items = self::reorderPostList($reorder_by, $reorder_type, $items);
						break;
					case 'ulp_post_reward_points':
						/// must check if ulp_post_reward_points does really exists or just create this meta key o post create
						$items = self::reorderPostListByPostMeta('ulp_post_reward_points', $reorder_type, $items);

						break;
				}
		}
		return $items;
	}

	public static function get_user_roles($uid=0){
			global $wpdb;
			if ($uid){
				$role_key = $wpdb->prefix . 'capabilities';
				$data = get_user_meta($uid, $role_key, TRUE);
				return $data;
			}
			return array();
	}

	// 1 - active instructor, 0 - no instructor yet, -1 - instructor pending
	public static function isUserInstructor($uid=0){
			if ($uid) {
					$temp = self::get_user_roles($uid);
					if (isset($temp['ulp_instructor'])){
							return 1;
					} else if (isset($temp['ulp_instructor_senior'])){
							return 1;
					} else if (isset($temp['administrator'])){
							return 1;
					} else if (isset($temp['ulp_instructor-pending'])){
							return -1;
					}
			}
			return 0;
	}

	public static function isInstructorSenior($uid=0)
	{
			if (!$uid){
					return false;
			}
			$roles = self::get_user_roles($uid);
			if (!empty($roles['ulp_instructor_senior'])){
					return true;
			} else if (!empty($roles['administrator'])){
					return true;
			}
			return false;
	}


	public static function getAllInstructors($limit=20, $offset=0, $search_substring=''){
			global $wpdb;
			$role_key = $wpdb->prefix . 'capabilities';
			$q = $wpdb->prepare("SELECT DISTINCT(a.user_id) as uid,
									b.user_login as user_login,
									b.user_registered as user_registered,
									b.user_email,
									CONCAT(c.meta_value, ' ', d.meta_value) as full_name
									FROM {$wpdb->usermeta} a
									INNER JOIN {$wpdb->users} b
									ON a.user_id=b.ID
									INNER JOIN {$wpdb->usermeta} c
									ON c.user_id=b.ID
									INNER JOIN {$wpdb->usermeta} d
									ON d.user_id=b.ID
									WHERE a.meta_key=%s
			", $role_key );
      $q .= "	AND (a.meta_value LIKE '%ulp_instructor%' OR a.meta_value LIKE '%admin%' OR a.meta_value LIKE '%ulp_instructor_senior%' )
      				AND c.meta_key='first_name'
      				AND d.meta_key='last_name' ";
			if ($search_substring){
				$search_substring = sanitize_textarea_field($search_substring);
				$q .= " AND (
									b.user_login LIKE '%$search_substring%'
									OR
									b.user_email LIKE '%$search_substring%'
									OR
									b.user_nicename LIKE '%$search_substring%'
				 ) ";
			}

			if ($limit){
					$limit = sanitize_text_field($limit);
					$offset = sanitize_text_field($offset);
					$q .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
			}
			return $wpdb->get_results($q);
	}

	public static function getAllInstructorsForCourse($courseId=0)
	{
			global $wpdb;
			if (empty($courseId)){
					return false;
			}
			$instructors = [];
			$data = get_post_meta($courseId, 'ulp_additional_instructors', TRUE);
			if ($data){
					$instructors = explode(',', $data);
			}
	}

	public static function isUserAdmin($uid=0)
	{
			global $wpdb;
			if (!$uid){
					return false;
			}
			$roles = self::get_user_roles($uid);
			if (!$roles){
					return false;
			}
			if (isset($roles['administrator'])){
					return true;
			}
			return false;
	}

	public static function get_full_name($uid=0){
			global $wpdb;
			$q = $wpdb->prepare("SELECT CONCAT(a.meta_value, ' ', b.meta_value) as full_name
								FROM {$wpdb->usermeta} a
								INNER JOIN {$wpdb->usermeta} b
								ON a.user_id=b.user_id
								WHERE a.user_id=%d
								AND a.meta_key='first_name' AND b.meta_key='last_name';
			", $uid);
			return $wpdb->get_var($q);
	}

	public static function isInstructorForCourse($uid=0, $courseId=0)
	{
			global $wpdb;
			if (empty($uid) || empty($courseId)){
					return false;
			}
			$query = $wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID=%d", $courseId);
			if ($wpdb->get_var($query)==$uid){
					return true;
			}
			$courses = self::getCoursesForAdditionalInstructor($uid);
			if ($courses){
					foreach ($courses as $course){
							if ($course->post_id==$courseId){
									return true;
							}
					}
			}
			return false;
	}

	public static function isUserAuhtorForPost($uid=0, $postId=0)
	{
			global $wpdb;
			if (!$uid || !$postId){
					return false;
			}
			$query = $wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID=%d;", $postId);
			return $wpdb->get_var($query);
	}

	public static function getCoursesForAdditionalInstructor($uid=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$q = "
			      SELECT a.post_id, b.post_title, b.post_modified
			          FROM {$wpdb->postmeta} a
			          INNER JOIN {$wpdb->posts} b
			          ON b.ID=a.post_id
			          WHERE
			          1=1
			          AND
			          a.meta_key='ulp_additional_instructors'
			          AND
			          (
			              a.meta_value='$uid'
			              OR
			              a.meta_value LIKE '%,$uid'
			              OR
			              a.meta_value LIKE '$uid,%'
			              OR
			              a.meta_value LIKE '%,$uid,%'
			          )
			";
			$data = $wpdb->get_results($q);
			if ($data==null){
					$data = false;
			}
			return $data;
	}

	public static function get_courses_for_instructor($uid=0, $limit=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$query = $wpdb->prepare("
								SELECT ID as post_id, post_title, post_modified
										FROM {$wpdb->posts}
										WHERE
										1=1
										AND
										post_author=%d
										AND
										post_status NOT IN ('auto-draft')
										AND
										post_type='ulp_course'
			", $uid);
			if ($limit){
					$query .= $wpdb->prepare(" ORDER BY post_id DESC LIMIT %d", $limit);
			}
			$data = $wpdb->get_results($query);
			if ($data==null){
					$data = false;
			}
			return $data;
	}

	public static function get_courses_for_instructor_as_array($uid=0, $limit=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$query = $wpdb->prepare("
								SELECT ID, post_title, post_modified
										FROM {$wpdb->posts}
										WHERE
										1=1
										AND
										post_author=%d
										AND
										post_status NOT IN ('auto-draft')
										AND
										post_type='ulp_course'
			", $uid);
			if ($limit){
					$query .= $wpdb->prepare(" ORDER BY post_id DESC LIMIT %d", $limit);
			}
			$data = $wpdb->get_results($query);
			if ($data==null){
					$data = false;
			}
			$data = indeed_convert_to_array( $data );
			return $data;
	}

	public static function getAllCoursesForInstructor($uid=0, $limit=0, $excludePostId=0)
	{
			global $wpdb;
			$uid = sanitize_text_field( $uid );
			$excludePostId = sanitize_text_field( $excludePostId );
			$limit = sanitize_text_field( $limit );
			$query = "
								SELECT DISTINCT(a.post_id) as courseId, b.post_title, b.post_modified
										FROM {$wpdb->postmeta} a
										INNER JOIN {$wpdb->posts} b
										ON b.ID=a.post_id
										WHERE
										(
												a.meta_key='ulp_additional_instructors'
												AND
												(
														a.meta_value='$uid'
														OR
														a.meta_value LIKE '%,$uid'
														OR
														a.meta_value LIKE '$uid,%'
														OR
														a.meta_value LIKE '%,$uid,%'
												)
										)
										OR
										b.post_author=$uid
										AND
										b.post_type='ulp_course'
										AND
										b.post_status = 'publish'
			";
			if ($excludePostId){
					$query .= " AND a.post_id NOT IN ($excludePostId)";
			}
					$query .= " LIMIT $limit";
			$data = $wpdb->get_results($query);
			return $data;
	}

	public static function remove_instructor_from_instructor($uid=0, $course_id=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$course_id = sanitize_text_field($course_id);
      $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d AND post_author=%d", $course_id, $uid );
			$data = $wpdb->get_var( $query );
			if ($data){
					$data = get_super_admins();
					if (isset($data[0])){
					    $admin_uid = DbUlp::getUidByUsername($data[0]);
              $query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_author=%d WHERE ID=%d ;", $admin_uid, $course_id );
							return $wpdb->query( $query );
					}
			}
	}

	public static function remove_additional_instructor_from_course($uid=0, $course_id=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$course_id = sanitize_text_field( $course_id );
      $query = "
					SELECT id, meta_value FROM {$wpdb->post_meta}
							meta_key='ulp_additional_instructors'
							AND
							(
									meta_value='$uid'
									OR
									meta_value LIKE '%,$uid'
									OR
									meta_value LIKE '$uid,%'
									OR
									meta_value LIKE '%,$uid,%'
							)
			";
			$data = $wpdb->get_row( $query );
			if ($data && isset($data->meta_value)){
					$new_data = unserialize($data->meta_value);
					$key = array_search($uid, $new_data);
					if ($key!==FALSE){
							unset($new_data[$key]);
					}
					update_post_meta($course_id, 'ulp_additional_instructors', $new_data);
			}
	}


	public static function set_course_instructor($uid=0, $course_id=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$course_id = sanitize_text_field($course_id);
      $query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_author=%d WHERE ID=%d ;", $uid, $course_id );
			return $wpdb->query( $query );
	}



	public static function set_role_for_user( $uid=0, $newRole='' )
	{
			if ( !$uid || !$newRole ){
					return;
			}
			global $wpdb;
			$capKey = $wpdb->prefix . 'capabilities';
			$newRole = sanitize_text_field( $newRole );
			$role[ $newRole ] = 1;
			$uid = sanitize_text_field( $uid );
			update_user_meta( $uid, $capKey, $role );
			do_action( 'ulp_set_user_new_role', $uid, $newRole );
	}

	/**
	 * @param int (user id)
	 * @param string (selected row)
	 * @return string
	 */
	public static function get_user_col_value($uid=0, $col_name=''){
		if ($uid && $col_name){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$table = $wpdb->base_prefix . 'users';
      $query = $wpdb->prepare( "SELECT $col_name FROM $table WHERE ID=%d;", $uid );
			return $wpdb->get_var( $query );
		}
	}


	public static function get_currencies_list($return='all'){
		/*
		 * @param string : all, basic, custom
		 * @return array
		 */
		$basic = array(
				'AUD' => 'Australian Dollar (A $)',
				'CAD' => 'Canadian Dollar (C $)',
				'EUR' => 'Euro (&#8364;)',
				'GBP' => 'British Pound (&#163;)',
				'JPY' => 'Japanese Yen (&#165;)',
				'USD' => 'U.S. Dollar ($)',
				'NZD' => 'New Zealand Dollar ($)',
				'CHF' => 'Swiss Franc',
				'HKD' => 'Hong Kong Dollar ($)',
				'SGD' => 'Singapore Dollar ($)',
				'SEK' => 'Swedish Krona',
				'DKK' => 'Danish Krone',
				'PLN' => 'Polish Zloty',
				'NOK' => 'Norwegian Krone',
				'HUF' => 'Hungarian Forint',
				'CZK' => 'Czech Koruna',
				'ILS' => 'Israeli New Shekel',
				'MXN' => 'Mexican Peso',
				'BRL' => 'Brazilian Real (only for Brazilian members)',
				'MYR' => 'Malaysian Ringgit (only for Malaysian members)',
				'PHP' => 'Philippine Peso',
				'TWD' => 'New Taiwan Dollar',
				'THB' => 'Thai Baht',
				'TRY' => 'Turkish Lira (only for Turkish members)',
				'RUB' => 'Russian Ruble',
		);
		require_once ULP_PATH . 'classes/Db/Db_Custom_Currencies.class.php';
		$Db_Custom_Currencies = new Db_Custom_Currencies();
		$custom = $Db_Custom_Currencies->getAll();
		if ($return=='all'){
			if ($custom!==FALSE && is_array($custom)){
				return $basic+$custom;
			}
			return $basic;
		} else if ($return=='basic'){
			return $basic;
		} else {
			return $custom;
		}
	}

	public static function get_post_status($post_id=0){
			global $wpdb;
			$post_id = sanitize_text_field($post_id);
      $query = $wpdb->prepare( "SELECT post_status FROM {$wpdb->posts} WHERE ID=%d;", $post_id );
			return $wpdb->get_var( $query );
	}

	public static function get_woo_product_course_relations(){
		$array = array();
		global $wpdb;
		$table = $wpdb->prefix . 'postmeta';
    $query = "SELECT meta_value, post_id FROM $table WHERE meta_key='ulp_woo_product_course_relation' AND meta_value!='' AND meta_value!='-1';";
		$data = $wpdb->get_results( $query );
		if ($data){
		 foreach ($data as $object){
			 $temp['course_label'] = self::getPostTitleByPostId($object->meta_value);
			 $temp['product_label'] = get_the_title($object->post_id);
			 $temp['course_id'] = $object->meta_value;
			 $temp['product_id'] = $object->post_id;
			 $array[] = $temp;
		 }
		}
		return $array;
	}

	public static function get_edd_product_course_relations(){
			$array = array();
			global $wpdb;
			$table = $wpdb->prefix . 'postmeta';
      $query = "SELECT meta_value, post_id FROM $table WHERE meta_key='ulp_edd_product_course_relation' AND meta_value!='' AND meta_value!='-1';";
			$data = $wpdb->get_results( $query );
			if ($data){
			 foreach ($data as $object){
				 $temp['course_label'] = self::getPostTitleByPostId($object->meta_value);
				 $temp['product_label'] = get_the_title($object->post_id);
				 $temp['course_id'] = $object->meta_value;
				 $temp['product_id'] = $object->post_id;
				 $array[] = $temp;
			 }
			}
			return $array;
	}

	public static function get_ump_levels_course_relations(){
			$array = array();
			$levels = get_option('ihc_levels');
			if ($levels){
					foreach ($levels as $id => $level_data){
							if (isset($level_data['ump_ulp_course']) && $level_data['ump_ulp_course']>0){
								 $temp['course_label'] = self::getPostTitleByPostId($level_data['ump_ulp_course']);
				 				 $temp['level_label'] = $level_data['label'];
				 				 $temp['course_id'] = $level_data['ump_ulp_course'];
				 				 $temp['level_id'] = $id;
				 				 $array[] = $temp;
							}
					}
			}
			return $array;
	}

	public static function get_course_id_for_ump_level($lid=0){
			$levels = get_option('ihc_levels');
			if ($levels){
					foreach ($levels as $id => $level_data){
							if ($id==$lid && isset($level_data['ump_ulp_course']) && $level_data['ump_ulp_course']>0){
								 return $level_data['ump_ulp_course'];
							}
					}
			}
			return FALSE;
	}


	public static function getPostIdForInstructor($uid=0)
	{
			global $wpdb;
			$query = $wpdb->prepare( "
									SELECT ID FROM {$wpdb->posts} a
											INNER JOIN {$wpdb->postmeta} b
											ON a.ID=b.post_id
											WHERE
											b.meta_key='instructor_uid'
											AND
											b.meta_value=%d
			", $uid );
			$data = $wpdb->get_var($query);
			return $data;
	}

	public static function getInstructorUidByPost($postId=0)
	{
			global $wpdb;
			$query = $wpdb->prepare("
										SELECT meta_value
											FROM {$wpdb->postmeta}
											WHERE
											meta_key='instructor_uid'
											AND
											post_id=%d
				", $postId
			);
			$data = $wpdb->get_var($query);
			return $data;
	}

	public static function insertCustomPostTypeInstructor($uid=0, $role='', $oldRoles='')
	{
			// this is used for custom post type. Connection between users, usermeta AND posts
			$postId = wp_insert_post([
					'post_title' => self::getUsernameByUID($uid),
					'post_status' => 'publish',
					'post_type' => 'ulp-instructor'
			]);
			if (!empty($postId)){
					update_post_meta($postId, 'instructor_uid', $uid);
			}
	}

	public static function get_grades($limit=0, $offset=0, $uid=0){
			/// todo test it
			global $wpdb;
			$table_b = $wpdb->prefix . 'ulp_user_entities_relations';
			$table_c = $wpdb->prefix . 'ulp_user_entities_relations_metas';
			$table_d = $wpdb->prefix . 'ulp_courses_modules';
			$table_e = $wpdb->prefix . 'ulp_course_modules_items';

			$limit = sanitize_text_field($limit);
			$offset = sanitize_text_field($offset);
			$uid = sanitize_text_field($uid);

			$q = "SELECT a.post_title as quiz_title,
						c.meta_value as grade,
						f.post_title as course_title,
						f.ID as course_id,
						g.meta_value as course_passed
								FROM {$wpdb->posts} a
								INNER JOIN $table_b b
								ON a.ID=b.entity_id
								INNER JOIN $table_c c
								ON b.id=c.user_entity_relation_id
								INNER JOIN $table_e e
								ON e.item_id=a.ID
								INNER JOIN $table_d d
								ON e.module_id=d.module_id
								INNER JOIN {$wpdb->posts} f
								ON d.course_id=f.ID
								INNER JOIN $table_c g
								ON c.user_entity_relation_id=g.user_entity_relation_id
								WHERE
								b.entity_type='quiz'
								AND
								b.user_id=$uid
								AND
								c.meta_key='grade'
								AND
								g.meta_key='quiz_passed'
			";
			if ($limit){
				$q .= $wpdb->prepare( " LIMIT %d OFFSET %d;", $limit, $offset );
			}
			$data = $wpdb->get_results($q);
			return $data;
	}


	public static function getCertificateForCourse($course_id=0){
			return get_post_meta($course_id, 'ulp_course_certificate', TRUE);
	}

	public static function get_courses_for_certificate($certificate_id=0){
			global $wpdb;
			$response = array();
			$certificate_id = sanitize_text_field($certificate_id);
      $query = $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='ulp_course_certificate' AND meta_value=%s ;", $certificate_id );
			$data = $wpdb->get_results( $query );
			if ($data){
					foreach ( $data as $key=>$array ){
							$response []	= $array->post_id;
					}
			}
			return $response;
	}


	public static function top_course(){
			global $wpdb;
			$q = "SELECT COUNT(a.user_id) as c, a.entity_id, b.post_title as post_title
								FROM {$wpdb->prefix}ulp_user_entities_relations a
								INNER JOIN {$wpdb->posts} b
								ON b.ID=a.entity_id
						WHERE a.entity_type='ulp_course'
						GROUP BY a.entity_id
						ORDER BY c DESC LIMIT 1";
			$data = $wpdb->get_row($q);
			if (isset($data->post_title)){
					return $data->post_title;
			}
			return '';
	}

	public static function getTotalEarnings(){
			global $wpdb;
      $query = "
					SELECT IFNULL(SUM(a.meta_value), 0) as sum FROM {$wpdb->prefix}ulp_order_meta a
							INNER JOIN {$wpdb->posts} b
							ON a.order_id=b.ID
							WHERE
							b.post_type='ulp_order'
							AND a.meta_key='amount';
			";
			return $wpdb->get_var( $query );
	}

	public static function getStudentsCountPerLevel(){
			$courses_data = array();
			$courses = self::getAllCourses();
			if ($courses){
					foreach ($courses as $course_array){
							$object = new UlpCourse($course_array['ID'], FALSE);
							$courses_data [$course_array['post_title']] = $object->TotalStudents();
					}
			}
			return $courses_data;
	}



	public static function ordersGetLast($limit=5){
			global $wpdb;
			$limit = sanitize_text_field($limit);
			$query = $wpdb->prepare("
				SELECT b.user_login as user, a.post_date
					FROM {$wpdb->posts} a
					INNER JOIN {$wpdb->users} b
					ON a.post_author=b.ID
					WHERE a.post_type='ulp_order'
					ORDER BY a.ID DESC LIMIT %d;
			", $limit );
			return $wpdb->get_results($query);
	}


	public static function user_got_this_order($uid=0, $order_id=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$order_id = sanitize_text_field($order_id);
			$q = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ulp_order_meta WHERE meta_key='user_id' AND meta_value=%d AND order_id=%d;", $uid, $order_id );
			return $wpdb->get_var($q);
	}

	public static function getOrdersByUser($uid=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$q = $wpdb->prepare( "
				SELECT b.ID,b.post_author,b.post_date,b.post_date_gmt,b.post_content,b.post_title,b.post_excerpt,b.post_status,b.comment_status,b.ping_status,b.post_password,b.post_name,b.to_ping,b.pinged,b.post_modified,b.post_modified_gmt,b.post_content_filtered,b.post_parent,b.guid,b.menu_order,b.post_type,b.post_mime_type,b.comment_count
					FROM {$wpdb->prefix}ulp_order_meta a
					INNER JOIN {$wpdb->posts} b
					ON a.order_id=b.ID
					WHERE
					a.meta_key='user_id'
					AND
					a.meta_value=%d
					ORDER BY order_id DESC
			", $uid );
			$data = $wpdb->get_results($q);
			if ($data){
					require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
					$DbUlpOrdersMeta = new DbUlpOrdersMeta();
					foreach ($data as $key => $object){
							$data [$key]->metas = $DbUlpOrdersMeta->getAllMetasAsArray($object->ID);
							if (!empty($data [$key]->metas['course_id']))
									$data [$key]->metas['course_label'] = self::getPostTitleByPostId($data [$key]->metas['course_id']);
					}
			}
			return $data;
	}


	public static function getPaymentTypeForCourse($course_id=0){
			/// DEFAULT
			$default_payment_type = get_option('ulp_default_payment_type');

			if (!empty($default_payment_type) && $default_payment_type!='checkout'){
					switch ($default_payment_type) {
						case 'woo':
							if (self::get_woo_product_id_by_course($course_id)){
                 return 'woo';
              }
							break;
						case 'ump':
							if (self::get_ump_product_id_by_course($course_id)){
                 return 'ump';
              }
							break;
						case 'edd':
							if (self::get_edd_product_id_by_course($course_id)){
                 return 'edd';
              }
							break;
					}
			}

			return 'checkout';

	}

	public static function get_ump_product_id_by_course($course_id=0){
			$levels = get_option('ihc_levels');
			if ($levels){
					foreach ($levels as $id => $level_data){
							if (isset($level_data['ump_ulp_course']) && $level_data['ump_ulp_course']==$course_id){
									return $id;
							}
					}
			}
			return 0;
	}

	public static function get_edd_product_id_by_course($course_id=0){
			global $wpdb;
			$course_id = sanitize_text_field($course_id);
      $query = $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='ulp_edd_product_course_relation' AND meta_value=%s;", $course_id );
			return $wpdb->get_var( $query );
	}


	public static function get_woo_product_id_by_course($course_id=0){
			global $wpdb;
			$course_id = sanitize_text_field($course_id);
      $query = $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta}
                                    WHERE meta_key='ulp_woo_product_course_relation'
                                    AND meta_value!='' AND meta_value!='-1'
                                    AND meta_value=%s ;", $course_id );
			return $wpdb->get_var( $query );
	}

	public static function check_envato_customer($code=''){
			if (!empty($code)){
				if (!class_exists('Envato_marketplace')){
					require_once ULP_PATH . 'classes/Envato_marketplace.class.php';
				}
				$api_key = 'z4dqvsth70g7qsr4f385fxjdt6wz9dfg';
				$user_name = 'azzaroco';
				$item_id = '21772657';
				$envato_object = new Envato_marketplaces($api_key);
				$buyer_verify = $envato_object->verify_purchase($user_name, $code);

				if ( isset($buyer_verify) && isset($buyer_verify->buyer)  && $buyer_verify->item->id==$item_id ){
					return TRUE;
				}
			}
			return FALSE;
	}

	public static function envato_licensing($code=''){
			$return = FALSE;
			if (DbUlp::check_envato_customer($code)){
				update_option('ulp_license_set', 1);
				$return = TRUE;
			} else {
				update_option('ulp_license_set', 0);
				$return = FALSE;
			}

			update_option('ulp_envato_code', $code);
			return $return;
	}

	public static function envato_check_license(){
			$check = get_option('ulp_license_set');
			if ($check!==FALSE){
				if ($check==1){
					return TRUE;
        }
				return FALSE;
			}
			return TRUE;
	}

	public static function user_constants(){
			return [
									'{username}'=>'',
									'{user_email}'=>'',
									'{first_name}'=>'',
									'{last_name}'=>'',
									'{account_page}'=>'',
									'{login_page}'=>'',
									//'{level_list}'=>'',
									'{blogname}'=>'',
									'{blogurl}'=>'',
									'{avatar}' => '',
									'{current_date}' => '',
									'{user_registered}' => '',
			];
	}

	public static function friendly_activity_actions(){
			return [
					'view_lesson' => esc_html__('Viewed lesson', 'ulp'),
					'complete_lesson' => esc_html__('Completed lesson', 'ulp'),
					'user_enroll' => esc_html__('Enrolled in course', 'ulp'),
					'finish_course' => esc_html__('Finished course', 'ulp'),
					'finish_quiz' => esc_html__('Finished quiz', 'ulp'),
					'quiz_grade' => esc_html__('Was graded on quiz', 'ulp'),
					'user_gets_points' => esc_html__('Received points', 'ulp'),
					'user_receive_certificate' => esc_html__('Received certificate.', 'ulp'),
					'user_receive_badge' => esc_html__('Received badge', 'ulp'),
			];
	}

	public static function friendly_reward_points_action_type(){
			return [
					'course_passed' => esc_html__('Course passed', 'ulp'),
					'quiz_passed' => esc_html__('Quiz passed', 'ulp'),
					'complete_lesson' => esc_html__('Complete lesson', 'ulp'),
			];
	}

	public static function updatePostStatus($ids=array()){
			global $wpdb;
			if (is_array($ids)){
					$ids_string = implode(',', $ids);
					$ids_string = sanitize_textarea_field($ids_string);
          $query = "UPDATE {$wpdb->posts} SET post_status='publish' WHERE ID IN ($ids_string)";
					$wpdb->query( $query );
			} else {
					$ids = sanitize_textarea_field($ids);
          $query = "UPDATE {$wpdb->posts} SET post_status='publish' WHERE ID=$ids";
					$wpdb->query( $query );
			}
	}

	public static function get_list_of_admins(){
			global $wpdb;
			$role_key = $wpdb->prefix . 'capabilities';
      $q = $wpdb->prepare("SELECT DISTINCT(a.user_id) as uid,
									b.user_login as user_login
									FROM {$wpdb->usermeta} a
									INNER JOIN {$wpdb->users} b
									ON a.user_id=b.ID
									WHERE a.meta_key=%s ", $role_key );
      $q .= " AND a.meta_value LIKE '%administrator%'";
			return $wpdb->get_results($q);
	}

	public static function getAllStudentsFromUsersTable(){
			global $wpdb;
			$q = "
					SELECT a.ID, a.user_login, a.user_pass, a.user_nicename, a.user_email, a.user_url, a.user_registered, a.user_activation_key, a.user_status, a.display_name
						FROM {$wpdb->users} a
						INNER JOIN
						{$wpdb->prefix}ulp_user_entities_relations b
						ON a.ID=b.user_id
			";
			$data = $wpdb->get_results($q);
			return $data;
  }

	public static function getAllInstructorsUsersTable(){
			global $wpdb;
			$role_key = $wpdb->prefix . 'capabilities';
			$q = "
					SELECT a.ID, a.user_login, a.user_pass, a.user_nicename, a.user_email, a.user_url, a.user_registered, a.user_activation_key, a.user_status, a.display_name
						FROM {$wpdb->users} a
						INNER JOIN
						{$wpdb->usermeta} b
						ON a.ID=b.user_id
						WHERE
						b.meta_key='$role_key'
						AND
						(
							b.meta_value LIKE '%ulp_instructor%'
							OR
							b.meta_value LIKE '%ulp_instructor_senior%'
						)
			";
			$data = $wpdb->get_results($q);
			return $data;
	}

	public static function getUserMetaForGroupType($type='instructors'){
			global $wpdb;
			if ($type=='instructors'){
					$role_key = $wpdb->prefix . 'capabilities';
					$q = "
							SELECT b.umeta_id, b.user_id, b.meta_key, b.meta_value
								FROM {$wpdb->users} a
								INNER JOIN
								{$wpdb->usermeta} b
								ON a.ID=b.user_id
								WHERE
								b.meta_key='$role_key'
								AND b.meta_value LIKE '%ulp_instructor%'
					";
			} else {
					/// users
					$q = "
							SELECT c.umeta_id, c.user_id, c.meta_key, c.meta_value
								FROM {$wpdb->users} a
								INNER JOIN
								{$wpdb->prefix}ulp_user_entities_relations b
								ON a.ID=b.user_id
								INNER JOIN
								{$wpdb->usermeta} c
								ON c.user_id=a.ID
					";
			}
			$data = $wpdb->get_results($q);
			return $data;
	}

	public static function get_post_by($slug='', $value='')
	{
			global $wpdb;
			$slug = sanitize_text_field($slug);
			$value = sanitize_textarea_field($value);
      $query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE $slug=%s ", $value );
			return $wpdb->get_var( $query );
	}

	public static function getAllCustomPostTypeItems(){
			global $wpdb;
			$post_types = self::plugin_post_types();
			foreach ($post_types as $value){
					$arr [] = "'$value'";
			}
			$search_in = implode(',', $arr);
			$q = "SELECT ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
			 					FROM {$wpdb->posts} WHERE post_type IN ($search_in);";
			$data = $wpdb->get_results($q);
			return $data;
	}

	public static function getAllCustomPostTypeMetas(){
			global $wpdb;
			$post_types = self::plugin_post_types();
			foreach ($post_types as $value){
					$arr [] = "'$value'";
			}
			$search_in = implode(',', $arr);
			$q = "SELECT b.meta_id,b.post_id,b.meta_key,b.meta_value
								FROM {$wpdb->posts} a
								INNER JOIN {$wpdb->postmeta} b
								ON
								a.ID=b.post_id
								WHERE post_type IN ($search_in);";
			$data = $wpdb->get_results($q);
			return $data;
	}

	public static function does_usermeta_exists($uid=0, $key_meta=''){
		 	 global $wpdb;
			 $uid = sanitize_text_field($uid);
			 $key_meta = sanitize_textarea_field($key_meta);
       $query = $wpdb->prepare( "SELECT umeta_id FROM {$wpdb->usermeta} WHERE user_id=%d AND meta_key=%s;", $uid, $key_meta );
			 $data = $wpdb->get_row( $query );
			 if (isset($data->umeta_id)){
			 	return TRUE;
			 }
			 return FALSE;
	}

	public static function custom_insert_usermeta($uid=0, $key_meta='', $meta_value=''){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$key_meta = sanitize_text_field($key_meta);
			$meta_value = sanitize_textarea_field($meta_value);
      $query = $wpdb->prepare( "INSERT INTO {$wpdb->usermeta} VALUES(
															null,
															%d,
															%s,
															%s );", $uid, $key_meta, $meta_value );
			return $wpdb->query( $query );
	}

	public static function custom_insert_post_with_ID($postData=[])
	{
			global $wpdb;
			if (empty($postData)){
					return;
			}
			foreach ($postData as $key=>$check_data){
				if (empty($postData[$key]) || is_object($postData[$key])){
					$postData[$key] = '';
				} else {
					$postData[$key] = addslashes($postData[$key]);
				}
			}
      $query = $wpdb->prepare( "INSERT INTO {$wpdb->posts} VALUES( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s );",
      $postData['ID'],
      $postData['post_author'],
      $postData['post_date'],
      $postData['post_date_gmt'],
      $postData['post_content'],
      $postData['post_title'],
      $postData['post_excerpt'],
      $postData['post_status'],
      $postData['comment_status'],
      $postData['ping_status'],
      $postData['post_password'],
      $postData['post_name'],
      $postData['to_ping'],
      $postData['pinged'],
      $postData['post_modified'],
      $postData['post_modified_gmt'],
      $postData['post_content_filtered'],
      $postData['post_parent'],
      $postData['guid'],
      $postData['menu_order'],
      $postData['post_type'],
      $postData['post_mime_type'],
      $postData['comment_count'] );
			return $wpdb->query( $query );
	}

	public static function custom_insert_user_with_ID($userdata=array()){
			global $wpdb;
			$table = $wpdb->prefix . 'users';
			foreach ($userdata as $key=>$check_data){
				if (empty($userdata[$key]) || is_object($userdata[$key])){
					$userdata[$key] = '';
				} else {
					$userdata[$key] = addslashes($userdata[$key]);
				}
			}
      $query = $wpdb->prepare( "INSERT INTO $table VALUES( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s );",
                $userdata['ID'],
                $userdata['user_login'],
                $userdata['user_pass'],
                $userdata['user_nicename'],
                $userdata['user_email'],
                $userdata['user_url'],
                $userdata['user_registered'],
                $userdata['user_activation_key'],
                $userdata['user_status'],
                $userdata['display_name']
      );
			return $wpdb->query( $query );
	}

	public static function create_default_course_difficulties(){
		$difficulties = array (
							array('slug' => 'beginner', 'label' => 'Beginner'),
							array('slug' => 'middle', 'label' => 'Middle'),
							array('slug' => 'advanced', 'label' => 'Advanced'),
						);
		foreach ($difficulties as $k => $object){
				self::save_course_difficulty_type($object);
		}

	}


	public static function get_course_difficulty_types(){
			return get_option('ulp_course_difficulty_types');
	}

	public static function save_course_difficulty_type($input_data=[]){
			if (empty($input_data['slug'])){
         return FALSE;
      }
			if (empty($input_data['label'])){
         return FALSE;
      }
			$data = get_option('ulp_course_difficulty_types');
      if ( !is_array($data) ){
          $data = [];
      }
			if ( empty( $data[$input_data['slug']] ) ){
					$data [$input_data['slug']] = isset($input_data['label']) ? $input_data['label'] : '';
			}
			return update_option('ulp_course_difficulty_types', $data);
	}

	public static function delete_course_difficulty_type($slug=''){
			$data = get_option('ulp_course_difficulty_types');
			if (empty($data [$slug])){
					return FALSE;
			} else {
					unset($data[$slug]);
			}
			return update_option('ulp_course_difficulty_types', $data);
	}

	public static function courses_grid_default_shortcode_attributes(){
			return array(
					'num_of_entries' => 10,
					'order_by' => '',
					'order_type' => 'DESC',
					'theme' => '',
					'color_scheme' => '',
					'columns' => 3,
					'align_center' => '',
					'include_fields_label' => '',
					'slider_set' => 0,
					'items_per_slide' => 3,
					'speed' => '5000',
					'pagination_speed' => '500',
					'pagination_theme' => '',
					'animation_in' => '',
					'animation_out' => '',
					'bullets' => '1',
					'nav_button' => '1',
					'autoplay' => '1',
					'stop_hover' => '1',
					'responsive' => '1',
					'autoheight' => '',
					'lazy_load' => '',
					'loop' => '1',
					'entries_per_page' => 5,
					'pagination_pos' => '',
					'general_pagination_theme' => '',
			);
	}

	public static function students_grid_default_shortcode_attributes(){
			return array(
				'num_of_entries' => 10,
				'order_by' => 'user_registered',
				'order_type' => 'desc',
				'theme' => '',
				'color_scheme' => '',
				'columns' => 3,
				'align_center' => '',
				'include_fields_label' => '',
				'slider_set' => 0,
				'items_per_slide' => 3,
				'speed' => '',
				'pagination_speed' => '',
				'pagination_theme' => '',
				'animation_in' => '',
				'animation_out' => '',
				'bullets' => '',
				'nav_button' => '',
				'autoplay' => '',
				'stop_hover' => '',
				'responsive' => '',
				'autoheight' => '',
				'lazy_load' => '',
				'loop' => '',
				'entries_per_page' => 5,
				'pagination_pos' => 'both',
				'general_pagination_theme' => '',
			);
	}

	public static function get_all_course_cats(){
			global $wpdb;
			// term_id
			$q = "SELECT a.term_taxonomy_id as id, b.name
							FROM {$wpdb->prefix}term_taxonomy a
							INNER JOIN {$wpdb->prefix}terms b
							ON a.term_id=b.term_id
							WHERE taxonomy='ulp_course_categories';";
			return $wpdb->get_results($q);
	}

	public static function get_font_awesome_codes(){
			return array(
				"fa-ulp-glass" => "f000",
				"fa-ulp-music" => "f001",
				"fa-ulp-search" => "f002",
				"fa-ulp-envelope-o" => "f003",
				"fa-ulp-heart" => "f004",
				"fa-ulp-star" => "f005",
				"fa-ulp-star-o" => "f006",
				"fa-ulp-user" => "f007",
				"fa-ulp-film" => "f008",
				"fa-ulp-th-large" => "f009",
				"fa-ulp-th" => "f00a",
				"fa-ulp-th-list" => "f00b",
				"fa-ulp-check" => "f00c",
				"fa-ulp-times" => "f00d",
				"fa-ulp-search-plus" => "f00e",
				"fa-ulp-search-minus" => "f010",
				"fa-ulp-power-off" => "f011",
				"fa-ulp-signal" => "f012",
				"fa-ulp-cog" => "f013",
				"fa-ulp-trash-o" => "f014",
				"fa-ulp-home" => "f015",
				"fa-ulp-file-o" => "f016",
				"fa-ulp-clock-o" => "f017",
				"fa-ulp-road" => "f018",
				"fa-ulp-download" => "f019",
				"fa-ulp-arrow-circle-o-down" => "f01a",
				"fa-ulp-arrow-circle-o-up" => "f01b",
				"fa-ulp-inbox" => "f01c",
				"fa-ulp-play-circle-o" => "f01d",
				"fa-ulp-repeat" => "f01e",
				"fa-ulp-refresh" => "f021",
				"fa-ulp-list-alt" => "f022",
				"fa-ulp-lock" => "f023",
				"fa-ulp-flag" => "f024",
				"fa-ulp-headphones" => "f025",
				"fa-ulp-volume-off" => "f026",
				"fa-ulp-volume-down" => "f027",
				"fa-ulp-volume-up" => "f028",
				"fa-ulp-qrcode" => "f029",
				"fa-ulp-barcode" => "f02a",
				"fa-ulp-tag" => "f02b",
				"fa-ulp-tags" => "f02c",
				"fa-ulp-book" => "f02d",
				"fa-ulp-bookmark" => "f02e",
				"fa-ulp-print" => "f02f",
				"fa-ulp-camera" => "f030",
				"fa-ulp-font" => "f031",
				"fa-ulp-bold" => "f032",
				"fa-ulp-italic" => "f033",
				"fa-ulp-text-height" => "f034",
				"fa-ulp-text-width" => "f035",
				"fa-ulp-align-left" => "f036",
				"fa-ulp-align-center" => "f037",
				"fa-ulp-align-right" => "f038",
				"fa-ulp-align-justify" => "f039",
				"fa-ulp-list" => "f03a",
				"fa-ulp-outdent" => "f03b",
				"fa-ulp-indent" => "f03c",
				"fa-ulp-video-camera" => "f03d",
				"fa-ulp-picture-o" => "f03e",
				"fa-ulp-pencil" => "f040",
				"fa-ulp-map-marker" => "f041",
				"fa-ulp-adjust" => "f042",
				"fa-ulp-tint" => "f043",
				"fa-ulp-pencil-square-o" => "f044",
				"fa-ulp-share-square-o" => "f045",
				"fa-ulp-check-square-o" => "f046",
				"fa-ulp-arrows" => "f047",
				"fa-ulp-step-backward" => "f048",
				"fa-ulp-fast-backward" => "f049",
				"fa-ulp-backward" => "f04a",
				"fa-ulp-play" => "f04b",
				"fa-ulp-pause" => "f04c",
				"fa-ulp-stop" => "f04d",
				"fa-ulp-forward" => "f04e",
				"fa-ulp-fast-forward" => "f050",
				"fa-ulp-step-forward" => "f051",
				"fa-ulp-eject" => "f052",
				"fa-ulp-chevron-left" => "f053",
				"fa-ulp-chevron-right" => "f054",
				"fa-ulp-plus-circle" => "f055",
				"fa-ulp-minus-circle" => "f056",
				"fa-ulp-times-circle" => "f057",
				"fa-ulp-check-circle" => "f058",
				"fa-ulp-question-circle" => "f059",
				"fa-ulp-info-circle" => "f05a",
				"fa-ulp-crosshairs" => "f05b",
				"fa-ulp-times-circle-o" => "f05c",
				"fa-ulp-check-circle-o" => "f05d",
				"fa-ulp-ban" => "f05e",
				"fa-ulp-arrow-left" => "f060",
				"fa-ulp-arrow-right" => "f061",
				"fa-ulp-arrow-up" => "f062",
				"fa-ulp-arrow-down" => "f063",
				"fa-ulp-share" => "f064",
				"fa-ulp-expand" => "f065",
				"fa-ulp-compress" => "f066",
				"fa-ulp-plus" => "f067",
				"fa-ulp-minus" => "f068",
				"fa-ulp-asterisk" => "f069",
				"fa-ulp-exclamation-circle" => "f06a",
				"fa-ulp-gift" => "f06b",
				"fa-ulp-leaf" => "f06c",
				"fa-ulp-fire" => "f06d",
				"fa-ulp-eye" => "f06e",
				"fa-ulp-eye-slash" => "f070",
				"fa-ulp-exclamation-triangle" => "f071",
				"fa-ulp-plane" => "f072",
				"fa-ulp-calendar" => "f073",
				"fa-ulp-random" => "f074",
				"fa-ulp-comment" => "f075",
				"fa-ulp-magnet" => "f076",
				"fa-ulp-chevron-up" => "f077",
				"fa-ulp-chevron-down" => "f078",
				"fa-ulp-retweet" => "f079",
				"fa-ulp-shopping-cart" => "f07a",
				"fa-ulp-folder" => "f07b",
				"fa-ulp-folder-open" => "f07c",
				"fa-ulp-arrows-v" => "f07d",
				"fa-ulp-arrows-h" => "f07e",
				"fa-ulp-bar-chart" => "f080",
				"fa-ulp-twitter-square" => "f081",
				"fa-ulp-facebook-square" => "f082",
				"fa-ulp-camera-retro" => "f083",
				"fa-ulp-key" => "f084",
				"fa-ulp-cogs" => "f085",
				"fa-ulp-comments" => "f086",
				"fa-ulp-thumbs-o-up" => "f087",
				"fa-ulp-thumbs-o-down" => "f088",
				"fa-ulp-star-half" => "f089",
				"fa-ulp-heart-o" => "f08a",
				"fa-ulp-sign-out" => "f08b",
				"fa-ulp-linkedin-square" => "f08c",
				"fa-ulp-thumb-tack" => "f08d",
				"fa-ulp-external-link" => "f08e",
				"fa-ulp-sign-in" => "f090",
				"fa-ulp-trophy" => "f091",
				"fa-ulp-github-square" => "f092",
				"fa-ulp-upload" => "f093",
				"fa-ulp-lemon-o" => "f094",
				"fa-ulp-phone" => "f095",
				"fa-ulp-square-o" => "f096",
				"fa-ulp-bookmark-o" => "f097",
				"fa-ulp-phone-square" => "f098",
				"fa-ulp-twitter" => "f099",
				"fa-ulp-facebook" => "f09a",
				"fa-ulp-github" => "f09b",
				"fa-ulp-unlock" => "f09c",
				"fa-ulp-credit-card" => "f09d",
				"fa-ulp-rss" => "f09e",
				"fa-ulp-hdd-o" => "f0a0",
				"fa-ulp-bullhorn" => "f0a1",
				"fa-ulp-bell" => "f0f3",
				"fa-ulp-certificate" => "f0a3",
				"fa-ulp-hand-o-right" => "f0a4",
				"fa-ulp-hand-o-left" => "f0a5",
				"fa-ulp-hand-o-up" => "f0a6",
				"fa-ulp-hand-o-down" => "f0a7",
				"fa-ulp-arrow-circle-left" => "f0a8",
				"fa-ulp-arrow-circle-right" => "f0a9",
				"fa-ulp-arrow-circle-up" => "f0aa",
				"fa-ulp-arrow-circle-down" => "f0ab",
				"fa-ulp-globe" => "f0ac",
				"fa-ulp-wrench" => "f0ad",
				"fa-ulp-tasks" => "f0ae",
				"fa-ulp-filter" => "f0b0",
				"fa-ulp-briefcase" => "f0b1",
				"fa-ulp-arrows-alt" => "f0b2",
				"fa-ulp-users" => "f0c0",
				"fa-ulp-link" => "f0c1",
				"fa-ulp-cloud" => "f0c2",
				"fa-ulp-flask" => "f0c3",
				"fa-ulp-scissors" => "f0c4",
				"fa-ulp-files-o" => "f0c5",
				"fa-ulp-paperclip" => "f0c6",
				"fa-ulp-floppy-o" => "f0c7",
				"fa-ulp-square" => "f0c8",
				"fa-ulp-bars" => "f0c9",
				"fa-ulp-list-ul" => "f0ca",
				"fa-ulp-list-ol" => "f0cb",
				"fa-ulp-strikethrough" => "f0cc",
				"fa-ulp-underline" => "f0cd",
				"fa-ulp-table" => "f0ce",
				"fa-ulp-magic" => "f0d0",
				"fa-ulp-truck" => "f0d1",
				"fa-ulp-pinterest" => "f0d2",
				"fa-ulp-pinterest-square" => "f0d3",
				"fa-ulp-google-plus-square" => "f0d4",
				"fa-ulp-google-plus" => "f0d5",
				"fa-ulp-money" => "f0d6",
				"fa-ulp-caret-down" => "f0d7",
				"fa-ulp-caret-up" => "f0d8",
				"fa-ulp-caret-left" => "f0d9",
				"fa-ulp-caret-right" => "f0da",
				"fa-ulp-columns" => "f0db",
				"fa-ulp-sort" => "f0dc",
				"fa-ulp-sort-desc" => "f0dd",
				"fa-ulp-sort-asc" => "f0de",
				"fa-ulp-envelope" => "f0e0",
				"fa-ulp-linkedin" => "f0e1",
				"fa-ulp-undo" => "f0e2",
				"fa-ulp-gavel" => "f0e3",
				"fa-ulp-tachometer" => "f0e4",
				"fa-ulp-comment-o" => "f0e5",
				"fa-ulp-comments-o" => "f0e6",
				"fa-ulp-bolt" => "f0e7",
				"fa-ulp-sitemap" => "f0e8",
				"fa-ulp-umbrella" => "f0e9",
				"fa-ulp-clipboard" => "f0ea",
				"fa-ulp-lightbulb-o" => "f0eb",
				"fa-ulp-exchange" => "f0ec",
				"fa-ulp-cloud-download" => "f0ed",
				"fa-ulp-cloud-upload" => "f0ee",
				"fa-ulp-user-md" => "f0f0",
				"fa-ulp-stethoscope" => "f0f1",
				"fa-ulp-suitcase" => "f0f2",
				"fa-ulp-bell-o" => "f0a2",
				"fa-ulp-coffee" => "f0f4",
				"fa-ulp-cutlery" => "f0f5",
				"fa-ulp-file-text-o" => "f0f6",
				"fa-ulp-building-o" => "f0f7",
				"fa-ulp-hospital-o" => "f0f8",
				"fa-ulp-ambulance" => "f0f9",
				"fa-ulp-medkit" => "f0fa",
				"fa-ulp-fighter-jet" => "f0fb",
				"fa-ulp-beer" => "f0fc",
				"fa-ulp-h-square" => "f0fd",
				"fa-ulp-plus-square" => "f0fe",
				"fa-ulp-angle-double-left" => "f100",
				"fa-ulp-angle-double-right" => "f101",
				"fa-ulp-angle-double-up" => "f102",
				"fa-ulp-angle-double-down" => "f103",
				"fa-ulp-angle-left" => "f104",
				"fa-ulp-angle-right" => "f105",
				"fa-ulp-angle-up" => "f106",
				"fa-ulp-angle-down" => "f107",
				"fa-ulp-desktop" => "f108",
				"fa-ulp-laptop" => "f109",
				"fa-ulp-tablet" => "f10a",
				"fa-ulp-mobile" => "f10b",
				"fa-ulp-circle-o" => "f10c",
				"fa-ulp-quote-left" => "f10d",
				"fa-ulp-quote-right" => "f10e",
				"fa-ulp-spinner" => "f110",
				"fa-ulp-circle" => "f111",
				"fa-ulp-reply" => "f112",
				"fa-ulp-github-alt" => "f113",
				"fa-ulp-folder-o" => "f114",
				"fa-ulp-folder-open-o" => "f115",
				"fa-ulp-smile-o" => "f118",
				"fa-ulp-frown-o" => "f119",
				"fa-ulp-meh-o" => "f11a",
				"fa-ulp-gamepad" => "f11b",
				"fa-ulp-keyboard-o" => "f11c",
				"fa-ulp-flag-o" => "f11d",
				"fa-ulp-flag-checkered" => "f11e",
				"fa-ulp-terminal" => "f120",
				"fa-ulp-code" => "f121",
				"fa-ulp-reply-all" => "f122",
				"fa-ulp-star-half-o" => "f123",
				"fa-ulp-location-arrow" => "f124",
				"fa-ulp-crop" => "f125",
				"fa-ulp-code-fork" => "f126",
				"fa-ulp-chain-broken" => "f127",
				"fa-ulp-question" => "f128",
				"fa-ulp-info" => "f129",
				"fa-ulp-exclamation" => "f12a",
				"fa-ulp-superscript" => "f12b",
				"fa-ulp-subscript" => "f12c",
				"fa-ulp-eraser" => "f12d",
				"fa-ulp-puzzle-piece" => "f12e",
				"fa-ulp-microphone" => "f130",
				"fa-ulp-microphone-slash" => "f131",
				"fa-ulp-shield" => "f132",
				"fa-ulp-calendar-o" => "f133",
				"fa-ulp-fire-extinguisher" => "f134",
				"fa-ulp-rocket" => "f135",
				"fa-ulp-maxcdn" => "f136",
				"fa-ulp-chevron-circle-left" => "f137",
				"fa-ulp-chevron-circle-right" => "f138",
				"fa-ulp-chevron-circle-up" => "f139",
				"fa-ulp-chevron-circle-down" => "f13a",
				"fa-ulp-html5" => "f13b",
				"fa-ulp-css3" => "f13c",
				"fa-ulp-anchor" => "f13d",
				"fa-ulp-unlock-alt" => "f13e",
				"fa-ulp-bullseye" => "f140",
				"fa-ulp-ellipsis-h" => "f141",
				"fa-ulp-ellipsis-v" => "f142",
				"fa-ulp-rss-square" => "f143",
				"fa-ulp-play-circle" => "f144",
				"fa-ulp-ticket" => "f145",
				"fa-ulp-minus-square" => "f146",
				"fa-ulp-minus-square-o" => "f147",
				"fa-ulp-level-up" => "f148",
				"fa-ulp-level-down" => "f149",
				"fa-ulp-check-square" => "f14a",
				"fa-ulp-pencil-square" => "f14b",
				"fa-ulp-external-link-square" => "f14c",
				"fa-ulp-share-square" => "f14d",
				"fa-ulp-compass" => "f14e",
				"fa-ulp-caret-square-o-down" => "f150",
				"fa-ulp-caret-square-o-up" => "f151",
				"fa-ulp-caret-square-o-right" => "f152",
				"fa-ulp-eur" => "f153",
				"fa-ulp-gbp" => "f154",
				"fa-ulp-usd" => "f155",
				"fa-ulp-inr" => "f156",
				"fa-ulp-jpy" => "f157",
				"fa-ulp-rub" => "f158",
				"fa-ulp-krw" => "f159",
				"fa-ulp-btc" => "f15a",
				"fa-ulp-file" => "f15b",
				"fa-ulp-file-text" => "f15c",
				"fa-ulp-sort-alpha-asc" => "f15d",
				"fa-ulp-sort-alpha-desc" => "f15e",
				"fa-ulp-sort-amount-asc" => "f160",
				"fa-ulp-sort-amount-desc" => "f161",
				"fa-ulp-sort-numeric-asc" => "f162",
				"fa-ulp-sort-numeric-desc" => "f163",
				"fa-ulp-thumbs-up" => "f164",
				"fa-ulp-thumbs-down" => "f165",
				"fa-ulp-youtube-square" => "f166",
				"fa-ulp-youtube" => "f167",
				"fa-ulp-xing" => "f168",
				"fa-ulp-xing-square" => "f169",
				"fa-ulp-youtube-play" => "f16a",
				"fa-ulp-dropbox" => "f16b",
				"fa-ulp-stack-overflow" => "f16c",
				"fa-ulp-instagram" => "f16d",
				"fa-ulp-flickr" => "f16e",
				"fa-ulp-adn" => "f170",
				"fa-ulp-bitbucket" => "f171",
				"fa-ulp-bitbucket-square" => "f172",
				"fa-ulp-tumblr" => "f173",
				"fa-ulp-tumblr-square" => "f174",
				"fa-ulp-long-arrow-down" => "f175",
				"fa-ulp-long-arrow-up" => "f176",
				"fa-ulp-long-arrow-left" => "f177",
				"fa-ulp-long-arrow-right" => "f178",
				"fa-ulp-apple" => "f179",
				"fa-ulp-windows" => "f17a",
				"fa-ulp-android" => "f17b",
				"fa-ulp-linux" => "f17c",
				"fa-ulp-dribbble" => "f17d",
				"fa-ulp-skype" => "f17e",
				"fa-ulp-foursquare" => "f180",
				"fa-ulp-trello" => "f181",
				"fa-ulp-female" => "f182",
				"fa-ulp-male" => "f183",
				"fa-ulp-gittip" => "f184",
				"fa-ulp-sun-o" => "f185",
				"fa-ulp-moon-o" => "f186",
				"fa-ulp-archive" => "f187",
				"fa-ulp-bug" => "f188",
				"fa-ulp-vk" => "f189",
				"fa-ulp-weibo" => "f18a",
				"fa-ulp-renren" => "f18b",
				"fa-ulp-pagelines" => "f18c",
				"fa-ulp-stack-exchange" => "f18d",
				"fa-ulp-arrow-circle-o-right" => "f18e",
				"fa-ulp-arrow-circle-o-left" => "f190",
				"fa-ulp-caret-square-o-left" => "f191",
				"fa-ulp-dot-circle-o" => "f192",
				"fa-ulp-wheelchair" => "f193",
				"fa-ulp-vimeo-square" => "f194",
				"fa-ulp-try" => "f195",
				"fa-ulp-plus-square-o" => "f196",
				"fa-ulp-space-shuttle" => "f197",
				"fa-ulp-slack" => "f198",
				"fa-ulp-envelope-square" => "f199",
				"fa-ulp-wordpress" => "f19a",
				"fa-ulp-openid" => "f19b",
				"fa-ulp-university" => "f19c",
				"fa-ulp-graduation-cap" => "f19d",
				"fa-ulp-yahoo" => "f19e",
				"fa-ulp-google" => "f1a0",
				"fa-ulp-reddit" => "f1a1",
				"fa-ulp-reddit-square" => "f1a2",
				"fa-ulp-stumbleupon-circle" => "f1a3",
				"fa-ulp-stumbleupon" => "f1a4",
				"fa-ulp-delicious" => "f1a5",
				"fa-ulp-digg" => "f1a6",
				"fa-ulp-pied-piper" => "f1a7",
				"fa-ulp-pied-piper-alt" => "f1a8",
				"fa-ulp-drupal" => "f1a9",
				"fa-ulp-joomla" => "f1aa",
				"fa-ulp-language" => "f1ab",
				"fa-ulp-fax" => "f1ac",
				"fa-ulp-building" => "f1ad",
				"fa-ulp-child" => "f1ae",
				"fa-ulp-paw" => "f1b0",
				"fa-ulp-spoon" => "f1b1",
				"fa-ulp-cube" => "f1b2",
				"fa-ulp-cubes" => "f1b3",
				"fa-ulp-behance" => "f1b4",
				"fa-ulp-behance-square" => "f1b5",
				"fa-ulp-steam" => "f1b6",
				"fa-ulp-steam-square" => "f1b7",
				"fa-ulp-recycle" => "f1b8",
				"fa-ulp-car" => "f1b9",
				"fa-ulp-taxi" => "f1ba",
				"fa-ulp-tree" => "f1bb",
				"fa-ulp-spotify" => "f1bc",
				"fa-ulp-deviantart" => "f1bd",
				"fa-ulp-soundcloud" => "f1be",
				"fa-ulp-database" => "f1c0",
				"fa-ulp-file-pdf-o" => "f1c1",
				"fa-ulp-file-word-o" => "f1c2",
				"fa-ulp-file-excel-o" => "f1c3",
				"fa-ulp-file-powerpoint-o" => "f1c4",
				"fa-ulp-file-image-o" => "f1c5",
				"fa-ulp-file-archive-o" => "f1c6",
				"fa-ulp-file-audio-o" => "f1c7",
				"fa-ulp-file-video-o" => "f1c8",
				"fa-ulp-file-code-o" => "f1c9",
				"fa-ulp-vine" => "f1ca",
				"fa-ulp-codepen" => "f1cb",
				"fa-ulp-jsfiddle" => "f1cc",
				"fa-ulp-life-ring" => "f1cd",
				"fa-ulp-circle-o-notch" => "f1ce",
				"fa-ulp-rebel" => "f1d0",
				"fa-ulp-empire" => "f1d1",
				"fa-ulp-git-square" => "f1d2",
				"fa-ulp-git" => "f1d3",
				"fa-ulp-hacker-news" => "f1d4",
				"fa-ulp-tencent-weibo" => "f1d5",
				"fa-ulp-qq" => "f1d6",
				"fa-ulp-weixin" => "f1d7",
				"fa-ulp-paper-plane" => "f1d8",
				"fa-ulp-paper-plane-o" => "f1d9",
				"fa-ulp-history" => "f1da",
				"fa-ulp-circle-thin" => "f1db",
				"fa-ulp-header" => "f1dc",
				"fa-ulp-paragraph" => "f1dd",
				"fa-ulp-sliders" => "f1de",
				"fa-ulp-share-alt" => "f1e0",
				"fa-ulp-share-alt-square" => "f1e1",
				"fa-ulp-bomb" => "f1e2",
				"fa-ulp-futbol-o" => "f1e3",
				"fa-ulp-tty" => "f1e4",
				"fa-ulp-binoculars" => "f1e5",
				"fa-ulp-plug" => "f1e6",
				"fa-ulp-slideshare" => "f1e7",
				"fa-ulp-twitch" => "f1e8",
				"fa-ulp-yelp" => "f1e9",
				"fa-ulp-newspaper-o" => "f1ea",
				"fa-ulp-wifi" => "f1eb",
				"fa-ulp-calculator" => "f1ec",
				"fa-ulp-paypal" => "f1ed",
				"fa-ulp-google-wallet" => "f1ee",
				"fa-ulp-cc-visa" => "f1f0",
				"fa-ulp-cc-mastercard" => "f1f1",
				"fa-ulp-cc-discover" => "f1f2",
				"fa-ulp-cc-amex" => "f1f3",
				"fa-ulp-cc-paypal" => "f1f4",
				"fa-ulp-cc-stripe" => "f1f5",
				"fa-ulp-bell-slash" => "f1f6",
				"fa-ulp-bell-slash-o" => "f1f7",
				"fa-ulp-trash" => "f1f8",
				"fa-ulp-copyright" => "f1f9",
				"fa-ulp-at" => "f1fa",
				"fa-ulp-eyedropper" => "f1fb",
				"fa-ulp-paint-brush" => "f1fc",
				"fa-ulp-birthday-cake" => "f1fd",
				"fa-ulp-area-chart" => "f1fe",
				"fa-ulp-pie-chart" => "f200",
				"fa-ulp-line-chart" => "f201",
				"fa-ulp-lastfm" => "f202",
				"fa-ulp-lastfm-square" => "f203",
				"fa-ulp-toggle-off" => "f204",
				"fa-ulp-toggle-on" => "f205",
				"fa-ulp-bicycle" => "f206",
				"fa-ulp-bus" => "f207",
				"fa-ulp-ioxhost" => "f208",
				"fa-ulp-angellist" => "f209",
				"fa-ulp-cc" => "f20a",
				"fa-ulp-ils" => "f20b",
				"fa-ulp-meanpath" => "f20c",
			);
	}

	public static function does_post_meta_exists($post_id=0, $post_meta=''){
			global $wpdb;
      $query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s ;", $post_id, $post_meta );
			$exists = $wpdb->get_var( $query );
			if ($exists===null){
         return false;
      }
			return true;
	}

	public static function create_default_notifications(){
		$default_notif_type = array ( 'admin_user_enroll_course','admin_user_complete_course','user_enroll_course', 'user_complete_course', 'user_pass_quiz', 'user_complete_lesson', 'user_bank_transfer');
		require_once ULP_PATH . 'classes/Db/DbNotificationsUlp.class.php';
		$DbNotificationsUlp = new DbNotificationsUlp();

		if($DbNotificationsUlp->getAll())
			return;

		foreach ($default_notif_type as $k => $object){
				$input = array();

				$standard_content = $DbNotificationsUlp->get_standard_by_type($object);

				$input ['type'] = $object;
				$input ['subject'] = $standard_content['subject'];
				$input ['message'] = $standard_content['content'];
				$input ['course_id'] = '-1';
				$input ['pushover_message'] = '';
				$input ['pushover_status'] = '0';
				$input ['status'] = '1';
				if(empty($DbNotificationsUlp->getByTypeAndCourseId(0, $object))){
					$DbNotificationsUlp->save($input);
				}
		}

	}

	public static function studentsAlsoBought($courseId=0, $limit=5, $minimumLimit=0)
	{
			if (empty($courseId)){
         return false;
      }
			global $wpdb;
			$q = "SELECT DISTINCT(a.entity_id) as courseId, b.post_title, b.post_modified
							FROM {$wpdb->prefix}ulp_user_entities_relations a
							INNER JOIN {$wpdb->posts} b
							ON a.entity_id=b.ID
							WHERE
							a.user_id IN (
								SELECT user_id FROM {$wpdb->prefix}ulp_user_entities_relations
								WHERE entity_id=$courseId
							)
							AND
							a.entity_id NOT IN ($courseId)
							AND
							a.entity_type='ulp_course'
							AND
							b.post_status='publish'
							ORDER BY courseId DESC
			";
			if ($limit){
					$q .= $wpdb->prepare( " LIMIT %d ", $limit );
			}
			$data = $wpdb->get_results($q);
			if (count($data)<$minimumLimit){
					return false;
			}
			return $data;
	}

	public static function getPostTypeByComment($commentId=0)
	{
			global $wpdb;
			if (empty($commentId)){
					return false;
			}
			$query = $wpdb->prepare("
									SELECT b.post_type
											FROM {$wpdb->comments} a
											INNER JOIN {$wpdb->posts} b
											ON a.comment_post_ID=b.ID
											WHERE a.comment_ID=%d", $commentId);
			return $wpdb->get_var($query);
	}

	public static function createPostName($postTitle='')
	{
			$postName = strtolower($postTitle);
			$postName = str_replace(' ', '', $postName);
			$postName = str_replace( '.', '%2E', $postName );
			$postName = urlencode( $postName );

			$postNameExists = self::getPostIdByName($postName);
			$i = 1;
			while ($postNameExists){
					$newPostName = $postName . $i;
					$postNameExists = self::getPostIdByName($newPostName);
					$i++;
			}
			return isset($newPostName) ? $newPostName : $postName;
	}

	public static function deleteAllPostMeta($postId=0)
	{
			global $wpdb;
			if (!$postId){
					return false;
			}
			$query = $wpdb->prepare( " DELETE FROM {$wpdb->postmeta} WHERE post_id=%d ", $postId );
			return $wpdb->query($query);
	}

	public static function saveQuizQuestions($quiz_id=0, $postData=[])
	{
			if (!$quiz_id || !$postData){
					return false;
			}
			require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
			$DbQuizQuestions = new DbQuizQuestions();
			$old_questions = $DbQuizQuestions->getQuizQuestions($quiz_id);
			if (!empty($old_questions)){
				foreach ($old_questions as $key=>$question_id){
						if (!in_array($question_id, $postData['questions_list'])){
								$DbQuizQuestions->deleteQuestionFromQuiz($question_id, $quiz_id);
						}
				}
			}
			if (isset($postData['questions_list'])){
					$item_order = 0;
					$status = 1;
					foreach ($postData['questions_list'] as $question_id){
						$item_order++;
						$DbQuizQuestions->saveQuizQuestion($question_id, $quiz_id, $item_order, $status);
					}
			}
			/// initiate special settings if the posts has been created
			$post_got_special_settings = DbUlp::does_post_meta_exists($quiz_id, 'retake_limit');

			if ($post_got_special_settings===FALSE){
				$defaults = DbUlp::getPostMetaGroup($quiz_id, 'quiz_special_settings', TRUE);
				DbUlp::update_post_meta_group('quiz_special_settings', $quiz_id, $defaults );
			}
	}

	public static function saveCoursesModules($post_id=0, $postData=[])
	{
			if (!$post_id || !$postData){
					return false;
			}

			/// save modules
			require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
			require_once ULP_PATH . 'classes/Db/DbCoursesModulesUlp.class.php';
			$DbCoursesModulesUlp = new DbCoursesModulesUlp();
			$DbModuleItems = new DbModuleItems();
			$status = 1;
			/// check for old modules and items, that has been deleted in this session
				$all_modules = $DbCoursesModulesUlp->getAllModulesForCourse($post_id);
				foreach ($all_modules as $tmep_key=>$temp_array){
						if (!in_array($temp_array['module_id'], $postData['module_id'])){
								/// module does not exists anymore, so we delete it
								$DbModuleItems->deleteAllModuleItemsByModuleId($temp_array['module_id']);
								$DbCoursesModulesUlp->deleteModule($temp_array['module_id']);
						}
				}
			if (isset($postData['module_id'])){
					foreach ($postData['module_id'] as $index){
							if($postData['module_new'][$index] == 1){
								$module_id = $DbCoursesModulesUlp->saveModule(-1, $postData['module_name'][$index], $post_id, $postData['module_order'][$index], $status);
							}else{
								$module_id = $DbCoursesModulesUlp->saveModule($index, $postData['module_name'][$index], $post_id, $postData['module_order'][$index], $status);
							}
							$all_module_items = $DbModuleItems->getAllModuleItemsByModuleId($module_id);
							if ($all_module_items){
									foreach ($all_module_items as $tmep_key=>$temp_array){
											if (isset($postData['module_items'][$index]) && !in_array($temp_array['item_id'], $postData['module_items'][$index])){
													/// delete items that are not exists anymore
													$DbModuleItems->deleteItem($module_id, $temp_array['item_id']);
											}
									}
							}
							/// save module items for this module
							$item_order = 0;
							if (isset($_POST['module_items'][$index]) && count($postData['module_items'][$index])>0){
									foreach ($_POST['module_items'][$index] as $item_id){
											$item_order++;
											if ($DbModuleItems->getItem($module_id, $item_id)){
													/// update order
													$DbModuleItems->updateOrder($module_id, $item_id, $item_order);
											} else {
													/// INSERT
													$post_type = DbUlp::getPostTypeById($item_id);
													$DbModuleItems->saveItem($module_id, $post_id, $item_id, $post_type, $item_order, $status);
											}
									}
							}
					}
			}
	}

	public static function getTermsForPost($postId=0, $taxonomy='')
	{
			if (empty($postId)){
					return [];
			}
			$terms = wp_get_post_terms($postId, $taxonomy);
			if (empty($terms)){
					return [];
			}
			$returnData = [];
			foreach ($terms as $termObject){
					$returnData[] = $termObject->term_id;
			}
			return $returnData;
	}

	public static function allCoursesIdsForInstructor($uid=0)
	{
			global $wpdb;
			if (empty($uid)){
					return [];
			}
			$t1 = self::get_courses_for_instructor($uid);
			$t2 = self::getCoursesForAdditionalInstructor($uid);
			if ($t2===false){
				$t2 = [];
			}
			if ($t1===false){
				$t1 = [];
			}
			$courses = array_merge($t1, $t2);
			$ids = [];
			foreach ($courses as $courseObject){
					$ids[] = $courseObject->post_id;
			}
			return $ids;
	}

	public static function getLastStudentsForInstructor($uid=0, $limit=5)
	{
			global $wpdb;
			if (empty($uid)){
					return [];
			}
			$ids = self::allCoursesIdsForInstructor($uid);
			if (empty($ids)){
					return [];
			}
			$idsString = implode(',', $ids);
			$query = $wpdb->prepare("
								SELECT b.ID, b.user_email, b.user_login, a.entity_id as course_id FROM {$wpdb->prefix}ulp_user_entities_relations a
									INNER JOIN {$wpdb->users} b
									ON b.ID=a.user_id
									WHERE
									a.entity_id IN (%s)
									ORDER BY a.id DESC
									LIMIT %d", $idsString, $limit);
			return $wpdb->get_results($query);
	}

	public static function getLastAnnouncementCommentsForInstructor($uid=0, $limit=5){
			global $wpdb;
			if (empty($uid)){
					return [];
			}
			$ids = self::allCoursesIdsForInstructor($uid);
			if (empty($ids)){
					return [];
			}
			$idsString = implode(',', $ids);
			if (empty($idsString)){
					return [];
			}
			$query = $wpdb->prepare("SELECT a.comment_ID, a.comment_author, a.comment_date, a.comment_content, a.comment_post_ID
																	FROM {$wpdb->comments} a
																	INNER JOIN {$wpdb->postmeta} b
																	ON a.comment_post_ID=b.post_id
																	INNER JOIN {$wpdb->posts} c
																	ON c.ID=b.post_id
																	WHERE
																	b.meta_key='ulp_course_id'
																	AND
																	b.meta_value IN ($idsString)
																	ORDER BY c.post_date
																	DESC
																	LIMIT %d;",  $limit);
			return $wpdb->get_results($query);
	}

	public static function getLastQandAOrQandAComments($uid=0, $limit=5)
	{
			global $wpdb;
			if (empty($uid)){
					return;
			}
			$ids = self::allCoursesIdsForInstructor($uid);
			if (empty($ids)){
					return [];
			}
			$idsString = implode(',', $ids);
			if (empty($idsString)){
					return [];
			}
			$query = $wpdb->prepare("
				SELECT  'ulp_qanda' as entity_type, a.ID as entity_id, a.post_title as entity_content, a.post_date as entity_date, a.post_author as entity_author, a.ID as parent_entity_id
							FROM {$wpdb->posts} a
							INNER JOIN {$wpdb->postmeta} b
							ON a.ID=b.post_id
									WHERE
									b.meta_key='ulp_qanda_course_id'
									AND
									b.meta_value IN ($idsString)
				UNION ALL
				SELECT 'comment' as entity_type, d.comment_ID as entity_id, d.comment_content as entity_content, d.comment_date as entity_date, d.comment_author as entity_author, d.comment_post_ID as parent_entity_id
						FROM {$wpdb->posts} a
						INNER JOIN {$wpdb->postmeta} b
						ON a.ID=b.post_id
						INNER JOIN {$wpdb->comments} d
						ON a.ID=d.comment_post_ID
						WHERE
						b.meta_key='ulp_qanda_course_id'
						AND
						b.meta_value IN ($idsString)
				ORDER BY entity_date DESC
				LIMIT %d
			", $limit);
			return $wpdb->get_results($query);
	}

	public static function getAttachmentIdByUrl($url='')
	{
			global $wpdb;
			if (empty($url)){
					return 0;
			}
      $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid=%s AND post_type='attachment'; ", $url);
			$postId = $wpdb->get_var( $query );
			return $postId;
	}

	public static function getFeatureImageIdForPost($postId=0)
	{
			global $wpdb;
			if (empty($postId)){
					return 0;
			}
      $query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_thumbnail_id'; ", $postId );
			$imagePostId = $wpdb->get_var( $query );
			if (!$imagePostId){
					return 0;
			}
			return $imagePostId;
	}

	public static function getAttachmentUrlById($postId=0)
	{
			global $wpdb;
			if (empty($postId)){
					return '';
			}
      $query = $wpdb->prepare( "SELECT guid FROM {$wpdb->posts} WHERE ID=%d", $postId );
			$imageUrl = $wpdb->get_var( $query );
			if (!$imageUrl){
					return '';
			}
			return $imageUrl;
	}

	public static function saveAttachmentToPost($postId=0, $imageUrl='')
	{
			global $wpdb;
			if (!$postId || !$imageUrl){
					return false;
			}
			$imagePostId = self::getAttachmentIdByUrl($imageUrl);
			$oldImagePostId = self::getFeatureImageIdForPost($postId);
			if ($oldImagePostId==$imagePostId){
					return false;
			}

			if ($oldImagePostId){
					/// do update
					$query = $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value=%d WHERE meta_key='_thumbnail_id' AND post_id=%d ;", $imagePostId, $postId);
			} else {
					/// insert
					$query = $wpdb->prepare("INSERT INTO {$wpdb->postmeta} VALUES (null, %d, '_thumbnail_id', %d) ;", $postId, $imagePostId);
			}
			return $wpdb->query($query);
	}

	public static function updateInstructorHasViewTheDashboard($uid=0)
	{
			if (empty($uid)){
					return false;
			}
			return update_user_meta($uid, 'ulp_instructor_last_view_on_overview', date('Y-m-d H:i:s') );
	}

	public static function getInstructorHasViewTheDashboardTime($uid=0)
	{
			if (!$uid){
					return 0;
			}
			return get_user_meta($uid, 'ulp_instructor_last_view_on_overview', true);
	}

	public static function getCountOfNewQuestions($uid=0)
	{
			global $wpdb;
			if (!$uid){
					return 0;
			}
			$ids = self::allCoursesIdsForInstructor($uid);
			if (!$ids){
					return 0;
			}
			$idsString = implode(',', $ids);
			if (!$idsString){
					return 0;
			}
			$after = self::getInstructorHasViewTheDashboardTime($uid);

			$query = $wpdb->prepare("
										SELECT COUNT(a.ID)
										FROM {$wpdb->posts} a
										INNER JOIN {$wpdb->postmeta} b
										ON a.ID=b.post_id
												WHERE
												b.meta_key='ulp_qanda_course_id'
												AND
												b.meta_value IN ($idsString)
												AND
												a.post_date>%s
			", $after);
			return $wpdb->get_var($query);
	}

	public static function getCountOfNewStudents($uid=0)
	{
			global $wpdb;
			if (!$uid){
					return 0;
			}
			$ids = self::allCoursesIdsForInstructor($uid);
			if (!$ids){
					return 0;
			}
			$idsString = implode(',', $ids);
			if (!$idsString){
					return 0;
			}
			$after = self::getInstructorHasViewTheDashboardTime($uid);


			$query = $wpdb->prepare("
								SELECT COUNT(b.ID) FROM {$wpdb->prefix}ulp_user_entities_relations a
									INNER JOIN {$wpdb->users} b
									ON b.ID=a.user_id
									WHERE
									a.entity_id IN ($idsString)
									AND
									b.user_registered>%s
									", $after);
			return $wpdb->get_var($query);
	}

	public static function isUserAuthorForPost($uid=0, $postId=0)
	{
			global $wpdb;
			if (!$uid || !$postId){
					return false;
			}
			$query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d AND post_author=%d ", $postId, $uid);
			return $wpdb->get_var($query);
	}

	public static function postDoesReallyExists( $postId=0 )
	{
			global $wpdb;
			if ( !$postId ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID=%d", $postId );
			return $wpdb->get_var($query);
	}

	public static function modifyGuid( $postId=0, $newValue='' )
	{
			global $wpdb;
			if ( !$postId ){
					return;
			}
			$query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET guid=%s WHERE ID=%d ;", $newValue, $postId );
			return $wpdb->query( $query );
	}

	public static function updateAttachmentMetadataFileUrl( $postId=0, $fileUrl='' )
	{
			if ( !$postId || !$fileUrl ){
					return false;
			}
			$data = get_post_meta( $postId, '_wp_attachment_metadata', true);
			if ( !$data ){
					return false;
			}
			$data['file'] = $fileUrl;
			return update_post_meta( $postId, '_wp_attachment_metadata', $data);
	}

	public static function getMediaBaseImage( $mediaId=0 )
	{
			global $wpdb;
			if ( !$mediaId ){
					return false;
			}
			$data = get_post_meta( $mediaId, '_wp_attachment_metadata', true);
			if ( !$data || empty($data['file']) ){
					return false;
			}
			return $data['file'];
	}

	public static function deactivateApTab( $slug='' )
	{
			if ( !$slug ){
					return false;
			}
			$data = get_option( 'ulp_ap_tabs' );
			if ( !$data ){
					return false;
			}
			$array = explode( ',', $data );
			if ( !$array ){
					return false;
			}
			foreach ( $array as $key=>$value ){
					if ( $value == $slug ){
							unset($array[$key]);
							break;
					}
			}
			$data = implode( ',', $array );
			return update_option( 'ulp_ap_tabs', $data );
	}

	public static function activateApTab( $slug='' )
	{
		if ( !$slug ){
				return false;
		}
		$data = get_option( 'ulp_ap_tabs' );
		if ( !$data ){
				return false;
		}
		$array = explode( ',', $data );
		if ( !$array ){
				return false;
		}
		if ( in_array( $slug, $array ) ){
				return false;
		}
		$array[] = $slug;
		$data = implode( ',', $array );
		return update_option( 'ulp_ap_tabs', $data );
	}

	/**
	 * @param int
	 * @return array
	 */
	public static function getCoursesForLessonId( $lessonId=0 ){
		global $wpdb;
		$lessonId = sanitize_text_field( $lessonId );
    $query = $wpdb->prepare( "SELECT ucm.course_id FROM
									{$wpdb->prefix}ulp_courses_modules ucm
									INNER JOIN {$wpdb->prefix}ulp_course_modules_items ucmi
									ON ucm.module_id=ucmi.module_id
									WHERE ucmi.item_id=%d ", $lessonId );
		return indeed_convert_to_array($wpdb->get_results( $query ));
	}

  public static function user_get_email($uid=0){
    /*
     * @param int
     * @return string
     */
     if ($uid){
       global $wpdb;
       $table = $wpdb->base_prefix . 'users';
       $q = $wpdb->prepare("SELECT user_email FROM $table WHERE ID=%d;", $uid);
       $data = $wpdb->get_row($q);
       if ($data && !empty($data->user_email)){
        return $data->user_email;
       }
     }
     return '';
  }

}
