<?php
/**
 * Simply Schedule Appointments Db.
 *
 * @since   0.0.2
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Db.
 *
 * @since 0.0.2
 */
abstract class SSA_Db {

	public function where_conditions( $args ) {
		global $wpdb;
		$where = '';

		if( ! empty( $args['id'] ) ) {

			if( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}
			$where .= " AND `".$this->primary_key."` IN( $ids ) ";
		}

		if ( !empty( $this->schema['user_id'] ) ) {		
			// rows for specific user actions	
			if( ! empty( $args['user_id'] ) ) {

				if( is_array( $args['user_id'] ) ) {
					$user_ids = implode( ',', array_map('intval', $args['user_id'] ) );
				} else {
					$user_ids = intval( $args['user_id'] );
				}

				$where .= " AND `user_id` IN( $user_ids ) ";

			}
		}

		if ( !empty( $this->post_id_field ) && !empty( $this->schema[$this->post_id_field] ) ) {		

			// rows for specific user accounts
			if( ! empty( $args['author_id'] ) ) {
				if( is_array( $args['author_id'] ) ) {
					$author_ids = implode( ',', array_map('intval', $args['author_id'] ) );
				} else {
					$author_ids = intval( $args['author_id'] );
				}
				$where .= $wpdb->prepare( " AND `%i` IN( SELECT ID FROM $wpdb->posts WHERE post_author IN ( $author_ids ) ) ", $this->post_id_field );
			}

			// rows for specific projectslug
			if ( !empty( $args['projectslug'] ) ) {
				if( is_array( $args['projectslug'] ) ) {
					$projectslugs = implode( ',', $args['projectslug'] );
				} else {
					$projectslugs = $args['projectslug'];
				}
				
				$where .= $wpdb->prepare( " AND `%i` IN( SELECT ID FROM $wpdb->posts WHERE post_name = $projectslugs ) ", $this->post_id_field );
			}

			// specific rows by name
			if( ! empty( $args[$this->post_id_field] ) ) {
				if ( is_array( $args[$this->post_id_field] ) ) {
					$post_ids = implode( ',', array_map('intval', $args[$this->post_id_field] ) );
					$where .=  $wpdb->prepare( " AND `%i` IN( $post_ids ) ", $this->post_id_field );
				} else {
					$where .= $wpdb->prepare( " AND `%i` = %d ", $this->post_id_field, $args[$this->post_id_field] );
				}
			}
		}

		// specific rows by name
		if ( !empty( $this->schema['type'] ) ) {		
			if( ! empty( $args['type'] ) ) {
				$where .= $wpdb->prepare( " AND `type` = '" . '%s' . "' ", $args['type'] );
			}
		}


		// specific rows by name
		if ( !empty( $this->schema['name'] ) ) {		
			if( ! empty( $args['name'] ) ) {
				$where .= $wpdb->prepare( " AND `name` = '" . '%s' . "' ", $args['name'] );
			}
		}

		if ( !empty( $this->schema['start_date'] ) ) {		
			// Customers created for a specific date or in a date range
			if( ! empty( $args['start_date'] ) ) {

				if( !is_array( $args['start_date'] ) ) {

					$year  = date( 'Y', strtotime( $args['start_date'] ) );
					$month = date( 'm', strtotime( $args['start_date'] ) );
					$day   = date( 'd', strtotime( $args['start_date'] ) );

					$where .= " AND $year = YEAR ( start_date ) AND $month = MONTH ( start_date ) AND $day = DAY ( start_date )";
				}

			} else {

				if( ! empty( $args['start_date_min'] ) ) {
					$where .=  $wpdb->prepare( " AND `start_date` >= '" . '%s' . "' ", $args["start_date_min"] );
				}

				if( ! empty( $args['start_date_max'] ) ) {
					$where .= $wpdb->prepare( " AND `start_date` <= '" . '%s' . "' ", $args["start_date_max"]);
				}

			}
		}

		if ( !empty( $this->schema['end_date'] ) ) {		
			// Customers created for a specific date or in a date range
			if( ! empty( $args['end_date'] ) ) {

				if( !is_array( $args['end_date'] ) ) {

					$year  = date( 'Y', strtotime( $args['end_date'] ) );
					$month = date( 'm', strtotime( $args['end_date'] ) );
					$day   = date( 'd', strtotime( $args['end_date'] ) );

					$where .= " AND $year = YEAR ( end_date ) AND $month = MONTH ( end_date ) AND $day = DAY ( end_date )";
				}

			} else {

				if( ! empty( $args['end_date_min'] ) ) {
					$where .=  $wpdb->prepare(  " AND `end_date` >= '" . '%s' . "' ", $args["end_date_min"] );
				}

				if( ! empty( $args['end_date_max'] ) ) {
					$where .=  $wpdb->prepare( " AND `end_date` <= '" . '%s' . "' ", $args["end_date_max"] );
				}

			}
		}

		if ( !empty( $this->schema['date_created'] ) ) {		
			// Customers created for a specific date or in a date range
			if( ! empty( $args['date_created'] ) ) {

				if( !is_array( $args['date_created'] ) ) {

					$year  = date( 'Y', strtotime( $args['date_created'] ) );
					$month = date( 'm', strtotime( $args['date_created'] ) );
					$day   = date( 'd', strtotime( $args['date_created'] ) );

					$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
				}

			} else {

				if( ! empty( $args['date_created_min'] ) && false !== strtotime( $args['date_created_min'] ) ) {
					$where .=  $wpdb->prepare( " AND `date_created` >= '" . '%s' . "' ", $args["date_created_min"] );
				}

				if( ! empty( $args['date_created_max'] ) && false !== strtotime( $args['date_created_max'] ) ) {
					$where .=  $wpdb->prepare( " AND `date_created` <= '" . '%s' . "' ", $args["date_created_max"] );
				}

			}
		}

		if ( !empty( $this->schema['date_completed'] ) ) {		
			// Action completed at a specific date or in a date range
			if( ! empty( $args['date_completed'] ) ) {

				if( !is_array( $args['date_completed'] ) ) {

					$year  = date( 'Y', strtotime( $args['date_completed'] ) );
					$month = date( 'm', strtotime( $args['date_completed'] ) );
					$day   = date( 'd', strtotime( $args['date_completed'] ) );

					$where .= " AND $year = YEAR ( date_completed ) AND $month = MONTH ( date_completed ) AND $day = DAY ( date_completed )";
				}

			} else {

				if( ! empty( $args['date_completed_min'] ) && false !== strtotime( $args['date_completed_min'] ) ) {
					$where .=  $wpdb->prepare( " AND `date_completed` >= '" . '%s' . "' ", $args["date_completed_min"] );
				}

				if( ! empty( $args['date_completed_max'] ) && false !== strtotime( $args['date_completed_max'] ) ) {
					$where .=  $wpdb->prepare( " AND `date_completed` <= '" . '%s' . "' ", $args["date_completed_max"] );
				}

			}
		}

		return $where;
	}



}
