<?php
#[AllowDynamicProperties]

class MailsterConditions {



	private $workflow_campaigns = array();

	// used for caching
	private $_custom_fields;
	private $_custom_date_fields;
	private $_fields;
	private $_time_fields;
	private $_meta_fields;
	private $_wp_user_meta;
	private $_campaign_related;
	private $_list_related;
	private $_tag_related;
	private $_all_operators;
	private $_all_value_fields;
	private $_operators;
	private $_simple_operators;
	private $_string_operators;
	private $_bool_operators;
	private $_date_operators;
	private $_relative_date_operators;
	private $_special_campaigns;
	private $_field_operator;

	public function __construct() {}


	public function check( $conditions, $subscribers, $status = false ) {

		$query = mailster( 'subscribers' )->query(
			array(
				'return_ids' => true,
				'include'    => $subscribers,
				'conditions' => $conditions,
				'status'     => $status, // ignore the status
			)
		);

		return ! empty( $query );
	}


	public function view( $conditions = array(), $inputname = null ) {

		if ( empty( $conditions ) ) {
			$conditions = array();
		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'mailster-conditions', MAILSTER_URI . 'assets/css/conditions-style' . $suffix . '.css', array(), MAILSTER_VERSION );
		wp_enqueue_script( 'mailster-conditions', MAILSTER_URI . 'assets/js/conditions-script' . $suffix . '.js', array( 'jquery', 'jquery-ui-autocomplete' ), MAILSTER_VERSION, true );

		if ( is_null( $inputname ) ) {
			$inputname = 'mailster_data[conditions]';
		}

		include MAILSTER_DIR . 'views/conditions/conditions.php';
	}

	public function render( $conditions = array(), $echo = true, $plain = false ) {

		if ( empty( $conditions ) ) {
			$conditions = array();
		}

		ob_start();
		include MAILSTER_DIR . 'views/conditions/render.php';
		$output = ob_get_contents();
		ob_end_clean();

		// remove any whitespace so :empty selector works
		if ( empty( $conditions ) ) {
			$output = preg_replace( '/>(\s+)<\//', '></', $output );
		}

		if ( $plain ) {
			$output = trim( strip_tags( $output ) );
			$output = preg_replace( '/\s*$^\s*/mu', "\n\n", $output );
			$output = preg_replace( '/[ \t]+/u', ' ', $output );
		}

		if ( ! $echo ) {
			return $output;
		}

		echo $output;
	}

	public function set_workflow_campaigns( $campaigns ) {

		foreach ( $campaigns as $campaign ) {
			$this->workflow_campaigns[ $campaign['id'] ] = $campaign;
		}
	}

	public function get_autocomplete_source( $field, $search = null ) {

		global $wpdb;

		$query = null;
		$data  = array();

		if ( isset( $this->get_fields()[ $field ] ) ) {
			$query = 'SELECT DISTINCT ' . esc_sql( $field ) . " AS %s FROM {$wpdb->prefix}mailster_subscribers WHERE 1";
			if ( $search ) {
				$query .= ' AND ' . esc_sql( $field ) . " LIKE '%%%s%%'";
			}
			$data = array();
		} elseif ( isset( $this->get_custom_fields()[ $field ] ) ) {
			$custom_fields = mailster()->get_custom_fields();

			$type = isset( $custom_fields[ $field ] ) ? $custom_fields[ $field ]['type'] : 'text';

			switch ( $type ) {
				case 'dropdown':
					$data = $custom_fields[ $field ]['values'];
					break;

				default:
					$query = "SELECT DISTINCT meta_value FROM {$wpdb->prefix}mailster_subscriber_fields WHERE meta_key = %s";
					if ( $search ) {
						$query .= " AND meta_value LIKE '%%%s%%'";
					}
					break;
			}
		} elseif ( isset( $this->get_tag_related()[ $field ] ) ) {
			$query = "SELECT DISTINCT `name` FROM {$wpdb->prefix}mailster_tags WHERE `ID` != %s"; // artifical for prepared statement
			if ( $search ) {
				$query .= " AND `name` LIKE '%%%s%%'";
			}
		} elseif ( isset( $this->get_meta_fields()[ $field ] ) ) {
			$query = "SELECT DISTINCT meta_value FROM {$wpdb->prefix}mailster_subscriber_meta WHERE meta_key = %s";
			if ( $search ) {
				$query .= " AND meta_value LIKE '%%%s%%'";
			}
		} elseif ( isset( $this->get_wp_user_meta()[ $field ] ) ) {
			$query = "SELECT DISTINCT meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s";
			if ( $search ) {
				$query .= " AND meta_value LIKE '%%%s%%'";
			}
		}

		if ( $query ) {
			// don't do to much
			$query .= ' LIMIT 1000';
			if ( $search ) {
				$query = $wpdb->prepare( $query, $field, $search );
			} else {
				$query = $wpdb->prepare( $query, $field );
			}
			$data = $wpdb->get_col( $query );
		}

		$data = array_filter( $data );
		$data = array_values( $data );

		return apply_filters( 'mailster_conditions_autocomplete_data', $data, $field, $search );
	}

	public function fielddropdown() {
		include MAILSTER_DIR . 'views/conditions/fielddropdown.php';
	}
	public function operatordropdown() {
		include MAILSTER_DIR . 'views/conditions/operatordropdown.php';
	}

	private function get_custom_fields() {
		if ( ! $this->_custom_fields ) {
			$this->_custom_fields = mailster()->get_custom_fields();
			$this->_custom_fields = wp_parse_args(
				(array) $this->_custom_fields,
				array(
					'email'     => array( 'name' => mailster_text( 'email' ) ),
					'firstname' => array( 'name' => mailster_text( 'firstname' ) ),
					'lastname'  => array( 'name' => mailster_text( 'lastname' ) ),
					'rating'    => array( 'name' => esc_html__( 'Rating', 'mailster' ) ),
				)
			);

			$this->_custom_fields = wp_list_pluck( $this->_custom_fields, 'name' );
			$this->_custom_fields = apply_filters( 'mailster_conditions_type_custom_fields', $this->_custom_fields );
		}

		return $this->_custom_fields;
	}

	private function get_custom_date_fields() {
		if ( ! $this->_custom_date_fields ) {
			$this->_custom_date_fields = mailster()->get_custom_date_fields( true );
			$this->_custom_date_fields = apply_filters( 'mailster_conditions_type_custom_date_fields', $this->_custom_date_fields );
		}

		return $this->_custom_date_fields;
	}

	private function get_fields() {

		if ( ! $this->_fields ) {
			$this->_fields = array(
				'id'         => esc_html__( 'ID', 'mailster' ),
				'hash'       => esc_html__( 'Hash', 'mailster' ),
				'email'      => esc_html__( 'Email', 'mailster' ),
				'wp_id'      => esc_html__( 'WordPress User ID', 'mailster' ),
				'added'      => esc_html__( 'Added', 'mailster' ),
				'updated'    => esc_html__( 'Updated', 'mailster' ),
				'signup'     => esc_html__( 'Signup', 'mailster' ),
				'confirm'    => esc_html__( 'Confirm', 'mailster' ),
				'ip_signup'  => esc_html__( 'IP on Signup', 'mailster' ),
				'ip_confirm' => esc_html__( 'IP on confirmation', 'mailster' ),
			);
			$this->_fields = apply_filters( 'mailster_conditions_type_fields', $this->_fields );
		}

		return $this->_fields;
	}

	private function get_time_fields() {
		if ( ! $this->_time_fields ) {
			$this->_time_fields = array( 'added', 'updated', 'signup', 'confirm', 'gdpr' );
			$this->_time_fields = array_merge( $this->_time_fields, $this->get_custom_date_fields() );
			$this->_time_fields = apply_filters( 'mailster_conditions_type_time_fields', $this->_time_fields );
		}

		return $this->_time_fields;
	}

	private function get_meta_fields() {
		if ( ! $this->_meta_fields ) {
			$this->_meta_fields = array(
				'form'       => esc_html__( 'Form', 'mailster' ),
				'referer'    => esc_html__( 'Referer', 'mailster' ),
				'client'     => esc_html__( 'Client', 'mailster' ),
				'clienttype' => esc_html__( 'Clienttype', 'mailster' ),
				'geo'        => esc_html__( 'Location', 'mailster' ),
				'lang'       => esc_html__( 'Language', 'mailster' ),
				'gdpr'       => esc_html__( 'GDPR Consent given', 'mailster' ),
			);
			$this->_meta_fields = apply_filters( 'mailster_conditions_type_meta_fields', $this->_meta_fields );
		}

		return $this->_meta_fields;
	}

	private function get_operator_fields( $type ) {
		$fields = array();

		switch ( $type ) {
			case 'operators':
				$fields = array();
				break;
			case 'simple_operators':
				$fields = array( 'rating' );
				break;
			case 'string_operators':
				$fields = array( 'lang', 'client', 'referer', 'firstname', 'lastname', 'email', 'tag' );
				break;
			case 'bool_operators':
				$fields = array( 'wp_capabilities', 'status', 'form', 'clienttype', 'geo' );
				break;
			case 'date_operators':
				$fields = $this->get_time_fields();
				break;
			case 'relative_date_operators':
				$fields = $this->get_time_fields();
				break;
			case 'hidden':
				$fields = array( '_sent', '_sent__not_in', '_open', '_open__not_in', '_click', '_click__not_in', '_click_link', '_click_link__not_in', '_lists__not_in', '_lists__in', '_tags__not_in', '_tags__in', '_tagname__not_in', '_tagname__in' );
				break;
			default:
				break;
		}

		return $fields;
	}

	private function get_value_fields( $type ) {

		$fields = array();

		switch ( $type ) {
			case 'integer':
				$fields = array( 'id', 'wp_id' );
				break;
			case 'timestamp':
				$fields = $this->get_time_fields();
				break;
			case 'campaign_related':
				$fields = array( '_sent', '_sent__not_in', '_open', '_open__not_in', '_click', '_click__not_in' );
				break;
			case 'list_related':
				$fields = array( '_lists__not_in', '_lists__in' );
				break;
			case 'tag_related':
				$fields = array( '_tags__not_in', '_tags__in' );
				break;
			case 'tagname_related':
				$fields = array( '_tagname__not_in', '_tagname__in' );
				break;
			case 'click_related':
				$fields = array( '_click_link', '_click_link__not_in' );
				break;
			default:
				$fields = array( $type );
				break;
		}

		return $fields;
	}

	private function get_wp_user_meta() {
		if ( ! $this->_wp_user_meta ) {
			$wpuser_meta_fields = mailster( 'helper' )->get_wpuser_meta_fields();
			$wpuser_meta_fields = array_combine( $wpuser_meta_fields, $wpuser_meta_fields );

			$this->_wp_user_meta = wp_parse_args(
				$wpuser_meta_fields,
				array(
					'wp_user_level'   => esc_html__( 'User Level', 'mailster' ),
					'wp_capabilities' => esc_html__( 'User Role', 'mailster' ),
				)
			);

			// removing custom fields from wp user meta to prevent conflicts
			$this->_wp_user_meta = array_diff( $this->_wp_user_meta, array_merge( array( 'email' ), array_keys( $this->get_custom_fields() ) ) );

			$this->_wp_user_meta = apply_filters( 'mailster_conditions_type_wp_user_meta', $this->_wp_user_meta );
		}

		return $this->_wp_user_meta;
	}


	private function get_campaign_related() {

		if ( ! $this->_campaign_related ) {
			$this->_campaign_related = array(
				'_sent'               => esc_html__( 'has received', 'mailster' ),
				'_sent__not_in'       => esc_html__( 'has not received', 'mailster' ),
				'_open'               => esc_html__( 'has received and opened', 'mailster' ),
				'_open__not_in'       => esc_html__( 'has received but not opened', 'mailster' ),
				'_click'              => esc_html__( 'has received and clicked', 'mailster' ),
				'_click__not_in'      => esc_html__( 'has received and not clicked', 'mailster' ),
				'_click_link'         => esc_html__( 'clicked link', 'mailster' ),
				'_click_link__not_in' => esc_html__( 'didn\'t clicked link', 'mailster' ),
			);
			$this->_campaign_related = apply_filters( 'mailster_conditions_type_campaign_related', $this->_campaign_related );
		}

		return $this->_campaign_related;
	}
	private function get_list_related() {
		if ( ! $this->_list_related ) {
			$this->_list_related = array(
				'_lists__in'     => esc_html__( 'is in List', 'mailster' ),
				'_lists__not_in' => esc_html__( 'is not in List', 'mailster' ),
			);
			$this->_list_related = apply_filters( 'mailster_conditions_type_list_related', $this->_list_related );
		}

		return $this->_list_related;
	}
	private function get_tag_related() {
		if ( ! $this->_tag_related ) {
			$this->_tag_related = array(
				'_tagname__in'     => esc_html__( 'has Tag', 'mailster' ),
				'_tagname__not_in' => esc_html__( 'doesn\'t have Tag', 'mailster' ),
				'_tags__in'        => esc_html__( 'has Tag (deprecated)', 'mailster' ),
				'_tags__not_in'    => esc_html__( 'doesn\'t have Tag (deprecated)', 'mailster' ),
			);
			$this->_tag_related = apply_filters( 'mailster_conditions_type_tag_related', $this->_tag_related );
		}

		return $this->_tag_related;
	}
	private function get_all_operators() {
		if ( ! $this->_all_operators ) {
			$this->_all_operators = array(
				'operators'        => array( $this->get_operators() ),
				'simple_operators' => array( $this->get_simple_operators() ),
				'string_operators' => array( $this->get_string_operators() ),
				'bool_operators'   => array( $this->get_bool_operators() ),
				'date_operators'   => array(
					esc_html__( 'absolute', 'mailster' ) => $this->get_date_operators(),
					esc_html__( 'relative', 'mailster' ) => $this->get_relative_date_operators(),
				),
			);

			$this->_all_operators = apply_filters( 'mailster_conditions_type_all_operators', $this->_all_operators );
		}

		return $this->_all_operators;
	}
	private function get_all_value_fields() {
		if ( ! $this->_all_value_fields ) {
			$this->_all_value_fields = array(
				'text',
				'integer',
				'rating',
				'timestamp',
				'wp_capabilities',
				'status',
				'form',
				'clienttype',
				'campaign_related',
				'list_related',
				'tag_related',
				'tagname_related',
				'click_related',
				'geo',
			);
			$this->_all_value_fields = apply_filters( 'mailster_conditions_type_all_value_fields', $this->_all_value_fields );
		}

		return $this->_all_value_fields;
	}
	private function get_operators() {
		if ( ! $this->_operators ) {
			$this->_operators = array(
				'is'               => esc_html__( 'is', 'mailster' ),
				'is_not'           => esc_html__( 'is not', 'mailster' ),
				'contains'         => esc_html__( 'contains', 'mailster' ),
				'contains_not'     => esc_html__( 'doesn\'t contain', 'mailster' ),
				'begin_with'       => esc_html__( 'starts with', 'mailster' ),
				'end_with'         => esc_html__( 'ends with', 'mailster' ),
				'is_greater'       => esc_html__( 'is greater', 'mailster' ),
				'is_smaller'       => esc_html__( 'is smaller', 'mailster' ),
				'is_greater_equal' => esc_html__( 'is greater or equal', 'mailster' ),
				'is_smaller_equal' => esc_html__( 'is smaller or equal', 'mailster' ),
				'pattern'          => esc_html__( 'match regex pattern', 'mailster' ),
				'not_pattern'      => esc_html__( 'doesn\'t match regex pattern', 'mailster' ),
			);
			$this->_operators = apply_filters( 'mailster_conditions_type_operators', $this->_operators );
		}

		return $this->_operators;
	}
	private function get_simple_operators() {
		if ( ! $this->_simple_operators ) {
			$this->_simple_operators = array(
				'is'               => esc_html__( 'is', 'mailster' ),
				'is_not'           => esc_html__( 'is not', 'mailster' ),
				'is_greater'       => esc_html__( 'is greater', 'mailster' ),
				'is_smaller'       => esc_html__( 'is smaller', 'mailster' ),
				'is_greater_equal' => esc_html__( 'is greater or equal', 'mailster' ),
				'is_smaller_equal' => esc_html__( 'is smaller or equal', 'mailster' ),
			);
			$this->_simple_operators = apply_filters( 'mailster_conditions_type_simple_operators', $this->_simple_operators );
		}

		return $this->_simple_operators;
	}
	private function get_string_operators() {
		if ( ! $this->_string_operators ) {
			$this->_string_operators = array(
				'is'           => esc_html__( 'is', 'mailster' ),
				'is_not'       => esc_html__( 'is not', 'mailster' ),
				'contains'     => esc_html__( 'contains', 'mailster' ),
				'contains_not' => esc_html__( 'doesn\'t contain', 'mailster' ),
				'begin_with'   => esc_html__( 'starts with', 'mailster' ),
				'end_with'     => esc_html__( 'ends with', 'mailster' ),
				'pattern'      => esc_html__( 'match regex pattern', 'mailster' ),
				'not_pattern'  => esc_html__( 'doesn\'t match regex pattern', 'mailster' ),
			);
			$this->_string_operators = apply_filters( 'mailster_conditions_type_string_operators', $this->_string_operators );
		}

		return $this->_string_operators;
	}
	private function get_bool_operators() {
		if ( ! $this->_bool_operators ) {
			$this->_bool_operators = array(
				'is'     => esc_html__( 'is', 'mailster' ),
				'is_not' => esc_html__( 'is not', 'mailster' ),
			);
			$this->_bool_operators = apply_filters( 'mailster_conditions_type_bool_operators', $this->_bool_operators );
		}

		return $this->_bool_operators;
	}
	private function get_date_operators() {
		if ( ! $this->_date_operators ) {
			$this->_date_operators = array(
				'is'               => esc_html__( 'is on the', 'mailster' ),
				'is_not'           => esc_html__( 'is not on the', 'mailster' ),
				'is_greater'       => esc_html__( 'is after', 'mailster' ),
				'is_smaller'       => esc_html__( 'is before', 'mailster' ),
				'is_greater_equal' => esc_html__( 'is after or on the', 'mailster' ),
				'is_smaller_equal' => esc_html__( 'is before or on the', 'mailster' ),
			);
			$this->_date_operators = apply_filters( 'mailster_conditions_type_date_operators', $this->_date_operators );
		}

		return $this->_date_operators;
	}
	private function get_relative_date_operators() {
		if ( ! $this->_relative_date_operators ) {
			$this->_relative_date_operators = array(
				'is_older'   => esc_html__( 'is older than', 'mailster' ),
				'is_younger' => esc_html__( 'is younger than', 'mailster' ),
			);
			$this->_relative_date_operators = apply_filters( 'mailster_conditions_type_relative_date_operators', $this->_relative_date_operators );
		}

		return $this->_relative_date_operators;
	}


	private function get_special_campaigns() {

		if ( ! $this->_special_campaigns ) {
			$this->_special_campaigns = array(
				'_last_5'       => esc_html__( 'Any of the Last 5 Campaigns', 'mailster' ),
				'_last_7day'    => esc_html__( 'Any Campaigns within the last 7 days', 'mailster' ),
				'_last_1month'  => esc_html__( 'Any Campaigns within the last 1 month', 'mailster' ),
				'_last_3month'  => esc_html__( 'Any Campaigns within the last 3 months', 'mailster' ),
				'_last_6month'  => esc_html__( 'Any Campaigns within the last 6 months', 'mailster' ),
				'_last_12month' => esc_html__( 'Any Campaigns within the last 12 months', 'mailster' ),
			);
			$this->_special_campaigns = apply_filters( 'mailster_conditions_type_special_campaigns', $this->_special_campaigns );
		}

		return $this->_special_campaigns;
	}
	private function get_field_operator( $operator ) {
		$operator = esc_sql( stripslashes( $operator ) );

		switch ( $operator ) {
			case '=':
				return 'is';
			case '!=':
				return 'is_not';
			case '<>':
				return 'contains';
			case '!<>':
				return 'contains_not';
			case '^':
				return 'begin_with';
			case '$':
				return 'end_with';
			case '>=':
				return 'is_greater_equal';
			case '<=':
				return 'is_smaller_equal';
			case '>':
				return 'is_greater';
			case '<':
				return 'is_smaller';
			case '%':
				return 'pattern';
			case '!%':
				return 'not_pattern';
		}

		return $operator;
	}


	private function print_condition( $condition, $formated = true ) {

		$field    = isset( $condition['field'] ) ? $condition['field'] : $condition[0];
		$operator = isset( $condition['operator'] ) ? $condition['operator'] : $condition[1];
		$value    = stripslashes_deep( isset( $condition['value'] ) ? $condition['value'] : $condition[2] );

		$return = array(
			'field'    => '<strong>' . $this->nice_name( $field, 'field', $field ) . '</strong>',
			'operator' => '',
			'value'    => '',
		);
		/* translators: opening and closing double quote in your language */
		$quotes = _x( '“ ”', 'openquote, space, closing quote', 'mailster' );

		$opening_quote = esc_html_x( '&#8220;', 'opening curly double quote', 'mailster' );
		$closing_quote = esc_html_x( '&#8221;', 'closing curly double quote', 'mailster' );

		if ( isset( $this->get_campaign_related()[ $field ] ) ) {

			$special_campaign_keys = array_keys( $this->get_special_campaigns() );

			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			$urls      = array();
			$campagins = array();

			if ( strpos( $field, '_click_link' ) !== false ) {
				foreach ( $value as $k => $v ) {
					if ( is_numeric( $v ) || in_array( $v, $special_campaign_keys ) ) {
						$campagins[] = $v;
					} else {
						$urls[] = $v;
					}
				}
				$return['value'] = implode( ' ' . esc_html__( 'or', 'mailster' ) . ' ', array_map( 'esc_url', $urls ) );
				if ( ! empty( $campagins ) ) {
					$return['value'] .= '<br> ' . esc_html__( 'in', 'mailster' ) . ' ' . $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_campaign_title' ), $campagins ) ) . $closing_quote;
				}
			} else {

				$return['value'] = $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_campaign_title' ), $value ) ) . $closing_quote;
			}
		} elseif ( isset( $this->get_list_related()[ $field ] ) ) {
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			$return['value'] = $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_list_title' ), $value ) ) . $closing_quote;
		} elseif ( isset( $this->get_tag_related()[ $field ] ) ) {
			if ( ! is_array( $value ) ) {
				$value = array_map( 'trim', explode( ',', $value ) );
			}
			$value           = array_filter( $value );
			$return['value'] = $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_tag_title' ), $value ) ) . $closing_quote;
		} elseif ( 'geo' == $field ) {
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			$return['operator'] = '<em>' . $this->nice_name( $operator, 'operator', $field ) . '</em>';
			$return['value']    = $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_country_name' ), $value ) ) . $closing_quote;
		} elseif ( 'rating' == $field ) {
			$stars              = ( round( $this->sanitize_rating( $value ) / 10, 2 ) * 50 );
			$full               = max( 0, min( 5, floor( $stars ) ) );
			$half               = max( 0, min( 5, round( $stars - $full ) ) );
			$empty              = max( 0, min( 5, 5 - $full - $half ) );
			$return['operator'] = '<em>' . $this->nice_name( $operator, 'operator', $field ) . '</em>';
			$return['value']    = '<span class="screen-reader-text">' . sprintf( esc_html__( '%d stars', 'mailster' ), $full ) . '</span>'
			. str_repeat( '<span class="mailster-icon mailster-icon-star"></span>', $full )
			. str_repeat( '<span class="mailster-icon mailster-icon-star-half"></span>', $half )
			. str_repeat( '<span class="mailster-icon mailster-icon-star-empty"></span>', $empty );

		} else {
			$return['operator'] = '<em>' . $this->nice_name( $operator, 'operator', $field ) . '</em>';
			$return['value']    = $opening_quote . '<strong>' . $this->nice_name( $value, 'value', $field ) . '</strong>' . $closing_quote;
		}

		return $formated ? $return : strip_tags( $return );
	}


	private function sanitize_rating( $value ) {
		if ( ! $value || ! (float) $value ) {
			return 0;
		}
		$value = str_replace( ',', '.', $value );
		if ( strpos( $value, '%' ) !== false || $value > 5 ) {
			$value = (float) $value / 100;
		} elseif ( $value > 1 ) {
			$value = (float) $value * 0.2;
		}
		return $value;
	}

	public function get_campaign_title( $post ) {

		if ( isset( $this->workflow_campaigns[ $post ] ) ) {
			$title = $this->workflow_campaigns[ $post ]['name'];
			if ( isset( $this->workflow_campaigns[ $post ]['campaign'] ) ) {
				$campaign_title = get_the_title( $this->workflow_campaigns[ $post ]['campaign'] );
				if ( $campaign_title != $title ) {
					$title .= ' (' . $campaign_title . ')';
				}
			}
			return $title;
		}

		if ( ! $post ) {
			return esc_html__( 'Any Campaign', 'mailster' );
		}

		if ( isset( $this->get_special_campaigns()[ $post ] ) ) {
			return $this->get_special_campaigns()[ $post ];
		}

		$title = get_the_title( $post );
		if ( empty( $title ) ) {
			$title = '#' . $post;
		}
		return $title;
	}

	public function get_list_title( $list_id ) {

		if ( $list = mailster( 'lists' )->get( $list_id ) ) {
			return $list->name;
		}
		return $list_id;
	}

	public function get_tag_title( $tag_id ) {

		if ( $tag = mailster( 'tags' )->get( $tag_id ) ) {
			return $tag->name;
		}
		return $tag_id;
	}

	public function get_country_name( $code ) {

		return mailster( 'geo' )->code2Country( $code );
	}


	private function nice_name( $string, $type = null, $field = null ) {

		switch ( $type ) {
			case 'field':
				if ( isset( $this->get_fields()[ $string ] ) ) {
					return $this->get_fields()[ $string ];
				}
				if ( isset( $this->get_custom_fields()[ $string ] ) ) {
					return $this->get_custom_fields()[ $string ];
				}
				if ( isset( $this->get_campaign_related()[ $string ] ) ) {
					return $this->get_campaign_related()[ $string ];
				}
				if ( isset( $this->get_list_related()[ $string ] ) ) {
					return $this->get_list_related()[ $string ];
				}
				if ( isset( $this->get_tag_related()[ $string ] ) ) {
					return $this->get_tag_related()[ $string ];
				}
				if ( isset( $this->get_meta_fields()[ $string ] ) ) {
					return $this->get_meta_fields()[ $string ];
				}
				if ( isset( $this->get_wp_user_meta()[ $string ] ) ) {
					return $this->get_wp_user_meta()[ $string ];
				}
				break;
			case 'operator':
				if ( in_array( $field, $this->get_time_fields() ) && isset( $this->get_date_operators()[ $string ] ) ) {
					return $this->get_date_operators()[ $string ];
				}
				if ( in_array( $field, $this->get_time_fields() ) && isset( $this->get_relative_date_operators()[ $string ] ) ) {
					return $this->get_relative_date_operators()[ $string ];
				}
				if ( isset( $this->get_operators()[ $string ] ) ) {
					return $this->get_operators()[ $string ];
				}
				if ( 'AND' == $string ) {
					return esc_html__( 'and', 'mailster' );
				}
				if ( 'OR' == $string ) {
					return esc_html__( 'or', 'mailster' );
				}
				break;
			case 'value':
				if ( in_array( $field, $this->get_time_fields() ) ) {
					if ( preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $string ) ) {
						return date_i18n( mailster( 'helper' )->dateformat(), strtotime( $string ) );
					} elseif ( $string ) {
						return human_time_diff( strtotime( $string ) );
					} else {
						return '';
					}
				}
				if ( 'form' == $field ) {
					if ( $form = mailster( 'forms' )->get( (int) $string, false, false ) ) {
						return $form->name;
					}
				} elseif ( 'wp_capabilities' == $field ) {
					global $wp_roles;
					if ( isset( $wp_roles->roles[ $string ] ) ) {
						return translate_user_role( $wp_roles->roles[ $string ]['name'] );
					}
				}

				break;

		}

		return apply_filters( 'mailster_conditions_nice_name', $string, $type, $field );
	}


	private function render_value_field( $field, $value, $inputname ) {
		?>
		<div class="mailster-conditions-value-field mailster-conditions-value-field-<?php echo esc_attr( $field ); ?>"  data-fields=",<?php echo implode( ',', $this->get_value_fields( $field ) ); ?>,">
		<?php if ( method_exists( $this, 'value_field_' . $field ) ) : ?>
			<?php call_user_func( array( $this, 'value_field_' . $field ), $value, $inputname ); ?>
		<?php else : ?>
			
		<?php endif; ?>
		</div>
		<?php
	}

	private function value_field_text( $value, $inputname ) {
		$value = is_array( $value ) ? $value[0] : $value;
		?>
		<input type="text" class="regular-text condition-value" disabled value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $inputname ); ?>">	
		<?php
	}

	private function value_field_integer( $value, $inputname ) {
		$this->value_field_text( $value, $inputname );
	}

	private function value_field_rating( $value, $inputname ) {
		$value = is_array( $value ) ? $value[0] : $value;

		$stars = ( round( $this->sanitize_rating( (float) $value ) / 10, 2 ) * 50 );
		$full  = max( 0, min( 5, floor( $stars ) ) );
		$half  = max( 0, min( 5, round( $stars - $full ) ) );
		$empty = max( 0, min( 5, 5 - $full - $half ) );
		?>
		<div class="mailster-rating">
		<?php
			echo str_repeat( '<span class="mailster-icon enabled"></span>', $full )
			. str_repeat( '<span class="mailster-icon enabled"></span>', $half )
			. str_repeat( '<span class="mailster-icon"></span>', $empty )
		?>
		</div>
		<input type="hidden" class="condition-value" disabled value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $inputname ); ?>">

		<?php
	}


	private function value_field_timestamp( $value, $inputname ) {
		$value = is_array( $value ) ? $value[0] : $value;

		// determine the type by checking the value
		$type = ( empty( $value ) || date( 'Y-m-d', strtotime( $value ) ) == $value ) ? 'date' : 'text';

		?>
					
		<input type="<?php echo esc_attr( $type ); ?>" class="regular-text datepicker condition-value" disabled autocomplete="off" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $inputname ); ?>">
		<input type="number" class="small-text relative-datepicker" autocomplete="off" value="" min="1">
		<select class="relative-datepicker">
			<option value="minutes"><?php esc_html_e( 'minute(s)', 'mailster' ); ?></option>
			<option value="hours"><?php esc_html_e( 'hour(s)', 'mailster' ); ?></option>
			<option value="days"><?php esc_html_e( 'day(s)', 'mailster' ); ?></option>
			<option value="weeks"><?php esc_html_e( 'week(s)', 'mailster' ); ?></option>
			<option value="months"><?php esc_html_e( 'month(s)', 'mailster' ); ?></option>
		</select>
		<?php
	}


	private function value_field_wp_capabilities( $value, $inputname ) {
		$value = is_array( $value ) ? $value[0] : $value;

		if ( ! function_exists( 'wp_dropdown_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/template.php';
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		?>
		<select name="<?php echo esc_attr( $inputname ); ?>" class="condition-value" disabled>
			<?php wp_dropdown_roles( $value ); ?>
		</select>
		<?php
	}


	private function value_field_status( $value, $inputname ) {
		$value    = is_array( $value ) ? $value[0] : $value;
		$statuses = mailster( 'subscribers' )->get_status( null, true );
		?>
		<select name="<?php echo esc_attr( $inputname ); ?>" class="condition-value" disabled>
			<?php foreach ( $statuses as $key => $name ) : ?>
				<option value="<?php echo (int) $key; ?>" <?php selected( $key, $value ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}


	private function value_field_form( $value, $inputname ) {
		$value = is_array( $value ) ? $value[0] : $value;
		$forms = mailster( 'forms' )->get_all();
		?>
		<?php if ( $forms ) : ?>
			<select name="<?php echo esc_attr( $inputname ); ?>" class="condition-value" disabled>
			<?php foreach ( $forms as $form ) : ?>
				<option value="<?php echo (int) $form->ID; ?>" <?php selected( $form->ID, $value ); ?>><?php echo esc_html( '#' . $form->ID . ' ' . $form->name ); ?></option>
			<?php endforeach; ?>
			</select>
		<?php else : ?>
			<p><?php esc_html_e( 'No Form available', 'mailster' ); ?><input type="hidden" class="condition-value" disabled value="0" name="<?php echo esc_attr( $inputname ); ?>"></p>
		<?php endif; ?>
				
		<?php
	}


	private function value_field_clienttype( $value, $inputname ) {
		$value = is_array( $value ) ? $value[0] : $value;
		?>
		<select name="<?php echo esc_attr( $inputname ); ?>" class="condition-value" disabled>
			<option value="desktop"<?php selected( $value, 'desktop' ); ?>><?php esc_html_e( 'Desktop', 'mailster' ); ?></option>
			<option value="webmail"<?php selected( $value, 'webmail' ); ?>><?php esc_html_e( 'Webmail', 'mailster' ); ?></option>
			<option value="mobile"<?php selected( $value, 'mobile' ); ?>><?php esc_html_e( 'Mobile', 'mailster' ); ?></option>
		</select>
		<?php
	}


	private function value_field_campaign_related( $value_arr, $inputname ) {
		$value_arr = is_array( $value_arr ) ? $value_arr : array( $value_arr );
		$this->campaign_dropdown( $value_arr, $inputname );
	}



	private function value_field_click_related( $value_arr, $inputname ) {
		$value_arr = is_array( $value_arr ) ? $value_arr : array( $value_arr );

		// remove empty values and make it unique
		$value_arr = array_unique( array_filter( $value_arr ) );

		?>
		<div>
			<?php foreach ( $value_arr as $k => $v ) : ?>
				<?php
				if ( is_numeric( $v ) || in_array( $v, array_keys( $this->get_special_campaigns() ) ) ) {
					continue;
				}
				?>
			<div class="mailster-conditions-value-field-multiselect">
				<span><?php esc_html_e( 'or', 'mailster' ); ?> </span>
					<input type="text" class="regular-text condition-value" disabled value="<?php echo esc_attr( $v ); ?>" name="<?php echo esc_attr( $inputname ); ?>[]" placeholder="https://example.com">
				<a class="mailster-condition-remove-multiselect" title="<?php esc_attr_e( 'remove', 'mailster' ); ?>">&#10005;</a>
				<a class="button button-small mailster-condition-add-multiselect"><?php esc_html_e( 'or', 'mailster' ); ?></a>
			</div>

			<?php endforeach; ?>
		</div>
			<span><?php esc_html_e( 'in', 'mailster' ); ?> </span>

		<?php $this->campaign_dropdown( $value_arr, $inputname ); ?>

		<?php
	}



	private function campaign_dropdown( $value_arr, $inputname ) {
		global $post, $wp_post_statuses;
		$all_campaigns       = mailster( 'campaigns' )->get_campaigns(
			array(
				'post__not_in' => $post ? array( $post->ID ) : null,
				'orderby'      => 'post_title',
				'order'        => 'ASC',
			)
		);
		$all_campaigns_stati = wp_list_pluck( $all_campaigns, 'post_status' );
		asort( $all_campaigns_stati );
		?>
		<?php if ( $all_campaigns ) : ?>
			<?php foreach ( $value_arr as $k => $v ) : ?>
			<div class="mailster-conditions-value-field-multiselect">
				<span><?php esc_html_e( 'or', 'mailster' ); ?> </span>
				<select name="<?php echo esc_attr( $inputname ); ?>[]" class="condition-value" disabled>
					<option value="0"><?php esc_html_e( 'Any Campaign', 'mailster' ); ?></option>
					<?php
					if ( ! empty( $this->workflow_campaigns ) ) :
						?>
						<optgroup label="<?php esc_attr_e( 'Workflow Campaigns', 'mailster' ); ?>">
						<?php
						foreach ( $this->workflow_campaigns as $key => $camp ) :
							echo '<option value="' . esc_attr( $camp['id'] ) . '"' . selected( $v, $camp['id'], false ) . '>' . esc_attr( $camp['name'] ) . '</option>';
						endforeach;
						?>
						</optgroup>
					<?php endif; ?>
					<optgroup label="<?php esc_attr_e( 'Aggregate Campaigns', 'mailster' ); ?>">
					<?php
					foreach ( $this->get_special_campaigns() as $key => $name ) :
						echo '<option value="' . esc_attr( $key ) . '"' . selected( $v, $key, false ) . '>' . esc_attr( $name ) . '</option>';
					endforeach;
					?>
					</optgroup>
					<?php
					$status = '';
					foreach ( $all_campaigns_stati as $cj => $c ) :
						$c = $all_campaigns[ $cj ];
						if ( $status != $c->post_status ) :
							if ( $status ) {
								echo '</optgroup>';
							}
							echo '<optgroup label="' . esc_attr( $wp_post_statuses[ $c->post_status ]->label ) . '">';
							$status = $c->post_status;
						endif;
						?>
					<option value="<?php echo $c->ID; ?>" <?php selected( $v, $c->ID ); ?>><?php echo ( $c->post_title ? esc_html( $c->post_title ) : '[' . esc_html__( 'no title', 'mailster' ) . ']' ) . ' (# ' . esc_html( $c->ID ) . ')'; ?></option>
					<?php endforeach; ?>
					</optgroup>
				</select>
				<a class="mailster-condition-remove-multiselect" title="<?php esc_attr_e( 'remove', 'mailster' ); ?>">&#10005;</a>
				<a class="button button-small mailster-condition-add-multiselect"><?php esc_html_e( 'or', 'mailster' ); ?></a>
				</div>
		<?php endforeach; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No campaigns available', 'mailster' ); ?><input type="hidden" class="condition-value" disabled value="0" name="<?php echo esc_attr( $inputname ); ?>"></p>
		<?php endif; ?>


		<?php
	}

	private function value_field_list_related( $value_arr, $inputname ) {
		$value_arr = is_array( $value_arr ) ? $value_arr : array( $value_arr );
		$lists     = mailster( 'lists' )->get();

		if ( $lists ) :
			?>
			<?php foreach ( $value_arr as $k => $v ) : ?>
			<div class="mailster-conditions-value-field-multiselect">
				<span><?php esc_html_e( 'or', 'mailster' ); ?> </span>
				<select name="<?php echo esc_attr( $inputname ); ?>[]" class="condition-value" disabled>
					<option value="0">---</option>
					<?php
					$status = '';
					foreach ( $lists as $lj => $list ) :
						?>
					<option value="<?php echo $list->ID; ?>" <?php selected( $v, $list->ID ); ?>><?php echo ( $list->name ? esc_html( $list->name ) : '[' . esc_html__( 'no title', 'mailster' ) . ']' ); ?></option>
					<?php endforeach; ?>
				</select>
			<a class="mailster-condition-remove-multiselect" title="<?php esc_attr_e( 'remove', 'mailster' ); ?>">&#10005;</a>
			<a class="button button-small mailster-condition-add-multiselect"><?php esc_html_e( 'or', 'mailster' ); ?></a>
			</div>
			<?php endforeach; ?>
		<?php else : ?>
		<p><?php esc_html_e( 'No lists available', 'mailster' ); ?><input type="hidden" class="condition-value" disabled value="0" name="<?php echo esc_attr( $inputname ); ?>"></p>
		<?php endif; ?>

		<?php
	}


	private function value_field_tag_related( $value_arr, $inputname ) {
		$value_arr = is_array( $value_arr ) ? $value_arr : array( $value_arr );
		$tags      = mailster( 'tags' )->get();

		if ( $tags ) :
			?>
			<?php foreach ( $value_arr as $k => $v ) : ?>
			<div class="mailster-conditions-value-field-multiselect">
				<span><?php esc_html_e( 'or', 'mailster' ); ?> </span>
				<select name="<?php echo esc_attr( $inputname ); ?>[]" class="condition-value" disabled>
					<option value="0">---</option>
					<?php foreach ( $tags as $lj => $tag ) : ?>
					<option value="<?php echo esc_attr( $tag->ID ); ?>" <?php selected( $v, $tag->ID ); ?>><?php echo ( $tag->name ? esc_html( $tag->name ) : '[' . esc_html__( 'no title', 'mailster' ) . ']' ); ?></option>
					<?php endforeach; ?>
				</select>
			<a class="mailster-condition-remove-multiselect" title="<?php esc_attr_e( 'remove', 'mailster' ); ?>">&#10005;</a>
			<a class="button button-small mailster-condition-add-multiselect"><?php esc_html_e( 'or', 'mailster' ); ?></a>
			</div>
			<?php endforeach; ?>
		<?php else : ?>
		<p><?php esc_html_e( 'No tags available', 'mailster' ); ?><input type="hidden" class="condition-value" disabled value="0" name="<?php echo esc_attr( $inputname ); ?>"></p>
		<?php endif; ?>
		<?php
	}


	private function value_field_tagname_related( $value_arr, $inputname ) {

		$value_arr = is_array( $value_arr ) ? $value_arr : array( $value_arr );
		$value_arr = array_map( 'trim', $value_arr );
		$value_arr = array_filter( $value_arr );
		$value_arr = array_unique( $value_arr );
		?>
		<input type="hidden" name="<?php echo esc_attr( $inputname ); ?>[]" class="condition-value skip-value" disabled value="">
		<input type="text" class="regular-text token-helper" data-token="true" value="<?php echo esc_attr( implode( ', ', $value_arr ) ); ?>">
		<div class="token-helper-fields">
		<?php foreach ( $value_arr as $key => $value ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $inputname ); ?>[]" class="condition-value" disabled value="<?php echo esc_attr( $value ); ?>">
		<?php endforeach; ?>
		</div>
		<p class="description"><?php esc_html_e( 'Separate tags with commas', 'mailster' ); ?></p>
		<?php
	}


	private function value_field_geo( $value_arr, $inputname ) {
		$value_arr  = is_array( $value_arr ) ? $value_arr : array( $value_arr );
		$countries  = mailster( 'geo' )->get_countries( true );
		$continents = mailster( 'geo' )->get_continents( true );
		?>
		<?php foreach ( $value_arr as $k => $v ) : ?>
			<div class="mailster-conditions-value-field-multiselect">
				<span><?php esc_html_e( 'or', 'mailster' ); ?> </span>
				<select name="<?php echo esc_attr( $inputname ); ?>[]" class="condition-value" disabled>
					<option value="0">---</option>
					<optgroup label="<?php esc_attr_e( 'Continents', 'mailster' ); ?>">
					<?php foreach ( $continents as $code => $continent ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $v, $code ); ?>><?php echo esc_attr( $continent ); ?></option>
					<?php endforeach; ?>
					</optgroup>
					<?php foreach ( $countries as $continent => $sub_countries ) : ?>
					<optgroup label="<?php echo esc_attr( $continent ); ?>">
						<?php foreach ( $sub_countries as $code => $country ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $v, $code ); ?>><?php echo esc_attr( $country ); ?></option>
						<?php endforeach; ?>
					</optgroup>
					<?php endforeach; ?>
				</select>
				<a class="mailster-condition-remove-multiselect" title="<?php esc_attr_e( 'remove', 'mailster' ); ?>">&#10005;</a>
				<a class="button button-small mailster-condition-add-multiselect"><?php esc_html_e( 'or', 'mailster' ); ?></a>
			</div>
	<?php endforeach; ?>
		<?php
	}
}
