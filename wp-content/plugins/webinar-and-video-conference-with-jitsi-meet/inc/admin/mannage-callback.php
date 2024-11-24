<?php
/**
 * If direct access than exit the file.
 *
 * @package JITSI_MEET_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class mannage callback
 */
class Mannage_Callback {
	/**
	 * Checkbox sanitize
	 *
	 * @param   boolean $input  input sanitize.
	 *
	 * @return  boolean
	 */
	public function checkboxsanitize( $input ) {
		return ( isset( $input ) ? true : false );
	}

	/**
	 * Jitsi_settings_callback
	 *
	 * @param string $input  Input.
	 *
	 * @return  $input
	 */
	public function jitsi_settings_callback( $input ) {
		return $input;
	}

	/**
	 * Jitsi general
	 *
	 * @param array $args  args array.
	 *
	 * @return void
	 */
	public function jitsi_general( $args ) {
		$name     = $args['label_for'];
		$value    = get_option( $name, '' );
		$disabled = isset( $args['disabled'] ) ? 'disabled' : '';

		if ( $disabled ) {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		printf( '<div class="%3$s"><input class="jitsi-admin-field" type="text" name="%1$s" id="%1$s" value="%2$s"/></div>', esc_attr( $name ), esc_attr( $value ), esc_attr( $disabled ) );
	}

	/**
	 * Jitsi general disable
	 *
	 * @param array $args  $args array.
	 *
	 * @return  void
	 */
	public function jitsi_general_disable( $args ) {
		$name        = $args['label_for'];
		$default     = isset( $args['default'] ) ? $args['default'] : '';
		$value       = $default;
		$data_depend = isset( $args['depend'] ) ? " data-depend='" . wp_json_encode( $args['depend'] ) . "'" : '';
		printf( '<input class="jitsi-admin-field" disabled type="text" name="%1$s" id="%1$s" value="%2$s"%3$s/>', esc_attr( $name ), esc_attr( $value ), esc_attr( $data_depend ) );
	}

	/**
	 * Jitsi textrea
	 *
	 * @param array $args  textrea field.
	 *
	 * @return  void
	 */
	public function jitsi_textrea( $args ) {
		$name     = $args['label_for'];
		$value    = get_option( $name, '' );
		$disabled = isset( $args['disabled'] ) ? 'disabled' : '';
		printf( '<div class="%3$s"><textarea class="jitsi-admin-field" name="%1$s" id="%1$s" rows="4">%2$s</textarea></div>', esc_attr( $name ), $disabled ? '' : esc_attr( $value ), esc_attr( $disabled ) );
	}

	/**
	 * Jitsi number field
	 *
	 * @param array $args  number field.
	 *
	 * @return  void
	 */
	public function jitsi_number( $args ) {
		$name     = $args['label_for'];
		$default  = intval( $args['default'] );
		$value    = get_option( $name, $default );
		$disabled = isset( $args['disabled'] ) ? 'disabled' : '';
		printf( '<div class="%3$s"><input class="jitsi-admin-field" type="number" name="%1$s" id="%1$s" value="%2$s"/></div>', esc_attr( $name ), intval( $value ), esc_attr( $disabled ) );
	}

	/**
	 * Jitsi select field
	 *
	 * @param  array $args  select field.
	 *
	 * @return  void
	 */
	public function jitsi_select( $args ) {
		$name       = $args['label_for'];
		$default    = $args['default'];
		$optionsarr = $args['options'];
		$value      = get_option( $name, $default );
		$options    = '';
		$disabled   = isset( $args['disabled'] ) ? 'disabled' : '';

		echo '<div class="' . esc_attr( $disabled ) . '"><select class="jitsi-admin-field ui" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';

		foreach ( $optionsarr as $key => $val ) {
			$selected = $key === $value ? 'selected' : '';
			echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $val ) . '</option>';
		}

		echo '</select></div>';
	}

	/**
	 * Jitsi switcher
	 *
	 * @param  array $args  switcher field.
	 *
	 * @return void
	 */
	public function jitsi_switch( $args ) {
		$name         = $args['label_for'];
		$default      = $args['default'];
		$value        = get_option( $name, $default ) ? 1 : 0;
		$disabled     = isset( $args['disabled'] ) ? 'disabled' : '';
		$new_tag      = '';
		$feature_type = '';

		if ( isset( $args['feature_type'] ) ) {
			$feature_type = $args['feature_type'];
			$new_tag      = 'new' === $feature_type ? '<span class="jitsi-new"> New </span>' : '';
		}

		$class = $value ? ( $disabled ? 'jitsi-field-switch' : 'jitsi-field-switch active' ) : 'jitsi-field-switch';
		// phpcs:ignore
		printf( '<div class="%3$s %4$s"><label class="%2$s"><input class="jitsi-admin-field" type="checkbox" name="%1$s" id="%1$s" value="1" ' . checked( 1, $value, false ) . '/><span></span></label>%5$s</div>', esc_attr( $name ), esc_attr( $class ), esc_attr( $disabled ), $feature_type, $new_tag );
	}

	/**
	 * Jitsi multiswitcher
	 *
	 * @param array $args   multiswitcher field.
	 *
	 * @return  void
	 */
	public function jitsi_multiswitch( $args ) {
		$name       = $args['label_for'];
		$default    = $args['default'];
		$optionsarr = $args['options'];
		$value      = get_option( $name, $default );

		$allowed_html = [
			'svg'    => [
				'xmlns'       => [],
				'fill'        => [],
				'viewbox'     => [],
				'role'        => [],
				'aria-hidden' => [],
				'focusable'   => [],
				'height'      => [],
				'width'       => [],
			],
			'circle' => [
				'cx'   => [],
				'cy'   => [],
				'r'    => [],
				'fill' => [],
			],
			'path'   => [
				'd'               => [],
				'fill'            => [],
				'stroke'          => [],
				'stroke-width'    => [],
				'stroke-linejoin' => [],
				'mask'            => [],
				'fill-rule'       => [],
				'clip-rule'       => [],

			],
			'mask'   => [
				'id'        => [],
				'maskUnits' => [],
				'x'         => [],
				'y'         => [],
				'fill'      => [],
				'height'    => [],
				'width'     => [],
			],
			'rect'   => [
				'x'      => [],
				'y'      => [],
				'fill'   => [],
				'height' => [],
				'width'  => [],
			],
			'span'   => [
				'class' => [],
			],
		];

		echo '<div class="jitsi-admin-field jitsi-admin-field-multiswitch">';
		foreach ( $optionsarr as $key => $val ) {
			$selected = $key === $value ? 'checked' : '';
			if ( strpos( $key, 'disable-' ) === 0 ) {
				//phpcs:ignore
				echo '<label class="disabled"><input class="jitsi-admin-field" disabled type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '/><span>' . wp_kses( $val, $allowed_html ) . '</span></label>';
			} else {
				echo '<label><input class="jitsi-admin-field" type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . ' /><span>' . wp_kses( $val, $allowed_html ) . '</span></label>';
			}
		}

		echo '</div>';
	}

	/**
	 * Jitsi single img field
	 *
	 * @param array $args  single image field.
	 *
	 * @return  void
	 */
	public function jitsi_single_img( $args ) {
		$name     = $args['label_for'];
		$default  = $args['default'];
		$value    = get_option( $name, $default );
		$disabled = isset( $args['disabled'] ) ? 'disabled' : ''; ?>
		<div class="jitsi-field-single-image <?php echo esc_attr( $disabled ); ?>">
			<div class="jitsi-uploader-preview">
				<?php
				if ( $value ) {
					printf( '<div id="%1$s-prev-wrap" class="jitsi-uploader-preview-img"><img  id="%1$s-prev" src="%2$s" alt="%3$s"/></div>', esc_attr( $name ), esc_url( $value ), esc_attr__( 'Preview Image Upload', 'jitsi-pro' ) );
				}
				?>
				<div data-for="<?php echo esc_attr( $name ); ?>" class="jitsi-uploader-preview-placehold jitsi-uploader-button">
					<div data-for="<?php echo esc_attr( $name ); ?>">
						<svg data-for="<?php echo esc_attr( $name ); ?>" width="47" 
						<?php  // phpcs:ignore ?>
						height="30" viewBox="0 0 47 30" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M46.25 20.625C46.25 15.712 42.4707 11.6816 37.6608 11.2825C36.0069 4.7964 30.1261 0 23.125 0C16.1239 0 10.2431 4.7964 8.58925 11.2825C3.77926 11.6816 0 15.7119 0 20.625C0 25.8027 4.19733 30 9.375 30H19.5267V19.5267H15L23.2766 11.25L31.5533 19.5267H27.0267V30H36.875C42.0527 30 46.25 25.8027 46.25 20.625Z" fill="#0376DA"></path></svg>
						<h5 data-for="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Upload an image', 'jitsi-pro' ); ?></h5>
						<p data-for="<?php echo esc_attr( $name ); ?>"><?php echo esc_attr( $args['size'] ); ?></p>
					</div>
				</div>
				<div class="jisti-uploader-btn-group">
					<input class="jitsi-admin-field" type="text" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo $value ? esc_attr( $value ) : esc_attr( $default ); ?>">

					<?php  // phpcs:ignore ?>
					<button data-for="<?php echo esc_attr( $name ); ?>" class="button jitsi-uploader-reset" type="button"><svg data-for="<?php echo esc_attr( $name ); ?>" size="20" color="#5E6D7A" viewBox="0 0 24 24" class="sc-htoDjs jivEt"><g><path fill-rule="evenodd" d="M8 2c-1.1046 0-2 .8954-2 2v2H3c-.5523 0-1 .4477-1 1s.4477 1 1 1h1.125l.7578 12.1248C4.9487 21.1788 5.8228 22 6.8789 22h10.2422c1.0561 0 1.9302-.8212 1.9961-1.8752L19.875 8H21c.5523 0 1-.4477 1-1s-.4477-1-1-1h-3V4c0-1.1046-.8954-2-2-2H8zm0 2v2h8V4H8zM6.1289 8h11.7422l-.75 12H6.8789l-.75-12zM9 11c0-.5523.4477-1 1-1s1 .4477 1 1v7c0 .5523-.4477 1-1 1s-1-.4477-1-1v-7zm5-1c-.5523 0-1 .4477-1 1v7c0 .5523.4477 1 1 1s1-.4477 1-1v-7c0-.5523-.4477-1-1-1z" clip-rule="evenodd"></path></g></svg></button>
					<button data-for="<?php echo esc_attr( $name ); ?>" class="button jitsi-uploader-button" type="button"><svg data-for="<?php echo esc_attr( $name ); ?>" size="20" color="#5E6D7A" viewBox="0 0 24 24" class="sc-htoDjs jivEt"><g>
						<?php  // phpcs:ignore ?>
					<path fill-rule="evenodd" d="M18.9706 3.4142l1.4142 1.4142c.7811.781.7811 2.0474 0 2.8285l-9.8995 9.8994-6.364 2.1214 2.1214-6.364 9.8995-9.8995c.781-.781 2.0474-.781 2.8284 0zm-1.4142 4.2426l1.4142-1.4142-1.4142-1.4142-1.4142 1.4142 1.4142 1.4143zm-1.4142 1.4143L14.728 7.6569 7.9908 14.394l-.7072 2.1213 2.1214-.7071 6.7372-6.7372z" clip-rule="evenodd"></path></g></svg></button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Jitsi other admin
	 *
	 * @return  void
	 */
	public function jitsi_other_admin() {
		// phpcs:ignore
		printf( '<div class="disabled"><p class="other-admin-setting">%1$s <a href="%2$s" target="_blank">%3$s</a></p></div>', __( 'Some settings like Comany Logo, Background etc are currently not available via api. You can set your company logo, background ect from the', 'jitsi-pro' ), esc_url( 'https://jaas.8x8.vc/#/branding' ), __( 'jaas console', 'jitsi-pro' ) );
	}
}
