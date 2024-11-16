<?php

/**
 * @param string $emailId Email ID, like 'mpa_pending_booking_admin_email'.
 *
 * @since 1.1.0
 */

use MotoPress\Appointment\Utils\ColorUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$baseColor     = mpapp()->settings()->getEmailBaseColor();
$baseTextColor = ColorUtils::darkOrLight( $baseColor, '#202020' );
$bgColor       = mpapp()->settings()->getEmailBackgroundColor();
$bodyColor     = mpapp()->settings()->getEmailBodyColor();
$textColor     = mpapp()->settings()->getEmailTextColor();

$baseLighter20 = ColorUtils::hexLighter( $baseColor, 20 );
$baseLighter40 = ColorUtils::hexLighter( $baseColor, 40 );
$bgDarker10    = ColorUtils::hexDarker( $bgColor, 10 );
$bodyDarker10  = ColorUtils::hexDarker( $bodyColor, 10 );
$textLighter20 = ColorUtils::hexLighter( $textColor, 20 );

/** @since 1.1.0 */
do_action( 'mpa_email_head_styles' );

/** @since 1.1.0 */
do_action( "{$emailId}_head_styles" );

?>
#wrapper {
	background-color: <?php echo esc_attr( $bgColor ); ?>;
	margin: 0;
	padding: 70px 0 70px 0;
	-webkit-text-size-adjust: none !important;
	width: 100%;
}
#template_container {
	box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important;
	background-color: <?php echo esc_attr( $bodyColor ); ?>;
	border: 1px solid <?php echo esc_attr( $bgDarker10 ); ?>;
	border-radius: 3px !important;
}
#template_header {
	background-color: <?php echo esc_attr( $baseColor ); ?>;
	border-radius: 3px 3px 0 0 !important;
	color: <?php echo esc_attr( $baseTextColor ); ?>;
	border-bottom: 0;
	font-weight: bold;
	line-height: 100%;
	vertical-align: middle;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}
#template_header h1 {
	color: <?php echo esc_attr( $baseTextColor ); ?>;
}
#template_footer td {
	padding: 0;
	-webkit-border-radius: 6px;
}
#template_footer #credit {
	border:0;
	color: <?php echo esc_attr( $baseLighter40 ); ?>;
	font-family: Arial;
	font-size:12px;
	line-height:125%;
	text-align:center;
	padding: 0 48px 48px 48px;
}
#body_content {
	background-color: <?php echo esc_attr( $bodyColor ); ?>;
}
#body_content table td {
	padding: 48px;
}
#body_content table td td {
	padding: 12px;
}
#body_content table td th {
	padding: 12px;
}
#body_content p {
	margin: 0 0 16px;
}
#body_content_inner {
	color: <?php echo esc_attr( $textLighter20 ); ?>;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 14px;
	line-height: 150%;
}
.td {
	color: <?php echo esc_attr( $textLighter20 ); ?>;
	border: 1px solid <?php echo esc_attr( $bodyDarker10 ); ?>;
}
.text {
	color: <?php echo esc_attr( $textColor ); ?>;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}
.link {
	color: <?php echo esc_attr( $baseColor ); ?>;
}
#header_wrapper {
	padding: 36px 48px;
	display: block;
}
h1 {
	color: <?php echo esc_attr( $baseColor ); ?>;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 30px;
	font-weight: 300;
	line-height: 150%;
	margin: 0;
	text-shadow: 0 1px 0 <?php echo esc_attr( $baseLighter20 ); ?>;
	-webkit-font-smoothing: antialiased;
}
h2 {
	color: <?php echo esc_attr( $baseColor ); ?>;
	display: block;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 18px;
	font-weight: bold;
	line-height: 130%;
	margin: 16px 0 8px;
}
h3 {
	color: <?php echo esc_attr( $baseColor ); ?>;
	display: block;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 16px;
	font-weight: bold;
	line-height: 130%;
	margin: 16px 0 8px;
}
a {
	color: <?php echo esc_attr( $baseColor ); ?>;
	font-weight: normal;
	text-decoration: underline;
}
img {
	border: none;
	display: inline;
	font-size: 14px;
	font-weight: bold;
	height: auto;
	line-height: 100%;
	outline: none;
	text-decoration: none;
	text-transform: capitalize;
}
