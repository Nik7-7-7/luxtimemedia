<?php
/**
 * If direct access than exit the file.
 *
 * @package JITSI_MEET_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jitsi_Settings class
 */
class Jitsi_Settings {
	/**
	 * Settings
	 *
	 * @var array
	 */
	public $settings = [];
	/**
	 * Sections
	 *
	 * @var array
	 */
	public $sections = [];
	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [];

	/**
	 * Jitsi_Settings constructor.
	 * Load admin site action and filter hook
	 *
	 * @return  void
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_custom_fields' ] );
	}

	/**
	 * Set Settings description
	 *
	 * @param array $settings  settings array.
	 *
	 * @return  object
	 */
	public function set_settings( array $settings ) {
		$this->settings = $settings;
		return $this;
	}

	/**
	 * Set Sections
	 *
	 * @param array $sections  sections array.
	 *
	 * @return  object
	 */
	public function set_sections( array $sections ) {
		$this->sections = $sections;
		return $this;
	}

	/**
	 * Set Fields
	 *
	 * @param  array $fields  fields array.
	 *
	 * @return  object
	 */
	public function set_fields( array $fields ) {
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Register Custom Fields
	 *
	 * @return  void
	 */
	public function register_custom_fields() {
		// Register Settings.
		foreach ( $this->settings as $setting ) {
			register_setting( $setting['option_group'], $setting['option_name'], ( isset( $setting['callback'] ) ? $setting['callback'] : '' ) );
		}

		// Register Setting Section.
		foreach ( $this->sections as $section ) {
			add_settings_section( $section['id'], $section['title'], ( isset( $section['callback'] ) ? $section['callback'] : '' ), $section['page'] );
		}

		// Settings Field.
		foreach ( $this->fields as $field ) {
			add_settings_field( $field['id'], $field['title'], ( isset( $field['callback'] ) ? $field['callback'] : '' ), $field['page'], $field['section'], ( isset( $field['args'] ) ? $field['args'] : '' ) );
		}
	}
}
