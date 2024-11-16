<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue\GroupSerializer;
use WP_REST_Request;
use WP_REST_Response;

final class GetGroupsController extends Controller {

	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data    = $this->get_validated_data();
		$groups_provider   = $this->get_checkout_provider()->get_provider( 'groups' );
		$groups_repository = ( new $groups_provider(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 1,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		) );

		// Sort by: revenue, students, courses, orders, name. Example: sort[name] = asc
		$groups       = $groups_repository->get_groups_data(
			$validated_data['columns'] ?? array(),
			$validated_data['order'] ?? array()
		);
		$total_groups = $groups_repository->get_total( array( 'publish', 'draft' ) );

		return new WP_REST_Response(
			array(
				'recordsTotal'    => $total_groups,
				'recordsFiltered' => $total_groups,
				'data'            => ( new GroupSerializer() )->collectionToArray( $groups ),
			)
		);
	}
}
