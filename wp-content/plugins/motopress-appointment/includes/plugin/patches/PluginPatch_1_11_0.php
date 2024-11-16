<?php

namespace MotoPress\Appointment\Plugin;

use MotoPress\Appointment\Metaboxes\Service\ServiceSettingsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginPatch_1_11_0 extends AbstractPluginPatch {

	public static function getVersion(): string {
		return '1.11.0';
	}


	public static function execute(): bool {

		$colorPalette = call_user_func_array( 'array_merge', ServiceSettingsMetabox::COLOR_PALETTE );

		$args       = array( 'fields' => 'ids' );
		$serviceIds = mpapp()->repositories()->service()->findAll( $args );

		foreach ( $serviceIds as $serviceId ) {

			if ( ! get_post_meta( $serviceId, '_mpa_color', true ) ) {

				$currentColor = current( $colorPalette );
				next( $colorPalette ) ?? reset( $colorPalette );

				update_post_meta( $serviceId, '_mpa_color', $currentColor );
			}
		}

		return true;
	}
}
