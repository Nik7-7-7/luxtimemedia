<?php

	array_unshift(
		$conditions,
		array(
			array(
				'field'    => '',
				'operator' => '',
				'value'    => '',
			),
		)
	);

	$groups = array(
		'fields'           => __( 'Fields', 'mailster' ),
		'custom_fields'    => __( 'User related', 'mailster' ),
		'campaign_related' => __( 'Campaign related', 'mailster' ),
		'list_related'     => __( 'List related', 'mailster' ),
		'tag_related'      => __( 'Tag related', 'mailster' ),
		'meta_fields'      => __( 'Meta Data', 'mailster' ),
		'wp_user_meta'     => __( 'WordPress User Meta', 'mailster' ),
	);

	$groups = apply_filters( 'mailster_condition_groups', $groups );

	if ( ! function_exists( 'wp_dropdown_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	?>
<div class="mailster-conditions">
	<?php echo mailster()->beacon( '611bb8346ffe270af2a9994e' ); ?>
	<div class="mailster-condition-container"></div>
	<div class="mailster-conditions-wrap" data-emptytext="<?php esc_attr_e( 'Please add your first condition.', 'mailster' ); ?>">
	<?php foreach ( $conditions as $i => $condition_group ) : ?>
	<div class="mailster-condition-group" data-id="<?php echo $i; ?>" data-operator="<?php esc_attr_e( 'and', 'mailster' ); ?>"<?php echo ( ! $i ) ? ' style="display:none"' : ''; ?>>
			<a class="add-or-condition button button-small"><?php esc_html_e( 'Add Condition', 'mailster' ); ?> [<span><?php esc_html_e( 'or', 'mailster' ); ?></span>]</a>
			<?php
			foreach ( $condition_group as $j => $condition ) :
				$value          = $condition['value'];
				$field          = $condition['field'];
				$field_operator = $this->get_field_operator( $condition['operator'] );
				$is_relative    = in_array( $field_operator, array( 'is_older', 'is_younger' ) );
				?>
		<div class="mailster-condition<?php echo $is_relative ? ' is-relative' : ''; ?>" data-id="<?php echo $j; ?>" data-operator="<?php esc_attr_e( 'or', 'mailster' ); ?>">
			<a class="remove-condition" title="<?php esc_attr_e( 'remove condition', 'mailster' ); ?>">&#10005;</a>
			<div class="mailster-conditions-field-fields">
				<select name="<?php echo esc_attr( $inputname ); ?>[<?php echo $i; ?>][<?php echo $j; ?>][field]" class="condition-field" disabled>

					<?php foreach ( $groups as $id => $label ) : ?>
						<?php

						// skip if group is empty
						if ( method_exists( $this, 'get_' . $id ) ) {
							$group_fields = $this->{'get_' . $id}();
						} else {
							$group_fields = apply_filters( 'mailster_conditions_type_' . $id, array() );
						}
						if ( empty( $group_fields ) ) {
							continue;
						}
						?>
						<optgroup label="<?php echo esc_attr( $label ); ?>">
						<?php
						foreach ( $group_fields as $key => $name ) :
							echo '<option value="' . esc_attr( $key ) . '"' . selected( $condition['field'], $key, false ) . '>' . esc_html( $name ) . '</option>';
						endforeach;
						?>
						</optgroup>
					<?php endforeach ?>

				</select>
			</div>

			<div class="mailster-conditions-operator-fields">

				<?php foreach ( $this->get_all_operators() as $type => $operators ) : ?>
				<div class="mailster-conditions-operator-field mailster-conditions-operator-field-<?php echo esc_attr( $type ); ?>" data-fields=",<?php echo implode( ',', $this->get_operator_fields( $type ) ); ?>,">
					<select name="<?php echo esc_attr( $inputname ); ?>[<?php echo $i; ?>][<?php echo $j; ?>][operator]" class="condition-operator" disabled>
					<?php if ( count( $operators ) == 1 ) : ?>

						<?php
						foreach ( $operators[0] as $key => $name ) :
							echo '<option value="' . esc_attr( $key ) . '"' . selected( $field_operator, $key, false ) . '>' . esc_html( $name ) . '</option>';
						endforeach;
						?>

					<?php else : ?>

						<?php foreach ( $operators as $label => $operator ) : ?>
							<optgroup label="<?php echo esc_attr( $label ); ?>">
							<?php
							foreach ( $operator as $key => $name ) :
								echo '<option value="' . esc_attr( $key ) . '"' . selected( $field_operator, $key, false ) . '>' . esc_html( $name ) . '</option>';
							endforeach;
							?>
							</optgroup>
						<?php endforeach; ?>

					<?php endif; ?>
					</select>
				</div>
				<?php endforeach ?>

				<div class="mailster-conditions-operator-field" data-fields=",<?php echo implode( ',', $this->get_operator_fields( 'hidden' ) ); ?>,">
					<input type="hidden" name="<?php echo esc_attr( $inputname ); ?>[<?php echo $i; ?>][<?php echo $j; ?>][operator]" class="condition-operator" disabled value="is">
				</div>
			</div>


			<div class="mailster-conditions-value-fields">

				<?php foreach ( $this->get_all_value_fields() as $value_field ) : ?>
					<?php $this->render_value_field( $value_field, $value, $inputname . '[' . $i . '][' . $j . '][value]' ); ?>
				<?php endforeach ?>
					
			</div>


				<div class="clear"></div>
			</div><?php endforeach; ?>
		</div><?php endforeach; ?>
	</div>

	<a class="button add-condition"><?php esc_html_e( 'Add Condition', 'mailster' ); ?></a>

	<div class="mailster-condition-empty"></div>
</div>
