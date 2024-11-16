<?php

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\Entities\Schedule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ManageSchedulesPage extends ManagePostsPage {

	/**
	 * @since 1.10.1
	 */
	public function __construct( $postType ) {
		parent::__construct( $postType );

		add_action(
			'admin_action_mpa_duplicate_schedule',
			function() {

				if ( empty( $_GET['mpa_schedule_id'] ) ||
					empty( $_GET['action'] ) ||
					'mpa_duplicate_schedule' != $_GET['action'] ||
					empty( $_GET['duplicate_nonce'] ) ||
					! wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) )
				) {
					return;
				}

				$this->duplicateSchedule();
			}
		);
	}

	/**
	 * @since 1.10.1
	 */
	protected function addActions() {
		parent::addActions();

		add_filter(
			'post_row_actions',
			function( $actions, $post ) {

				if ( current_user_can( 'edit_posts' ) ) {

					$actions['duplicate'] = '<a href="' .
					wp_nonce_url(
						'admin.php?action=mpa_duplicate_schedule&mpa_schedule_id=' . $post->ID,
						basename( __FILE__ ),
						'duplicate_nonce'
					) .
						'" title="' . esc_attr__( 'Duplicate', 'motopress-appointment' ) .
						'" rel="permalink">' . esc_html__( 'Duplicate', 'motopress-appointment' ) . '</a>';
				}
				return $actions;
			},
			10,
			2
		);
	}

	/**
	 * @since 1.10.1
	 */
	private function duplicateSchedule() {
		$scheduleId = absint( $_GET['mpa_schedule_id'] );
		$post       = get_post( $scheduleId );

		if ( empty( $post ) ) {

			// Translators: %s: Post ID.
			wp_die( sprintf( esc_html__( 'Duplication failed, could not find original post with ID = %s', 'motopress-appointment' ), $scheduleId ) );
		}

		$newScheduleId = wp_insert_post(
			array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => get_current_user_id(),
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name . '_copy',
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				// translators: title of the post that was copied
				'post_title'     => sprintf( esc_html__( 'Copy of %s', 'motopress-appointment' ), $post->post_title ),
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			)
		);

		$taxonomies = get_object_taxonomies( $post->post_type );
		foreach ( $taxonomies as $taxonomy ) {

			$post_terms = wp_get_object_terms( $scheduleId, $taxonomy, array( 'fields' => 'slugs' ) );
			wp_set_object_terms( $newScheduleId, $post_terms, $taxonomy, false );
		}

		$postMetas = get_post_meta( $scheduleId, '', true );

		foreach ( $postMetas as $metaKey => $metaValue ) {

			if ( '_mpa_employee' != $metaKey ) {

				$value = @unserialize( $metaValue[0] );
				if ( 'b:0;' != $metaValue[0] && false === $value ) {

					$value = $metaValue[0];
				}

				update_post_meta( $newScheduleId, $metaKey, $value );
			}
		}

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $newScheduleId ) );
		exit;
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function customColumns() {
		return array(
			'employee' => esc_html__( 'Employee', 'motopress-appointment' ),
			'location' => esc_html__( 'Location', 'motopress-appointment' ),
		);
	}

	/**
	 * @param string $columnName
	 * @param Schedule $entity
	 *
	 * @since 1.0
	 */
	protected function displayValue( $columnName, $entity ) {
		switch ( $columnName ) {
			case 'employee':
				if ( $entity->getEmployeeId() > 0 ) {
					echo mpa_tmpl_edit_post_link( $entity->getEmployeeId() );
				} else {
					echo mpa_tmpl_placeholder();
				}
				break;

			case 'location':
				if ( 0 < $entity->getLocationId() ) {
					echo mpa_tmpl_edit_post_link( $entity->getLocationId() );
				} else {
					echo '<strong>' .
						esc_html__( 'This schedule will not work until you set the main location for it.', 'motopress-appointment' ) .
						'</strong>';
				}
				break;
		}
	}
}
