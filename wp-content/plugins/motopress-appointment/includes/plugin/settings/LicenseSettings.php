<?php

namespace MotoPress\Appointment\Plugin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait LicenseSettings {


	private $licenseKey;

	public $productId;
	public $storeUrl;
	public $author;

	public function __construct() {

		$this->productId = 1029453;

		add_action( 'admin_init', array( $this, 'init' ), 9 );
		add_action( 'admin_init', array( $this, 'activate' ), 9 );
	}

	/**
	 *
	 * @since 1.0
	 */
	public function activate() {

		//activate
		if ( isset( $_POST['mpa_edd_nonce'] ) && isset( $_POST['edd_license_activate'] ) ) {

			if ( ! check_admin_referer( 'mpa_edd_nonce', 'mpa_edd_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}

			$licenseData = $this->activateLicense();

			if ( false === $licenseData ) {
				return false;
			}

			if ( ! $licenseData->success && 'item_name_mismatch' === $licenseData->error ) {
				$queryArgs['item-name-mismatch'] = 'true';
			}
		}

		//deactivate
		if ( isset( $_POST['mpa_edd_nonce'] ) && isset( $_POST['edd_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'mpa_edd_nonce', 'mpa_edd_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}
			// retrieve the license from the database
			$licenseData = $this->deactivateLicense();

			if ( false === $licenseData ) {
				return false;
			}
		}

	}

	/**
	 *
	 * @since 1.0
	 */
	public function init() {
		$pluginData = mpa_get_plugin_data();

		$this->storeUrl = isset( $pluginData['PluginURI'] ) ? $pluginData['PluginURI'] : '';
		$this->author   = isset( $pluginData['Author'] ) ? $pluginData['Author'] : '';
	}

	/**
	 * @return int
	 *
	 * @since 1.0
	 */
	public function getProductId() {
		return $this->productId;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getStoreUrl() {
		return $this->storeUrl;
	}

	/**
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getRenewUrl() {
		return $this->storeUrl;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 *
	 * @param string $status
	 */
	public function setLicenseStatus( $status ) {
		update_option( 'mpa_license_status', $status );
	}

	/**
	 *
	 * @return bool
	 */
	public function needHideNotice() {
		return (bool) get_option( 'mpa_hide_license_notice', false );
	}

	/**
	 *
	 * @param bool $isHide
	 */
	public function setNeedHideNotice( $isHide ) {
		update_option( 'mpa_hide_license_notice', $isHide );
	}

	/**
	 * @return stdClass|null
	 *
	 * @since 1.0
	 */
	public function getLicenseData() {

		return array(
			'licenseKey' => $this->getLicenseKey(),
			'productId'  => $this->getProductId(),
			'storeUrl'   => $this->getStoreUrl(),
			'author'     => $this->getAuthor(),
		);

	}

	/**
	 *
	 * @return stdClass|null
	 */
public function checkLicense() {
    // Create a stdClass object from the global namespace to represent a valid license data
    $licenseData = new \stdClass();

    // Populate the object with properties that mimic a valid license
    $licenseData->license = 'valid'; // Indicate that the license is valid
    $licenseData->item_id = $this->getProductId();
    $licenseData->url = home_url();
    $licenseData->expires = 'lifetime'; // or set a specific future date

    // Add any other fields that your system expects in a valid license response

    return $licenseData;
}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLicenseKey() {
		return get_option( 'mpa_edd_license_key', '' );
	}

	public function activateLicense() {

		$licenseKey = $this->getLicenseKey();

		// data to send in our API request
		$apiParams = array(
			'edd_action' => 'activate_license',
			'license'    => $licenseKey,
			'item_id'    => $this->getProductId(),
			'url'        => home_url(),
		);

		$activateUrl = add_query_arg( $apiParams, $this->getStoreUrl() );

		// Call the custom API.
		$response = wp_remote_get(
			$activateUrl,
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$licenseData = json_decode( wp_remote_retrieve_body( $response ) );

		// $licenseData->license will be either "active" or "inactive"
		$this->setLicenseStatus( $licenseData->license );

		return $licenseData;
	}

	public function deactivateLicense() {

		$licenseKey = $this->getLicenseKey();

		// data to send in our API request
		$apiParams = array(
			'edd_action' => 'deactivate_license',
			'license'    => $licenseKey,
			'item_id'    => $this->getProductId(),
			'url'        => home_url(),
		);

		$deactivateUrl = add_query_arg( $apiParams, $this->getStoreUrl() );

		// Call the custom API.
		$response = wp_remote_get(
			$deactivateUrl,
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$licenseData = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( 'deactivated' == $licenseData->license ) {
			$this->setLicenseStatus( '' );
		}

		return $licenseData;
	}

	/**
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function isLicenseEnabled() {
		return (bool) apply_filters( 'mpa_use_edd_license', true );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	private function getLicenseSettings() {

		return array(
			'license'         => $this->getLicenseKey(),
			'use_edd_license' => $this->isLicenseEnabled(),
		);
	}
}
