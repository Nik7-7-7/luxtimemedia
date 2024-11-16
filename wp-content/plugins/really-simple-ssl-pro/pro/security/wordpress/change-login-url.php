<?php defined( 'ABSPATH' ) or die();
class rsssl_change_login_url {

	private $wp_login_php;

	function __construct() {

		if ( ! rsssl_get_option('change_login_url') || rsssl_get_option('change_login_url') === '' || rsssl_get_option('change_login_url_enabled') != '1' ) {
			return;
		}

		// postpass is specific to post/page password forms, therefore the login url cannot be bypassed by adding these params to an URL
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'postpass' ) {
			return;
		}

		//when saving a password for a password protected page, inline, we need to skip to prevent infinite loops.
		if ( current_user_can('edit_posts') && isset( $_POST['action'] ) && $_POST['action']==='inline-save' ) {
			return;
		}

		//send login mail if user uses this parameter
		if ( isset( $_GET['rssslgetlogin'] ) ) {
			require_once( rsssl_path . 'mailer/class-mail.php');
			$mailer = new rsssl_mailer();
			$url = trailingslashit( site_url() ) . ( ! empty( rsssl_get_option( 'change_login_url' ) ) ? rsssl_get_option( 'change_login_url' ) : wp_login_url() );
			$mailer->warning_blocks[] = [
				'title'   => __("Login URL request", "really-simple-ssl"),
				'message' =>sprintf(__("You have requested the login URL for your website. You can log in on %s.", "really-simple-ssl"), '<a href="'.$url.'" target="_blank">'.$url.'</a>' ),
				'url'     => 'https://really-simple-ssl.com/instructions/login-url-changed',
			];
			@$mailer->send_mail();
		}

		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 9999 );
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'redirect_export_data' ) );
		add_filter( 'login_url', array( $this, 'login_url' ), 10, 3 );
		add_action( 'setup_theme', array( $this, 'prevent_customizer_access_not_logged_in' ), 1 );

	}

	/**
	 * initialize the template loader
	 *
	 * @return void
	 */

	private function wp_template_loader() {
		global $pagenow;
		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true );
		}

		wp();
		require_once( ABSPATH . WPINC . '/template-loader.php' );
		die;
	}

	/**
	 * Override the login slug
	 *
	 * @return string
	 */
	private function new_login_slug(): string {
		return ! empty( rsssl_get_option('change_login_url') ) ? rsssl_get_option('change_login_url') : 'wplogin';
	}

	/**
	 * @return void
	 *
	 * Prevent access to the customizer when not logged in
	 */
	public function prevent_customizer_access_not_logged_in() {

		global $pagenow;

		if ( ! is_user_logged_in() && 'customize.php' === $pagenow ) {
			$this->redirect_to_404();
		}
	}

	/**
	 * Redirect to 404
	 * @return void
	 */
	public function redirect_to_404() {

		global $wp;

		$current_url = '';

		if ( isset( $wp->request ) ) {
			$current_url = home_url( add_query_arg( array(), $wp->request ) );
		}

		// Prevent redirect if already on the target URL
		$error_page_url = site_url() . '/' . '404';
		if ( $current_url == $error_page_url ) {
			return;
		}

		// If empty, use default
		if ( empty( rsssl_get_option( 'change_login_url_failure_url' ) )
		     || rsssl_get_option( 'change_login_url_failure_url' ) === '404'
		     || rsssl_get_option( 'change_login_url_failure_url' ) === '404_default' ) {
			wp_safe_redirect( site_url() . '/' . '404' );
		} else {
			// get post for ID
			$post_id  = rsssl_get_option( 'change_login_url_failure_url' );
			$post_url = get_permalink( $post_id );
			if ( $post_id === '404' ) {
				wp_safe_redirect( site_url() . '/' . '404' );
			} else {
				wp_safe_redirect( $post_url );
			}
		}
		die();
	}

	/**
	 * Get new login URL
	 *
	 * @return string
	 */
	public function new_login_url(): string {

		$url = home_url();
		if ( get_option( 'permalink_structure' ) ) {
			return trailingslashit( $url ) . $this->new_login_slug();
		}

		return $url . '?' . $this->new_login_slug();
	}

	public function redirect_export_data() {
		if ( ! empty( $_GET ) && isset( $_GET['action'] ) && 'confirmaction' === $_GET['action'] && isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
			$request_id = (int) $_GET['request_id'];
			$key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
			$result     = wp_validate_user_request_key( $request_id, $key );
			if ( ! is_wp_error( $result ) ) {
				wp_redirect( add_query_arg( array(
					'action'      => 'confirmaction',
					'request_id'  => $request_id,
					'confirm_key' => $key,
				), $this->new_login_url()
				) );
				exit();
			}
		}
	}

	public function plugins_loaded() {

		global $pagenow;

		if ( $pagenow === 'wp-login.php' ) {
			$this->redirect_to_404();
		}

		if ( ! is_multisite()
		     && ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-signup' ) !== false
		          || strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-activate' ) !== false ) ) {

			wp_die( __( 'This feature is not enabled.', 'really-simple-ssl' ) );
		}

		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );
		if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
		       || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
		     && ! is_admin() ) {
			$this->wp_login_php = true;

			$pagenow = 'index.php';
		} elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' ) )
		           || ( ! get_option( 'permalink_structure' )
		                && isset( $_GET[ $this->new_login_slug() ] )
		                && empty( $_GET[ $this->new_login_slug() ] ) ) ) {

			$pagenow = 'wp-login.php';
		} elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
		             || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
		           && ! is_admin() ) {

			$this->wp_login_php = true;

			$pagenow = 'index.php';
		}
	}

	/**
	 *
	 * @return void
	 */

	public function wp_loaded() {

		global $pagenow;
		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

		if ( $pagenow === 'wp-login.php' ) {
			// These globals should be available on wp-login.php. Do not remove
			global $error, $interim_login, $action, $user_login;

			$redirect_to = admin_url();
			$requested_redirect_to = '';
			if ( isset( $_REQUEST['redirect_to'] ) ) {
				$requested_redirect_to = $_REQUEST['redirect_to'];
			}

			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( ! isset( $_REQUEST['action'] ) ) {
					wp_safe_redirect( $redirect_to );
					die();
				}
			}

			@require_once ABSPATH . 'wp-login.php';
			die;
		}

		if ( ! ( isset( $_GET['action'] ) && $_GET['action'] === 'postpass' && isset( $_POST['post_password'] ) ) ) {

			if ( is_admin() && ! is_user_logged_in() && ! defined( 'WP_CLI' ) && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' ) && $pagenow !== 'admin-post.php'
			) {
				$this->redirect_to_404();
			}

			if ( ! is_user_logged_in() && isset( $request['path'] ) && $request['path'] === '/wp-admin/options.php' ) {
				$this->redirect_to_404();
			}

			if ( $pagenow === 'wp-login.php' && isset( $request['path'] ) && $request['path'] !== $this->user_trailingslashit( $request['path'] ) && get_option( 'permalink_structure' ) ) {
				wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
				die();
			}

			if ( $this->wp_login_php ) {
				$wp_referer = wp_get_referer();
				$referer = parse_url( $wp_referer );
				if (  strpos( $wp_referer, 'wp-activate.php' ) !== false && ! empty( $referer['query'] ) ) {
					parse_str( $referer['query'], $referer );

					@require_once WPINC . '/ms-functions.php';
					if ( ! empty( $referer['key'] )
					     && ( $result = wpmu_activate_signup( $referer['key'] ) )
					     && is_wp_error( $result )
					     && ( $result->get_error_code() === 'already_active'
					          || $result->get_error_code() === 'blog_taken' ) ) {
						wp_safe_redirect( $this->new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
						die;
					}
				}

				$this->wp_template_loader();

			}
		}
	}

	/**
	 * @param string   $url
	 * @param string   $path
	 * @param          $blog_id
	 *
	 * @return string
	 */
	public function site_url( string $url, $path, $blog_id ): string {
		return $this->filter_wp_login_php( $url );
	}

	/**
	 * @param string $url
	 * @param string $path
	 *
	 * @return string
	 */
	public function network_site_url( $url, $path ): string {
		return $this->filter_wp_login_php( $url );
	}

	/**
	 * @param string $location
	 * @param string $status
	 *
	 * @return string
	 */
	public function wp_redirect( string $location, string $status ): string {
		if ( strpos( $location, 'https://wordpress.com/wp-login.php' ) !== false ) {
			return $location;
		}

		return $this->filter_wp_login_php( $location );
	}

	private function use_trailing_slashes() {
		return ( '/' === substr( get_option( 'permalink_structure' ), - 1, 1 ) );
	}

	private function user_trailingslashit( $string ) {
		return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
	}

	/**
	 * Adjust the login URL if necessary
	 *
	 * @param string $url
	 *
	 * @return string
	 */

	public function filter_wp_login_php( string $url ): string {

		global $pagenow;

		$origin_url = $url;

		//don't change login url if we're posting the password
		if ( strpos( $url, 'wp-login.php?action=postpass' ) !== false ) {
			return $url;
		}

		if ( is_multisite() && 'install.php' === $pagenow ) {
			return $url;
		}

		if ( isset( $_POST['post_password'] ) ) {
			global $current_user;
			if ( is_wp_error( wp_authenticate_username_password( null, $current_user->user_login, $_POST['post_password'] ) ) ) {
				return $origin_url;
			}
		}

		if ( strpos( $url, 'wp-login.php' ) !== false && strpos( wp_get_referer(), 'wp-login.php' ) === false ) {
			$args = explode( '?', $url );
			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $args );
				if ( isset( $args['login'] ) ) {
					$args['login'] = rawurlencode( $args['login'] );
				}
				$url = add_query_arg( $args, $this->new_login_url() );
			} else {
				$url = $this->new_login_url();
			}
		}


		if ( ! is_user_logged_in() ) {
			if ( (  is_plugin_active( 'gravityforms/gravityforms.php' ) || ( is_multisite() && is_plugin_active_for_network( 'gravityforms/gravityforms.php' ) ) ) && isset( $_GET['gf_page'] ) ) {
				return $origin_url;
			}
		}

		return $url;
	}

	/**
	 *
	 * Update url redirect : wp-admin/options.php
	 *
	 * @param $login_url
	 * @param $redirect
	 * @param $force_reauth
	 *
	 * @return string
	 */
	public function login_url( $login_url, $redirect, $force_reauth ) {

		if ( is_404() ) {
			$this->redirect_to_404();
		}

		if ( $force_reauth === false ) {
			return $login_url;
		}

		if ( empty( $redirect ) ) {
			return $login_url;
		}

		$redirect = explode( '?', $redirect );

		if ( $redirect[0] === admin_url( 'options.php' ) ) {
			$login_url = admin_url();
		}

		return $login_url;
	}
}

if ( !defined('RSSSL_DISABLE_CHANGE_LOGIN_URL') ){
	new rsssl_change_login_url();
}

/**
 * Validate login url
 * @return void
 */
function rsssl_sanitize_login_url( $field_value, $field_id, $field_type ) {
	if ( $field_id==='change_login_url' ) {
		$field_value = preg_replace('/[^a-zA-Z0-9\/_-]/i', '', $field_value);
	}
	return $field_value;
}
add_action( "rsssl_fieldvalue", 'rsssl_sanitize_login_url', 100, 4 );