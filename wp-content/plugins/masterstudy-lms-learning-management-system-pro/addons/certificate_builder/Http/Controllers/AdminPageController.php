<?php

namespace MasterStudy\Lms\Pro\addons\certificate_builder\Http\Controllers;

use MasterStudy\Lms\Pro\addons\certificate_builder\CertificateRepository;
use MasterStudy\Lms\Repositories\CourseRepository;
use MasterStudy\Lms\Pro\addons\certificate_builder\ImageEncoder;

class AdminPageController {
	public function __invoke(): void {
		$this->enqueue_scripts();

		$translations = array(
			'text'         => esc_html__( 'Text', 'masterstudy-lms-learning-management-system-pro' ),
			'course_name'  => esc_html__( 'Course name', 'masterstudy-lms-learning-management-system-pro' ),
			'student_name' => esc_html__( 'Student name', 'masterstudy-lms-learning-management-system-pro' ),
			'image'        => esc_html__( 'Image', 'masterstudy-lms-learning-management-system-pro' ),
			'author'       => esc_html__( 'Author', 'masterstudy-lms-learning-management-system-pro' ),
		);

		wp_localize_script( 'masterstudy_certificate_builder', 'stm_translations', $translations );

		\STM_LMS_Templates::show_lms_template( 'certificate-builder/main' );
	}

	private function enqueue_scripts(): void {
		wp_enqueue_style( 'masterstudy-loader' );
		wp_enqueue_style(
			'masterstudy_certificate_builder',
			STM_LMS_PRO_URL . '/assets/css/certificate-builder/main.css',
			array(),
			STM_LMS_PRO_VERSION
		);
		wp_enqueue_style(
			'masterstudy_certificate_fonts',
			'https://fonts.googleapis.com/css?family=Katibeh|Amiri|Merriweather:400,700|Montserrat:400,700|Open+Sans:400,700|Oswald:400,700|M+PLUS+2:400,800',
			array(),
			STM_LMS_PRO_VERSION
		);
		wp_enqueue_script(
			'masterstudy_certificate',
			STM_LMS_PRO_URL . '/assets/js/certificate-builder/main.js',
			array(
				'jquery',
				'vue.js',
				'vue-resource.js',
				'jspdf',
				'pdfjs',
				'qrcode',
				'pdfjs_worker',
				'masterstudy_certificate_fonts',
			),
			STM_LMS_PRO_VERSION,
			true
		);
		wp_localize_script(
			'masterstudy_certificate',
			'masterstudy_certificate_data',
			array(
				'is_admin'               => current_user_can( 'administrator' ),
				'not_generated_previews' => get_option( 'stm_lms_certificates_previews_generated', '' ),
				'default_certificate'    => get_option( 'stm_default_certificate', '' ),
				'shapes'                 => ( new CertificateRepository() )->get_shapes(),
				'certificate_page'       => ( new CertificateRepository() )->certificate_page_url(),
				'qr_image_url'           => ImageEncoder::to_base64( STM_LMS_PRO_PATH . '/assets/img/certificate-builder/qrcode.png' ),
			)
		);
		wp_enqueue_script(
			'vue2-color.js',
			STM_LMS_URL . '/nuxy/metaboxes/assets/js/vue-color.min.js',
			array(
				'jquery',
				'vue.js',
				'vue-resource.js',
			),
			STM_LMS_PRO_VERSION,
			true
		);
		wp_enqueue_script(
			'vue-draggable-resizable',
			STM_LMS_PRO_URL . '/assets/js/certificate-builder/VueDraggableResizable.js',
			array(
				'jquery',
				'vue.js',
				'vue-resource.js',
			),
			STM_LMS_PRO_VERSION,
			true
		);
	}
}
