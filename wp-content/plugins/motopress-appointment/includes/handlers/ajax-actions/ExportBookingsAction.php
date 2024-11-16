<?php

namespace MotoPress\Appointment\Handlers\AjaxActions;

use Exception;
use MotoPress\Appointment\Crons\ExportBookingsCron;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ExportBookingsAction extends AbstractAjaxAction {

	const REQUEST_DATA_COMMAND            = 'command';
	const REQUEST_DATA_COMMAND_START      = 'start';
	const REQUEST_DATA_COMMAND_GET_STATUS = 'get_status';
	const REQUEST_DATA_COMMAND_CANCEL     = 'cancel';

	const REQUEST_DATA_BOOKING_STATUS    = 'booking_status';
	const REQUEST_DATA_SERVICE_DATE_FROM = 'service_date_from';
	const REQUEST_DATA_SERVICE_DATE_TO   = 'service_date_to';
	const REQUEST_DATA_SERVICE_ID        = 'service_id';
	const REQUEST_DATA_EMPLOYEE_ID       = 'employee_id';
	const REQUEST_DATA_LOCATION_ID       = 'location_id';


	public static function getAjaxActionName(): string {
		return 'mpa_export_bookings';
	}

	public static function isActionForGuestUser(): bool {
		return false;
	}

	protected static function getValidatedRequestData(): array {

		$requestData = parent::getValidatedRequestData();

		$requestData[ static::REQUEST_DATA_COMMAND ] = static::getStringFromRequest( static::REQUEST_DATA_COMMAND );

		if ( static::REQUEST_DATA_COMMAND_START !== $requestData[ static::REQUEST_DATA_COMMAND ] &&
			static::REQUEST_DATA_COMMAND_GET_STATUS !== $requestData[ static::REQUEST_DATA_COMMAND ] &&
			static::REQUEST_DATA_COMMAND_CANCEL !== $requestData[ static::REQUEST_DATA_COMMAND ]
		) {
			throw new Exception(
				'Parameter ' . static::REQUEST_DATA_COMMAND . ' must have one of following values: ' .
				static::REQUEST_DATA_COMMAND_START . ', ' .
				static::REQUEST_DATA_COMMAND_GET_STATUS . ', ' .
				static::REQUEST_DATA_COMMAND_CANCEL
			);
		}

		$requestData[ static::REQUEST_DATA_BOOKING_STATUS ] = static::getStringFromRequest( static::REQUEST_DATA_BOOKING_STATUS );

		if ( mpapp()->postTypes()->booking()->statuses()->hasStatus( $requestData[ static::REQUEST_DATA_BOOKING_STATUS ] ) ) {

			$requestData[ static::REQUEST_DATA_BOOKING_STATUS ] = array( $requestData[ static::REQUEST_DATA_BOOKING_STATUS ] );

		} else {

			$requestData[ static::REQUEST_DATA_BOOKING_STATUS ] = array(
				BookingStatuses::STATUS_PENDING,
				BookingStatuses::STATUS_CANCELLED,
				BookingStatuses::STATUS_ABANDONED,
				BookingStatuses::STATUS_CONFIRMED,
			);
		}

		$requestData[ static::REQUEST_DATA_SERVICE_DATE_FROM ] = static::getDateFromRequest( static::REQUEST_DATA_SERVICE_DATE_FROM );

		if ( empty( $requestData[ static::REQUEST_DATA_SERVICE_DATE_FROM ] ) ) {

			$requestData[ static::REQUEST_DATA_SERVICE_DATE_FROM ] = '';

		} else {

			$requestData[ static::REQUEST_DATA_SERVICE_DATE_FROM ] = $requestData[ static::REQUEST_DATA_SERVICE_DATE_FROM ]->format( 'Y-m-d' );
		}

		$requestData[ static::REQUEST_DATA_SERVICE_DATE_TO ] = static::getDateFromRequest( static::REQUEST_DATA_SERVICE_DATE_TO );

		if ( empty( $requestData[ static::REQUEST_DATA_SERVICE_DATE_TO ] ) ) {

			$requestData[ static::REQUEST_DATA_SERVICE_DATE_TO ] = '';

		} else {

			$requestData[ static::REQUEST_DATA_SERVICE_DATE_TO ] = $requestData[ static::REQUEST_DATA_SERVICE_DATE_TO ]->format( 'Y-m-d' );
		}

		$requestData[ static::REQUEST_DATA_SERVICE_ID ]  = static::getIntegerFromRequest( static::REQUEST_DATA_SERVICE_ID );
		$requestData[ static::REQUEST_DATA_EMPLOYEE_ID ] = static::getIntegerFromRequest( static::REQUEST_DATA_EMPLOYEE_ID );
		$requestData[ static::REQUEST_DATA_LOCATION_ID ] = static::getIntegerFromRequest( static::REQUEST_DATA_LOCATION_ID );

		return $requestData;
	}

	protected static function doAction( array $requestData ) {

		if ( static::REQUEST_DATA_COMMAND_START === $requestData[ static::REQUEST_DATA_COMMAND ] &&
			! ExportBookingsCron::isStarted() &&
			! ExportBookingsCron::isCanceling()
		) {

			ExportBookingsCron::initTaskChainDataAndStartCron(
				array(
					ExportBookingsCron::TASK_DATA_KEY_BOOKING_STATUSES => $requestData[ static::REQUEST_DATA_BOOKING_STATUS ],
					ExportBookingsCron::TASK_DATA_KEY_RESERFATION_DATE_FROM => $requestData[ static::REQUEST_DATA_SERVICE_DATE_FROM ],
					ExportBookingsCron::TASK_DATA_KEY_RESERVATION_DATE_TO => $requestData[ static::REQUEST_DATA_SERVICE_DATE_TO ],
					ExportBookingsCron::TASK_DATA_KEY_SERVICE_ID          => $requestData[ static::REQUEST_DATA_SERVICE_ID ],
					ExportBookingsCron::TASK_DATA_KEY_EMPLOYEE_ID         => $requestData[ static::REQUEST_DATA_EMPLOYEE_ID ],
					ExportBookingsCron::TASK_DATA_KEY_LOCATION_ID         => $requestData[ static::REQUEST_DATA_LOCATION_ID ],
				)
			);

		} elseif ( static::REQUEST_DATA_COMMAND_CANCEL === $requestData[ static::REQUEST_DATA_COMMAND ] ) {

			ExportBookingsCron::cancelCronExecution();
		}

		// send current execution status
		$executionPercentage = ExportBookingsCron::getCurrentExecutionPercentage();

		$result = array(
			'status'     => ExportBookingsCron::getCurrentExecutionStatus(),
			'percentage' => $executionPercentage,
		);

		if ( ExportBookingsCron::isFinished() ) {

			$exportFilePath = ExportBookingsCron::getExportFilePath();

			if ( file_exists( $exportFilePath ) ) {

				$result['exportFileUrl'] = ExportBookingsCron::getExportFileUrl();
			}
		}

		wp_send_json_success( $result, 200 );
	}
}
