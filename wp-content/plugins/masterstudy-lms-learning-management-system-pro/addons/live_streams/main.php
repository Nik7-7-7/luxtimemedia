<?php

new STM_LMS_Live_Streams();

class STM_LMS_Live_Streams {

	public function __construct() {
		add_filter( 'stm_lms_course_item_content', array( $this, 'course_item_content' ), 10, 4 );

		add_filter( 'stm_lms_show_item_content', array( $this, 'show_item_content' ), 10, 3 );

		add_filter( 'stm_lms_navigation_complete_class', array( $this, 'navigation_complete_class' ), 10, 2 );

		add_filter( 'stm_lms_navigation_complete_atts', array( $this, 'navigation_complete_atts' ), 10, 2 );
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
			STM_LMS_Templates::show_lms_template( 'course-player/stream', compact( 'post_id', 'item_id', 'data' ) );
			return ob_get_clean();}
		return $content;
	}

	public static function is_stream( $post_id ) {
		$type = get_post_meta( $post_id, 'type', true );

		return 'stream' === $type;
	}

	public static function time_offset() {
		return get_option( 'gmt_offset' ) * 60 * 60;
	}

	public static function stream_start_time( $item_id ) {
		$start_date = get_post_meta( $item_id, 'stream_start_date', true );
		$start_time = get_post_meta( $item_id, 'stream_start_time', true );

		if ( empty( $start_date ) ) {
			return '';
		}

		$offset       = self::time_offset();
		$stream_start = strtotime( 'today', ( $start_date / 1000 ) ) - $offset;

		if ( ! empty( $start_time ) ) {
			$time = explode( ':', $start_time );
			if ( is_array( $time ) && count( $time ) === 2 ) {
				$stream_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $stream_start );
			}
		}

		return $stream_start;
	}

	public static function get_stream_date( $post_id, $start ) {
		$date_key   = $start ? 'stream_start_date' : 'stream_end_date';
		$time_key   = $start ? 'stream_start_time' : 'stream_end_time';
		$meta_value = get_post_meta( $post_id, $date_key, true );
		$time_value = get_post_meta( $post_id, $time_key, true );

		$current_date = self::validate_stream_start_date( $meta_value );
		$date_time    = gmdate( 'F j, Y', $current_date / 1000 );
		$time         = gmdate( 'g:i a', strtotime( $time_value ) );

		return "$date_time, $time";
	}

	public static function validate_stream_start_date( $start_date ) {
		if ( is_numeric( $start_date ) && 0 !== $start_date ) {
			return $start_date;
		}

		return strtotime( 'today' ) . '000';
	}

	public static function is_stream_started( $item_id ) {
		$stream_start = self::stream_start_time( $item_id );

		/*NO TIME - STREAM STARTED*/
		if ( empty( $stream_start ) ) {
			return true;
		}

		if ( $stream_start > time() ) {
			return false;
		}

		return true;
	}

	public static function stream_end_time( $item_id ) {
		$end_date = get_post_meta( $item_id, 'stream_end_date', true );
		$end_time = get_post_meta( $item_id, 'stream_end_time', true );

		if ( empty( $end_date ) || empty( $end_time ) ) {
			return '';
		}

		$offset = self::time_offset();

		$stream_end = strtotime( 'today', ( $end_date / 1000 ) - $offset );

		if ( ! empty( $end_time ) ) {
			$time = explode( ':', $end_time );
			if ( is_array( $time ) && count( $time ) === 2 ) {
				$stream_end = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $stream_end );
			}
		}

		return $stream_end;
	}

	public static function is_stream_ended( $item_id ) {
		$time_now = time();

		$stream_end = self::stream_end_time( $item_id );

		if ( empty( $stream_end ) ) {
			return true;
		}

		if ( $stream_end > $time_now ) {
			return false;
		}

		return true;
	}

	public static function navigation_complete_class( $class, $item_id ) {
		if ( self::is_stream( $item_id ) && ! self::is_stream_started( $item_id ) ) {
			return "stream-cannot-be-completed stream-is-not-started {$class}";
		}

		if ( self::is_stream( $item_id ) && ! self::is_stream_ended( $item_id ) ) {
			return "stream-cannot-be-completed stream-is-not-ended {$class}";
		}

		return $class;
	}

	public static function navigation_complete_atts( $atts, $item_id ) {
		if ( self::is_stream( $item_id ) && ! self::is_stream_ended( $item_id ) ) {
			$end_time = self::stream_end_time( $item_id );

			$end_time = ( $end_time - time() );

			return $atts . " data-timer='" . $end_time . "' data-disabled='true'";
		}

		return $atts;
	}

}
