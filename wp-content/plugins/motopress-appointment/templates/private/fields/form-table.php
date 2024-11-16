<?php

use MotoPress\Appointment\Fields\Basic\GroupField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param \MotoPress\Appointment\Fields\AbstractField[] $fields
 * @param string $class Optional.
 *
 * @since 1.0
 */

if ( ! isset( $class ) ) {
	$class = '';
}

$groups = mpa_group_fields( $fields ); // [Group name => Group fields]

// Start template
foreach ( $groups as $groupFields ) {
	$groupField = reset( $groupFields );

	// Print group heading
	if ( $groupField instanceof GroupField ) {
		// Print group heading
		echo $groupField->renderLabel();
		echo $groupField->renderDescription();

		// Remove GroupField from fields list
		array_shift( $groupFields );
	}

	// Print group fields ?>
	<table class="form-table <?php echo esc_attr( $class ); ?>">
		<tbody>
			<?php foreach ( $groupFields as $field ) {
				$classes = array(
					'mpa-ctrl-wrapper',
					'mpa-' . $field->getType() . '-ctrl-wrapper',
					'hidden' == $field->getType() ? 'mpa-hide' : '',
				);
				?>
				<tr class="<?php echo implode( ' ', $classes ); ?>">
					<?php if ( $field->hasLabel() ) { ?>
						<th><?php echo $field->renderLabel(); ?></th>
					<?php } ?>

					<td colspan="<?php echo $field->hasLabel() ? 1 : 2; ?>">
						<?php echo $field->renderBody(); ?>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } // For each group ?>
