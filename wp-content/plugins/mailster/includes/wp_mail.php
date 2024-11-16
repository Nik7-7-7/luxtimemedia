<?php

/**
 * Mailster WP_mail
 *
 * @package Mailster
 * @subpackage WP_mail
 *
 * This file is used to replace the wp_mail function with Mailster's wp_mail function
 */

// don't use Mailster for system mails
if ( mailster_option( 'system_mail' ) != 1 ) {
	return;
}


// check if the function is already used by another plugin
if ( ! function_exists( 'wp_mail' ) ) :

	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array(), $file = null, $template = null ) {
		return Mailster::get_instance()->wp_mail( $to, $subject, $message, $headers, $attachments, $file, $template );
	}

elseif ( is_admin() ) :

	$message = sprintf( esc_html__( 'The %s method already exists from a different plugin! Please disable it before using Mailster for system mails!', 'mailster' ), '<code>wp_mail()</code>' );

	if ( class_exists( 'ReflectionFunction' ) ) {

		$reflFunc = new ReflectionFunction( 'wp_mail' );

		$file_name = $reflFunc->getFileName();

		if ( strpos( $file_name, WP_PLUGIN_DIR ) !== false ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';
			$all_plugins = get_plugins();
			$stripped    = ( str_replace( WP_PLUGIN_DIR . '/', '', $file_name ) );
			$plugin      = array_values( preg_grep( '/^' . preg_quote( ( strtok( $stripped, '/' ) ) . '/', '/' ) . '/', array_keys( $all_plugins ) ) );

			if ( ! empty( $plugin ) ) {

				if ( ! function_exists( 'wp_create_nonce' ) ) {
					require_once ABSPATH . '/wp-includes/pluggable.php';
				}

				$slug = $plugin[0];

				$plugin_file = WP_PLUGIN_DIR . '/' . $slug;
				$plugin_data = get_plugin_data( $plugin_file );


				$deactivate = '<a class="button button-primary" href="' .
					add_query_arg(
						array(
							's'             => urlencode( $slug ),
							'plugin_status' => 'active',
						),
						admin_url( 'plugins.php' )
					) . '">' . esc_html( esc_html_x( 'Show Plugin', 'mailster' ) ) . '</a>';

				$edit = '<a class="button" href="' . add_query_arg(
					array(
						'file'   => urlencode( $stripped ),
						'plugin' => urlencode( $slug ),
					),
					admin_url( 'plugin-editor.php' )
				) . '">' . esc_html__( 'View file in editor', 'mailster' ) . '</a>';

				$message .= '<h3>' . esc_html__( 'Plugin Name', 'mailster' ) . ': ' . esc_html( $plugin_data['Name'] ) . '</h3>';
				$message .= '<p>' . $deactivate . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $edit . '</p>';



			}
		}

		$message .= '<p>' . esc_html__( 'File:', 'mailster' ) . ' - ' . $file_name . ':' . $reflFunc->getStartLine() . '</p>';

	}

	mailster_notice( $message, 'error', true, 'wp_mail_notice' );

endif;
