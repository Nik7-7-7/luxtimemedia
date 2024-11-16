<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Controllers\V1;

use MotoPress\Appointment\Rest\Controllers\AbstractRestObjectController;

class LocationsController extends AbstractRestObjectController {


	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mpa/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'locations';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mpa_location';

}
