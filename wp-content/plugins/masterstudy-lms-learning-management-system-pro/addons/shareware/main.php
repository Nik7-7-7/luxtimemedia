<?php

use MasterStudy\Lms\Repositories\CurriculumMaterialRepository;

new STM_LMS_Shareware();

class STM_LMS_Shareware {

	public function __construct() {
		add_filter( 'stm_lms_global/price', array( $this, 'global_price' ), 10, 2 );
		add_filter( 'stm_lms_has_course_access', array( $this, 'course_access' ), 10, 3 );
		add_filter( 'wpcfto_options_page_setup', array( $this, 'stm_lms_settings_page' ), 100 );
	}

	public function stm_lms_settings_page( $setups ) {
		$setups[] = array(
			'page'        => array(
				'parent_slug' => 'stm-lms-settings',
				'page_title'  => 'Trial Course Settings',
				'menu_title'  => 'Trial Course Settings',
				'menu_slug'   => 'stm-lms-shareware',
			),
			'fields'      => $this->stm_lms_settings(),
			'option_name' => 'stm_lms_shareware_settings',
		);

		return $setups;
	}

	public function stm_lms_settings() {
		return apply_filters(
			'stm_lms_shareware_settings',
			array(
				'credentials' => array(
					'name'   => esc_html__( 'Credentials', 'masterstudy-lms-learning-management-system-pro' ),
					'fields' => array(
						'shareware_count'       => array(
							'type'        => 'number',
							'label'       => esc_html__( 'Number of free lessons', 'masterstudy-lms-learning-management-system-pro' ),
							'description' => esc_html__( 'Specify the number of lessons that are available for free in trial courses', 'masterstudy-lms-learning-management-system-pro' ),
						),
						'shareware_guest_trial' => array(
							'type'        => 'checkbox',
							'label'       => esc_html__( 'Enable Guest access to trial courses', 'masterstudy-lms-learning-management-system-pro' ),
							'description' => esc_html__( 'Allow access to trial courses without login', 'masterstudy-lms-learning-management-system-pro' ),
							'value'       => true,
						),
					),
				),
			)
		);
	}

	public function is_shareware( $post_id ) {
		$shareware = get_post_meta( $post_id, 'shareware', true );

		return ( 'on' === $shareware );
	}

	public function global_price( $content, $vars ) {
		if ( ! empty( $vars['post_id'] ) ) {
			$course_id = $vars['post_id'];
		}

		if ( ! empty( $vars['id'] ) ) {
			$course_id = $vars['id'];
		}

		if ( ! empty( $course_id ) ) {
			$shareware = self::is_shareware( $course_id );
			if ( $shareware ) {
				return '';
			}
		}

		return $content;
	}

	public function course_access( $access, $course_id, $item_id ) {
		if ( ! empty( $course_id ) ) {
			$shareware_lessons = get_option( 'stm_lms_shareware_settings', array() );
			$shareware_count   = ( ! empty( $shareware_lessons['shareware_count'] ) ) ? intval( $shareware_lessons['shareware_count'] ) : 1;
			$shareware         = self::is_shareware( $course_id );

			if ( $shareware && ! empty( $item_id ) ) {
				$course_materials = ( new CurriculumMaterialRepository() )->get_course_materials( $course_id );

				$item_order = array_search( intval( $item_id ), $course_materials, true );

				if ( isset( $item_order ) && $item_order < $shareware_count ) {
					return true;
				}
			} elseif ( $shareware ) {
				return true;
			}
		}

		return $access;
	}
}
