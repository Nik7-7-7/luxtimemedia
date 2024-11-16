<?php

class MsLmsEmailsAnalyticsHelper {

	private $date_from;
	private $date_to;
	private $user_id;
	private $db; // Property to store the wpdb instance

	/**
	 * Constructor to initialize properties.
	 *
	 * @param string $date_from Start date in 'Y-m-d' format.
	 * @param string $date_to End date in 'Y-m-d' format.
	 * @param int|null $user_id (Optional) The user ID.
	 */
	public function __construct( $date_from, $date_to, $user_id = null ) {
		// Set date range for the full day in GMT format
		$this->date_from = gmdate( 'Y-m-d 00:00:00', strtotime( $date_from ) );
		$this->date_to   = gmdate( 'Y-m-d 23:59:59', strtotime( $date_to ) );
		$this->user_id   = $user_id;

		// Assign the global $wpdb to the class property
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Get the sum of points for the user within the date range.
	 *
	 * @return int Sum of points.
	 */
	public function get_user_points_sum() {
		if ( ! $this->user_id ) {
			return 0;
		}

		global $wpdb; // Ensure $wpdb is available
		$user_id    = intval( $this->user_id );
		$table_name = $wpdb->prefix . 'stm_lms_user_points'; // Dynamically add prefix

		// Check if the table exists without using wpdb::prepare() here
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) { // phpcs:ignore
			return 0;
		}

		// Proceed with the query if the table exists
		$sum = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(score) FROM {$table_name} WHERE user_id = %d AND timestamp BETWEEN %d AND %d", // phpcs:ignore
				$user_id,
				strtotime( $this->date_from ),
				strtotime( $this->date_to )
			)
		);

		return intval( $sum );
	}

	/**
	 * Get the count of assignments for the user within the date range.
	 *
	 * @return int Count of assignments.
	 */
	public function get_user_assignments_count() {
		if ( ! $this->user_id ) {
			return 0;
		}

		$date_from_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $this->date_from ) );
		$date_to_gmt   = gmdate( 'Y-m-d H:i:s', strtotime( $this->date_to ) );

		// Prepare the SQL query with correct placeholders
		$query = $this->db->prepare(
			"
        SELECT p.ID 
        FROM {$this->db->posts} p
        INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
        INNER JOIN {$this->db->postmeta} pm_status ON p.ID = pm_status.post_id
        WHERE p.post_type = %s
          AND p.post_status IN ('publish', 'pending')
          AND p.post_author = %d
          AND pm.meta_key = %s
          AND pm.meta_value = %d
          AND pm_status.meta_key = %s
          AND pm_status.meta_value = %s
          AND p.post_date_gmt BETWEEN %s AND %s
        ",
			'stm-user-assignment',  // Post type
			$this->user_id,          // Post author (user ID)
			'student_id',            // Meta key for student ID
			$this->user_id,          // Student ID
			'status',                // Meta key for status
			'passed',                // Meta value for passed assignments
			$date_from_gmt,          // Date range start (string)
			$date_to_gmt             // Date range end (string)
		);

		$results = $this->db->get_results( $query, ARRAY_A );

		return count( $results );
	}

	/**
	 * Get the number of instructors registered within the date range.
	 *
	 * @return int Number of instructors registered.
	 */
	public function get_registrations_count_by_role( $role ) {
		$role_meta_key = $this->db->prefix . 'capabilities';

		$query = $this->db->prepare(
			"SELECT u.ID 
         FROM {$this->db->users} u
         INNER JOIN {$this->db->usermeta} um ON u.ID = um.user_id
         WHERE um.meta_key = %s 
         AND um.meta_value LIKE %s
         AND u.user_registered BETWEEN %s AND %s",
			$role_meta_key,
			'%' . $this->db->esc_like( $role ) . '%',
			$this->date_from,
			$this->date_to
		);

		$results = $this->db->get_results( $query, ARRAY_A );

		return count( $results );
	}

	/**
	 * Get the number of posts published between two dates for a given post type.
	 *
	 * @param string $post_type The post type to query.
	 *
	 * @return int Number of posts published in the given date range.
	 */
	public function get_courses_published_count( $post_type ) {
		$post_type   = sanitize_key( $post_type );
		$post_status = 'publish';

		$sql = "
        SELECT ID 
        FROM {$this->db->posts} 
        WHERE post_type = %s 
        AND post_status = %s 
        AND post_date >= %s 
        AND post_date <= %s
    ";

		if ( ! empty( $this->user_id ) ) {
			// Get user data
			$user = get_userdata( $this->user_id );

			// Check if the user does not have the 'administrator' role
			if ( ! in_array( 'administrator', (array) $user->roles ) ) {
				$sql          .= ' AND post_author = %d';
				$prepared_sql = $this->db->prepare( $sql, $post_type, $post_status, $this->date_from, $this->date_to, $this->user_id );
			} else {
				$prepared_sql = $this->db->prepare( $sql, $post_type, $post_status, $this->date_from, $this->date_to );
			}
		}

		$results = $this->db->get_results( $prepared_sql, ARRAY_A );

		return count( $results );
	}

	public function get_reviews_for_instructor_courses( $author_id, $date_from, $date_to ) {
		global $wpdb;

		// Prepare the SQL query
		$query = $wpdb->prepare(
			"
        SELECT pm.post_id AS review_id, p.post_title AS review_title, pm.meta_value AS course_id, p.post_date
        FROM {$wpdb->postmeta} pm
        JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = %s  -- Meta key for course ID in reviews
          AND pm.meta_value IN (
              SELECT ID
              FROM {$wpdb->posts}
              WHERE post_type = %s
                AND post_status = %s
                AND post_author = %d
          )
          AND p.post_type = %s  -- Filter by reviews
          AND p.post_date BETWEEN %s AND %s
        ",
			'review_course',   // The meta key that links the review to a course
			'stm-courses',     // Post type for courses
			'publish',         // Only published courses
			$author_id,        // Author (instructor) ID
			'stm-reviews',     // Post type for reviews
			$date_from,        // Starting date for the date range
			$date_to           // Ending date for the date range
		);

		// Execute the query and get the results
		$reviews = $wpdb->get_results( $query ); // phpcs:ignore

		return count( $reviews );
	}

}
