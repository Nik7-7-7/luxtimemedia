<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\Bundles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class BundlesRegistry {

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $bundles = array();

	/**
	 * @return Bundles\AssetsBundle
	 *
	 * @since 1.0
	 */
	public function assets() {
		if ( ! isset( $this->bundles['assets'] ) ) {
			$this->bundles['assets'] = new Bundles\AssetsBundle();
		}

		return $this->bundles['assets'];
	}

	/**
	 * @return Bundles\CountriesBundle
	 *
	 * @since 1.0
	 */
	public function countries() {
		if ( ! isset( $this->bundles['countries'] ) ) {
			$this->bundles['countries'] = new Bundles\CountriesBundle();
		}

		return $this->bundles['countries'];
	}

	/**
	 * @return Bundles\CurrenciesBundle
	 *
	 * @since 1.0
	 */
	public function currencies() {
		if ( ! isset( $this->bundles['currencies'] ) ) {
			$this->bundles['currencies'] = new Bundles\CurrenciesBundle();
		}

		return $this->bundles['currencies'];
	}

	/**
	 * @return Bundles\SettingsBundle
	 *
	 * @since 1.0
	 */
	public function settings() {
		if ( ! isset( $this->bundles['settings'] ) ) {
			$this->bundles['settings'] = new Bundles\SettingsBundle();
		}

		return $this->bundles['settings'];
	}
}
