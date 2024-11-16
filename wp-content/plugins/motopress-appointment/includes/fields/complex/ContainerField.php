<?php

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Fields\FieldsFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This field contains subfields in the 'fields' property and saves them to the database as serialized data.
 */
class ContainerField extends AbstractField {

	const TYPE = 'container';

	protected $fields = array();

	public function hasLabel() {
		return false;
	}

	protected function mapFields() {
		return parent::mapFields() + array(
			'fields' => 'fields',
		);
	}

	protected function validateValue( $value ) {

		if ( '' === $value || ! is_array( $value ) ) {
			return $this->default;
		}

		return $value;
	}

	public function renderLabel() {

		if ( ! empty( $this->label ) ) {
			return '<h3 class="mpa-container-ctrl__label">' . esc_html( $this->label ) . '</h3>';
		} else {
			return '';
		}
	}

	public function renderInput() {

		$fields = FieldsFactory::createFields( $this->fields, $this->value );

		ob_start();
		echo $this->renderLabel();
		?>
		<table class="form-table">
			<tbody>
			<?php
			foreach ( $fields as $field ) {
				$field->setName( mpa_prefix( $this->getName() . '[' . $field->getName() . ']' ) );
				?>
					<tr class="mpa-ctrl-wrapper <?php echo 'hidden' == $field->getType() ? 'mpa-hide' : ''; ?>">
						<?php if ( $field->hasLabel() ) { ?>
							<th><?php echo $field->renderLabel(); ?></th>
						<?php } ?>

						<td colspan="<?php echo $field->hasLabel() ? 1 : 2; ?>">
							<?php echo $field->renderBody(); ?>
						</td>
					</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		<?php

		return ob_get_clean();
	}
}
