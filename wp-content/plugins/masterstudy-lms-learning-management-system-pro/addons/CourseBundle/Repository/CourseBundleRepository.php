<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle\Repository;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Validation\Validator;

class CourseBundleRepository {
	const POST_TYPE = 'stm-course-bundles';

	const PRICE_META_KEY = 'stm_lms_bundle_price';

	const COURSES_META_KEY = 'stm_lms_bundle_ids';

	/**
	 * Sorting mapping for Get Bundles
	 */
	public const SORT_MAPPING = array(
		'date_low'   => array(
			'orderby' => 'date',
			'order'   => 'ASC',
		),
		'price_high' => array(
			'meta_key' => 'stm_lms_bundle_price',
			'orderby'  => 'meta_value_num',
			'order'    => 'DESC',
		),
		'price_low'  => array(
			'meta_key' => 'stm_lms_bundle_price',
			'orderby'  => 'meta_value_num',
			'order'    => 'ASC',
		),
	);

	public function get_instructor_bundles( $args = array() ) {
		$default_args = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => 6,
			'paged'          => 1,
			'post_status'    => array( 'publish' ),
		);

		$query_args   = wp_parse_args( $args, $default_args );
		$query        = new \WP_Query( $query_args );
		$course_ids   = array();
		$bundles_data = array(
			'posts' => array(),
		);

		if ( ! empty( $query->posts ) ) {
			foreach ( $query->posts as $post ) {
				$bundle_courses = self::get_bundle_courses( $post->ID );
				$bundle_courses = array_filter(
					$bundle_courses,
					function( $course ) {
						return ! empty( get_post_type( $course ) ) && 'publish' === get_post_status( $course );
					}
				);

				if ( ! empty( $bundle_courses ) ) {
					$course_ids = array_unique( array_merge( $course_ids, $bundle_courses ) );
				}

				$bundles_data['posts'][] = array_merge(
					$this->get_bundle_post_data( $post->ID ),
					array( 'courses' => $bundle_courses )
				);
			}
		}

		$bundles_data['courses']     = $this->get_courses_data( $course_ids );
		$bundles_data['total_pages'] = $query->max_num_pages;

		$response = array();

		if ( ! empty( $bundles_data['posts'] ) ) {
			$response['courses'] = array_map(
				function( $bundle ) use ( $bundles_data ) {
					return \STM_LMS_Templates::load_lms_template(
						'components/bundle/card/default',
						array(
							'bundle'  => $bundle,
							'courses' => $bundles_data['courses'],
						)
					);
				},
				$bundles_data['posts'],
			);

			$response['pagination'] = \STM_LMS_Templates::load_lms_template(
				'components/pagination',
				array(
					'max_visible_pages' => 5,
					'total_pages'       => $bundles_data['total_pages'],
					'current_page'      => $args['paged'] ?? 1,
					'dark_mode'         => false,
					'is_queryable'      => false,
					'done_indicator'    => false,
					'is_api'            => true,
				)
			);

			$response['total_pages'] = $bundles_data['total_pages'];
		}

		return $response;
	}

	/* Deprecated: delete after user account styles will change */
	public function get_bundles( $args = array(), $public = true ) {
		$per_page = $args['posts_per_page'] ?? 6;
		$paged    = get_query_var( 'paged' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_page = ! empty( $paged ) ? intval( $paged ) : intval( $_GET['page'] ?? 0 );

		$default_args = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => $per_page,
			'post_status'    => array( 'publish', 'draft' ),
			'author'         => get_current_user_id(),
			'offset'         => $current_page > 0 ? ( $current_page * $per_page ) - $per_page : 0,
		);

		$response = array(
			'posts' => array(),
		);

		if ( ! is_user_logged_in() && ! $public ) {
			return $response;
		}

		$query = new \WP_Query( wp_parse_args( $args, $default_args ) );

		$course_ids = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$bundle_courses = self::get_bundle_courses( get_the_ID() );

				foreach ( $bundle_courses as $course_key => $course ) {
					if ( empty( get_post_type( $course ) ) || 'publish' !== get_post_status( $course ) ) {
						unset( $bundle_courses[ $course_key ] );
					}
				}

				if ( ! empty( $bundle_courses ) ) {
					$course_ids = array_unique( array_merge( $course_ids, $bundle_courses ) );
				}

				$response['posts'][] = array_merge(
					$this->get_bundle_post_data( get_the_ID() ),
					array(
						'courses' => $bundle_courses,
					)
				);
			}

			wp_reset_postdata();
		}

		$response['courses'] = $this->get_courses_data( $course_ids );
		$response['pages']   = ceil( $query->found_posts / $per_page );
		$response['total']   = $query->found_posts;

		return $response;
	}

	public function get_bundle_data( int $bundle_id ) {
		$bundle = get_post( $bundle_id );

		if ( ! empty( $bundle ) ) {
			$bundle_courses = self::get_bundle_courses( $bundle_id );

			if ( empty( $bundle_courses ) ) {
				$bundle->bundle_courses = '';
			} else {
				$bundle_courses         = \STM_LMS_Instructor::get_courses(
					array(
						'posts_per_page' => count( $bundle_courses ),
						'post__in'       => $bundle_courses,
					),
					true
				);
				$bundle->bundle_courses = $bundle_courses['posts'];
			}

			$bundle->bundle_price    = floatval( self::get_bundle_price( $bundle_id ) );
			$image_id                = get_post_thumbnail_id( $bundle_id );
			$bundle->bundle_image_id = ! empty( $image_id ) ? get_the_title( $image_id ) : '';
		}

		return $bundle;
	}

	public function get_bundle_post_data( $bundle_id ): array {
		$price         = self::get_bundle_price( $bundle_id );
		$courses_price = self::get_bundle_courses_price( $bundle_id );

		return array(
			'id'            => $bundle_id,
			'title'         => get_the_title( $bundle_id ),
			'url'           => get_the_permalink( $bundle_id ),
			'edit_url'      => ms_plugin_user_account_url( "bundles/$bundle_id" ),
			'raw_price'     => $price,
			'price'         => \STM_LMS_Helpers::display_price( $price ),
			'rating'        => self::get_bundle_rating( $bundle_id ),
			'courses_price' => \STM_LMS_Helpers::display_price( $courses_price ),
			'status'        => get_post_status( $bundle_id ),
		);
	}

	public function get_courses_data( array $course_ids = array() ) {
		if ( empty( $course_ids ) ) {
			return array();
		}

		$courses = get_posts(
			array(
				'post_type'      => PostType::COURSE,
				'posts_per_page' => count( $course_ids ),
				'post__in'       => $course_ids,
			)
		);

		$courses_data = array();

		if ( ! empty( $courses ) ) {
			$size_large = function_exists( 'stm_get_VC_img' ) ? '272x161' : 'img-300-225';
			$size_small = function_exists( 'stm_get_VC_img' ) ? '50x50' : 'img-300-225';

			foreach ( $courses as $course ) {
				$rating          = get_post_meta( $course->ID, 'course_marks', true );
				$rates           = \STM_LMS_Course::course_average_rate( $rating );
				$price           = get_post_meta( $course->ID, 'price', true );
				$sale_price      = \STM_LMS_Course::get_sale_price( $course->ID );
				$thumbnail_id    = (int) get_post_meta( $course->ID, '_thumbnail_id', true );
				$image           = function_exists( 'stm_get_VC_img' )
					? html_entity_decode( stm_get_VC_img( $thumbnail_id, $size_large ) )
					: get_the_post_thumbnail( $course->ID, $size_large );
				$image_small     = function_exists( 'stm_get_VC_img' )
					? html_entity_decode( stm_get_VC_img( $thumbnail_id, $size_small ) )
					: get_the_post_thumbnail( $course->ID, $size_small );
				$image_url       = wp_get_attachment_image_url( $thumbnail_id, $size_large );
				$image_url_small = wp_get_attachment_image_url( $thumbnail_id, $size_small );
				if ( empty( $price ) && ! empty( $sale_price ) ) {
					$price = $sale_price;
				}
				$sale_price_active = \STM_LMS_Helpers::is_sale_price_active( $course->ID );

				$courses_data[ $course->ID ] = array(
					'id'              => $course->ID,
					'time'            => get_post_timestamp( $course ),
					'title'           => $course->post_title,
					'link'            => get_permalink( $course->ID ),
					'image'           => $image,
					'image_small'     => $image_small,
					'image_url'       => $image_url,
					'image_url_small' => $image_url_small,
					'terms'           => stm_lms_get_terms_array( $course->ID, 'stm_lms_course_taxonomy', false, true ),
					'status'          => $course->post_status,
					'percent'         => $rates['percent'],
					'is_featured'     => get_post_meta( $course->ID, 'featured', true ),
					'average'         => $rates['average'],
					'total'           => ! empty( $rating ) ? count( $rating ) : '',
					'views'           => \STM_LMS_Course::get_course_views( $course->ID ),
					'price'           => \STM_LMS_Helpers::display_price( $price ),
					'simple_price'    => $sale_price && $sale_price_active ? $sale_price : $price,
					'sale_price'      => $sale_price && $sale_price_active ? \STM_LMS_Helpers::display_price( $sale_price ) : 0,
				);
			}
		}

		wp_reset_postdata();

		return $courses_data;
	}

	public static function save_bundle() {
		do_action( 'stm_lms_save_bundle' );

		$validator = new Validator(
			$_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing
			array(
				'id'          => 'nullable|numeric',
				'name'        => 'required|string',
				'courses'     => 'required|string',
				'description' => 'required|string',
				'price'       => 'required|numeric',
			)
		);

		if ( $validator->fails() ) {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => $validator->get_errors_array(),
				)
			);
		}

		$data = $validator->get_validated();

		if ( empty( $data['id'] ) && empty( $_FILES['file'] ) ) {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Please, upload bundle image', 'masterstudy-lms-learning-management-system-pro' ),
				)
			);
		}

		if ( empty( $data ) ) {
			return false;
		}

		$bundle_id   = $data['id'] ?? 0;
		$post_status = 'draft';

		if ( empty( $data['id'] ) ) {
			if ( floatval( self::count() ) < floatval( ( new CourseBundleSettings() )->get_bundles_limit() ) ) {
				$post_status = 'publish';
			}
		} elseif ( 'publish' === get_post_status( $data['id'] ) ) {
			$post_status = 'publish';
		}

		if ( ! $bundle_id ) {
			$bundle_id = wp_insert_post(
				array(
					'post_status'  => $post_status,
					'post_type'    => self::POST_TYPE,
					'post_title'   => $data['name'],
					'post_content' => $data['description'],
				)
			);
		} else {
			wp_update_post(
				array(
					'ID'           => $bundle_id,
					'post_status'  => $post_status,
					'post_title'   => $data['name'],
					'post_content' => $data['description'],
				)
			);
		}

		// Update bundle meta
		$limit = ( new CourseBundleSettings() )->get_bundle_courses_limit();

		update_post_meta( $bundle_id, self::COURSES_META_KEY, array_slice( explode( ',', $data['courses'] ), 0, $limit ) );
		update_post_meta( $bundle_id, self::PRICE_META_KEY, $data['price'] );

		// Update bundle image
		if ( ! empty( $_FILES['file'] ) ) {
			$image = self::upload_image( $bundle_id, $_FILES['file'] );

			if ( $image['error'] ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => $image,
					)
				);
			}
		}

		wp_send_json(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Bundle saved. Redirecting...', 'masterstudy-lms-learning-management-system-pro' ),
				'url'     => ms_plugin_user_account_url( 'bundles' ),
			)
		);
	}

	public static function upload_image( $bundle_id, $file ): array {
		do_action( 'stm_lms_upload_files' );

		$filename = basename( $file['name'] );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$upload_file = wp_upload_bits( $filename, null, file_get_contents( $file['tmp_name'] ) );

		if ( ! empty( $upload_file['error'] ) ) {
			return array(
				'error'   => true,
				'message' => $upload_file['error'],
			);
		}

		$wp_filetype   = wp_check_filetype( $filename, null );
		$attachment    = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_parent'    => $bundle_id,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_excerpt'   => 'stm_lms_assignment',
			'post_status'    => 'inherit',
		);
		$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $bundle_id );

		if ( ! is_wp_error( $attachment_id ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );

			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			set_post_thumbnail( $bundle_id, $attachment_id );
		}

		return array(
			'error' => false,
			'id'    => $attachment_id,
			'link'  => wp_get_attachment_url( $attachment_id ),
		);
	}

	public static function count( $args = array() ): int {
		$default = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => 1,
			'post_status'    => array( 'publish' ),
		);

		$bundles = new \WP_Query( wp_parse_args( $args, $default ) );

		return $bundles->found_posts;
	}

	public static function get_bundle_price( $bundle_id ) {
		return get_post_meta( $bundle_id, self::PRICE_META_KEY, true );
	}

	public static function get_bundle_courses( $bundle_id ) {
		return get_post_meta( $bundle_id, self::COURSES_META_KEY, true );
	}

	public static function get_bundle_courses_price( $bundle_id ): float {
		$price   = 0;
		$courses = self::get_bundle_courses( $bundle_id );

		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course_id ) {
				$price += \STM_LMS_Course::get_course_price( $course_id );
			}
		}

		return $price;
	}

	public static function get_bundle_rating( int $bundle_id ): array {
		$rating  = array(
			'count'   => 0,
			'average' => 0,
			'percent' => 0,
		);
		$courses = self::get_bundle_courses( $bundle_id );

		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course_id ) {
				$reviews = get_post_meta( $course_id, 'course_marks', true );

				if ( ! empty( $reviews ) ) {
					$rates = \STM_LMS_Course::course_average_rate( $reviews );
					++$rating['count'];
					$rating['average'] += $rates['average'];
					$rating['percent'] += $rates['percent'];
				}
			}
		}

		return $rating;
	}

	public static function check_bundle_author( int $post_id, int $user_id ): bool {
		$author_id = get_post_field( 'post_author', $post_id );

		return intval( $author_id ) === $user_id;
	}

	public function get_all( array $request = array() ): array {
		$result = array();

		$args = array(
			'post_type'      => 'stm-course-bundles',
			'posts_per_page' => $request['per_page'] ?? 10,
			'post_status'    => 'publish',
			'author'         => '',
		);

		if ( ! empty( $request['bundle_ids'] ) ) {
			$args['post__in'] = explode( ',', $request['bundle_ids'] );
		}

		if ( ! empty( $request['sort'] ) && ! empty( self::SORT_MAPPING[ $request['sort'] ] ) ) {
			$args = array_merge( $args, self::SORT_MAPPING[ $request['sort'] ] );
		}

		$bundles = $this->get_bundles( $args );

		if ( isset( $bundles['posts'] ) && is_array( $bundles['posts'] ) ) {
			foreach ( $bundles['posts'] as $value ) {
				$result[] = array(
					'bundle_info'    => array(
						'id'            => $value['id'],
						'title'         => $value['title'],
						'url'           => get_permalink( $value['id'] ),
						'price'         => \STM_LMS_Helpers::display_price( $this->get_bundle_price( $value['id'] ) ),
						'rating'        => $this->get_bundle_rating( $value['id'] ),
						'courses_price' => \STM_LMS_Helpers::display_price( $this->get_bundle_courses_price( $value['id'] ) ),
					),
					'bundle_courses' => $this->get_courses_data( $value['courses'] ),
				);
			}
		}

		return array(
			'bundles' => apply_filters( 'stm_autocomplete_terms', $result ),
			'total'   => $bundles['total'],
			'pages'   => $bundles['pages'],
		);
	}
}
