<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ChecklistField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'checklist';

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $options = array();

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $default = array();

	/**
	 * @var bool
	 */
	protected $isAddSelectAllOption = false;

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function mapFields() {

		return array_merge(
			parent::mapFields(),
			array(
				'options'              => 'options',
				'isAddSelectAllOption' => 'isAddSelectAllOption',
			)
		);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 *
	 * @since 1.0
	 */
	protected function validateValue( $value ) {

		if ( '' === $value ) {
			return $this->default;
		}

		$values = is_array( $value ) ? $value : array( $value );
		$values = array_map( 'sanitize_text_field', $values );
		$values = mpa_evaluate_numbers( $values ); // If it's IDs, then convert it to ints
		$values = array_intersect( $values, array_keys( $this->options ) );
		$values = array_unique( $values );

		return array_values( $values ); // Get rid of gaps in keys after array_unique()
	}

	/**
	 * @return string
	 */
	public function renderLabel() {
		return $this->hasLabel() ? '<label>' . esc_html( $this->label ) . '</label>' : '';
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderInput() {

		$output = '<input type="hidden" name="' .
			esc_attr( $this->inputName ) . '" value="">';

		$selectAllId = "{$this->inputId}-select_all";

		if ( $this->isAddSelectAllOption ) {

			$inputAtts = array(
				'type'  => 'checkbox',
				'name'  => "{$this->inputName}-select_all",
				'id'    => $selectAllId,
				'value' => '',
			);

			if ( count( $this->options ) === count( $this->value ) ) {

				$inputAtts['checked'] = 'checked';
			}

			$output .= '<label>' .
				'<input' . mpa_tmpl_atts( $inputAtts ) . '>&nbsp;' .
				esc_html__( 'Select all', 'motopress-appointment' ) . '</label><br>';
		}

		foreach ( $this->options as $value => $label ) {

			$inputAtts = $this->inputAtts( $value );

			$output .= '<label>' .
				'<input' . mpa_tmpl_atts( $inputAtts ) . '>&nbsp;' .
				esc_html( $label ) . '</label><br>';
		}

		if ( $this->isAddSelectAllOption ) {

			ob_start();

			?>

			<script type="text/javascript">
				(function() {
					var selectAll = jQuery("#<?php echo esc_attr( $selectAllId ); ?>");
					var checkboxes = jQuery('input[name="_mpa_employees[]"]');

					selectAll.change(function() {
						checkboxes.prop('checked', jQuery(this).prop('checked'));
					});

					checkboxes.change(function() {
						selectAll.prop('checked', checkboxes.length === checkboxes.filter(':checked').length);
					});
				}());
			</script>
			<?php

			$output .= ob_get_clean();
		}

		return $output;
	}

	/**
	 * @param string $currentItem Value of the current checkbox. Optional only
	 *     for compatibility purposes.
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function inputAtts( $currentItem = null ) {

		$inputAtts = array_merge(
			parent::inputAtts(),
			array(
				'type'  => 'checkbox',
				'name'  => "{$this->inputName}[]",
				'id'    => "{$this->inputId}-{$currentItem}",
				'value' => $currentItem,
			)
		);

		if ( in_array( $currentItem, $this->value ) ) {
			$inputAtts['checked'] = 'checked';
		}

		return $inputAtts;
	}
}
