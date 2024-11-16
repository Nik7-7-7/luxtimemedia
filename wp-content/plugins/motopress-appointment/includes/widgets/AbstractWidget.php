<?php

namespace MotoPress\Appointment\Widgets;

use WP_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
abstract class AbstractWidget extends WP_Widget {

	/**
	 * @var \MotoPress\Appointment\Fields\AbstractField[]
	 *
	 * @since 1.3
	 */
	public $fields = array();

	/**
	 * @param array $widgetOptions Optional.
	 *
	 * @since 1.3
	 */
	public function __construct( $widgetOptions = array() ) {
		$id   = $this->getId();
		$name = $this->getName();

		// Add description
		if ( empty( $widgetOptions['description'] ) ) {
			$description = $this->getDescription();

			if ( ! empty( $description ) ) {
				$widgetOptions['description'] = $description;
			}
		}

		// Init widget
		parent::__construct( $id, $name, $widgetOptions );

		$this->addActions();
	}

	/**
	 * @since 1.3
	 */
	protected function addActions() {
		add_action( 'widgets_init', array( $this, 'register' ) );

		// Load when all post types and taxonomies are registered
		add_action( 'init', array( $this, 'load' ), 15 );

		add_filter( 'widget_display_callback', array( $this, 'filterArgs' ), 10, 2 );
	}

	/**
	 * @return string Unprefixed ID for the widget, lowercase and unique.
	 *
	 * @since 1.3
	 */
	abstract public function getId();

	/**
	 * @return string Name for the widget displayed on the configuration page.
	 *
	 * @since 1.3
	 */
	abstract public function getName();

	/**
	 * @return string
	 *
	 * @since 1.3
	 */
	public function getDescription() {
		if ( isset( $this->widget_options['description'] ) ) {
			return $this->widget_options['description'];
		} else {
			// Translators: %s: Widget name, like "Appointment Form".
			return sprintf( esc_html__( 'Display %s', 'motopress-appointment' ), $this->getName() );
		}
	}

	/**
	 * @return array
	 *
	 * @since 1.3
	 */
	abstract protected function getFields();

	/**
	 * @access protected
	 *
	 * @since 1.3
	 */
	public function load() {
		/**
		 * @param array $fields
		 *
		 * @since 1.3
		 */
		$fields = apply_filters( "{$this->id_base}_widget_fields", $this->getFields() );

		$fields = array_map(
			function ( $fieldArgs ) {
				return $fieldArgs + array( 'inline' => true );
			},
			$fields
		);

		// Create fields
		$this->fields = mpa_create_fields( $fields, 'widget' );
	}

	/**
	 * Filters the widget's settings before displaying it on the frontend.
	 *
	 * Returning false will effectively short-circuit display of the widget.
	 *
	 * @access protected
	 *
	 * @param array $instanceArgs
	 * @param WP_Widget $widget The widget instance.
	 * @return array
	 *
	 * @since 1.3
	 */
	public function filterArgs( $instanceArgs, $widget ) {
		if ( $widget === $this ) {
			$instanceArgs['template_name'] = $this->id_base;
		}

		return $instanceArgs;
	}

	/**
	 * @param array $widgetArgs Widget args: before_widget, after_widget,
	 *     before_title, after_title.
	 * @param array $instanceArgs Optional. Saved parameters from the database.
	 *
	 * @since 1.3
	 */
	protected function filterWidgetArgs( $widgetArgs, $instanceArgs = array() ) {
		$widgetAtts = array(
			'id'    => mpa_parse_html_attr( 'id', $widgetArgs['before_widget'] ), // "{$widgetId}-{$number}"
			'class' => 'widget mpa-widget ' . mpa_tmpl_id( "{$this->id_base}-widget" ), // 'mpa-abstract-widget'
		);

		// Add custom classes
		if ( isset( $instanceArgs['html_class'] ) ) {
			$widgetAtts['class'] = rtrim( $widgetAtts['class'] . ' ' . $instanceArgs['html_class'] );
		}

		// Replace generated ID with the custom one
		if ( ! empty( $instanceArgs['html_id'] ) ) {
			$widgetAtts['id'] = trim( $instanceArgs['html_id'] );
		}

		$widgetArgs['before_widget'] = '<section' . mpa_tmpl_atts( $widgetAtts, false ) . '>';
		$widgetArgs['after_widget']  = '</section>';

		return $widgetArgs;
	}

	/**
	 * @access protected
	 *
	 * @since 1.3
	 */
	public function register() {
		register_widget( $this );
	}

	/**
	 * Frontend view of the widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @access protected
	 *
	 * @param array $widgetArgs Widget args.
	 * @param array $instanceArgs Saved parameters from the database.
	 *
	 * @since 1.3
	 */
	public function widget( $widgetArgs, $instanceArgs ) {
		$widgetArgs = $this->filterWidgetArgs( $widgetArgs, $instanceArgs );

		// Prepare title
		$title = isset( $instanceArgs['title'] ) ? $instanceArgs['title'] : '';

		/**
		 * @param string $title
		 * @param array $instanceArgs Saved values from the database.
		 * @param string $id Widget ID.
		 *
		 * @since 1.3
		 */
		$title = apply_filters( 'widget_title', $title, $instanceArgs, $this->id_base );

		// Render widget
		echo $widgetArgs['before_widget'];

		if ( ! empty( $title ) ) {
			echo $widgetArgs['before_title'], esc_html( $title ), $widgetArgs['after_title'];
		}

			// Display inner content
			echo '<div class="widget-body">';
				echo $this->renderContent( $instanceArgs );
			echo '</div>';

		echo $widgetArgs['after_widget'];
	}

	/**
	 * @param array $instanceArgs Saved parameters from the database.
	 * @return string
	 *
	 * @since 1.3
	 */
	abstract protected function renderContent( $instanceArgs);

	/**
	 * Backend view of the widget.
	 *
	 * @see WP_Widget::form()
	 *
	 * @access protected
	 *
	 * @param array $instanceArgs Previously saved parameters from the database.
	 *
	 * @since 1.3
	 */
	public function form( $instanceArgs ) {
		// Parse args
		$args = wp_parse_args( $instanceArgs );

		// Prepare fields
		foreach ( $this->fields as $name => $field ) {
			$field->setName( $this->get_field_name( $name ) );

			if ( isset( $args[ $name ] ) ) {
				$field->setValue( $args[ $name ], 'validate' );
			}
		}

		// Display form
		echo $this->renderForm();
	}

	/**
	 * @return string
	 *
	 * @since 1.3
	 */
	protected function renderForm() {
		return mpa_render_template( 'private/fields/fields-list.php', array( 'fields' => $this->fields ) );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $newValues Values just sent to be saved.
	 * @param array $oldValues Previously saved values from database.
	 * @return array Values to be saved.
	 *
	 * @since 1.3
	 */
	public function update( $newValues, $oldValues ) {
		$values = array();

		foreach ( $newValues as $name => $value ) {
			if ( array_key_exists( $name, $this->fields ) ) {
				$field = $this->fields[ $name ];

				$field->setValue( $value, 'validate' );
				$value = $field->getValue( 'save' );
			}

			$values[ $name ] = $value;
		}

		/**
		 * @param array $newValues Values to be saved.
		 * @param array $oldValues Previously saved values from database.
		 * @param WP_Widget $widget Widget instance.
		 *
		 * @since 1.3
		 */
		$values = apply_filters( 'mpa_widget_update', $values, $oldValues, $this );

		return $values;
	}
}
