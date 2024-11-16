<?php
/**
 * Automatic.css Settings_Page UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Settings_Page;

use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Helpers\Locale;
use Automatic_CSS\Traits\Singleton;

/**
 * Settings_Page UI class.
 */
class Settings_Page {

	use Singleton;

	/**
	 * Capability needed to operate the plugin
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * Initialize the Settings_Page class
	 *
	 * @return Settings_Page
	 */
	public function init() {
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 500 );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_filter( 'automaticcss_admin_stylesheets', array( $this, 'enqueue_admin_styles' ) );
			add_filter( 'automaticcss_admin_scripts', array( $this, 'enqueue_admin_scripts' ) );
		}
		return $this;
	}

	/**
	 * Render the plugin's settings page
	 *
	 * @return void
	 */
	public function render() {
		Locale::fix_locale();
		$tab_get = filter_input( INPUT_GET, 'tab' );
		$tab = null === $tab_get ? false : sanitize_text_field( $tab_get );
		?>
		<div class="wrap acss-wrapper">
			<h1>Welcome to the ACSS settings page</h1>

			<nav class="nav-tab-wrapper">
				<a href="?page=automatic-css&tab=welcome" class="nav-tab<?php echo ( false === $tab || 'welcome' === $tab ) ? ' nav-tab-active' : ''; ?>">Welcome</a>
				<a href="?page=automatic-css&tab=license" class="nav-tab<?php echo 'license' === $tab ? ' nav-tab-active' : ''; ?>">License</a>
				<a href="?page=automatic-css&tab=import-export" class="nav-tab<?php echo 'import-export' === $tab ? ' nav-tab-active' : ''; ?>">Import & Export</a>
				<a href="?page=automatic-css&tab=support" class="nav-tab<?php echo 'support' === $tab ? ' nav-tab-active' : ''; ?>">Support</a>
				<a href="?page=automatic-css&tab=dashboard" class="nav-tab<?php echo ( false === $tab || 'dashboard' === $tab ) ? ' nav-tab-active' : ''; ?>">Dashboard</a>
			</nav>

			<div class="tab-content">
		<?php
		switch ( $tab ) :
			case 'license':
				$plugin_updater = Plugin_Updater::get_instance();
				$plugin_updater->settings_page();
				break;
			case 'import-export':
				Import_Export::settings_page();
				break;
			case 'dashboard':
				Dashboard::settings_page();
				break;
			case 'support':
				Support::settings_page();
				break;
			case 'welcome':
			default:
				Welcome::settings_page();
				break;
			endswitch;
		?>
			</div>
		</div> <!-- .acss-wrapper -->
		<?php
		Locale::restore_locale();
	}

	/**
	 * Enqueue admin styles
	 *
	 * @param array $styles The existing styles.
	 * @return array
	 */
	public function enqueue_admin_styles( $styles ) {
		$styles['automaticcss-admin'] = array(
			'url'   => ACSS_CLASSES_URL . '/UI/Settings_Page/css/acss-settings-page.css',
			'version' => filemtime( ACSS_CLASSES_DIR . '/UI/Settings_Page/css/acss-settings-page.css' ),
			'hook'       => array( 'toplevel_page_automatic-css' ),
		);
		return $styles;
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param array $scripts The existing scripts.
	 * @return array
	 */
	public function enqueue_admin_scripts( $scripts ) {
		return $scripts;
	}

	/**
	 * Add admin bar item
	 *
	 * @param \WP_Admin_Bar $admin_bar The Admin Bar object.
	 * @return void
	 */
	public function add_admin_bar_item( \WP_Admin_Bar $admin_bar ) {
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}
		$model = Database_Settings::get_instance();
		$admin_bar_option = $model->get_var( 'admin-bar-enabled' );
		$admin_bar_enabled = null === $admin_bar_option ? true : ( 'on' === $admin_bar_option ? true : false );
		if ( ! $admin_bar_enabled ) {
			return;
		}
		$admin_bar_url = is_admin() ? home_url( '/?acssOpenDashboard=1' ) : admin_url( 'admin.php?page=automatic-css' );
		$admin_bar->add_menu(
			array(
				'id'    => 'automatic-css-admin-bar',
				'parent' => null,
				'group'  => null,
				'title' => 'Automatic.css', // you can use img tag with image link. it will show the image icon Instead of the title.
				'href'  => $admin_bar_url
			)
		);
	}

	/**
	 * Add the plugin's settings page to the menu
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		$model = Database_Settings::get_instance();
		$admin_position_option = $model->get_var( 'admin-link-position' );
		$admin_position = null === $admin_position_option ? 90 : $admin_position_option;
		add_menu_page(
			'Automatic CSS', // page_title.
			'Automatic CSS', // menu_title.
			$this->capability, // capability.
			'automatic-css', // menu_slug.
			array( $this, 'render' ), // function.
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA5NC4wNSA3MS40MyI+CiAgPGRlZnM+CiAgICA8c3R5bGU+LmNscy0xe2ZpbGw6I2ZmZjt9PC9zdHlsZT4KICA8L2RlZnM+CiAgPGcgaWQ9IkxheWVyXzIiIGRhdGEtbmFtZT0iTGF5ZXIgMiI+CiAgICA8ZyBpZD0iTGF5ZXJfMS0yIiBkYXRhLW5hbWU9IkxheWVyIDEiPgogICAgICA8cG9seWdvbiBjbGFzcz0iY2xzLTEiIHBvaW50cz0iOTQuMDUgNDguNjMgMTkuNTcgNTQuMiA0MS4yNCAxNi42NiA1OS4yMiA0Ny44IDY4LjQ0IDQ3LjExIDQxLjI0IDAgMCA3MS40MyA2My45MiA1NS45NCA2Ny43OCA2Mi42NiAzMy4wNyA3MS40MyA4Mi40OCA3MS40MyA3Mi4zNiA1My45IDk0LjA1IDQ4LjYzIj48L3BvbHlnb24+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4K', // icon_url.
			$admin_position // position.
		);
	}

}
