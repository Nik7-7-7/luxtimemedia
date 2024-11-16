<?php

use MotoPress\Appointment\Fields\Basic\GroupField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param AbstractField[] $fields Required.
 * @param string          $class  Optional.
 *
 * @since 1.3
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
	<div class="mpa-fields-list <?php echo esc_attr( $class ); ?>">
		<?php foreach ( $groupFields as $field ) { ?>
			<p class="mpa-ctrl-wrapper <?php echo 'hidden' == $field->getType() ? 'mpa-hide' : ''; ?>">
				<?php if ( $field->hasLabel() ) { ?>
					<?php echo $field->renderLabel(); ?>
					<br>
				<?php } ?>

				<?php echo $field->renderBody(); ?>
			</p>
		<?php } ?>
	</div>
<?php } // For each group ?>
