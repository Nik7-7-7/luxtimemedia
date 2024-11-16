<?php

new STM_LMS_Zoom_Conference();

class STM_LMS_Zoom_Conference {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_filter( 'stm_lms_course_item_content', array( $this, 'course_item_content' ), 10, 4 );

		add_action( 'masterstudy_lms_save_lesson', array( $this, 'update_meeting' ), 10, 2 );

		add_action( 'save_post', array( $this, 'update_admin_meeting' ), 10, 2 );

		add_filter( 'stm_lms_show_item_content', array( $this, 'show_item_content' ), 10, 3 );

		add_filter( 'wp_ajax_install_zoom_addon', array( $this, 'install_zoom_addon' ), 10, 2 );

		add_filter(
			'stm_lms_duration_field_type',
			function() {
				return 'number';
			}
		);

		add_action( 'wpcfto_options_page_setup', array( $this, 'stm_lms_settings_page' ) );

		add_action(
			'wpcfto_settings_screen_stm_lms_zoom_conference_settings_after',
			function() {
				require_once STM_LMS_PRO_ADDONS . '/zoom_conference/admin_views/install_zoom_plugin.php';
			}
		);
	}

	public static function admin_scripts() {
		wp_enqueue_script( 'admin_zoom_conference', STM_LMS_PRO_URL . '/assets/js/admin-zoom-conference.js', array( 'jquery' ), '1.0', true );
	}

	public static function install_zoom_addon() {
		check_ajax_referer( 'install_zoom_addon', 'nonce' );
		$install_plugin = STM_LMS_PRO_Plugin_Installer::install_plugin(
			array(
				'slug' => 'eroom-zoom-meetings-webinar',
			)
		);
		wp_send_json( $install_plugin );
	}

	public static function show_item_content( $show, $post_id, $item_id ) {
		if ( self::is_stream( $item_id ) ) {
			return false;
		}

		return $show;
	}

	public static function course_item_content( $content, $post_id, $item_id, $data ) {
		if ( self::is_stream( $item_id ) ) {
			ob_start();
			STM_LMS_Templates::show_lms_template( 'course-player/zoom-conference', compact( 'post_id', 'item_id', 'data' ) );
			return ob_get_clean();
		}

		return $content;
	}

	public static function is_stream( $post_id ) {
		$type = get_post_meta( $post_id, 'type', true );

		return 'zoom_conference' === $type;

	}

	public static function get_video_url( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		$url_parsed = wp_parse_url( $url );

		if ( empty( $url_parsed['host'] ) || 'www.youtube.com' !== $url_parsed['host'] || empty( $url_parsed['path'] ) ) {
			return $url;
		}

		if ( ! empty( $url_parsed['query'] ) ) {
			return str_replace( array( '/embed/', 'v=' ), array( '' ), $url_parsed['query'] ) . '&is_youtube';
		}

		return str_replace( array( '/embed/', 'v=' ), array( '' ), $url_parsed['path'] ) . '&is_youtube';
	}

	public static function stream_end_time( $item_id ) {
		$end_date = get_post_meta( $item_id, 'stream_end_date', true );
		$end_time = get_post_meta( $item_id, 'stream_end_time', true );
		$timezone = get_post_meta( $item_id, 'timezone', true );

		if ( empty( $end_date ) ) {
			return '';
		}

		$stream_end = strtotime( 'today', $end_date / 1000 );

		if ( ! empty( $end_time ) ) {
			$time = explode( ':', $end_time );
			if ( is_array( $time ) && count( $time ) === 2 && ! empty( $timezone ) ) {
				$stream_end = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $stream_end );

				$date = new DateTime( '@' . $stream_start );
				$date->setTimezone( new DateTimeZone( $timezone ) );
				$stream_start = $date->format( 'U' );
			}
		}

		return $stream_end;
	}

	public static function is_stream_ended( $item_id ) {
		$stream_end = self::stream_end_time( $item_id );

		if ( empty( $stream_end ) ) {
			return true;
		}

		if ( $stream_end > time() ) {
			return false;
		}

		return true;
	}

	public static function navigation_complete_atts( $atts, $item_id ) {
		if ( self::is_stream( $item_id ) && ! self::is_stream_ended( $item_id ) ) {
			$end_time = self::stream_end_time( $item_id );

			$end_time = ( $end_time - time() );

			return $atts . " data-timer='" . $end_time . "' data-disabled='true'";
		}

		return $atts;
	}

	public static function update_meeting( $post_id, $data ) {
		if ( 'zoom_conference' === $data['type'] ) {
			$is_edit   = get_post_meta( $post_id, 'meeting_created', true );
			$user_id   = get_current_user_id();
			$user_host = get_the_author_meta( 'stm_lms_zoom_host', $user_id );

			if ( empty( $user_host ) ) {
				return '';
			}

			$agenda                                = $data['excerpt'] ?? '';
			$timezone                              = $data['zoom_conference_timezone'] ?? 'UTC';
			$duration                              = $data['duration'] ?? '';
			$password                              = $data['zoom_conference_password'] ?? '';
			$stream_start_date                     = $data['zoom_conference_start_date'] ?? '';
			$stream_start_time                     = $data['zoom_conference_start_time'] ?? '';
			$join_before_host                      = $data['zoom_conference_join_before_host'] ?? '';
			$option_host_video                     = $data['zoom_conference_host_video'] ?? '';
			$option_participants_video             = $data['zoom_conference_participants_video'] ?? '';
			$option_mute_participants              = $data['zoom_conference_mute_participants'] ?? '';
			$option_enforce_login                  = $data['zoom_conference_enforce_login'] ?? '';
			$_POST['post_title']                   = get_the_title( $post_id );
			$_POST['stm_host']                     = $user_host;
			$_POST['stm_agenda']                   = $agenda;
			$_POST['stm_date']                     = sanitize_text_field( $stream_start_date );
			$_POST['stm_time']                     = sanitize_text_field( $stream_start_time );
			$_POST['stm_timezone']                 = sanitize_text_field( $timezone );
			$_POST['stm_duration']                 = sanitize_text_field( $duration );
			$_POST['stm_password']                 = sanitize_text_field( $password );
			$_POST['stm_join_before_host']         = sanitize_text_field( $join_before_host );
			$_POST['stm_host_join_start']          = sanitize_text_field( $option_host_video );
			$_POST['stm_start_after_participants'] = sanitize_text_field( $option_participants_video );
			$_POST['stm_mute_participants']        = sanitize_text_field( $option_mute_participants );
			$_POST['stm_enforce_login']            = sanitize_text_field( $option_enforce_login );
			$_POST['post_type']                    = 'stm-zoom';

			$post_data = array(
				'post_title'  => get_the_title( $post_id ),
				'post_status' => 'publish',
				'post_author' => $user_id,
				'post_type'   => 'stm-zoom',
			);

			if ( $is_edit ) {
				$post_data['ID'] = intval( $is_edit );
			}

			$meeting_id = wp_insert_post( $post_data );

			update_post_meta( $post_id, 'meeting_created', $meeting_id );

			if ( ! empty( $meeting_id ) ) {
				update_post_meta( $meeting_id, 'stm_host', $user_host );
				update_post_meta( $meeting_id, 'stm_agenda', $agenda );
				update_post_meta( $meeting_id, 'stm_date', $stream_start_date );
				update_post_meta( $meeting_id, 'stm_time', $stream_start_time );
				update_post_meta( $meeting_id, 'stm_timezone', $timezone );
				update_post_meta( $meeting_id, 'stm_duration', $duration );
				update_post_meta( $meeting_id, 'stm_password', $password );
				update_post_meta( $meeting_id, 'stm_join_before_host', $join_before_host );
				update_post_meta( $meeting_id, 'stm_host_join_start', $option_host_video );
				update_post_meta( $meeting_id, 'stm_start_after_participants', $option_participants_video );
				update_post_meta( $meeting_id, 'stm_mute_participants', $option_mute_participants );
				update_post_meta( $meeting_id, 'stm_enforce_login', $option_enforce_login );
			}
		}
	}

	public function update_admin_meeting( $post_id, $post ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post_type   = ! empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
		$is_revision = wp_is_post_revision( $post_id );

		if ( ! empty( $is_revision ) ) {
			$post_id = $is_revision;
		}

		if ( empty( $post_type ) ) {
			$post_type = get_post_type( $post_id );
		}

		if ( 'stm-lessons' === $post_type ) {
			$lesson_type = ! empty( $_POST['type'] ) ? $_POST['type'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( 'zoom_conference' === $lesson_type ) {
				remove_action( 'save_post', array( $this, 'update_admin_meeting' ), 10 );
				$is_edit                   = get_post_meta( $post_id, 'meeting_created', true );
				$timezone                  = ! empty( $_POST['timezone'] ) ? $_POST['timezone'] : 'UTC'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$duration                  = ! empty( $_POST['duration'] ) ? $_POST['duration'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$stream_start_date         = ! empty( $_POST['stream_start_date'] ) ? $_POST['stream_start_date'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$stream_start_time         = ! empty( $_POST['stream_start_time'] ) ? $_POST['stream_start_time'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$join_before_host          = isset( $_POST['join_before_host'] ) ? $_POST['join_before_host'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$option_host_video         = isset( $_POST['option_host_video'] ) ? $_POST['option_host_video'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$option_participants_video = isset( $_POST['option_participants_video'] ) ? $_POST['option_participants_video'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$option_mute_participants  = isset( $_POST['option_mute_participants'] ) ? $_POST['option_mute_participants'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$option_enforce_login      = isset( $_POST['option_enforce_login'] ) ? $_POST['option_enforce_login'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$agenda                    = ! empty( $_POST['lesson_excerpt'] ) ? $_POST['lesson_excerpt'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$user_id                   = get_current_user_id();
				$user_host                 = get_the_author_meta( 'stm_lms_zoom_host', $user_id );

				if ( empty( $user_host ) ) {
					return '';
				}

				$_POST['stm_host']                     = $user_host;
				$_POST['stm_agenda']                   = $agenda;
				$_POST['stm_date']                     = $stream_start_date;
				$_POST['stm_time']                     = $stream_start_time;
				$_POST['stm_timezone']                 = $timezone;
				$_POST['stm_duration']                 = $duration;
				$_POST['stm_join_before_host']         = $join_before_host;
				$_POST['stm_host_join_start']          = $option_host_video;
				$_POST['stm_start_after_participants'] = $option_participants_video;
				$_POST['stm_mute_participants']        = $option_mute_participants;
				$_POST['stm_enforce_login']            = $option_enforce_login;

				$_POST['post_type'] = 'stm-zoom';

				$post_data = array(
					'post_title'  => wp_strip_all_tags( $_POST['post_title'] ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
					'post_status' => 'publish',
					'post_author' => intval( $user_id ),
					'post_type'   => 'stm-zoom',
				);

				if ( ! empty( $is_edit ) ) {
					$post_data['ID'] = intval( $is_edit );
				}

				$new_meeting = wp_insert_post( $post_data );

				update_post_meta( $post_id, 'meeting_created', $new_meeting );
			}
		}

		remove_action( 'save_post', array( $this, 'update_admin_meeting' ), 10 );
	}

	public static function create_zoom_shortcode( $item_id, $title = '' ) {
		$meeting_id = '';
		$meeting    = get_post_meta( $item_id, 'meeting_data', true );

		if ( ! empty( $meeting ) ) {
			$meeting_id = $meeting->id;
		}

		return '[zoom_api_link meeting_id="' . $meeting_id . '" class="zoom-meeting-window" id="zoom-meeting-window" title="' . $title . '"]';
	}

	public function stm_lms_settings_page( $setups ) {
		$setups[] = array(
			'page'        => array(
				'parent_slug' => 'admin.php?page=stm-lms-settings',
				'page_title'  => 'Zoom conference',
				'menu_title'  => 'Import Classrooms',
				'menu_slug'   => 'stm_lms_zoom_conference',
			),
			'fields'      => $this->stm_lms_settings(),
			'option_name' => 'stm_lms_zoom_conference_settings',
		);

		return $setups;

	}

	public function stm_lms_settings() {
		return apply_filters( 'stm_lms_zoom_conference_settings', array() );
	}

}
