<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'luxtimemedia' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '8>hI]SB`&vW#jTy<3rY$%rC`vhdD9p>91%+^z%7T^UFcXT|_V,8 m4^cE,k<gOS5' );
define( 'SECURE_AUTH_KEY',  'T}K:qC~2fnp]>|Zmz@XL3EHNkIXlvZ}Cx4t7/K^TkOM&)H,f1JmQ}9S`lh(mWL;r' );
define( 'LOGGED_IN_KEY',    '5*oD%k?/8D3cD8Y~5ME@=A,*_;>NL=4o/:sa%/NIg!Wf1WM]<I:_wm$Uuv<DM1UQ' );
define( 'NONCE_KEY',        '2V_CL#}(TGs/])tZjjg5Rb/KA*0WjOKdW:J<>BY]Suve.UoLDL:*fUfDbP&O]BL|' );
define( 'AUTH_SALT',        '74@F^OI`iLC6 M0?FIC+v[qT#px`wU%_8- q7a:Kg{RRkx7gNi/&Jh((!WS*05Af' );
define( 'SECURE_AUTH_SALT', '(f`%!Lg{7C}Yl^c@W.j`5;;&Md5zL(|+r(mp 67<$nE1Y!G~_8p2JR:k {KWLRn7' );
define( 'LOGGED_IN_SALT',   'Ej!}VR]zN}?dAL9(=c@SIozAVp&Va<+7dkkIug?ocV=Vfvd$Q) F,F>+h$]GSE=:' );
define( 'NONCE_SALT',       'B&?=5[DGiTH<cQR:t!wTd>44mmoG]*Ei_`Qy9n!.gE[L&$jGTNet0h:$m7KK0?Ru' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
