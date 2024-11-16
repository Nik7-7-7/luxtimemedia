<?php

$editable = ! in_array( $post->post_status, array( 'active', 'finished' ) );
if ( isset( $_GET['showstats'] ) && $_GET['showstats'] ) {
	$editable = false;
}
?>
	<p>
		<label>
		<input name="mailster_data[track_opens]" id="mailster_data_track_opens" value="1" type="checkbox" <?php echo ( isset( $this->post_data['track_opens'] ) ) ? ( ( $this->post_data['track_opens'] ) ? 'checked' : '' ) : ( mailster_option( 'track_opens' ) ? 'checked' : '' ); ?>> <?php esc_html_e( 'Track Opens', 'mailster' ); ?>
		</label>
	</p>
	<p>
		<label>
		<input name="mailster_data[track_clicks]" id="mailster_data_track_clicks" value="1" type="checkbox" <?php echo ( isset( $this->post_data['track_clicks'] ) ) ? ( ( $this->post_data['track_clicks'] ) ? 'checked' : '' ) : ( mailster_option( 'track_clicks' ) ? 'checked' : '' ); ?>> <?php esc_html_e( 'Track Clicks', 'mailster' ); ?>
		</label>
	</p>

<?php if ( $editable ) : ?>

	<?php $colors = mailster( 'templates' )->colors( $post, $this->get_template(), $this->get_file() ); ?>
	
	<?php $customcolors = false; ?>

	<h4><?php esc_html_e( 'Colors', 'mailster' ); ?></h4>
		
	<ul class="colors has-labels" data-original-colors='<?php echo json_encode( $colors ); ?>'>
	<?php foreach ( $colors['colors'] as $color ) : ?>
		<?php
			$color_value = substr( esc_attr( $color['value'] ), 1 );
			$label       = $color['label'];
		?>
		<li class="mailster-color">
			<label title="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></label>
			<input type="text" class="form-input-tip color" id="mailster-color-<?php echo esc_attr( $color['id'] ); ?>" name="mailster_data[newsletter_color][<?php echo esc_attr( $color_value ); ?>]" value="<?php echo esc_attr( $color['value'] ); ?>" data-value="<?php echo esc_attr( $color['value'] ); ?>" data-default-color="<?php echo esc_attr( $color['original'] ); ?>" data-id="<?php echo esc_attr( $color['id'] ); ?>" data-var="<?php echo esc_attr( $color['var'] ); ?>">
			<a class="default-value mailster-icon" href="#" tabindex="-1"></a>
		</li>
	<?php endforeach; ?>
		</ul>
	<p>
		<a class="savecolorschema button button-small"><?php esc_html_e( 'Save Color Schema', 'mailster' ); ?></a>
	</p>

	<span class="spinner" id="colorschema-ajax-loading"></span>
	<?php if ( ! empty( $colors['schemas'] ) ) : ?>
	<h4><?php esc_html_e( 'Colors Schemas', 'mailster' ); ?></h4>	
	<div class="colorschemas">
		<?php foreach ( $colors['schemas'] as $hash => $colorschema ) : ?>
		<div class="colorschema" title="<?php echo esc_attr( $colorschema['name'] ); ?>">
			<span class="colorschema-title"><?php echo esc_html( $colorschema['name'] ); ?></span>
			<?php foreach ( $colorschema['colors'] as $id => $color ) : ?>
			<span class="colorschema-field" data-id="<?php echo esc_attr( $id ); ?>" data-hex="<?php echo esc_attr( strtolower( $color ) ); ?>" style="background-color:<?php echo esc_attr( $color ); ?>"></span>
			<?php endforeach; ?>
			<?php
			if ( isset( $colorschema['hash'] ) ) :
				$customcolors = true;
				?>
			<a class="colorschema-delete" data-hash="<?php echo esc_attr( $colorschema['hash'] ); ?>">&#10005;</a>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<?php if ( ! empty( $customcolors ) ) : ?>
	<p>
		<a class="colorschema-delete-all button-link button-small button-link-delete"><?php esc_html_e( 'Delete all Custom Schemas', 'mailster' ); ?></a>
	</p>
	<?php endif; ?>
	<?php endif; ?>
