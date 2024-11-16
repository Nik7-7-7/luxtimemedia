<?php

namespace MasterStudy\Lms\Pro\RestApi\Traits;

use MasterStudy\Lms\Validation\Validator;
use MasterStudy\Lms\Http\WpResponseFactory;
use WP_REST_Request;

trait AnalyticsValidator {
	protected $validated_data = array();

	protected function validate( WP_REST_Request $request, array $extra_rules = array() ) {
		$rules = array_merge(
			array(
				'date_from' => 'required|date',
				'date_to'   => 'required|date',
			),
			$extra_rules
		);

		$validator = new Validator( $request->get_params(), $rules );

		if ( $validator->fails() ) {
			return WpResponseFactory::validation_failed( $validator->get_errors_array() );
		}

		$this->validated_data = $validator->get_validated();

		return null;
	}

	protected function validate_datatable( WP_REST_Request $request, array $extra_rules = array() ) {
		$rules = array_merge(
			array(
				'start'   => 'required|integer',
				'length'  => 'required|integer',
				'order'   => 'array',
				'columns' => 'array',
				'search'  => 'array',
			),
			$extra_rules
		);

		return $this->validate( $request, $rules );
	}

	public function get_validated_data() {
		return $this->validated_data;
	}

	public function get_validated_field( $field ) {
		return $this->validated_data[ $field ] ?? null;
	}

	public function get_date_from() {
		return $this->validated_data['date_from'] ?? null;
	}

	public function get_date_to() {
		return $this->validated_data['date_to'] ?? null;
	}
}
