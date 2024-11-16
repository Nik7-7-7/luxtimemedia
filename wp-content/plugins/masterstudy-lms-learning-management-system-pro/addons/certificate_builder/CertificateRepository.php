<?php

namespace MasterStudy\Lms\Pro\addons\certificate_builder;

use MasterStudy\Lms\Repositories\AbstractRepository;

final class CertificateRepository extends AbstractRepository {
	const DEFAULT_CERTIFICATE = 'stm_default_certificate';

	protected static string $post_type = 'stm-certificates';

	protected static array $fields_post_map = array(
		'id'        => 'ID',
		'title'     => 'post_title',
		'author_id' => 'post_author',
	);

	protected static array $fields_meta_map = array(
		'orientation' => 'stm_orientation',
		'fields'      => 'stm_fields',
		'category'    => 'stm_category',
	);

	public function get_first_for_categories( array $categories ): int {
		global $wpdb;
		$categories_list = implode( ',', array_map( 'intval', $categories ) );

		$certificate_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT p.ID
				FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
				WHERE p.post_type = 'stm-certificates'
				AND pm.meta_key = 'stm_category'
				AND (pm.meta_value REGEXP CONCAT('(^|,)', %s, '(,|$)'))
				ORDER BY pm.meta_value ASC
				LIMIT 1
				",
				$categories_list
			)
		);

		if ( empty( $certificate_ids ) ) {
			$default_certificate = self::get_default_certificate();
			if ( ! empty( $default_certificate ) ) {
				$certificate_ids[] = $default_certificate;
			}
		}

		return $certificate_ids[0] ?? 0;
	}

	public function get_all(): array {
		$author_id  = get_current_user_id();
		$admin_page = is_admin();
		$pages      = $admin_page ? -1 : intval( $_GET['per_page'] ?? 10 );
		$args       = array(
			'post_type'      => 'stm-certificates',
			'posts_per_page' => $pages,
		);

		if ( ! $admin_page ) {
			$args['paged'] = intval( $_GET['page'] ?? ( get_query_var( 'page' ) ?? 1 ) );
			if ( ! empty( $_GET['s'] ) ) {
				$args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
			}
			if ( ! empty( $_GET['by_category'] ) ) {
				$args['post__in'] = array( $this->get_first_for_categories( array( intval( $_GET['by_category'] ) ) ) );
			}
			if ( ! empty( $_GET['by_instructor'] ) ) {
				$args['author'] = intval( $_GET['by_instructor'] );
			}
		}

		if ( ! current_user_can( 'administrator' ) ) {
			$args['author'] = $author_id;
		}

		$query = new \WP_Query();

		$certificates        = array();
		$default_certificate = get_option( 'stm_default_certificate' );

		foreach ( $query->query( $args ) as $post ) {
			$certificate = $this->map_post( $post );

			foreach ( self::$fields_meta_map as $field => $meta ) {
				$certificate[ $field ] = $this->cast( $field, get_post_meta( $post->ID, $meta, true ) );

				if ( 'fields' !== $field && isset( $certificate['category'] ) ) {
					$category_ids                 = explode( ',', $certificate['category'] );
					$category                     = get_term_by( 'id', $category_ids[0], 'stm_lms_course_taxonomy' );
					$certificate['category_name'] = $category ? $category->name : '';
				}
			}

			if ( ! $admin_page ) {
				$certificate['image']      = get_post_meta( $post->ID, 'certificate_preview', true );
				$certificate['is_default'] = ! empty( $default_certificate ) && intval( $default_certificate ) === intval( $post->ID );
				$certificate['instructor'] = get_the_author_meta( 'display_name', $certificate['author_id'] );
				$certificate['edit_link']  = admin_url( 'admin.php?page=certificate_builder&certificate_id=' . $certificate['id'] );
			}

			$certificates[] = $certificate;
		}

		if ( ! $admin_page ) {
			return array(
				'certificates' => $certificates,
				'max_pages'    => $query->max_num_pages,
				'per_page'     => intval( $_GET['per_page'] ?? 10 ),
				'page'         => intval( $_GET['page'] ?? 1 ),
			);
		}

		return $certificates;
	}

	public static function get_default_certificate() {
		return get_option( self::DEFAULT_CERTIFICATE, '' );
	}

	public static function set_default_certificate( $certificate_id ): void {
		update_option( self::DEFAULT_CERTIFICATE, $certificate_id );
	}

	public function certificate_page_url() {
		$settings = get_option( 'stm_lms_settings', array() );

		if ( empty( $settings['certificate_page_url'] ) ) {
			return null;
		}

		return get_the_permalink( $settings['certificate_page_url'] );

	}

	public function get_shapes() {
		$shapes = array(
			array(
				'id'  => 1,
				'svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M24 0H0V24H24V0Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 2,
				'svg' => '<svg width="35" height="34" viewBox="0 0 35 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M17.8571 0.0294375L0.886536 17L17.8571 33.9706L34.8277 17L17.8571 0.0294375Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 3,
				'svg' => '<svg width="25" height="18" viewBox="0 0 25 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M17.1806 17.2332H0.714294L8.24799 0.767578H24.7143L17.1806 17.2332Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 4,
				'svg' => '<svg width="25" height="22" viewBox="0 0 25 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M12.5714 0.607422L18.5711 10.9999L24.5714 21.3924H12.5714H0.571411L6.57106 10.9999L12.5714 0.607422Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 5,
				'svg' => '<svg width="25" height="28" viewBox="0 0 25 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Union" d="M12.4286 27.4524L0.428589 20.5694L0.467686 7.45337L12.4286 0.547852L24.4286 7.45337V20.5694L12.4286 27.4524Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 6,
				'svg' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M12.2856 0.464844L0.285645 9.39482L4.93658 23.5343H12.2856H19.6347L24.2856 9.39482L12.2856 0.464844Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 7,
				'svg' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M20.6281 20.4853C25.3144 15.799 25.3144 8.20101 20.6281 3.51472C15.9418 -1.17157 8.34382 -1.17158 3.65752 3.51472C-1.02877 8.20101 -1.02877 15.799 3.65752 20.4853C8.34382 25.1716 15.9418 25.1716 20.6281 20.4853Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 8,
				'svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M12 0C5.37248 0 0 5.37247 0 12C0 18.6275 5.37248 24 12 24C18.6275 24 24 18.6275 24 12C24 5.37247 18.6275 0 12 0ZM12 18.6528C8.32596 18.6528 5.34721 15.674 5.34721 12C5.34721 8.32594 8.32596 5.3472 12 5.3472C15.6741 5.3472 18.6528 8.32594 18.6528 12C18.6528 15.674 15.6741 18.6528 12 18.6528Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 9,
				'svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Union" d="M12.2862 23.9967C18.7814 23.8446 24 18.5318 24 12C24 5.37258 18.6274 0 12 0C5.46825 0 0.155367 5.2186 0.003346 11.7138L0 24L12.2862 23.9967Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 10,
				'svg' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Ellipse 191" d="M24.8571 0C11.6023 0 0.857117 10.7452 0.857117 24H24.8571V0Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 11,
				'svg' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Rectangle 75" d="M0.714294 0C13.9691 0 24.7143 10.7452 24.7143 24C11.4595 24 0.714294 13.2548 0.714294 0Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 12,
				'svg' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M24.5714 7.69125H16.8794V0H8.26266V7.69125H0.571411V16.308H8.26266V24H16.8794V16.308H24.5714V7.69125Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 13,
				'svg' => '<svg width="25" height="22" viewBox="0 0 25 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M18.3039 5.79815L12.1785 0.595703V6.99818H0.428589V15.0022H12.1785V21.4047L18.3039 16.2023L24.4286 11.0006L18.3039 5.79815Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 14,
				'svg' => '<svg width="25" height="20" viewBox="0 0 25 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M8.05342 19.935L0.285645 12.1665L3.92296 8.52919L8.05342 12.6596L20.6476 0.0654297L24.2856 3.70345L8.05342 19.935Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 15,
				'svg' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M12.1428 0.586914L15.8514 8.10115L24.1428 9.30558L18.1428 15.1546L19.5594 23.413L12.1428 19.5139L4.72626 23.413L6.14282 15.1546L0.142822 9.30558L8.43484 8.10115L12.1428 0.586914Z" fill="#808C98"/></svg>',
			),
			array(
				'id'  => 16,
				'svg' => '<svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="Vector" d="M12.022 21.4433L1.83571 11.2562C-0.612285 8.80824 -0.611523 4.84035 1.83571 2.39235C4.28295 -0.0556446 8.2516 -0.0548821 10.6996 2.39235L12.0227 3.71548L13.3001 2.43811C15.7481 -0.00988954 19.716 -0.00988954 22.164 2.43811C24.612 4.8861 24.612 8.854 22.164 11.302L12.0227 21.4433H12.022Z" fill="#808C98"/></svg>',
			),
		);

		return $shapes;
	}

	protected function update_meta( $id, $data ): void {
		parent::update_meta( $id, $data );

		if ( ! empty( $data['thumbnail_id'] ) ) {
			set_post_thumbnail( $id, intval( $data['thumbnail_id'] ) );
		} else {
			delete_post_thumbnail( $id );
		}

		$code = get_post_meta( $id, 'code', true );
		if ( empty( $code ) ) {
			update_post_meta( $id, 'code', CodeGenerator::generate() );
		}
	}
}
