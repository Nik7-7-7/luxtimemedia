<?php

class MailsterWorkflow {

	private $entry;
	private $is_search;
	private $workflow;
	private $trigger;
	private $subscriber;
	private $subscriber_id;
	private $step;
	private $timestamp;
	private $context;
	private $steps;
	private $args;
	private $current_step;
	private $max_steps = 1024; // max steps to prevent endless loops (per process)
	private $steps_map = array();

	static $total_steps = 0;

	public function __construct( $workflow, $trigger, $subscriber_id = null, $step = null, $timestamp = null, $context = null ) {

		$this->set_workflow( $workflow );
		$this->set_trigger( $trigger );
		$this->set_subscriber( $subscriber_id );
		$this->set_step( $step );
		$this->set_timestamp( $timestamp );
	}

	public function set_workflow( $workflow ) {

		$this->workflow = get_post( $workflow );
	}

	public function set_trigger( $trigger ) {

		$this->trigger = $trigger;
	}

	public function set_subscriber( $subscriber_id ) {

		$this->subscriber_id = $subscriber_id;
		$this->subscriber    = mailster( 'subscribers' )->get( $subscriber_id );
	}

	public function set_step( $step ) {

		$this->step  = $step;
		$this->steps = $this->get_steps();
	}

	public function set_timestamp( $timestamp ) {

		$this->timestamp = $timestamp ? $timestamp : 0;
	}

	public function get_steps() {

		if ( $this->steps ) {
			return $this->steps;
		}

		$blocks      = parse_blocks( $this->workflow->post_content );
		$this->steps = $this->parse( $blocks );

		return $this->steps;
	}

	/**
	 * Parse the blocks and return a structured array
	 *
	 * @param array  $blocks
	 * @param string $parent
	 * @return array
	 */
	private function parse( $blocks, $parent = null ) {

		$parsed = array();
		foreach ( $blocks as $block ) {
			if ( ! $block['blockName'] ) {
				continue;
			}

			$id = isset( $block['attrs']['id'] ) ? $block['attrs']['id'] : null;

			$type = str_replace( 'mailster-workflow/', '', $block['blockName'] );
			$arg  = array(
				'type' => $type,
				'attr' => $block['attrs'],
				'id'   => $id,
			);

			if ( $parent ) {
				$arg['parent'] = $parent;
			}

			if ( $type === 'conditions' ) {
				$arg['yes'] = $this->parse( $block['innerBlocks'][0]['innerBlocks'], $id );
				$arg['no']  = $this->parse( $block['innerBlocks'][1]['innerBlocks'], $id );
			} elseif ( $type === 'triggers' ) {
				$triggers = $this->parse( $block['innerBlocks'], $id );
				$parsed   = array_merge( $triggers, $parsed );
				continue;
			}

			if ( $id ) {
				$this->steps_map[ $id ] = $block['attrs'];
			}

			$parsed[] = $arg;

		}

		return $parsed;
	}

	/**
	 * Start the workflow
	 * retuns true if the workflow is finished or false if not. WP_Error if there was an error
	 *
	 * @return mixed
	 */
	public function run() {

		if ( ! $this->workflow ) {
			return new WP_Error( 'error', 'Workflow does not exist.', $this->step );
		}

		if ( get_post_type( $this->workflow ) !== 'mailster-workflow' ) {
			return new WP_Error( 'info', 'This is not a correct workflow.', $this->step );
		}
		if ( get_post_status( $this->workflow ) !== 'publish' ) {
			return new WP_Error( 'info', 'This is workflow is not published.', $this->step );
		}

		$this->args = array(
			'trigger'       => $this->trigger,
			'id'            => $this->workflow->ID,
			'subscriber_id' => $this->subscriber_id,
			'step'          => $this->step,
		);

		// if a step is defined we have to find it first
		$this->is_search = ! is_null( $this->step );
		if ( ! $this->is_search ) {

			$this->log( 'RUN JOB ' . $this->trigger . ' for ' . $this->subscriber_id . ' on ' . $this->trigger );

			$enddate = get_post_meta( $this->workflow->ID, 'enddate', true );

			// if enddate is set and in the past
			if ( $enddate && time() > strtotime( $enddate ) ) {

				$this->log( 'END DATE REACHED' );
				return false;
			}
		}

		// check if subscriber exists if it's not 0 ( 'date', 'anniversary', 'published_post' )
		if ( $this->subscriber_id !== 0 ) {
			if ( ! in_array( $this->trigger, array( 'date', 'anniversary', 'published_post', 'hook' ) ) ) {
				if ( ! mailster( 'subscribers' )->get( $this->subscriber_id ) ) {
					$this->log( 'SUBSCRIBER DOES NOT EXIST' );
					return false;
				}
			}
		}

		// start the workflow
		$result = $this->do_steps( $this->steps );

		$this->log( 'RUN for ' . self::$total_steps . ' steps' );

		// all good => finish
		if ( $result === true ) {

			// still in search mode => current step not found
			if ( $this->is_search ) {
				$this->delete();
			} else {
				$this->finish();
			}

			// more info here
		} elseif ( is_wp_error( $result ) ) {

			$this->error_notice( $result );

		}

		return $result;
	}

	/**
	 * Outputs an error notice
	 *
	 * @param WP_Error $error
	 * @param string   $notice_id
	 */
	private function error_notice( WP_Error $error, $notice_id = null ) {

		if ( is_null( $notice_id ) ) {
			$notice_id = 'workflow_error_' . $this->workflow->ID;
		}

		$error_code = $error->get_error_code();
		$error_data = $error->get_error_data();
		$error_msg  = $error->get_error_message();
		$link       = admin_url( 'post.php?post=' . $this->workflow->ID . '&action=edit' );
		$steplink   = $link;
		if ( isset( $error_data['id'] ) ) {
			$steplink .= '#step-' . $error_data['id'];
		}
		mailster_notice( sprintf( 'Workflow %s had a problem: %s', '"<a href="' . esc_url( $steplink ) . '">' . get_the_title( $this->workflow ) . '</a>"', '<strong>' . $error_msg . '</strong>' ), $error_code, false, $notice_id );
	}

	/**
	 * Gets the workflow from the database
	 *
	 * @return string|null
	 */
	private function get( $workflow_id ) {

		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailster_workflows WHERE `ID` = %d LIMIT 1", $workflow_id ) );
	}

	/**
	 * Returns the id of the current Workflow from the database
	 *
	 * @return string|null
	 */
	private function get_entry() {

		global $wpdb;

		$workflow_id   = $this->workflow->ID;
		$trigger       = $this->trigger;
		$subscriber_id = $this->subscriber_id;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailster_workflows WHERE `workflow_id` = %d AND `trigger` = %d AND `subscriber_id` = %d AND `timestamp` IS NOT NULL AND finished = 0 LIMIT 1", $workflow_id, $trigger, $subscriber_id ) );
	}

	/**
	 * Checks if the count of the workflow has been reached
	 *
	 * @param mixed $count
	 * @return bool
	 */
	private function limit_reached( $count ) {

		global $wpdb;

		$workflow_id   = $this->workflow->ID;
		$trigger       = $this->trigger;
		$subscriber_id = $this->subscriber_id;

		$entries = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mailster_workflows WHERE `workflow_id` = %d AND `trigger` = %s AND `subscriber_id` = %d AND timestamp IS NULL", $workflow_id, $trigger, $subscriber_id ) );

		// enough entries in the database
		if ( $entries >= $count ) {
			return true;
		}

		return false;
	}


	/**
	 * Deletes the current Workflow from the database
	 *
	 * @return bool
	 */
	private function delete() {

		global $wpdb;

		if ( $this->entry ) {
			return false !== $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}mailster_workflows WHERE ID = %d", $this->entry->ID ) );
		}

		// used if step is missing
		if ( $this->args ) {
			$delete = array(
				'workflow_id'   => $this->workflow->ID,
				'trigger'       => $this->trigger,
				'step'          => $this->step,
				'subscriber_id' => $this->subscriber_id,
				'finished'      => 0,
			);

			return false !== $wpdb->delete( "{$wpdb->prefix}mailster_workflows", $delete );
		}

		return false;
	}


	/**
	 * adds a Workflow in the database
	 *
	 * @return object
	 */
	private function add( array $args = array() ) {

		global $wpdb;

		$workflow_id   = $this->workflow->ID;
		$trigger       = $this->trigger;
		$subscriber_id = $this->subscriber_id;
		$step          = $this->step;
		$timestamp     = $this->timestamp;

		$suppress_errors = $wpdb->suppress_errors( true );

		$args = wp_parse_args( $args, array( 'step' => $this->current_step ) );

		$wpdb->insert(
			"{$wpdb->prefix}mailster_workflows",
			array(
				'workflow_id'   => $workflow_id,
				'trigger'       => $trigger,
				'subscriber_id' => $subscriber_id,
				'step'          => $step,
				'added'         => time(),
				'timestamp'     => $timestamp,
			)
		);

		$wpdb->suppress_errors( $suppress_errors );

		return $this->get( $wpdb->insert_id );
	}


	/**
	 * Updates the current Workflow in the database
	 *
	 * @return bool
	 */
	private function update( array $args = array() ) {

		global $wpdb;

		$success = true;

		$workflow_id   = $this->workflow->ID;
		$trigger       = $this->trigger;
		$subscriber_id = $this->subscriber_id;
		$step          = $this->step;

		$suppress_errors = $wpdb->suppress_errors( true );

		$args = wp_parse_args( $args, array( 'step' => $this->current_step ) );

		$where = array(
			'workflow_id'   => $workflow_id,
			'trigger'       => $trigger,
			'subscriber_id' => $subscriber_id,
			'finished'      => 0,
		);

		if ( $wpdb->update( "{$wpdb->prefix}mailster_workflows", $args, $where ) ) {

		} else {

			$success = false;
		}

		$wpdb->suppress_errors( $suppress_errors );

		return $success;
	}

	/**
	 * processes the current steps
	 *
	 * @param mixed $steps
	 * @return mixed
	 */
	private function do_steps( $steps ) {

		// step by step (ooh baby)
		foreach ( $steps as $i => $step ) {

			$result = $this->do_step( $step );

			if ( $result === true ) {
				continue;
			}

			return $result;

		}

		return true;
	}

	/**
	 * processes the current step
	 *
	 * @param mixed $step
	 * @return mixed
	 */
	private function do_step( $step ) {

		$this->current_step = $step['id'];

		if ( $this->max_steps && self::$total_steps >= $this->max_steps ) {
			$this->log( 'MAX STEPS REACHED' );
			$this->update( array( 'step' => $step['id'] ) );
			return false;
		}
		// we are in search mode, let's find our step
		if ( $this->is_search ) {

			// not our step
			if ( $step['id'] !== $this->args['step'] ) {

				// we need to search condtions as well
				if ( $step['type'] == 'conditions' ) {
					$result = $this->do_steps( $step['yes'] );
					if ( $this->is_search ) {
						$result = $this->do_steps( $step['no'] );
					}
					return $result;
				}

				// return true so we can search in the next step
				return true;
			}

			// got it => continue
			$this->is_search = false;
			$this->log( 'FOUND  ' . $step['id'] . ' for ' . $this->subscriber_id );

			$this->entry = $this->get_entry();

		}

		if ( isset( $step['attr']['disabled'] ) && $step['attr']['disabled'] ) {
			$this->log( 'STEP DISABLED ' . $step['id'] . ' for ' . $this->subscriber_id );

			// re-schedule on current step
			if ( $this->is_current_step( $step ) ) {
				$try_again_after = MINUTE_IN_SECONDS * 5; // TODO find reasonable timeframe
				// $try_again_after = 1;

				$this->update( array( 'timestamp' => ( time() + $try_again_after ) ) );
				return false;
			}

			return true;
		}

		++self::$total_steps;

		switch ( $step['type'] ) {
			case 'trigger':
				return $this->trigger( $step );
			break;

			case 'action':
				$result = $this->action( $step );

				// try again wuth logic of retry action
				if ( is_wp_error( $result ) ) {

					$tries = (int) $this->entry->try;
					++$tries;
					$error_msg = $result->get_error_message();
					$max_tries = 10;

					// Stop after more tries
					if ( $tries > $max_tries ) {

						$error = new WP_Error( 'error', sprintf( __( 'Action failed with %1$s after %2$d tries. Workflow has been finished.', 'mailster' ), '"' . $error_msg . '"', $tries ), $step );
						// finish with error
						$this->finish( array( 'error' => $error_msg ) );

						return $error;
					}

					$try_again_after = 60 * $tries + 60;
					$try_again_after = 6;

					$error = new WP_Error( 'warning', sprintf( __( 'Action failed with %1$s', 'mailster' ), '"' . $error_msg . '"', $tries ), $step );
					$this->error_notice( $error, 'workflow_error_action_' . $step['id'] . '_' . $this->subscriber_id );

					$this->update(
						array(
							'timestamp' => time() + $try_again_after,
							'error'     => $error_msg,
							'try'       => $tries,
						)
					);

					// return false to not go to the next step
					return false;

				}

				return $result;
			break;

			case 'email':
				$result = $this->email( $step );

				// try again
				if ( is_wp_error( $result ) ) {
					$this->update(
						array(
							'timestamp' => time() + 60,
							'error'     => $result->get_error_message(),
						)
					);
				}

				return $result;
			break;

			case 'jumper':
				return $this->jumper( $step );
			break;

			case 'notification':
				return $this->notification( $step );
			break;

			case 'stop':
				return $this->stop( $step );
			break;

			case 'delay':
				return $this->delay( $step );
			break;

			case 'conditions':
				return $this->conditions( $step );
			break;
		}

		return true;
	}

	/**
	 * Run the action step
	 *
	 * @param array $step
	 * @return WP_Error|true|false
	 */
	private function action( array $step ) {

		$attr = isset( $step['attr'] ) ? $step['attr'] : array();

		$action = isset( $step['attr']['action'] ) ? $step['attr']['action'] : null;

		if ( ! $action ) {
			return new WP_Error( 'info', 'No Action for this step . ', $step );
		}

		$this->log( 'ACTION ' . $step['attr']['action'] . ' ' . $step['id'] . ' for ' . $this->subscriber_id );

		switch ( $action ) {
			case 'nothing':
				$this->log( 'nothing' );
				break;

			case 'update_field':
				$this->log( 'update_field' );
				$remove_old = false;
				$field      = isset( $attr['field'] ) ? $attr['field'] : null;
				$value      = isset( $attr['value'] ) ? $attr['value'] : '';
				if ( $field ) {

					// special case for date fields
					$datefields = mailster()->get_custom_date_fields( true );

					if ( in_array( $field, $datefields ) ) {

						if ( is_numeric( $value ) ) {
							if ( $value == 0 ) {
								$value = date( 'Y-m-d' );
							} else {

								// relative date so we ned the current one
								$fields = mailster( 'subscribers' )->get_custom_fields( $this->subscriber_id );

								if ( ! isset( $fields[ $field ] ) ) {
									return true;
								}
								// stop if no initial value is set
								if ( empty( $fields[ $field ] ) ) {
									return true;
								}

								// to the current add the offset (maybe negative)
								$value = date( 'Y-m-d', strtotime( $fields[ $field ] ) + ( $value * DAY_IN_SECONDS ) );

							}
						} elseif ( $value ) {
							// some sanitizations
							$value = date( 'Y-m-d', strtotime( $value ) );
						} else {
							$value = '';
						}
					}

					if ( $value !== '' ) {
						mailster( 'subscribers' )->add_custom_field( $this->subscriber_id, $field, $value );

					} else {
						mailster( 'subscribers' )->remove_custom_field( $this->subscriber_id, $field );
					}
				}
				break;

			case 'add_list':
				$this->log( 'add_list' );
				if ( isset( $attr['lists'] ) ) {
					$remove_old  = false;
					$doubleoptin = isset( $attr['doubleoptin'] ) && $attr['doubleoptin'];
					mailster( 'lists' )->assign_subscribers( $attr['lists'], $this->subscriber_id, $remove_old, ! $doubleoptin );
				}
				break;

			case 'remove_list':
				$this->log( 'remove_list' );
				if ( isset( $attr['lists'] ) ) {
					mailster( 'lists' )->unassign_subscribers( $attr['lists'], $this->subscriber_id );
				}
				break;

			case 'add_tag':
				$this->log( 'add_tag' );
				if ( isset( $attr['tags'] ) ) {
					mailster( 'tags' )->assign_subscribers( $attr['tags'], $this->subscriber_id );
				}

				break;

			case 'remove_tag':
				$this->log( 'remove_tag' );
				if ( isset( $attr['tags'] ) ) {
					mailster( 'tags' )->unassign_subscribers( $attr['tags'], $this->subscriber_id );
				}
				break;

			case 'unsubscribe':
				$this->log( 'unsubscribe' );

				mailster( 'subscribers' )->unsubscribe( $this->subscriber_id, $this->workflow->ID, 'UNSUBSCRIBED FROM WORKFLOW' );
				break;

			case 'webhook':
				return $this->webhook( $step );
				break;

			default:
				return new WP_Error( 'info', 'Invalid action', $step );
				break;
		}

		return true;
	}


	/**
	 * Run the webhook action
	 *
	 * @param mixed $step
	 * @return WP_Error|true
	 */
	private function webhook( $step ) {

		$url = isset( $step['attr']['webhook'] ) ? $step['attr']['webhook'] : null;

		if ( ! $url ) {
			return new WP_Error( 'error', 'No Webhook defined', $step );
		}
		$subscriber = mailster( 'subscribers' )->get( $this->subscriber_id, true );

		$data = array(
			'workflow'   => array(
				'id'        => $this->workflow->ID,
				'step'      => $step['attr']['id'],
				'name'      => $this->workflow->post_title,
				'trigger'   => $this->entry->trigger,
				'added'     => $this->entry->added,
				'timestamp' => $this->entry->timestamp,
				'try'       => $this->entry->try,
			),
			'subscriber' => $subscriber,
		);

		$args = array(
			'timeout'    => 5,
			'headers'    => array(
				'content-type' => 'application/json',
			),
			'user-agent' => 'Mailster/' . MAILSTER_VERSION,
			'method'     => 'POST',
			'body'       => json_encode( $data ),
		);

		$response = wp_remote_request( $url, $args );

		if ( $this->entry->try > 3 ) {
			$this->log( 'MAX TRIES REACHED' );
			return true;
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		// $body     = wp_remote_retrieve_body( $response );

		// if the webhook failed try again after 5 minutes and stop the workflow after 3 tries
		if ( $code !== 200 ) {

			$error = get_status_header_desc( $code );
			return new WP_Error( 'error', $error, $step );

		}

		return true;
	}



	/**
	 * Handle trigger
	 *
	 * @param mixed $step
	 * @return bool|WP_Error
	 */
	private function trigger( $step ) {

		// no => try next
		if ( $step['attr']['trigger'] !== $this->trigger ) {
			return true;
		}

		// check how often we can run this trigger
		$repeat = isset( $step['attr']['repeat'] ) ? $step['attr']['repeat'] : 1;

		// repeat is not unlimited so we check the limit before we add an entry to the database
		if ( $repeat !== -1 ) {
			if ( $this->limit_reached( $repeat ) ) {
				$this->log( 'LIMIT REACHED' );
				return false;
			} else {
				$this->log( 'LIMIT NOT REACHED' );
			}
		}
		$allow_pending = isset( $step['attr']['pending'] ) ? (bool) $step['attr']['pending'] : false;

		// check if we should do pending subscribers
		if ( $this->subscriber ) {
			$is_pending = $this->subscriber->status === 0;
			// we check for pending subscribers and we are not pending
			if ( ! $allow_pending && $is_pending ) {
				$this->log( 'NO PENDING SUBSCRIBER!' );
				return false;
			}
		}

		// check for conditions
		$conditions = isset( $step['attr']['conditions'] ) ? $step['attr']['conditions'] : array();

		if ( $conditions ) {
			$conditions = $this->sanitize_conditions( $conditions );

			if ( $this->subscriber_id && ! mailster( 'conditions' )->check( $conditions, $this->subscriber_id ) ) {
				$this->log( 'CONDITION NOT PASSED! Entry deleted' );
				$this->delete();
				return false;
			}
		}

		// load existing entry
		$this->entry = $this->get_entry();

		// add if missing
		if ( ! $this->entry ) {
			$this->log( 'ADD TO DATABASE' );
			$this->entry = $this->add();

			// stop if existing didn't finished
		} elseif ( ! $this->entry->finished ) {
			$this->log( 'ENTRY FOUND!' );

			// if found entry is at the trigger we can continue
			if ( $this->entry->step == $step['id'] ) {
				$this->log( 'CONTINUE' );

				// Stop if the entry is not finished and not with these triggers
			} elseif ( ! in_array( $this->trigger, array( 'date', 'anniversary', 'published_post' ) ) ) {
				return false;
			}
		}

		$this->log( 'use TRIGGER ' . $this->trigger );

		// check if user is subscribed
		if ( $this->subscriber && $is_pending ) {
			$this->log( 'SUBSCRIBER NOT SUBSCRIBED ' . $this->subscriber->status );

			$try_again_after = MINUTE_IN_SECONDS * 5; // TODO find reasonable timeframe
			// $try_again_after = 1;

			$this->update( array( 'timestamp' => time() + $try_again_after ) );

			return false;
		}

		switch ( $this->trigger ) {
			case 'date':
			case 'anniversary':
				$timestamp = isset( $step['attr']['date'] ) ? strtotime( $step['attr']['date'] ) : null;

				// if this is not defined we get all based on the condtion
				if ( ! $this->subscriber_id ) {
					if ( ! $timestamp ) {
						$this->delete();
						return false;
					}

					$query_args = array(
						'return_ids' => true,
						'conditions' => $conditions,
					);

					$field = isset( $step['attr']['field'] ) ? $step['attr']['field'] : null;

					// handle custom field options
					if ( $field ) {
						// $query_args['return_sql'] = true;

						// get timestamp for the defined time of today
						$timestamp = strtotime( 'today ' . date( 'H:i', $timestamp ) );

						if ( $this->trigger === 'anniversary' ) {
							$cond = array(
								'field'    => $field,
								'operator' => 'end_with',
								'value'    => date( '-m-d' ),
							);
						} else {
							$cond = array(
								'field'    => $field,
								'operator' => 'is',
								'value'    => date( 'Y-m-d' ),
							);
						}

						// for anniversary get all with the field on today, otherwise exactly today
						$value = $this->trigger == 'anniversary' ? '-m-d' : 'Y-m-d';

						// add the date field as AND condition
						$query_args['conditions'][] = array( $cond );

						// not in the future
						$query_args['conditions'][] = array(
							array(
								'field'    => $field,
								'operator' => 'is_smaller_equal',
								'value'    => date( 'Y-m-d' ),
							),
						);

						// $query_args['return_sql'] = true;

					}

					$step_id = isset( $step['attr']['id'] ) ? $step['attr']['id'] : null;
					$step_id = null;

					if ( isset( $step['attr']['pending'] ) && $step['attr']['pending'] ) {
						// allow pending subscribers to be added
						$query_args['status'] = array( 0, 1 );
					}

					$subscriber_ids = mailster( 'subscribers' )->query( $query_args );

					if ( ! empty( $subscriber_ids ) ) {
						mailster( 'triggers' )->bulk_add( $this->workflow->ID, $this->trigger, $subscriber_ids, $step_id, $timestamp );
					}
					// delete our temp entry
					$this->delete();
					return false;
				}

				// round it down to second 00
				$timestamp = strtotime( date( 'Y-m-d H:i', $timestamp ) );

				if ( time() < $timestamp ) {
					$this->log( 'TIMESTAMP NOT REACHED' );
					return false;
				}

				break;
			case 'published_post':
			case 'hook':
				// if this is not defined we get all based on the condtion
				if ( ! $this->subscriber_id ) {

					$query_args = array(
						'return_ids' => true,
						'conditions' => $conditions,
					);

					$step_id   = isset( $step['attr']['id'] ) ? $step['attr']['id'] : null;
					$timestamp = time();

					$subscriber_ids = mailster( 'subscribers' )->query( $query_args );

					$context = $this->entry->context;

					if ( ! empty( $subscriber_ids ) ) {
						mailster( 'triggers' )->bulk_add( $this->workflow->ID, $this->trigger, $subscriber_ids, null, $timestamp, $context );
					}
					$this->delete();
					return false;

				}

				break;

			default:
				break;
		}

		// everything is prepared and we can move on
		return true;
	}

	private function email( $step ) {

		// TODO invalid step can cause email to get stuck
		if ( ! isset( $step['attr']['campaign'] ) ) {
			return new WP_Error( 'error', 'Step is incomplete', $step );
		}

		if ( ! $campaign = mailster( 'campaigns' )->get( $step['attr']['campaign'] ) ) {
			return new WP_Error( 'error', 'Step is incomplete', $step );
		}

		// skip that if it's the current step and a timestamp is defined
		if ( $this->is_current_step( $step ) ) {

			$this->log( 'SKIP AS ITS CURRENT' );

			$has_been_sent = mailster( 'actions' )->get_by_subscriber( $this->subscriber_id, 'sent', $campaign->ID );
			return (bool) $has_been_sent;

			// step done => continue
			return true;
		}

		$this->args['step'] = $step['id'];
		$this->log( 'EMAIL ' . $step['id'] . ' for ' . $this->subscriber_id );

		// use the timestamp from the step for correct queueing
		$timestamp = $this->entry && $this->entry->timestamp ? $this->entry->timestamp : time();

		$tags = array();
		if ( isset( $step['attr']['subject'] ) ) {
			$tags['subject'] = $step['attr']['subject'];
		}
		if ( isset( $step['attr']['preheader'] ) ) {
			$tags['preheader'] = $step['attr']['preheader'];
		}
		if ( isset( $step['attr']['from_name'] ) ) {
			$tags['from_name'] = $step['attr']['from_name'];
		}
		if ( isset( $step['attr']['from_email'] ) ) {
			$tags['from_email'] = $step['attr']['from_email'];
		}

		$args = array(
			'campaign_id'   => $campaign->ID,
			'subscriber_id' => $this->subscriber_id,
			'priority'      => 15,
			'timestamp'     => $timestamp,
			'ignore_status' => false,
			'options'       => false,
			'tags'          => $tags,
		);

		// TODO: send via queue or directly
		$queue = true;

		// if timestamp is in the future
		if ( $timestamp > time() ) {
			$queue = true;
		}

		if ( $queue ) {
			if ( mailster( 'queue' )->add( $args ) ) {
				$this->update( array( 'timestamp' => $timestamp ) );
			}

			return false;
		}

		$track       = null;
		$force       = false;
		$log         = true;
		$attachments = array();

		// TODO: make sure the user is subscribed!
		$result = mailster( 'campaigns' )->send( $args['campaign_id'], $args['subscriber_id'], $track, $force, $log, $args['tags'], $attachments );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;

		// TODO check when step is inclomplete and the campaigns hasn't been sent already

		// only continue with the next step if campaign has been sent
		$has_been_sent = mailster( 'actions' )->get_by_subscriber( $this->subscriber_id, 'sent', $campaign->ID );

		return (bool) $has_been_sent;
	}

	private function jumper( $step ) {

		$this->log( 'JUMPER ' . $step['attr']['step'] . ' for ' . $this->subscriber_id );

		// if conditions are present only jump if they are met
		if ( isset( $step['attr']['conditions'] ) ) {
			$conditions = $this->sanitize_conditions( $step['attr']['conditions'] );

			if ( ! mailster( 'conditions' )->check( $conditions, $this->subscriber_id ) ) {

				$this->log( 'CONDITION NOT PASSED ' . $step['id'] . ' for ' . $this->subscriber_id );

				// return true to execute the next step
				return true;

			}
		}

		// move to the new step
		$this->update(
			array(
				'step'      => $step['attr']['step'],
				'timestamp' => null, // needs to be NULL otherwise delay steps will get triggered on that timestamp
			)
		);

		// since we are jumping we need to re-schedule the workflow
		mailster( 'automations' )->wp_schedule(
			array(
				'workflow_id'   => $this->workflow->ID,
				'step'          => $step['attr']['step'],
				'subscriber_id' => $this->subscriber_id,
			)
		);

		// return false to exist the queue
		return false;
	}



	private function notification( $step ) {

		$this->log( 'notification ' . $step['attr']['email'] . ' for ' . $this->subscriber_id );

		if ( ! isset( $step['attr']['email'] ) ) {
			return new WP_Error( 'error', 'No email defined', $step );
		}

		$receiver = $step['attr']['email'];
		$subject  = esc_html( $step['attr']['subject'] );
		$message  = nl2br( esc_html( $step['attr']['message'] ) );

		$link         = admin_url( 'post.php?post=' . $this->workflow->ID . '&action=edit#step-' . $step['id'] );
		$notification = sprintf( esc_html__( 'This email was triggered by workflow %s', 'mailster' ), '<a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $this->workflow->ID ) ) . '</a>' );

		$userdata = mailster( 'subscribers' )->get( $this->subscriber_id, true );

		$n = mailster( 'notification' );
		$n->replace( (array) $userdata, true );
		$n->replace(
			array(
				'notification' => $notification,
				'can-spam'     => $notification,
			),
			true
		);
		$n->to( $receiver );
		$n->subject( $subject );
		$n->message( $message );
		$n->requeue( false );
		return $n->add();
	}

	private function stop( $step ) {

		$this->log( 'STOP ' . $step['id'] . ' for ' . $this->subscriber_id );
		$this->finish();

		// return false to not execute the next step
		return false;
	}

	private function delay( $step ) {

		// skip that if it's the current step and a timestamp is defined
		if ( $this->is_current_step( $step ) ) {

			$this->log( 'SKIP AS ITS CURRENT' );
			// step done => continue
			return true;
		}

		$this->args['step'] = $step['id'];

		$amount     = $step['attr']['amount'];
		$unit       = $step['attr']['unit'];
		$timeoffset = 0;
		$date       = 0;

		if ( isset( $step['attr']['date'] ) ) {
			$date = strtotime( $step['attr']['date'] );
			if ( isset( $step['attr']['timezone'] ) && $step['attr']['timezone'] ) {
				$user_timeoffset = mailster( 'subscribers' )->meta( $this->subscriber_id, 'timeoffset' );

				// timeoffset must be defined
				if ( ! is_null( $user_timeoffset ) ) {
					// add the sites timeoffset
					$timeoffset += mailster( 'helper' )->gmt_offset() * HOUR_IN_SECONDS;
					// remove the users timeoffset
					$timeoffset -= $user_timeoffset * HOUR_IN_SECONDS;
				}
			}
		}

		switch ( $unit ) {
			case 'minutes':
			case 'hours':
			case 'days':
			case 'weeks':
			case 'months':
				$timestamp = strtotime( '+' . $amount . ' ' . $unit );
				break;

			case 'day':
				// get the timestamp for the time of the day
				$timestamp = strtotime( date( 'Y-m-d ' . date( 'H:i', $date ) ) );

				// add timeoffset if set (for timezone based sending)
				$timestamp += $timeoffset;

				// time is in the past so postpone it for 24 hours
				if ( $timestamp < time() ) {
					$timestamp = mailster( 'helper' )->get_next_date_in_future( $timestamp, 1, 'day', array(), true );
				}

				break;

			case 'week':
				if ( ! isset( $step['attr']['weekdays'] ) ) {
					return new WP_Error( 'error', 'No weekdays defined!', $step );
				}

				$weekdays = $step['attr']['weekdays'];

				// get the timestamp for the time of the day
				$timestamp = strtotime( date( 'Y-m-d ' . date( 'H:i', $date ) ) );

				// add timeoffset if set (for timezone based sending)
				$timestamp += $timeoffset;

				// time is in the past so postpone it for at least 24 hours (check weekdays)
				if ( $timestamp < time() ) {
					$timestamp = mailster( 'helper' )->get_next_date_in_future( $timestamp, 1, 'day', $weekdays, true );

				} else {
					// today in in the list of weekdays
					if ( empty( $weekdays ) || in_array( date( 'w' ), $weekdays ) ) {
						$timestamp = $timestamp;
					} else {
						$timestamp = mailster( 'helper' )->get_next_date_in_future( $timestamp, 1, 'day', $weekdays, false );
					}
				}

				break;

			case 'month':
				if ( ! isset( $step['attr']['month'] ) ) {
					return new WP_Error( 'error', 'No month defined!', $step );
				}

				$month = $step['attr']['month'];

				if ( $month === -1 ) { // last day of the month
					// t returns the number of days in the month of a given date
					$timestamp = strtotime( date( 'Y-m-t ' . date( 'H:i', $date ) ) );

				} else {

					$timestamp = strtotime( date( 'Y-m-' . $month . ' ' . date( 'H:i', $date ) ) );

					// check if the current month has this day
					if ( $month > 28 ) {

						// get last day of the month
						$last = strtotime( date( 'Y-m-t ' . date( 'H:i', $date ) ) );

						if ( $timestamp != $last ) {
							// the last day of the current month + our days
							$timestamp = $last + ( $month * DAY_IN_SECONDS );
						}
					}
				}

				// add timeoffset if set (for timezone based sending)
				$timestamp += $timeoffset;

				// timestamp is in the past
				if ( $timestamp < time() ) {
					$weekdays  = array(); // no support for that
					$timestamp = mailster( 'helper' )->get_next_date_in_future( $timestamp, 1, 'month', $weekdays, false );
				}
				break;

			case 'year':
				// remove seconds from our date
				$timestamp = strtotime( date( 'Y-m-d H:i', $date ) );

				// add timeoffset if set (for timezone based sending)
				$timestamp += $timeoffset;

				// timestamp is in the past
				if ( $timestamp < time() ) {
					return new WP_Error( 'error', 'Date of step is in the past.', $step );
				}
				break;

			default:
				return new WP_Error( 'error', 'No matching delay option found.', $step );
			break;
		}

		// TODO: maybe set timestamp to now if we're in a "Testing mode"
		// $timestamp = time();

		// no need to schedule if in the past
		if ( $timestamp <= time() ) {
			$this->log( 'SKIP DELAY' );
			return true;
		}
		$this->update( array( 'timestamp' => $timestamp ) );

		$this->log( 'SCHEDULE DELAY ' . $step['id'] . ' for ' . human_time_diff( $timestamp ) );

		// return false to stop the queue from processing
		return false;
	}


	private function conditions( $step ) {

		if ( ! isset( $step['attr']['conditions'] ) ) {
			return new WP_Error( 'missing_arg', 'Condition missing', $step );
		}

		$conditions = $this->sanitize_conditions( $step['attr']['conditions'] );

		if ( mailster( 'conditions' )->check( $conditions, $this->subscriber_id ) ) {
			$use = $step['yes'];
			$this->log( 'CONDITION PASSED ' . $step['id'] . ' for ' . $this->subscriber_id );
		} else {
			$use = $step['no'];
			$this->log( 'CONDITION NOT PASSED ' . $step['id'] . ' for ' . $this->subscriber_id );
		}

		return $this->do_steps( $use );
	}

	private function sanitize_conditions( $conditions ) {

		wp_parse_str( $conditions, $params );
		$conditions = $params['conditions'];

		// replace the step id with the actual campaing id to get the correct condition
		// TOTO optimze this
		foreach ( $conditions as $i => $condition_group ) {
			foreach ( $condition_group as $j => $condition ) {
				if ( ! is_array( $condition['value'] ) && isset( $this->steps_map[ $condition['value'] ] ) ) {
					$from_map                        = $this->steps_map[ $condition['value'] ];
					$conditions[ $i ][ $j ]['value'] = $from_map['campaign'] ? $from_map['campaign'] : null;
				}
			}
		}

		return $conditions;
	}

	private function finish( array $args = array() ) {
		$this->log( 'FINISHED' );

		$args = wp_parse_args(
			$args,
			array(
				'finished'  => time(),
				'step'      => null,
				'timestamp' => null,
				'error'     => '',
			)
		);

		$this->update( $args );
	}


	private function is_current_step( $step ) {
		return $step['id'] === $this->args['step'] && $this->entry && $this->entry->timestamp;
	}


	private function log( $str ) {
		if ( WP_DEBUG ) {
			error_log( print_r( $str, true ) );
		}
	}
}
