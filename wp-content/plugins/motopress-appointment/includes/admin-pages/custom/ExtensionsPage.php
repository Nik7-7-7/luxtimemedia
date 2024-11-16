<?php

namespace MotoPress\Appointment\AdminPages\Custom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.16.0
 */
class ExtensionsPage extends AbstractCustomPage {

	const REQUEST_URL = 'https://motopress.com/edd-api/v2/products/?category=appointment-booking-addons';

	/**
	 * @var array [ [
	 *  'slug' => {string},
	 *  'title' => {string},
	 *  'excerpt' => {string},
	 *  'thumbnail' => {string},
	 *  'link' => {string},
	 * ], ... ]
	 */
	protected $products = array();

	protected function enqueueScripts() {
		mpa_assets()->enqueueStyle( 'mpa-admin' );
	}

	public function load() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		$products = $this->loadProducts();

		if ( false !== $products ) {
			$this->products = $products;
		}
	}

	/**
	 * @access protected
	 */
	public function display() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php esc_html_e( 'Extensions', 'motopress-appointment' ); ?>
			</h1>
			<?php mpa_display_template( 'private/pages/extensions-list.php', array( 'products' => $this->products ) ); ?>
		</div>
		<?php
	}

	/**
	 * @return string
	 */
	protected function getPageTitle() {
		return esc_html__( 'Extensions', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function getMenuTitle() {
		return '<span class="dashicons dashicons-admin-plugins" style="font-size:17px;"></span> ' .
			esc_html__( 'Extensions', 'motopress-appointment' );
	}

	/**
	 * @return array|false
	 */
	protected function loadProducts() {
		$products = get_transient( 'mpa_extensions' );

		if ( false !== $products ) {
			return $products;
		}

		// Request products
		$apiProducts = $this->requestProducts();
		$products    = $this->parseProducts( $apiProducts );

		// Load from reserve option
		if ( false === $products ) {
			$products = get_option( 'mpa_last_known_extensions', false );
		} else {
			update_option( 'mpa_last_known_extensions', $products, 'no' );
		}

		if ( false !== $products ) {
			set_transient( 'mpa_extensions', $products, DAY_IN_SECONDS );
		}

		return $products;
	}

	/**
	 * @return \stdClass[]|false
	 */
	protected function requestProducts() {
		$requestArgs = array(
			'timeout' => 15,
		);

		$request = wp_remote_get( self::REQUEST_URL, $requestArgs );

		if ( is_wp_error( $request ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );
		/** @var \stdClass[]|null */
		$json = json_decode( $body );

		if ( empty( $json ) ) {
			return false;
		}

		return $json->products;
	}

	/**
	 * @param \stdClass[]|false $apiProducts
	 *
	 * @return array|false
	 */
	protected function parseProducts( $apiProducts ) {
		if ( false === $apiProducts ) {
			return false;
		}

		$products = array();

		$pluginProductId = mpapp()->settings()->getProductId();

		foreach ( $apiProducts as $product ) {
			/** @var \stdClass */
			$info = $product->info;

			if ( 'publish' != $info->status || $info->id == $pluginProductId ) {
				continue;
			}

			$products[] = array(
				'slug'      => $info->slug,
				'title'     => $info->title,
				'excerpt'   => $info->excerpt,
				'thumbnail' => $info->thumbnail,
				'link'      => $info->link,
			);
		}

		return $products;
	}
}
