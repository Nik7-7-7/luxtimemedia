<?php

namespace MotoPress\Appointment\Shortcodes;

use MotoPress\Appointment\Utils\ParseUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractShortcode {

	/**
	 * @var string 'mpa_shortcode'
	 *
	 * @since 1.0
	 */
	protected $name;

	/**
	 * @var array [Name => [type, default]]
	 *
	 * @since 1.2
	 */
	protected $attributes = array();

	/**
	 * @since 1.0
	 */
	public function __construct() {
		$this->name       = $this->getName();
		$this->attributes = $this->getAttributes();

		$this->addActions();
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract public function getName();

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	abstract public function getLabel();

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		return array(
			'html_id'    => array(
				// Only "type" and "default" are required. "description" and
				// "default_label" used on the Help page (shortcodes list)
				'type'        => 'string',
				'description' => mpa_kses_link( __( 'HTML Anchor. Anchors lets you link directly to a section on a page. <a href="https://wordpress.org/support/article/page-jumps/" target="_blank">Learn more about anchors.</a>', 'motopress-appointment' ) ),
				'default'     => '',
			),
			'html_class' => array(
				'type'        => 'string',
				'description' => esc_html__( 'Additional CSS Class(es). Separate multiple classes with spaces.', 'motopress-appointment' ),
				'default'     => '',
			),
		);
	}

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		add_action( 'init', array( $this, 'register' ), 5 );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function register() {
		add_shortcode( $this->name, array( $this, 'render' ) );
	}

	/**
	 * @param array $args
	 * @param string $content Optional. '' by default.
	 * @param string $shortcodeTag Optional. '' by default.
	 * @return string
	 *
	 * @since 1.0
	 */
	public function render( $args, $content = '', $shortcodeTag = '' ) {
		if ( empty( $shortcodeTag ) ) {
			$shortcodeTag = $this->name;
		}

		$shortName = mpa_unprefix( $shortcodeTag );

		// Parse args
		$shortcodeArgs = $this->parseArgs( $args, $this->getDefaultArgs(), $content, $shortcodeTag );

		// Filter wrapper atts
		$wrapperId = $shortcodeArgs['html_id'];

		$wrapperClass = 'mpa-shortcode ' . mpa_tmpl_id( "{$this->name}-shortcode" ); // 'mpa-abstract-shortcode'
		$wrapperClass = rtrim( $wrapperClass . ' ' . $shortcodeArgs['html_class'] );

		/**
		 * @param string Current wrapper class.
		 * @param array Shortcode args.
		 *
		 * @since 1.0
		 * @since 1.2 added second parameter.
		 */
		$wrapperClass = apply_filters( "{$this->name}_shortcode_wrapper_class", $wrapperClass, $shortcodeArgs );

		// Render shortcode
		ob_start();

		echo '<div' . mpa_tmpl_atts(
			array(
				'id'    => $wrapperId,
				'class' => $wrapperClass,
			),
			false
		) . '>';

			/**
			 * @param array Shortcode args.
			 * @param string Shortcode inner content (empty string in most cases).
			 * @param string Shortcode tag.
			 *
			 * @since 1.0
			 */
			do_action( "mpa_before_{$shortName}_shortcode", $shortcodeArgs, $content, $shortcodeTag );

			// Render inner HTML
			echo $this->renderContent( $shortcodeArgs, $content, $shortcodeTag );

			/**
			 * @param array Shortcode args.
			 * @param string Shortcode inner content (empty string in most cases).
			 * @param string Shortcode tag.
			 *
			 * @since 1.0
			 */
			do_action( "mpa_after_{$shortName}_shortcode", $shortcodeArgs, $content, $shortcodeTag );

		echo '</div>';

		$output = ob_get_clean();

		return $output;
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract protected function renderContent( $args, $content, $shortcodeTag);

	/**
	 * @param array $args
	 * @param array $defaults
	 * @param string $content
	 * @param string $shortcodeTag
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function parseArgs( $args, $defaults, $content, $shortcodeTag ) {
		$shortcodeArgs = shortcode_atts( $defaults, $args, $shortcodeTag );
		$shortcodeArgs = $this->validateArgs( $shortcodeArgs );

		$shortcodeArgs['shortcode_name'] = $shortcodeTag;

		return $shortcodeArgs;
	}

	/**
	 * @param array $args
	 * @return array
	 *
	 * @since 1.2
	 */
	public function validateArgs( $args ) {
		$validArgs = array();

		foreach ( $args as $name => $value ) {
			$type = $this->getAttributeType( $name );

			switch ( $type ) {
				// Boolean type
				case 'bool':
					$validValue = ParseUtils::parseBool( $value );
					break;

				// Number types
				case 'integer':
					$validValue = ParseUtils::parseInt( $value );
					break;
				case 'float':
					$validValue = ParseUtils::parseFloat( $value );
					break;

				// String types
				case 'string':
					$validValue = sanitize_text_field( $value );
					break;
				case 'order':
					$validValue = ParseUtils::parseOrder( $value );
					break;
				case 'id':
					$validValue = ParseUtils::parseId( $value );
					break;
				case 'ids':
					$validValue = ParseUtils::parseIds( $value );
					break;
				case 'slug':
					$validValue = ParseUtils::parseSlug( $value );
					break;
				case 'slugs':
					$validValue = ParseUtils::parseSlugs( $value );
					break;

				case 'post':
				case 'term':
					$validValue = ParseUtils::parseSlugOrId( $value );
					break;

				case 'posts':
				case 'terms':
					$validValue = ParseUtils::parseSlugsAndIds( $value );
					break;

				// Undefined type
				default:
					$validValue = sanitize_text_field( $value );
					break;
			}

			$validArgs[ $name ] = $validValue;
		}

		return $validArgs;
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getDefaultArgs() {
		$defaults = array_map(
			function ( $attribute ) {
				return $attribute['default'];
			},
			$this->attributes
		);

		return array_combine( array_keys( $this->attributes ), $defaults );
	}

	/**
	 * @param string $name
	 * @param string $default Optional. 'undefined' by default.
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function getAttributeType( $name, $default = 'undefined' ) {
		if ( array_key_exists( $name, $this->attributes ) ) {
			return $this->attributes[ $name ]['type'];
		} else {
			return $default;
		}
	}
}
