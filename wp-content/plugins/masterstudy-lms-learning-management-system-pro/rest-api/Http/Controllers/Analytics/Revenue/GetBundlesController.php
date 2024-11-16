<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue\BundleSerializer;
use WP_REST_Request;
use WP_REST_Response;

final class GetBundlesController extends Controller {

	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data     = $this->get_validated_data();
		$bundles_provider   = $this->get_checkout_provider()->get_provider( 'bundles' );
		$bundles_repository = ( new $bundles_provider(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 1,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		) );

		// Sort by: revenue, courses, orders, name. Example: sort[name] = asc
		$bundles       = $bundles_repository->get_bundles_data(
			$validated_data['columns'] ?? array(),
			$validated_data['order'] ?? array()
		);
		$total_bundles = $bundles_repository->get_total();

		return new WP_REST_Response(
			array(
				'recordsTotal'    => $total_bundles,
				'recordsFiltered' => $total_bundles,
				'data'            => ( new BundleSerializer() )->collectionToArray( $bundles ),
			)
		);
	}
}
