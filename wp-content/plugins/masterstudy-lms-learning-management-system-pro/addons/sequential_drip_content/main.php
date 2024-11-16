<?php

use \MasterStudy\Lms\Repositories\CurriculumMaterialRepository;

new STM_LMS_Sequential_Drip_Content();

class STM_LMS_Sequential_Drip_Content {

	public function __construct() {
		add_filter( 'stm_lms_show_item_content', array( $this, 'show_item_content' ), 10, 3 );
		add_filter( 'stm_lms_course_item_content', array( $this, 'lesson_content' ), 10, 4 );
		add_filter( 'stm_lms_prev_status', array( $this, 'prev_status' ), 10, 4 );

		add_filter( 'wpcfto_options_page_setup', array( $this, 'stm_lms_settings_page' ) );
	}

	/*Settings*/
	public function stm_lms_settings_page( $setups ) {
		$setups[] = array(
			'page'        => array(
				'parent_slug' => 'stm-lms-settings',
				'page_title'  => 'Sequential Drip Content',
				'menu_title'  => 'Drip Content Settings',
				'menu_slug'   => 'sequential_drip_content',
			),
			'fields'      => $this->stm_lms_settings(),
			'option_name' => 'stm_lms_sequential_drip_content_settings',
		);

		return $setups;
	}

	public function stm_lms_settings() {
		return apply_filters(
			'stm_lms_sequential_drip_content_settings',
			array(
				'credentials' => array(
					'name'   => esc_html__( 'Credentials', 'masterstudy-lms-learning-management-system-pro' ),
					'fields' => array(
						'locked'            => array(
							'type'        => 'checkbox',
							'label'       => esc_html__( 'Lock lessons in order', 'masterstudy-lms-learning-management-system-pro' ),
							'description' => esc_html__( 'Enable this to lock lessons in a course so that students can only access them sequentially, one after another', 'masterstudy-lms-learning-management-system-pro' ),
							'value'       => false,
						),
						'lock_before_start' => array(
							'type'        => 'checkbox',
							'label'       => esc_html__( 'Lock lesson till its start time', 'masterstudy-lms-learning-management-system-pro' ),
							'description' => esc_html__( 'If enabled, lessons will remain locked until their designated start time, so that students can not access them before then', 'masterstudy-lms-learning-management-system-pro' ),
							'value'       => false,
						),
					),
				),
			)
		);
	}

	public static function stm_lms_get_settings() {
		return get_option( 'stm_lms_sequential_drip_content_settings', array() );
	}

	public static function time_offset() {
		return get_option( 'gmt_offset' ) * 60 * 60;
	}

	public static function lesson_start_time( $item_id, $post_id ) {
		$lock_from_start = get_post_meta( $item_id, 'lesson_lock_from_start', true );
		$lock_from_time  = get_post_meta( $item_id, 'lesson_lock_start_days', true );

		if ( ! empty( $lock_from_start ) && ! empty( $lock_from_time ) ) {
			$user_course = stm_lms_get_user_course( get_current_user_id(), $post_id, array( 'start_time' ) );

			if ( ! empty( $user_course ) ) {
				$user_course = STM_LMS_Helpers::simplify_db_array( $user_course );
			}
			if ( ! empty( $user_course ) && ! empty( $user_course['start_time'] ) ) {
				return strtotime( "+{$lock_from_time} days", $user_course['start_time'] );
			}
		}

		$start_date = get_post_meta( $item_id, 'lesson_start_date', true );
		$start_time = get_post_meta( $item_id, 'lesson_start_time', true );

		if ( empty( $start_date ) || empty( $start_date ) ) {
			return '';
		}

		$offset = self::time_offset();

		$stream_start = strtotime( 'today', ( $start_date / 1000 ) ) - $offset;

		if ( ! empty( $start_time ) ) {
			$time = explode( ':', $start_time );
			if ( is_array( $time ) && count( $time ) === 2 ) {
				$stream_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $stream_start );
			}
		}

		return $stream_start;
	}

	public static function is_lesson_started( $item_id, $post_id ) {
		$stream_start = self::lesson_start_time( $item_id, $post_id );

		/*NO TIME - STREAM STARTED*/
		if ( empty( $stream_start ) ) {
			return true;
		}

		if ( $stream_start > time() ) {
			return false;
		}

		return true;
	}

	public static function show_item_content( $show, $post_id, $item_id ) {
		$settings = self::stm_lms_get_settings();
		if ( ( ! empty( $settings['lock_before_start'] ) && ! self::is_lesson_started( $item_id, $post_id ) ) ) {
			return false;
		}

		return ( self::lesson_is_locked( $post_id, $item_id ) ) ? false : $show;
	}

	public static function lesson_is_locked( $post_id, $item_id ) {
		$settings = self::stm_lms_get_settings();
		if ( empty( $settings['locked'] ) ) {
			$parent_passed = self::is_parent_passed( $post_id, $item_id, true );
			if ( isset( $parent_passed['passed'] ) && ! $parent_passed['passed'] ) {
				return true;
			}
		} else {
			$course_materials = ( new CurriculumMaterialRepository() )->get_course_materials( $post_id );
			$item_order       = array_search( intval( $item_id ), $course_materials, true );
			if ( 0 === $item_order ) {
				return false;
			}
			$prev_lesson              = ( ! empty( $course_materials[ $item_order - 1 ] ) ) ? $course_materials[ $item_order - 1 ] : 0;
			$is_prev_lesson_completed = STM_LMS_Lesson::is_lesson_completed( '', $post_id, $prev_lesson );
			if ( ! $is_prev_lesson_completed ) {
				return true;
			}
		}

		return false;
	}

	public function lesson_content( $html, $post_id, $item_id, $data ) {
		$settings = self::stm_lms_get_settings();

		if ( ! empty( $settings['lock_before_start'] ) ) {
			if ( ! self::is_lesson_started( $item_id, $post_id ) ) {
				ob_start();
				STM_LMS_Templates::show_lms_template( 'course-player/drip-content', compact( 'post_id', 'item_id', 'data' ) );
				$html = ob_get_clean();
			}
		}

		if ( empty( $settings['locked'] ) ) {
			/*Check Deps*/
			$parent_passed = self::is_parent_passed( $post_id, $item_id, true );

			if ( isset( $parent_passed['passed'] ) && ! $parent_passed['passed'] ) {
				$prev_lesson_url = STM_LMS_Lesson::get_lesson_url( $post_id, $parent_passed['parent'] );
				return STM_LMS_User::js_redirect( $prev_lesson_url );
			}

			return $html;
		}

		$course_materials = ( new CurriculumMaterialRepository() )->get_course_materials( $post_id );

		$item_order = array_search( intval( $item_id ), $course_materials, true );

		/*First item is always allowed to do*/
		if ( 0 === $item_order ) {
			return $html;
		}

		/*Check if prev lesson is passed*/
		$prev_lesson              = ( ! empty( $course_materials[ $item_order - 1 ] ) ) ? $course_materials[ $item_order - 1 ] : 0;
		$is_prev_lesson_completed = STM_LMS_Lesson::is_lesson_completed( '', $post_id, $prev_lesson );

		if ( ! $is_prev_lesson_completed ) {
			$passed_lessons     = stm_lms_get_user_course_lessons( get_current_user_id(), $post_id, array( 'lesson_id' ) );
			$last_passed_lesson = end( $passed_lessons );
			if ( ! empty( $last_passed_lesson[0] ) ) {
				$prev_lesson = $last_passed_lesson[0];
			}
			$prev_lesson_url = STM_LMS_Lesson::get_lesson_url( $post_id, $prev_lesson );
			return STM_LMS_User::js_redirect( $prev_lesson_url );
		} else {
			return $html;
		}
	}

	public function prev_status( $status, $course_id, $item_id, $user_id ) {
		$settings = self::stm_lms_get_settings();
		if ( empty( $settings['locked'] ) ) {
			$status = '';
		}

		/*Check Item Deps*/
		$parent_passed = self::is_parent_passed( $course_id, $item_id, false, $user_id );
		$status        = ( ! $parent_passed ) ? '' : 'opened';

		return "prev-status-{$status}";
	}

	public static function is_parent_passed( $course_id, $item_id, $get_parent = false, $user_id = '' ) {
		$check_parent_passed = true;

		$item_id = intval( $item_id );

		$drip_content = get_post_meta( $course_id, 'drip_content', true );

		if ( ! empty( $drip_content ) ) {
			$drip_content = json_decode( $drip_content, true );
			if ( ! empty( $drip_content ) ) {
				foreach ( $drip_content as $drip_content_single ) {
					if ( ! empty( $drip_content_single['childs'] ) ) {
						foreach ( $drip_content_single['childs'] as $drip_content_child ) {
							if ( $item_id === $drip_content_child['id'] ) {
								$parent              = $drip_content_single['parent']['id'];
								$check_parent_passed = STM_LMS_Lesson::is_lesson_completed( $user_id, $course_id, $parent );
								if ( $get_parent ) {
									$check_parent_passed = array(
										'passed' => $check_parent_passed,
										'parent' => $parent,
									);
								}
							}
						}
					}
				}
			}
		}

		return $check_parent_passed;
	}
}
