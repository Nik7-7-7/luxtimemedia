<?php

namespace MasterStudy\Lms\Pro\addons\certificate_builder\Http\Controllers;

use MasterStudy\Lms\Pro\addons\certificate_builder\CertificateFieldsDataResolver;
use MasterStudy\Lms\Pro\addons\certificate_builder\CertificateRepository;
use MasterStudy\Lms\Pro\addons\certificate_builder\ImageEncoder;

class AjaxController {
	public static function get_certificates(): void {
		check_ajax_referer( 'stm_get_certificates', 'nonce' );

		$repo     = self::get_repository();
		$response = array();

		foreach ( $repo->get_all() as $certificate ) {
			$resource = array(
				'id'           => $certificate['id'],
				'title'        => $certificate['title'],
				'thumbnail_id' => get_post_thumbnail_id( $certificate['id'] ),
				'thumbnail'    => get_the_post_thumbnail_url( $certificate['id'], 'full' ),
				'classes'      => '',
				'filename'     => '',
				'author'       => array(
					'id'   => intval( $certificate['author_id'] ),
					'name' => get_the_author_meta( 'display_name', $certificate['author_id'] ),
				),
				'data'         => array(
					'orientation' => $certificate['orientation'],
					'fields'      => array(),
					'category'    => ! empty( $certificate['category'] ) ? sanitize_text_field( $certificate['category'] ) : '',
				),
			);

			$certificate_preview_path = get_post_meta( $certificate['id'], 'certificate_preview', true );
			$resource['image']        = $certificate_preview_path ?? '';

			if ( ! empty( $resource['thumbnail_id'] ) ) {
				$resource['filename'] = basename( get_attached_file( $resource['thumbnail_id'] ) );
			}

			if ( ! empty( $certificate['fields'] ) ) {
				$resource['data']['fields'] = json_decode( $certificate['fields'], true );
			}

			if ( empty( $resource['data']['orientation'] ) ) {
				$resource['data']['orientation'] = 'landscape';
			}

			$response[] = $resource;
		}

		wp_send_json( $response );
	}

	public static function get_fields(): void {
		check_ajax_referer( 'stm_get_certificate_fields', 'nonce' );

		$fields = array(
			'text'            => array(
				'name'     => esc_html__( 'Text', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( 'Any text', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'certificate',
			),
			'image'           => array(
				'name'     => esc_html__( 'Image', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => '',
				'category' => 'certificate',
			),
			'shape'           => array(
				'name'     => esc_html__( 'Shape', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => '',
				'category' => 'certificate',
			),
			'code'            => array(
				'name'     => esc_html__( 'Certificate code', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Certificate code-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'certificate',
			),
			'qrcode'          => array(
				'name'     => esc_html__( 'QR code', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => get_current_user_id(),
				'category' => 'certificate',
			),
			'current_date'    => array(
				'name'     => esc_html__( 'Current Date', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Current Date-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'certificate',
			),
			'course_name'     => array(
				'name'     => esc_html__( 'Course name', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Course name-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'course',
			),
			'details'         => array(
				'name'     => esc_html__( 'Details', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Details-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'course',
			),
			'progress'        => array(
				'name'     => esc_html__( 'Progress', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Progress-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'course',
			),
			'course_duration' => array(
				'name'     => esc_html__( 'Course Duration', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Course Duration-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'course',
			),
			'start_date'      => array(
				'name'     => esc_html__( 'Start Date', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Start Date-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'course',
			),
			'end_date'        => array(
				'name'     => esc_html__( 'End Date', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-End Date-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'course',
			),
			'student_name'    => array(
				'name'     => esc_html__( 'Student name', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Student name-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'student',
			),
			'student_code'    => array(
				'name'     => esc_html__( 'Student code', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Student code-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'student',
			),
			'author'          => array(
				'name'     => esc_html__( 'Instructor name', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Instructor-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'instructor',
			),
			'co_instructor'   => array(
				'name'     => esc_html__( 'Co Instructor name', 'masterstudy-lms-learning-management-system-pro' ),
				'value'    => esc_html__( '-Co Instructor-', 'masterstudy-lms-learning-management-system-pro' ),
				'category' => 'instructor',
			),
		);

		wp_send_json( apply_filters( 'stm_certificates_fields', $fields ) );
	}

	public static function save_certificate(): void {
		check_ajax_referer( 'stm_save_certificate', 'nonce' );

		do_action( 'masterstudy_before_save_certificate' );

		if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( null, 403 );
		}

		if ( empty( $_POST['certificate'] ) ) {
			return;
		}

		$certificate = json_decode( wp_unslash( $_POST['certificate'] ), true );
		$orientation = $certificate['data']['orientation'] ?? 'landscape';

		$args = array(
			'title'        => esc_html__( 'New template', 'masterstudy-lms-learning-management-system-pro' ),
			'orientation'  => $orientation,
			'fields'       => '',
			'category'     => '',
			'thumbnail_id' => $certificate['thumbnail_id'] ?? 0,
			'author_id'    => $certificate['author']['id'] ?? get_current_user_id(),
		);

		if ( ! empty( $certificate['title'] ) ) {
			$args['title'] = wp_strip_all_tags( $certificate['title'] );
		}
		if ( ! empty( $certificate['data']['fields'] ) ) {
			$args['fields'] = wp_json_encode( $certificate['data']['fields'], JSON_HEX_APOS + JSON_UNESCAPED_UNICODE );
		}

		$repo = self::get_repository();
		if ( empty( $certificate['id'] ) ) {
			$post_id = $repo->create( wp_slash( $args ) );
		} else {
			$post_id = intval( $certificate['id'] );
			$repo->update( $post_id, $args );
		}

		if ( ! empty( $_FILES['preview']['tmp_name'] ) ) {
			$upload_dir             = wp_upload_dir();
			$current_time_formatted = current_time( 'm-d-Y-H-i-s' );
			$custom_folder          = 'masterstudy-uploads';
			$file_name              = 'certificate-' . $post_id . '-' . $current_time_formatted . '.jpg';

			$custom_dir_path = $upload_dir['basedir'] . '/' . $custom_folder;
			$custom_dir_url  = $upload_dir['baseurl'] . '/' . $custom_folder;

			if ( ! file_exists( $custom_dir_path ) ) {
				wp_mkdir_p( $custom_dir_path );
			}

			$file_path    = $custom_dir_path . '/' . $file_name;
			$file_url     = $custom_dir_url . '/' . $file_name;
			$old_file_url = get_post_meta( $post_id, 'certificate_preview', true );

			if ( ! empty( $old_file_url ) ) {
				$old_file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $old_file_url );
				$old_file_path = str_replace( '/', DIRECTORY_SEPARATOR, $old_file_path );
				if ( file_exists( $old_file_path ) ) {
					unlink( $old_file_path );
				}
			}

			if ( move_uploaded_file( $_FILES['preview']['tmp_name'], $file_path ) ) {
				update_post_meta( $post_id, 'certificate_preview', $file_url );
			}
		}

		$response = array(
			'id'    => $post_id,
			'image' => '',
		);

		if ( ! empty( $file_url ) ) {
			$response['image'] = $file_url;
		}

		do_action( 'wp_ajax_stm_lms_pro_certificate_update' );

		wp_send_json( $response );
	}

	public static function generate_previews(): void {
		check_ajax_referer( 'stm_generate_certificates_preview', 'nonce' );

		if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( null, 403 );
		}

		$already_generated = get_option( 'stm_lms_certificates_previews_generated', '' );
		$response          = array(
			'success'      => false,
			'certificates' => array(),
		);

		if ( ! empty( $already_generated ) ) {
			wp_send_json( $response );
		}

		if ( empty( $_POST['previews'] ) ) {
			return;
		}

		$previews               = $_POST['previews'];
		$upload_dir             = wp_upload_dir();
		$current_time_formatted = current_time( 'm-d-Y-H-i-s' );
		$custom_folder          = 'masterstudy-uploads';
		$custom_dir_path        = $upload_dir['basedir'] . '/' . $custom_folder;
		$custom_dir_url         = $upload_dir['baseurl'] . '/' . $custom_folder;

		if ( ! file_exists( $custom_dir_path ) ) {
			wp_mkdir_p( $custom_dir_path );
		}

		foreach ( $previews as $key => $preview ) {
			if ( ! empty( $_FILES['previews']['tmp_name'][ $key ]['blob'] ) ) {
				$file_name = 'certificate-' . $preview['id'] . '-' . $current_time_formatted . '.jpg';
				$file_path = $custom_dir_path . '/' . $file_name;
				$file_url  = $custom_dir_url . '/' . $file_name;

				if ( move_uploaded_file( $_FILES['previews']['tmp_name'][ $key ]['blob'], $file_path ) ) {
					update_post_meta( $preview['id'], 'certificate_preview', $file_url );
					$response['certificates'][] = array(
						'id'    => intval( $preview['id'] ),
						'image' => $file_url,
					);
				}
			}
		}

		update_option( 'stm_lms_certificates_previews_generated', '1' );
		$response['success'] = true;

		wp_send_json( $response );
	}

	public static function save_default_certificate(): void {
		check_ajax_referer( 'stm_save_default_certificate', 'nonce' );

		do_action( 'masterstudy_before_save_default_certificate' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( null, 403 );
		}

		CertificateRepository::set_default_certificate( intval( $_POST['new_certificate'] ?? '' ) );

		wp_send_json( 'saved' );
	}

	public static function upload_certificate_images() {
		check_ajax_referer( 'stm_upload_certificate_images', 'nonce' );

		do_action( 'masterstudy_before_upload_certificate_images' );

		if ( isset( $_FILES['image'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attachment_id = media_handle_upload( 'image', 0 );

			if ( ! is_wp_error( $attachment_id ) ) {
				$attachment_url = wp_get_attachment_url( $attachment_id );

				return wp_send_json(
					array(
						'image' => array(
							'id'       => $attachment_id,
							'url'      => $attachment_url,
							'filename' => basename( $attachment_url ),
						),
					)
				);
			}

			return wp_send_json( array() );
		}
	}

	public static function delete_default_certificate(): void {
		check_ajax_referer( 'stm_delete_default_certificate', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( null, 403 );
		}

		do_action( 'masterstudy_before_delete_default_certificate' );

		CertificateRepository::set_default_certificate( '' );

		wp_send_json( 'deleted' );
	}

	public static function save_certificate_category(): void {
		check_ajax_referer( 'stm_save_certificate_category', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( null, 403 );
		}

		do_action( 'masterstudy_before_save_certificate_category' );

		if ( empty( $_POST['new_certificate'] ) || empty( $_POST['category'] ) ) {
			return;
		}

		$repo     = self::get_repository();
		$category = sanitize_text_field( wp_unslash( $_POST['category'] ) );

		if ( ! empty( $_POST['new_certificate']['id'] ) ) {
			$existing_categories = get_post_meta( intval( $_POST['new_certificate']['id'] ), 'stm_category', true );
			$existing_categories = $existing_categories ? explode( ',', $existing_categories ) : array();
			if ( ! in_array( $category, $existing_categories, true ) ) {
				$existing_categories[] = $category;
				$repo->update( intval( $_POST['new_certificate']['id'] ), array( 'category' => implode( ',', $existing_categories ) ) );
			}
		}

		if ( ! empty( $_POST['old_certificate']['id'] ) ) {
			$existing_categories = get_post_meta( intval( $_POST['old_certificate']['id'] ), 'stm_category', true );
			$existing_categories = $existing_categories ? explode( ',', $existing_categories ) : array();
			$key                 = array_search( $category, $existing_categories, true );
			if ( false !== $key ) {
				unset( $existing_categories[ $key ] );
				$repo->update( intval( $_POST['old_certificate']['id'] ), array( 'category' => implode( ',', $existing_categories ) ) );
			}
		}

		wp_send_json( 'saved' );
	}

	public static function delete_certificate_category(): void {
		check_ajax_referer( 'stm_delete_certificate_category', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( null, 403 );
		}

		do_action( 'masterstudy_before_delete_certificate_category' );

		if ( empty( $_POST['certificate'] || empty( $_POST['category'] ) ) ) {
			return;
		}

		$repo     = self::get_repository();
		$category = sanitize_text_field( wp_unslash( $_POST['category'] ) );

		if ( ! empty( $_POST['certificate']['id'] ) ) {
			$existing_categories = get_post_meta( intval( $_POST['certificate']['id'] ), 'stm_category', true );
			$existing_categories = $existing_categories ? explode( ',', $existing_categories ) : array();
			$key                 = array_search( $category, $existing_categories, true );
			if ( false !== $key ) {
				unset( $existing_categories[ $key ] );
				$repo->update( intval( $_POST['certificate']['id'] ), array( 'category' => implode( ',', $existing_categories ) ) );
			}
		}

		wp_send_json( 'saved' );
	}

	public static function delete_certificate() {
		check_ajax_referer( 'stm_delete_certificate', 'nonce' );

		if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( null, 403 );
		}

		do_action( 'masterstudy_before_delete_certificate' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['certificate_id'] ) ) {
			return;
		}

		self::get_repository()->delete( intval( $_GET['certificate_id'] ) );
		wp_send_json( 'deleted' );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	public static function get_certificate() {
		check_ajax_referer( 'stm_get_certificate', 'nonce' );

		$id        = '';
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT );
		$user_id   = filter_input( INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT );

		$repo = self::get_repository();
		if ( $course_id ) {
			$id = get_post_meta( $course_id, 'course_certificate', true );

			if ( ! $id ) {
				$terms = wp_get_post_terms( $course_id, 'stm_lms_course_taxonomy', array( 'fields' => 'ids' ) );
				$id    = $repo->get_first_for_categories( $terms );
			}
		}

		if ( empty( $id ) ) {
			$id = filter_input( INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		}

		if ( empty( $id ) ) {
			return;
		}

		$certificate              = $repo->get( $id );
		$certificate['course_id'] = $course_id;

		if ( empty( $certificate['orientation'] ) ) {
			$certificate['orientation'] = 'landscape';
		}

		$base64     = false;
		$image_size = false;
		$image      = get_post_thumbnail_id( $id );
		if ( $image ) {
			$image_file = get_attached_file( $image );

			if ( $image_file ) {
				$image_size = getimagesize( $image_file );
				$base64     = ImageEncoder::to_base64( $image_file );
			}
		}

		$fields = CertificateFieldsDataResolver::resolve( $certificate, $user_id ?? null );
		$fields = apply_filters( 'masterstudy_lms_certificate_fields_data', $fields, $certificate );

		$response = array(
			'data' => array(
				'orientation' => $certificate['orientation'],
				'fields'      => $fields,
				'thumbnail'   => $base64,
				'image_size'  => $image_size,
			),
		);

		wp_send_json( $response );
	}

	public static function get_categories() {
		check_ajax_referer( 'stm_get_certificate_categories', 'nonce' );

		global $wpdb;
		$offset = isset( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0;
		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
		$number = 10;

		$sql = "SELECT t.term_id, t.name 
				FROM {$wpdb->terms} AS t
				JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
				WHERE tt.taxonomy = 'stm_lms_course_taxonomy'";

		if ( ! empty( $search ) ) {
			$sql .= $wpdb->prepare( ' AND t.name LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$sql .= $wpdb->prepare( ' LIMIT %d, %d', $offset, $number );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$terms = $wpdb->get_results( $sql );

		$total_sql = "SELECT COUNT(*) 
					FROM {$wpdb->terms} AS t
					JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
					WHERE tt.taxonomy = 'stm_lms_course_taxonomy'";

		if ( ! empty( $search ) ) {
			$total_sql .= $wpdb->prepare( ' AND t.name LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total_terms = $wpdb->get_var( $total_sql );
		$result      = array();

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
				);
			}
		}

		$response = array(
			'categories' => $result,
			'total'      => intval( $total_terms ),
		);

		wp_send_json( $response );
	}

	private static function get_repository(): CertificateRepository {
		return new CertificateRepository();
	}
}
