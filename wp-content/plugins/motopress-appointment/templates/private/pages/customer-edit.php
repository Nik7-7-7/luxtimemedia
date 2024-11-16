<?php

/**
 * @param array $fields
 * @param int $userId 0 or user id
 * @param WP_Error[]|null $errorMessagess
 * @param bool $successMessage
 *
 * @see MotoPress\Appointment\AdminPages\Custom\CustomersPage
 *
 * @since 1.18.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Customer', 'motopress-appointment' ); ?></h1>

    <hr class="wp-header-end"/>

	<?php
	if ( ! empty( $errorMessages ) ) {
		foreach ( $errorMessages as $errorMessage ) {
			?>
            <div class="error notice notice-error is-dismissible"><p>
                    <strong>Error: </strong><?php echo $errorMessage; ?></p></div>
			<?php
		}
	}

	if ( $successMessage ) {
		?>
        <div class="updated notice notice-success is-dismissible"><p><?php echo $successMessage ?></p></div>
		<?php
	} ?>

    <div class="mpa-page-top-menu">
        <a href="<?php echo mpapp()->pages()->customers()->getUrl(); ?>" class="button mpa-page-top-menu_button">
            <span class="dashicons dashicons-arrow-left"></span>
			<?php esc_html_e( 'Customers', 'motopress-appointment' ); ?>
        </a>
		<?php
		if ( $userId && \MotoPress\Appointment\Handlers\SecurityHandler::isUserCanEditUsers() ) {
			?>
            <a href="<?php echo admin_url( sprintf( 'user-edit.php?user_id=%d', $userId ) ); ?>"
               class="button mpa-page-top-menu_button">
				<?php esc_html_e( 'Edit User', 'motopress-appointment' ); ?>
            </a>
			<?php
		}
		?>
    </div>
    <form method="POST" action="">
		<?php wp_nonce_field();
		if ( ! empty( $fields ) ) {
			?>
            <table class="form-table">
                <tbody>
				<?php
				foreach ( $fields as $field ) {
					?>
                    <tr class="mpa-customer-field-wrap">
                        <th>
							<?php echo $field->renderLabel(); ?>
                        </th>
                        <td>
							<?php echo $field->renderBody(); ?>
                        </td>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>
			<?php
		}
		?>
        <p>
            <input type="submit" class="button button-primary" name="save"
                   value="<?php echo esc_html__( 'Update', 'motopress-appointment' ); ?>"/>
        </p>
    </form>
</div>