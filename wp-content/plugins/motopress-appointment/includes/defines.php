<?php

namespace MotoPress\Appointment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin headers

/** @since 1.5.0 */
define( 'MotoPress\\Appointment\\PLUGIN_URI', 'https://motopress.com/products/appointment-booking/' );
/** @since 1.5.0 */
define( 'MotoPress\\Appointment\\PLUGIN_AUTHOR', 'MotoPress' );

// Directories (all with trailing slash)
$uploads = wp_upload_dir();

define( 'MotoPress\Appointment\PLUGIN_DIR', plugin_dir_path( PLUGIN_FILE ) );
define( 'MotoPress\Appointment\UPLOADS_DIR', trailingslashit( $uploads['basedir'] ) . 'motopress-appointment/' );

define( 'MotoPress\Appointment\PLUGIN_URL', plugin_dir_url( PLUGIN_FILE ) );

// Vendor
define( 'MotoPress\Appointment\FLATPICKR_VERSION', '4.6.3' );
define( 'MotoPress\Appointment\SPECTRUM_VERSION', '2.0.8' );

// Schedule

/** @since 1.6.0 */
define( 'MotoPress\Appointment\ACTIVITY_WORK', 'work' );
/** @since 1.6.0 */
define( 'MotoPress\Appointment\ACTIVITY_LUNCH', 'lunch' );
/** @since 1.6.0 */
define( 'MotoPress\Appointment\ACTIVITY_BREAK', 'break' );

/** @since 2.0.0 */
define( 'MotoPress\Appointment\TIMETABLE_DAYS',
	// The order is equal to values of DateTime->format( 'w' ):
	//     0 => Sunday,
	//     1 => Monday
	//     ...
	//     6 => Saturday
	[
		'sunday',
		'monday',
		'tuesday',
		'wednesday',
		'thursday',
		'friday',
		'saturday',
	]
);
