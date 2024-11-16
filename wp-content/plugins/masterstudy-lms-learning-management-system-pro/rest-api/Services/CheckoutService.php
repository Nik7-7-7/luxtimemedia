<?php

namespace MasterStudy\Lms\Pro\RestApi\Services;

use MasterStudy\Lms\Pro\RestApi\Interfaces\ProviderInterface;

class CheckoutService {
	protected $providers = array();

	public function __construct( ProviderInterface $checkout ) {
		$this->providers = apply_filters( 'masterstudy_lms_checkout_providers', $checkout->get_providers() );
	}

	public function get_provider( string $key ) {
		if ( ! isset( $this->providers[ $key ] ) ) {
			throw new \Exception( "Checkout Provider for {$key} not found!" );
		}

		return $this->providers[ $key ];
	}
}
