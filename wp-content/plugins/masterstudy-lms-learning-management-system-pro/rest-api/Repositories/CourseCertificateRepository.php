<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

use MasterStudy\Lms\Plugin\Taxonomy;
use MasterStudy\Lms\Pro\addons\certificate_builder\CertificateRepository;

final class CourseCertificateRepository {
	public array $course_categories = array();

	public function certificate_allowed( int $course_id ): bool {
		$course_certificate = get_post_meta( $course_id, 'course_certificate', true );

		if ( empty( $course_certificate ) ) {
			$course_categories  = $this->get_course_categories( $course_id );
			$course_certificate = ( new CertificateRepository() )->get_first_for_categories( $course_categories );
		}

		return ! empty( $course_certificate );
	}

	public function get_course_categories( int $course_id ): array {
		if ( ! isset( $this->course_categories[ $course_id ] ) ) {
			$this->course_categories[ $course_id ] = wp_get_post_terms( $course_id, Taxonomy::COURSE_CATEGORY, array( 'fields' => 'ids' ) );
		}

		return $this->course_categories[ $course_id ];
	}
}
