<?php

namespace MotoPress\Appointment\Crons;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Such crons process all data in tasks (chunks) using a post-requests chain.
 * This approach allows you to avoid disabling the cron script on low
 * traffic sites because of the long execution time of free hosting.
 */
abstract class AbstractTaskChainCron extends AbstractWPCron {

	const TASKS_PROCESSING_TIME_LIMIT_IN_SECONDS = 20;

	const TRANSIENT_NAME_CRON_LOCKED_POSTFIX = '_lock';

	const OPTION_NAME_START_TASK_DATA_POSTFIX      = '_start_task_data';
	const OPTION_NAME_EXECUTION_PERCENTAGE_POSTFIX = '_execution_percentage';
	const OPTION_NAME_EXECUTION_STATUS_POSTFIX     = '_execution_status';

	const EXECUTION_STATUS_STARTED   = 'started';
	const EXECUTION_STATUS_CANCELING = 'canceling';
	const EXECUTION_STATUS_CANCELED  = 'canceled';
	const EXECUTION_STATUS_FINISHED  = 'finished';

	public function __construct() {

		parent::__construct();

		add_action(
			'wp_ajax_' . static::getCronActionHookName(),
			function() {
				$this->processHttpPostRequestChain();
			}
		);
		add_action(
			'wp_ajax_nopriv_' . static::getCronActionHookName(),
			function() {
				$this->processHttpPostRequestChain();
			}
		);

		// make sure cron scheduled itself on the right time
		// and make sure we have cron as healthcheck process just in case
		// when task chain execution is terminated unexpectedly
		if ( ! static::isFinished() && ! static::isCanceled() ) {

			static::schedule();
		}
	}


	public static function getCronStartIntervalId(): string {
		return static::CRON_START_INTERVAL_ID_EVERY_10_MIN;
	}


	final protected function executeCron() {

		// Start/restart the task chain execution if it is not already processing

		if ( static::isTaskChainExecutingNow() ) {

			exit;
		}

		$startData = static::getStartTaskData();

		if ( static::isCanceled() || static::isFinished() ) {

			static::setCurrentExecutionStatus( static::EXECUTION_STATUS_STARTED );
		}

		$this->executeTasks( $startData );

		exit;
	}

	private function processHttpPostRequestChain() {

		try {

			// Don't lock up other requests while processing
			session_write_close();

			if ( static::isTaskChainExecutingNow() ) {
				wp_die();
			}

			check_ajax_referer( static::getCronActionHookName(), 'nonce' );

			$this->executeTasks( wp_unslash( $_POST ) );

		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
		}

		wp_die();
	}


	private static function isTaskChainExecutingNow(): bool {

		return false !== get_transient( static::getCronActionHookName() . static::TRANSIENT_NAME_CRON_LOCKED_POSTFIX );
	}

	/**
	 * @param $nextTaskData - cron can send POST data to itself in http post request chain processing.
	 * For example we can find necessary data for tasks and send unprocessed part of it to the next
	 * cron execution to save time on the next step.
	 */
	private static function sendHttpPostRequestToContinueTaskChain( array $nextTaskData = array() ) {

		// initate next chunk to be able to execute it without cron system
		$ajaxUrlForRequestsChaine = add_query_arg(
			array(
				'action' => static::getCronActionHookName(),
				'nonce'  => wp_create_nonce( static::getCronActionHookName() ),
			),
			admin_url( 'admin-ajax.php' )
		);

		return wp_remote_post(
			esc_url_raw( $ajaxUrlForRequestsChaine ),
			array(
				'timeout'   => 0.01,
				'blocking'  => false,
				'body'      => $nextTaskData,
				'cookies'   => $_COOKIE,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			)
		);
	}

	/**
	 * Init new task chain data. You can overwrite this method to add some custom initialisation.
	 * @throws Exception if cron already started or canceling.
	 */
	public static function initTaskChainDataAndStartCron( array $startTaskData ) {

		if ( static::isCanceling() ) {
			throw new Exception( 'Could not start export bookings because it is canceling now.' );
		}

		if ( ! static::isCanceled() && ! static::isFinished() ) {
			throw new Exception( 'Could not start export booking because it is working now.' );
		}

		update_option(
			static::getCronActionHookName() . static::OPTION_NAME_START_TASK_DATA_POSTFIX,
			$startTaskData,
			false
		);

		// set execution percentage to 0
		update_option(
			static::getCronActionHookName() . static::OPTION_NAME_EXECUTION_PERCENTAGE_POSTFIX,
			0,
			false
		);

		static::setCurrentExecutionStatus( static::EXECUTION_STATUS_STARTED );

		static::schedule( true );

		// send first post request right now to make sure
		// we start processing now even if wordpress will wait before start cron
		static::sendHttpPostRequestToContinueTaskChain( $startTaskData );
	}

	protected static function getStartTaskData(): array {

		$startTaskData = get_option(
			static::getCronActionHookName() . static::OPTION_NAME_START_TASK_DATA_POSTFIX,
			array()
		);

		return $startTaskData;
	}


	final public static function cancelCronExecution() {

		if ( ! static::isFinished() && ! static::isCanceled() ) {

			static::setCurrentExecutionStatus( static::EXECUTION_STATUS_CANCELING );
			static::unschedule();

			if ( ! static::isTaskChainExecutingNow() ) {
				static::setCurrentExecutionStatus( static::EXECUTION_STATUS_CANCELED );
			}
		}
	}

	final public static function isStarted(): bool {
		return static::EXECUTION_STATUS_STARTED === static::getCurrentExecutionStatus();
	}

	final public static function isCanceling(): bool {
		return static::EXECUTION_STATUS_CANCELING === static::getCurrentExecutionStatus();
	}

	final public static function isCanceled(): bool {
		return static::EXECUTION_STATUS_CANCELED === static::getCurrentExecutionStatus();
	}

	final public static function isFinished(): bool {
		return static::EXECUTION_STATUS_FINISHED === static::getCurrentExecutionStatus();
	}

	final public static function getCurrentExecutionStatus(): string {

		return get_option(
			static::getCronActionHookName() . static::OPTION_NAME_EXECUTION_STATUS_POSTFIX,
			static::EXECUTION_STATUS_FINISHED
		);
	}

	private static function setCurrentExecutionStatus( string $executionStatus ) {

		update_option(
			static::getCronActionHookName() . static::OPTION_NAME_EXECUTION_STATUS_POSTFIX,
			$executionStatus,
			false
		);
	}

	final public static function getCurrentExecutionPercentage(): float {

		$currentExecutionPercentage = (float) get_option(
			static::getCronActionHookName() . static::OPTION_NAME_EXECUTION_PERCENTAGE_POSTFIX,
			0
		);

		return $currentExecutionPercentage;
	}

	/**
	 * @return float 0.0-100.0 - percentage must be rounded to lower value with PHP_ROUND_HALF_DOWN
	 * to make sure that 100% is calculated when all crons tasks are done!
	 */
	protected function calculateCurrentExecutionPercentage( array $taskData ): float {
		return 0.0;
	}

	/**
	 * Some small part (less than 25 seconds) of cron job must be executed here.
	 * If nothing left for processing - reschedule cron here to the right time for optimization!
	 */
	final protected function executeTasks( array $nextTaskData = array() ) {

		if ( static::isCanceling() ) {

			static::setCurrentExecutionStatus( static::EXECUTION_STATUS_CANCELED );
			static::unschedule();
			wp_die();
		}

		if ( static::isCanceled() || empty( $nextTaskData ) ) {

			wp_die();
		}

		// Lock tasks execution of tasks to ensure that other threads
		// of this cron are not doing anything at the same time.
		set_transient(
			static::getCronActionHookName() . static::TRANSIENT_NAME_CRON_LOCKED_POSTFIX,
			microtime(),
			static::TASKS_PROCESSING_TIME_LIMIT_IN_SECONDS + 10 // seconds
		);

		// Set start time of current task processing step
		$finishTaskProcessingTime = time() + static::TASKS_PROCESSING_TIME_LIMIT_IN_SECONDS;

		do {
			// if task returns empty $nextTaskData then execution is over
			$nextTaskData = $this->processTask( $nextTaskData );

			update_option(
				static::getCronActionHookName() . static::OPTION_NAME_EXECUTION_PERCENTAGE_POSTFIX,
				$this->calculateCurrentExecutionPercentage( $nextTaskData ),
				false
			);

			if ( 100 <= static::getCurrentExecutionPercentage() ) {
				// if cron calculates percentage then we allow it do not clear $nextTaskData
				// to be able to keep necessary task data for percentage calculation
				$nextTaskData = array();
			}
		} while (
			$finishTaskProcessingTime > time() &&
			! empty( $nextTaskData ) &&
			! static::isCanceling() &&
			! static::isCanceled()
		);

		// Unlock tasks execution for other threads of this cron
		delete_transient( static::getCronActionHookName() . static::TRANSIENT_NAME_CRON_LOCKED_POSTFIX );

		if ( static::isCanceling() || static::isCanceled() ) {

			if ( static::isCanceling() ) {

				static::setCurrentExecutionStatus( static::EXECUTION_STATUS_CANCELED );
				static::unschedule();
			}
		} elseif ( ! empty( $nextTaskData ) ) {

			// store task data in case if task chain will be stopped not finished
			// and will be reactivated with wp cron mechanism
			update_option(
				static::getCronActionHookName() . static::OPTION_NAME_START_TASK_DATA_POSTFIX,
				$nextTaskData,
				false
			);

			static::sendHttpPostRequestToContinueTaskChain( $nextTaskData );
		} else {

			static::setCurrentExecutionStatus( static::EXECUTION_STATUS_FINISHED );
			static::unschedule();
		}

		wp_die();
	}

	/**
	 * Process single task of the current task chain. It has to be as fast as possible!
	 * @return array of data for the next tasks in the chain that still need to be processed.
	 * If the returned array is empty, the task chain stops. If cron calculates execution
	 * percentage then it must round it to lower value to make sure that 100% is calculated
	 * when all crons tasks are done!
	 */
	abstract protected function processTask( array $taskData ): array;
}
