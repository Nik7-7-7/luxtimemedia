<?php

namespace MotoPress\Appointment\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractField {

	/** @since 1.0 */
	const TYPE = 'abstract';

	/**
	 * @var string Without prefix.
	 *
	 * @since 1.0
	 */
	protected $name = '';

	/**
	 * @var string With prefix.
	 *
	 * @since 1.0
	 */
	protected $inputName = '';

	/**
	 * @var string Like the input name, but all '_' replaced with '-'.
	 *
	 * @since 1.0
	 */
	protected $inputId = '';

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	public $label = '';

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	public $description = '';

	/**
	 * @var mixed
	 *
	 * @since 1.0
	 */
	protected $value = '';

	/**
	 * @var mixed
	 *
	 * @since 1.0
	 */
	protected $default = '';

	/**
	 * @var string Additional control classes.
	 *
	 * @since 1.0
	 */
	protected $class = '';

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $sizeClass = '';

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $atts = array();

	/**
	 * @var bool
	 *
	 * @since 1.3
	 */
	public $isInline = false;

	/**
	 * @var bool
	 *
	 * @since 1.7.0
	 */
	protected $isReadonly = false;

	/**
	 * @var bool
	 *
	 * @since 1.7.0
	 */
	protected $isDisabled = false;

	/**
	 * @var array [ 'fieldName' => field name, 'fieldValue' => [ value1, value2, ... ],
	 *              'fieldValueNot' => [ value1, value2, ... ],
	 *              'else' => 'hide' | 'disable' ]
	 */
	protected $activeIf = array();

	/**
	 * @param string $inputName Prefixed name.
	 * @param array $args
	 * @param mixed $value Optional. Null by default.
	 *
	 * @since 1.0
	 */
	public function __construct( $inputName, $args, $value = null ) {

		$this->setName( $inputName );
		$this->setupArgs( $args );

		if ( null !== $value ) {

			$this->setValue( $value );
		}
	}

	/**
	 * @param array $args
	 *
	 * @since 1.0
	 */
	protected function setupArgs( $args ) {

		// Set most values
		foreach ( $this->mapFields() as $parameter => $field ) {

			if ( array_key_exists( $parameter, $args ) ) {

				$this->$field = $args[ $parameter ];
			}
		}

		// Set $default
		if ( isset( $args['default'] ) ) {
			$this->setDefault( $args['default'] );
		}

		// Set up the initial value
		$this->value = $this->default;

		// Set size
		if ( isset( $args['size'] ) ) {
			$this->setSize( $args['size'] );
		}
	}

	/**
	 * @return array
	 */
	protected function mapFields() {
		return array(
			// Parameter name => Field name
			// inner_label    => innerLabel
			'label'       => 'label',
			'description' => 'description',
			'class'       => 'class',
			'atts'        => 'atts',
			'inline'      => 'isInline',
			'readonly'    => 'isReadonly',
			'disabled'    => 'isDisabled',
			'activeIf'    => 'activeIf',
		);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 *
	 * @since 1.0
	 * @throws \Exception when value is invalid
	 */
	protected function validateValue( $value ) {

		return sanitize_text_field( $value );
	}

	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $inputName Prefixed name.
	 *
	 * @since 1.0
	 */
	public function setName( $inputName ) {
		$this->name      = mpa_unprefix( $inputName );
		$this->inputName = $inputName;
		// Underscores are important for widget field names: "my-widget[__i__][title]"
		$this->inputId = mpa_tmpl_id( $inputName, true );
	}

	/**
	 * @param string $size small|regular|mild|large|half|wide
	 *
	 * @since 1.0
	 */
	public function setSize( $size ) {

		switch ( $size ) {
			case 'small':
				$this->sizeClass = 'small-text';
				break;
			case 'mild':
				$this->sizeClass = 'all-options';
				break;
			case 'regular':
				$this->sizeClass = 'regular-text';
				break;
			case 'large':
				$this->sizeClass = 'large-text';
				break;
			case 'half':
				$this->sizeClass = 'mpa-half-width';
				break;
			case 'wide':
				$this->sizeClass = 'widefat';
				break;
			case 'none':
			default:
				$this->sizeClass = '';
				break;
		}
	}

	/**
	 * @param mixed $value
	 * @param mixed $validate Optional. False by default.
	 *
	 * @since 1.0
	 * @throws \Exception when value is invalid
	 */
	public function setValue( $value, $validate = false ) {

		if ( false !== $validate ) {

			$this->value = $this->validateValue( $value );

		} else {

			// Metabox will always pass a single value if the array length = 1
			$this->value = $this->isSingle() ? $value : (array) $value;
		}
	}

	/**
	 * @param mixed $default
	 *
	 * @since 1.0
	 */
	public function setDefault( $default ) {
		$this->default = $default;
	}

	/**
	 * @param string $context Optional. 'internal' by default. Variants:
	 *     'internal' - for internal use (in the functions of the plugin);
	 *     'save'     - prepare the value for the database.
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public function getValue( $context = 'internal' ) {
		return $this->value;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function hasLabel() {
		return ! empty( $this->label );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function hasDescription() {
		return ! empty( $this->description );
	}

	/**
	 * @return string
	 */
	public function renderLabel() {

		// Tip: use '&nbsp;' to output an empty label
		if ( $this->hasLabel() ) {
			return '<label for="' . esc_attr( $this->inputId ) . '">' . esc_html( $this->label ) . '</label>';
		} else {
			return '';
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderBody() {

		$wrapperTag = $this->getWrapperTag();

		ob_start();

		/** @since 1.0 */
		do_action( 'mpa_render_field_before_start', static::TYPE, $this );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<{$wrapperTag}" . mpa_tmpl_atts( $this->controlAtts() ) . '>';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->renderInput();

			/** @since 1.0 */
			do_action( 'mpa_render_field_after_input', static::TYPE, $this );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->renderDescription();

			/** @since 1.0 */
			do_action( 'mpa_render_field_before_end', static::TYPE, $this );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "</{$wrapperTag}>";

		/** @since 1.0 */
		do_action( 'mpa_render_field_after_end', static::TYPE, $this );

		// render javascript if this field depends on another one
		if ( ! empty( $this->activeIf ) && ! empty( $this->activeIf['fieldName'] ) &&
			(
				( ! empty( $this->activeIf['fieldValue'] ) && is_array( $this->activeIf['fieldValue'] ) ) ||
				( ! empty( $this->activeIf['fieldValueNot'] ) && is_array( $this->activeIf['fieldValueNot'] ) )
			)
		) {

			$isDisable = empty( $this->activeIf['else'] ) || 'disable' === $this->activeIf['else'];

			?>
			<script type="text/javascript">
				(function($) {

					var directValues = [
						<?php
						if ( ! empty( $this->activeIf['fieldValue'] ) ) {

							foreach ( $this->activeIf['fieldValue'] as $value ) {
								?>
								'<?php echo esc_html( $value ); ?>',
								<?php
							}
						}
						?>
					];

					var directValuesNot = [
						<?php
						if ( ! empty( $this->activeIf['fieldValueNot'] ) ) {

							foreach ( $this->activeIf['fieldValueNot'] as $value ) {
								?>
								'<?php echo esc_html( $value ); ?>',
								<?php
							}
						}
						?>
					];

					var directField = $('[name="<?php echo esc_attr( $this->activeIf['fieldName'] ); ?>"]');
					var dependedField = $('[name="<?php echo esc_attr( $this->inputName ); ?>"]');
					var isDependedFieldContainerOfInputs = false;

					// in case when field is complex
					if (!dependedField.length) {
						dependedField = $('[data-base-name="<?php echo esc_attr( $this->inputName ); ?>"]');
						isDependedFieldContainerOfInputs = true;
					}

					if (directField.length && dependedField.length) {

						var dependedFieldParent = dependedField.closest('tr');
						var prevDisplay = dependedFieldParent.css('display');

						activateOrDeativateField();

						directField.change(function(event) {
							activateOrDeativateField();
						});

						directField.on('mpaFieldActivated', function(event) {
							activateOrDeativateField();
						});

						directField.on('mpaFieldDeactivated', function(event) {
							activateOrDeativateField();
						});

						function getDirectFieldValue() {

							if ( 1 === directField.length ) {
								return directField.val();
							} else {
								// radio and checkbox case
								var checkedDirectField = directField.filter(":checked");
								return checkedDirectField.val();
							}
						}

						function activateOrDeativateField() {

							if ( ( 0 === directValues.length || directValues.includes(getDirectFieldValue()) ) &&
								( 0 === directValuesNot.length || !directValuesNot.includes(getDirectFieldValue()) ) &&
								!directField.prop('disabled') && directField.is(':visible')
							) {

								<?php if ( $isDisable ) : ?>

									if (isDependedFieldContainerOfInputs) {
										dependedField.find(':input').prop('disabled', false);
									} else {
										dependedField.prop('disabled', false);
									}

								<?php else : ?>

									dependedFieldParent.css('display', prevDisplay);

								<?php endif; ?>

								// trigger custom event to make sure depended fields from this
								//depended field will be activated or deactivated accordinly
								dependedField.trigger('mpaFieldActivated');
								// trigger event for children in case of checkboxes and radio buttons
								dependedField.find(':input').trigger('mpaFieldActivated');

							} else {

								<?php if ( $isDisable ) : ?>

									if (isDependedFieldContainerOfInputs) {
										dependedField.find(':input').prop('disabled', true);
									} else {
										dependedField.prop('disabled', true);
									}

								<?php else : ?>

									dependedFieldParent.css('display', 'none');

								<?php endif; ?>

								// trigger custom event to make sure depended fields from this
								//depended field will be activated or deactivated accordinly
								dependedField.trigger('mpaFieldDeactivated');
								// trigger event for children in case of checkboxes and radio buttons
								dependedField.find(':input').trigger('mpaFieldDeactivated');
							}
						}
					}
				}(jQuery));
			</script>
			<?php
		}

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * @param string $blockTag Optional. 'div' by default.
	 * @param string $inlineTag Optional. 'span' by default.
	 * @return string
	 *
	 * @since 1.3
	 */
	protected function getWrapperTag( $blockTag = 'div', $inlineTag = 'span' ) {
		return $this->isInline ? $inlineTag : $blockTag;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract public function renderInput();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderDescription() {

		if ( $this->hasDescription() ) {
			$wrapperTag = $this->getWrapperTag( 'p' );

			return "<{$wrapperTag} class=\"description\">{$this->description}</{$wrapperTag}>";
		} else {
			return '';
		}
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function controlAtts() {

		return array_merge(
			array(
				'class'          => rtrim( 'mpa-ctrl mpa-' . static::TYPE . '-ctrl ' . $this->class ),
				'data-type'      => static::TYPE,
				'data-name'      => $this->name,
				'data-base-name' => $this->inputName,
			),
			$this->atts
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function inputAtts() {

		$inputAtts = array(
			'name'  => $this->inputName,
			'id'    => $this->inputId,
			'class' => $this->sizeClass,
		);

		if ( $this->isReadonly ) {
			$inputAtts['readonly'] = 'readonly';
		}
		if ( $this->isDisabled ) {
			$inputAtts['disabled'] = 'disabled';
		}

		return $inputAtts;
	}

	/**
	 * @return bool Uses single or multiple postmetas.
	 *
	 * @since 1.0
	 */
	public function isSingle() {
		return true;
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getType() {
		return static::TYPE;
	}
}
