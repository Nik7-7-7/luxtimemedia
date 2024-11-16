<?php

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\Entities\Employee;
use MotoPress\Appointment\Handlers\GoogleCalendarSyncHandler;
use MotoPress\Appointment\Handlers\SecurityHandler;
use MotoPress\Appointment\Entities\Notification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ManageEmployeesPage extends ManagePostsPage {


	/**
	 * @since 1.10.1
	 */
	public function __construct( $postType ) {

		parent::__construct( $postType );

		add_action(
			'admin_action_mpa_duplicate_employee',
			function() {

				if ( empty( $_GET['mpa_employee_id'] ) ||
					empty( $_GET['action'] ) ||
					'mpa_duplicate_employee' !== $_GET['action'] ||
					empty( $_GET['duplicate_nonce'] ) ||
					! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['duplicate_nonce'] ) ), basename( __FILE__ ) )
				) {
					return;
				}

				$this->duplicateEmployee();
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
						'admin.php?action=mpa_duplicate_employee&mpa_employee_id=' . $post->ID,
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
	private function duplicateEmployee() {

		$employeeId = isset( $_GET['mpa_employee_id'] ) ? absint( wp_unslash( $_GET['mpa_employee_id'] ) ) : 0;
		$post       = get_post( $employeeId );

		if ( empty( $post ) ) {
			// Translators: %s: Post ID.
			wp_die(
				sprintf(
					esc_html__( 'Duplication failed, could not find original post with ID = %s', 'motopress-appointment' ),
					esc_html( $employeeId )
				)
			);
		}

		$newEmployeeId = wp_insert_post(
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

			$post_terms = wp_get_object_terms( $employeeId, $taxonomy, array( 'fields' => 'slugs' ) );
			wp_set_object_terms( $newEmployeeId, $post_terms, $taxonomy, false );
		}

		$postMetas = get_post_meta( $employeeId, '', true );

		foreach ( $postMetas as $metaKey => $metaValue ) {

			if ( GoogleCalendarSyncHandler::MPA_EMPLOYEE_META_KEY_GOOGLE_CALENDAR_TOKEN !== $metaKey &&
				SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER !== $metaKey ) {

				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$value = @unserialize( $metaValue[0] );

				if ( 'b:0;' !== $metaValue[0] && false === $value ) {

					$value = $metaValue[0];
				}

				update_post_meta( $newEmployeeId, $metaKey, $value );
			}
		}

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $newEmployeeId ) );
		exit;
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function customColumns() {
		return array(
			'contacts' => __( 'Contacts', 'motopress-appointment' ),
			'services' => __( 'Services', 'motopress-appointment' ),
		);
	}

	/**
	 * @param string $columnName
	 * @param Employee $entity
	 *
	 * @since 1.0
	 */
	protected function displayValue( $columnName, $entity ) {

		switch ( $columnName ) {
			case 'contacts':
				$contacts = array();

				if ( ! empty( $entity->getEmail() ) ) {
					$contacts[] = $entity->getEmail();
				}

				if ( ! empty( $entity->getPhoneNumber() ) ) {
					$contacts[] = $entity->getPhoneNumber();
				}

				if ( ! empty( $contacts ) ) {

					echo wp_kses_post( implode( '<br>', $contacts ) );
				}
				break;

			case 'services':
				$serviceIds = mpapp()->repositories()->service()->findAllByValueInMeta(
					'_mpa_employees',
					$entity->getId(),
					array(
						'fields' => array( 'id' => 'title' ),
					)
				);

				if ( empty( $serviceIds ) ) {
					echo esc_html( mpa_tmpl_placeholder() );
				} else {
					$serviceLinks = mpa_tmpl_edit_post_links( $serviceIds );

					echo '<p>';
					echo wp_kses_post( implode( '<br>', $serviceLinks ) );
					echo '</p>';
				}

				break;
		}
	}
}
