<?php

namespace MotoPress\Appointment\AdminPages\Custom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractCustomPage {

	/**
	 * @var string 'admin_page'
	 *
	 * @since 1.0
	 */
	protected $name;

	/**
	 * @var string 'mpa_admin_page
	 *
	 * @since 1.0
	 */
	protected $id;

	/**
	 * @var string 'toplevel_page_mpa_admin_page'
	 *
	 * @since 1.0
	 */
	protected $screenId;

	/**
	 * @var string Use '' to create a top-level menu and 'none' to create a
	 *     hidden page.
	 *
	 * @since 1.0
	 */
	protected $parentMenu;

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $pageTitle;

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $menuTitle;

	/**
	 * @var string 'dashicons-calendar'
	 *
	 * @see https://developer.wordpress.org/resource/dashicons/
	 *
	 * @since 1.0
	 */
	protected $menuIcon;

	/**
	 * @var int
	 *
	 * @since 1.0
	 */
	protected $position;

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $capability;

	/**
	 * @param string $name
	 * @param array $args Optional.
	 *
	 * @since 1.0
	 *
	 * @todo Add parent menu slug.
	 */
	public function __construct( $name, $args = array() ) {
		$this->name = $name;
		$this->id   = mpa_prefix( $name );

		$args = array_merge( $this->getDefaults(), $args );

		$this->parentMenu = $args['parent_menu'];
		$this->pageTitle  = $args['page_title'];
		$this->menuTitle  = $args['menu_title'];
		$this->menuIcon   = $args['menu_icon'];
		$this->position   = $args['position'];
		$this->capability = $args['capability'];

		$this->addActions();
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function getDefaults() {
		return array(
			'parent_menu' => 'none',
			'page_title'  => $this->getPageTitle(),
			'menu_title'  => $this->getMenuTitle(),
			'menu_icon'   => '',
			'position'    => 50,
			'capability'  => 'manage_options',
		);
	}

	/**
	 * @since 1.0
	 */
	public function addActions() {
		add_action( 'admin_menu', array( $this, 'addMenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybeEnqueueScripts' ) );
	}

	/**
	 * @access protected
	 *
	 * @since 1.1.0
	 */
	public function maybeEnqueueScripts() {
		if ( $this->isCurrentPage() ) {
			$this->enqueueScripts();
		}
	}

	/**
	 * @since 1.1.0
	 */
	protected function enqueueScripts() {}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function addMenu() {
		if ( '' === $this->parentMenu ) {
			$this->screenId = add_menu_page(
				$this->pageTitle,
				$this->menuTitle,
				$this->capability,
				$this->id,
				array( $this, 'display' ),
				$this->menuIcon,
				$this->position
			);
		} else {
			$this->screenId = add_submenu_page(
				$this->parentMenu,
				$this->pageTitle,
				$this->menuTitle,
				$this->capability,
				$this->id,
				array( $this, 'display' ),
				$this->position
			);
		}

		add_action( "load-{$this->screenId}", array( $this, 'load' ) );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function load() {}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	abstract public function display();

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract protected function getPageTitle();

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract protected function getMenuTitle();

	/**
	 * @param array $additionalArgs Optional.
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getUrl( $additionalArgs = array() ) {
		$args = array_merge(
			array(
				'page' => $this->id,
			),
			$additionalArgs
		);

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * <b>Note:</b> use the method after 'admin_init' hook ('current_screen' for
	 * example).
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isCurrentPage() {
		if ( ! is_admin() ) {
			return false;
		}

		$currentScreen = get_current_screen();

		return $currentScreen->id === $this->screenId;
	}

	/**
	 * @param string $name
	 * @return mixed|null
	 *
	 * @since 1.0
	 */
	public function __get( $name ) {
		return isset( $this->$name ) ? $this->$name : null;
	}
}
