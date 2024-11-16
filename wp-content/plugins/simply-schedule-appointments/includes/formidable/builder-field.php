<?php
$iframe_embed = ssa()->shortcodes->ssa_booking( array(
	'integration' => 'form',
	'type' => $field['appointment_type_id'],
	'edit' => '',
	'view' => '',
) );

echo $iframe_embed;