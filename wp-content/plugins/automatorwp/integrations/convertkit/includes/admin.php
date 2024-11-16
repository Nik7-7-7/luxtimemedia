<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\ConvertKit\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function automatorwp_convertkit_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_convertkit_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_convertkit_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['convertkit'] = array(
        'title' => __( 'ConvertKit', 'automatorwp' ),
        'icon' => 'dashicons-convertkit',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_convertkit_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_convertkit_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_convertkit_';

    $meta_boxes['automatorwp-convertkit-settings'] = array(
        'title' => automatorwp_dashicon( 'convertkit' ) . __( 'ConvertKit', 'automatorwp' ),
        'fields' => apply_filters( 'automatorwp_convertkit_settings_fields', array(
            $prefix . 'key' => array(
                'name' => __( 'API key:', 'automatorwp' ),
                'desc' => sprintf( __( 'Your ConvertKit API key.'), 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'secret' => array(
                'name' => __( 'API Secret:', 'automatorwp' ),
                'desc' => sprintf( __( 'Your ConvertKit API Secret.'), 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_convertkit_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_convertkit_meta_boxes", 'automatorwp_convertkit_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_convertkit_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $key = automatorwp_convertkit_get_option( 'key', '' );
    $secret = automatorwp_convertkit_get_option( 'secret', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-convertkit-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with ConvertKit:', 'automatorwp' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you ConvertKit API key and API secret fields and click on "Authorize" to connect.', 'automatorwp' ); ?></p>
            <?php if ( ! empty( $key ) && ! empty( $secret ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with ConvertKit successfully.', 'automatorwp' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php
}