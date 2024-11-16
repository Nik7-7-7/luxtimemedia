<?php

namespace MotoPress\Appointment\Shortcodes;

use MotoPress\Appointment\Entities\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerAccountShortcode extends AbstractShortcode {

	/**
	 * @var array
	 */
	protected $currentPageRewriteRules = array();


	/**
	 * @return bool
	 */
	protected function isNeedFlushRewriteRules(): bool {
		global $wp_rewrite;

		$wpRewriteRules = $wp_rewrite->wp_rewrite_rules();

		foreach ( $this->currentPageRewriteRules as $currentPageRewriteRule ) {
			if ( ! isset( $wpRewriteRules[ $currentPageRewriteRule ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $pageSlug
	 * @param string $subpageSlug
	 *
	 * Allow URL:
	 * /{$pageSlug}/{$subpageSlug}/
	 *
	 * for page url:
	 * /{$pageSlug}
	 *
	 * Warning: $subpageSlug is used as a URL pattern. Query variables must be requested with the mpa_ prefix.
	 * e.g.: if $subpageSlug = 'bookings'
	 * then use get_query_var( 'mpa_bookings' ) for getting param value.
	 */
	protected function addRewriteRulesForSubpage( string $pageSlug, string $subpageSlug ) {
		$regex = '^(' . $pageSlug . ')/' . $subpageSlug . '/?$';
		$query = 'index.php?pagename=$matches[1]&' . mpa_prefix( $subpageSlug ) . '=1';
		add_rewrite_rule( $regex, $query, 'top' );

		$this->currentPageRewriteRules[ $regex ] = $query;

		add_filter(
			'query_vars',
			function ( $vars ) use ( $subpageSlug ) {
				$vars[] = mpa_prefix( $subpageSlug );

				return $vars;
			}
		);
	}

	/**
	 * @param string $pageSlug
	 * @param string $subpageSlug
	 *
	 * Allow URL:
	 * /{$pageSlug}/{$subpageSlug}/<id>/
	 *
	 * for page url:
	 * /{$pageSlug}/
	 *
	 * Warning: $subpageSlug is used as a URL pattern. Query variables must be requested with the mpa_ prefix.
	 * e.g.: if $subpageSlug = 'booking'
	 * then use get_query_var( 'mpa_booking' ) for create a condition of determining the current page $subpageSlug
	 * and use get_query_var( 'mpa_booking_id' ) for getting param of entities id.
	 */
	protected function addRewriteRulesForSubpageWithId( string $pageSlug, string $subpageSlug ) {
		$regex = '^(' . $pageSlug . ')/' . $subpageSlug . '/(\d+)/?';

		$subpageSlugVar   = mpa_prefix( $subpageSlug );
		$subpageSlugIdVar = $subpageSlugVar . '_id';

		$query = sprintf(
			'index.php?pagename=$matches[1]&%s=1&%s=$matches[2]',
			$subpageSlugVar, // {$subpageSlug}
			$subpageSlugIdVar // <id>
		);
		add_rewrite_rule( $regex, $query, 'top' );

		$this->currentPageRewriteRules[ $regex ] = $query;

		add_filter(
			'query_vars',
			function ( $vars ) use ( $subpageSlugVar, $subpageSlugIdVar ) {
				$vars[] = $subpageSlugVar;
				$vars[] = $subpageSlugIdVar;

				return $vars;
			}
		);
	}

	protected function getCustomerAccountPageSlug() {
		$customerAccountPageId = mpapp()->settings()->getCustomerAccountPage();

		if ( ! $customerAccountPageId ) {
			return null;
		}

		$customerAccountPage = get_post( $customerAccountPageId );

		if ( null === $customerAccountPage ) {
			return null;
		}

		return $customerAccountPage->post_name;
	}

	/**
	 * Must fire on 'init' hook.
	 *
	 * @return void
	 */
	public function addRewriteRules() {
		$customerAccountPageSlug = $this->getCustomerAccountPageSlug();
		if ( ! $customerAccountPageSlug ) {
			return;
		}

		/**
		 * /page-slug/bookings/
		 * or
		 * /?page_id=<id>&bookings=1
		 */
		$this->addRewriteRulesForSubpage( $customerAccountPageSlug, 'bookings' );

		/**
		 * /page-slug/booking/<id>/
		 * or
		 * /?page_id=<id>&booking=<id>
		 */
		$this->addRewriteRulesForSubpageWithId( $customerAccountPageSlug, 'booking' );

		if ( $this->isNeedFlushRewriteRules() ) {
			flush_rewrite_rules( false );
		}
	}


	public function addActions() {
		parent::addActions();

		add_action( 'init', array( $this, 'addRewriteRules' ) );
		add_action( 'wp_login_failed', array( $this, 'onLoginFailedRedirect' ), 10, 1 );
		add_filter( 'login_form_middle', array( $this, 'onLoginFailedRedirectShowMessage' ), 10, 1 );
	}

	/**
	 * @param string $username
	 *
	 * @access protected
	 */
	public function onLoginFailedRedirect( $username ) {

		$referrer = wp_get_referer();

		if ( false === $referrer ) {
			return;
		}

		$slug = $this->getAccountURL();

		if ( strstr( $referrer, $slug ) ) {
			$redirectTo = add_query_arg( array( 'username' => $username, ), $referrer );
			wp_safe_redirect( $redirectTo );
			exit;
		}
	}

	/**
	 * @param $content
	 *
	 * @return string
	 * @access protected
	 */
	public function onLoginFailedRedirectShowMessage( $content ) {
		$failMessage = sprintf( '<p class="mpa-error">%s</p>',
			esc_html__( 'Invalid Username or Email Address.', 'motopress-appointment' )
		);

		if ( isset( $_GET['username'] ) ) {
			$content .= $failMessage;
		}

		return $content;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return mpa_prefix( 'customer_account' );
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return esc_html__( 'Customer Account', 'motopress-appointment' );
	}

	protected function isBookingsTab(): bool {
		return (bool) get_query_var( 'mpa_bookings' );
	}

	protected function isBookingByIdTab(): bool {
		return (bool) get_query_var( 'mpa_booking' );
	}


	/**
	 * @param string $endpoint
	 * @param string $value
	 *
	 * @return string
	 *
	 * @todo: need make better
	 */
	protected function getURL( string $endpoint, string $value ) {
		$permalink = get_permalink();

		if ( ! $endpoint ) {
			return $permalink;
		}

		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
				$permalink    = current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}
			$url = trailingslashit( $permalink );

			if ( $value ) {
				$url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
			} else {
				$url .= user_trailingslashit( $endpoint );
			}

			$url .= $query_string;
		} else {
			switch ( $endpoint ) {
				case 'booking':
					$queryArgs = array(
						'mpa_booking'    => 1,
						'mpa_booking_id' => $value,
					);
					break;
				case 'bookings':
					$queryArgs = array(
						'mpa_bookings' => 1,
					);
					break;
				default:
					// todo: Need to check and improve
					if ( ! $value ) {
						$value = '1';
					}

					$queryArgs = array(
						mpa_prefix( $endpoint ) => $value,
					);
			}

			$url = add_query_arg( $queryArgs, $permalink );
		}

		return $url;
	}

	public function getAccountURL(): string {
		return $this->getURL( '', '' );
	}

	/**
	 * @param $bookingId
	 *
	 * @return string
	 */
	public function getBookingURL( $bookingId ): string {

		return $this->getURL( 'booking', $bookingId );
	}

	/**
	 * @return string
	 */
	public function getBookingsURL(): string {

		return $this->getURL( 'bookings', '' );
	}

	protected function getBookingsContent( $customerId ): string {
		$args     = array(
			'order' => 'DESC',
		);
		$bookings = mpapp()->repositories()->booking()->findAllByMeta( '_mpa_customer_id', $customerId, '=', $args );

		return mpa_render_template( 'shortcodes/customer-account/bookings.php', array( 'bookings' => $bookings ) );
	}

	protected function getBookingContent( $customerId ): string {
		$bookingId = get_query_var( 'mpa_booking_id' );
		$booking   = mpapp()->repositories()->booking()->findById( $bookingId );

		if ( ! $booking || ( $booking->getCustomerId() !== $customerId ) ) {
			return $this->getPermissionDeniedContent();
		}

		return mpa_render_template(
			'shortcodes/customer-account/booking.php',
			array(
				'booking' => $booking,
			)
		);
	}

	protected function getAccountContent( Customer $customer ): string {
		return mpa_render_template(
			'shortcodes/customer-account/account.php',
			array(
				'customerName'  => $customer->getName(),
				'customerEmail' => $customer->getEmail(),
				'customerPhone' => $customer->getPhone(),
				'totalBookings' => mpapp()->repositories()->customer()->getTotalBookingsOfCustomer( $customer->getId() ),
				'bookingsURL'   => $this->getBookingsURL(),
			)
		);
	}

	protected function getMenuContent(): string {
		$accountURL = $this->getAccountURL();
		$args       = array(
			'customerAccountURL' => esc_url( $accountURL ),
			'bookingsURL'        => esc_url( $this->getBookingsURL() ),
			'logoutURL'          => esc_url( wp_logout_url( $accountURL ) ),
		);

		return mpa_render_template( 'shortcodes/customer-account/menu.php', $args );
	}

	protected function getPermissionDeniedContent(): string {
		return sprintf(
			'<p>%s</p>',
			esc_html__( 'You do not have permission to do this action.', 'motopress-appointment' )
		);
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 *
	 * @return string
	 */
	public function renderContent( $args, $content, $shortcodeTag ): string {

		mpa_assets()->enqueueBundle( 'mpa-public' );

		$user = wp_get_current_user();

		if ( ! $user->ID ) {
			return mpa_render_template( 'shortcodes/customer-account/login-form.php', $args );
		}

		$customer = mpapp()->repositories()->customer()->findByUserId( $user->ID );

		if ( is_null( $customer ) ) {
			return $this->getPermissionDeniedContent();
		}

		$customerId = $customer->getId();

		$output = '';
		$output .= $this->getMenuContent();

		switch ( true ) {
			case $this->isBookingsTab() :
				$output .= $this->getBookingsContent( $customerId );
				break;
			case $this->isBookingByIdTab() :
				$output .= $this->getBookingContent( $customerId );
				break;
			default:
				$output .= $this->getAccountContent( $customer );
		}

		return $output;
	}
}