<?php
$args = array(
	'integration' => 'form',
	'type' => $field['appointment_type_id'] ? esc_attr( $field['appointment_type_id'] ) : '',
	'edit' => $field['value'] ? esc_attr($field['value']) : '',
	'token' => '',
	'view' => '',
);

if ( $field['value'] ) {
	$token = ssa()->appointment_model->get_id_token( $field['value'] );

	if ( $token ) {
		$args['token'] = $token . $field['value'];
	}
}

$iframe_embed = ssa()->shortcodes->ssa_booking( $args );

echo $iframe_embed;
