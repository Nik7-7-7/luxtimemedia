<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest;

class ApiHelper {

	const DATETIME_FORMAT_ISO8601 = 'Y-m-d\TH:i:s';

	/**
	 * Check permissions of posts on REST API.
	 *
	 * @param  string  $postType  Post type.
	 * @param  string  $context  Request context.
	 * @param  int  $objectId  Post ID.
	 *
	 * @return bool
	 */
	public static function checkPostPermissions( $postType, $context = 'read', $objectId = 0 ) {
		$contexts = array(
			'read' => 'read',
		);

		if ( 'revision' === $postType ) {
			$permission = false;
		} else {
			$cap            = $contexts[ $context ];
			$postTypeObject = get_post_type_object( $postType );
			$permission     = current_user_can( $postTypeObject->cap->$cap, $objectId );
		}

		return apply_filters( 'mpa_rest_check_permissions', $permission, $context, $objectId, $postType );
	}

	/**
	 * @param  \DateTime  $dateTime
	 * @param  string PHP timezone string or a ±HH:MM offset., by default UTC
	 *
	 * @return string formatted datetime in ISO8601
	 */
	public static function prepareDateTimeResponse( \DateTime $dateTime, $timezoneString = 'UTC' ) {
		$timezone = new \DateTimeZone( $timezoneString );

		return $dateTime->setTimezone( $timezone )->format( self::DATETIME_FORMAT_ISO8601 );
	}

	/**
	 * @param  string  $snakeString
	 *
	 * @return string
	 */
	public static function convertSnakeToCamelString( string $snakeString ) {
		return str_replace( ' ', '', ucwords( str_replace( '_', ' ', $snakeString ) ) );
	}
}
