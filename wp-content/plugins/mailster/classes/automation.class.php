<?php

class MailsterAutomations {

	private $jobs = array();

	public function __construct() {

		// since 5.8
		if ( ! function_exists( 'get_allowed_block_types' ) ) {
			return;
		}

		add_action( 'init', array( &$this, 'register_post_type' ) );
		add_action( 'init', array( &$this, 'register_post_meta' ) );
		add_action( 'init', array( &$this, 'block_init' ) );
		add_action( 'rest_api_init', array( &$this, 'register_block_patterns' ) );
		add_action( 'rest_api_init', array( &$this, 'rest_api_init' ) );
		add_action( 'rest_api_init', array( &$this, 'register_conditions_block' ) );

		add_action( 'admin_print_scripts-edit.php', array( &$this, 'overview_script_styles' ), 1 );

		add_action( 'enqueue_block_assets', array( &$this, 'block_script_styles' ), 1 );

		add_filter( 'allowed_block_types_all', array( &$this, 'allowed_block_types' ), PHP_INT_MAX, 2 );
		add_filter( 'block_editor_settings_all', array( &$this, 'block_editor_settings' ), PHP_INT_MAX, 2 );
		add_filter( 'block_categories_all', array( &$this, 'block_categories' ) );
		add_filter( 'use_block_editor_for_post_type', array( &$this, 'force_block_editor' ), PHP_INT_MAX, 2 );

		add_filter( 'manage_mailster-workflow_posts_columns', array( &$this, 'columns' ), 1 );
		add_action( 'manage_mailster-workflow_posts_custom_column', array( &$this, 'columns_content' ), 10, 2 );
		add_filter( 'quick_edit_enabled_for_post_type', array( &$this, 'quick_edit_enabled_for_post_type' ), 10, 3 );
		add_filter( 'wp_list_table_class_name', array( &$this, 'wp_list_table_class_name' ), 10, 2 );

		add_action( 'mailster_cron_workflow', array( &$this, 'wp_schedule' ) );

		add_action( 'mailster_workflow', array( &$this, '_run_delayed_workflow' ), 10, 3 );

		add_action( 'wp_after_insert_post', array( &$this, 'save_workflow' ), 10, 4 );

		add_action( 'publish_mailster-workflow', array( &$this, 'limit_posts' ), 10, 3 );

		add_action( 'classic_editor_plugin_settings', array( &$this, 'enable_on_classic_editor' ) );
		add_filter( 'display_post_states', array( &$this, 'display_post_states' ), 10, 2 );

		add_shortcode( 'newsletter_block_form', array( &$this, 'block_forms_shortcode' ) );

		add_action( 'admin_init', array( &$this, 'edit_hook' ) );
		add_filter( 'post_row_actions', array( &$this, 'quick_edit_btns' ), 10, 2 );

		add_action( 'after_delete_post', array( &$this, 'after_delete_post' ), 10, 2 );

		add_action( 'mailster_unsubscribe', array( &$this, 'on_unsubscribe' ), 10, 4 );
	}


	public function enable_on_classic_editor( $settings ) {
		if ( ! $settings && isset( $_GET['post'] ) && get_post_type( (int) $_GET['post'] ) === 'mailster-workflow' ) {
			$settings = array( 'editor' => 'block' );
		}
		if ( ! $settings && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'mailster-workflow' ) {
			$settings = array( 'editor' => 'block' );
		}

		return $settings;
	}

	public function get_actions() {

		$actions = array(
			array(
				'id'    => 'update_field',
				'icon'  => 'replace',
				'label' => __( 'Update Custom Field', 'mailster' ),
				'info'  => __( 'Update subscribers field with custom value.', 'mailster' ),
			),
			array(
				'id'    => 'add_list',
				'icon'  => 'list',
				'label' => __( 'Add to List(s)', 'mailster' ),
				'info'  => __( 'Adds subscribers to one or more lists.', 'mailster' ),
			),
			array(
				'id'    => 'remove_list',
				'icon'  => 'list',
				'label' => __( 'Remove from List(s)', 'mailster' ),
				'info'  => __( 'Removes subscribers from one or more lists.', 'mailster' ),
			),
			array(
				'id'    => 'add_tag',
				'icon'  => 'tag',
				'label' => __( 'Add Tag(s)', 'mailster' ),
				'info'  => __( 'Adds one ore more tags to a subscriber.', 'mailster' ),
			),
			array(
				'id'    => 'remove_tag',
				'icon'  => 'tag',
				'label' => __( 'Remove Tag(s)', 'mailster' ),
				'info'  => __( 'Removes one or more tags from a subscriber.', 'mailster' ),
			),
			array(
				'id'    => 'unsubscribe',
				'icon'  => 'external',
				'label' => __( 'Unsubscribe', 'mailster' ),
				'info'  => __( 'Unsubscribes a subscriber.', 'mailster' ),
			),
			array(
				'id'    => 'webhook',
				'icon'  => 'cog',
				'label' => __( 'Webhook', 'mailster' ),
				'info'  => __( 'Triggers a webhook.', 'mailster' ),
			),
		);

		return apply_filters( 'mailster_workflow_actions', $actions );
	}



	public function get_triggers() {

		$triggers = array(
			array(
				'id'    => 'list_add',
				'icon'  => 'formatListBullets',
				'label' => __( 'Subscriber added to list', 'mailster' ),
				'info'  => __( 'When a subscriber joins a list', 'mailster' ),
			),
			array(
				'id'    => 'list_removed',
				'icon'  => 'formatListBullets',
				'label' => __( 'Subscriber removed from a list', 'mailster' ),
				'info'  => __( 'When a subscriber is removed from a list', 'mailster' ),
			),
			array(
				'id'    => 'tag_added',
				'icon'  => 'tag',
				'label' => __( 'Tag added', 'mailster' ),
				'info'  => __( 'When a Tag is added to a subscriber', 'mailster' ),
			),
			array(
				'id'    => 'tag_removed',
				'icon'  => 'tag',
				'label' => __( 'Tag removed', 'mailster' ),
				'info'  => __( 'When a Tag is removed from a subscriber', 'mailster' ),
			),
			array(
				'id'    => 'updated_field',
				'icon'  => 'update',
				'label' => __( 'Field updated', 'mailster' ),
				'info'  => __( 'When a Field is added or updated by a subscriber', 'mailster' ),
			),
			array(
				'id'    => 'form_conversion',
				'icon'  => 'commentEditLink',
				'label' => __( 'Form Conversion', 'mailster' ),
				'info'  => __( 'When someone fills out and submits a form', 'mailster' ),
			),
			array(
				'id'    => 'date',
				'icon'  => 'calendar',
				'label' => __( 'Specific date', 'mailster' ),
				'info'  => __( 'On a specific date', 'mailster' ),
			),
			array(
				'id'    => 'anniversary',
				'icon'  => 'calendar',
				'label' => __( 'Anniversary', 'mailster' ),
				'info'  => __( 'On an anniversary of a date', 'mailster' ),
			),
			array(
				'id'    => 'link_click',
				'icon'  => 'link',
				'label' => __( 'Click a link', 'mailster' ),
				'info'  => __( 'When a subscriber clicks a link', 'mailster' ),
			),
			array(
				'id'    => 'page_visit',
				'icon'  => 'page',
				'label' => __( 'Visits a page', 'mailster' ),
				'info'  => __( 'When a user visits a given page', 'mailster' ),
			),
			array(
				'id'    => 'hook',
				'icon'  => 'shortcode',
				'label' => __( 'Custom Hook', 'mailster' ),
				'info'  => __( 'When a custom hook is called', 'mailster' ),
			),
			array(
				'id'       => 'opened_campaign',
				'icon'     => 'key',
				'label'    => __( 'Open a campaign', 'mailster' ),
				'info'     => __( 'When a users opens a campaign', 'mailster' ),
				'disabled' => true,
				'reason'   => __( 'Comming soon!', 'mailster' ),
			),
		);

		return apply_filters( 'mailster_workflow_triggers', $triggers );
	}


	public function get_limit() {

		$limit = 3;
		if ( mailster_freemius()->is_plan( 'professional', true ) ) {
			$limit = 10;
		} elseif ( mailster_freemius()->is_plan( 'agency', true ) ) {
			$limit = false;
		}

		return $limit;
	}



	public function limit_reached() {

		$limit = $this->get_limit();

		if ( ! $limit ) {
			return false;
		}

		global $wpdb;

		$query = "SELECT COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s";

		$count = $wpdb->get_var( $wpdb->prepare( $query, 'mailster-workflow', 'publish' ) );

		if ( $count <= $limit ) {
			return false;
		}

		return true;
	}


	public function limit_posts( $post_id, $post, $old_status ) {

		if ( $this->limit_reached() ) {
			$post = array(
				'ID'          => $post_id,
				'post_status' => 'private',
			);
			wp_update_post( $post );

			$agency_plan = mailster()->get_plan_by_name( 'agency' );

			$args = array(
				'utm_medium' => 'workflow_limit_reached',
				'plan_id'    => $agency_plan ? $agency_plan->id : null,
			);

			$checkout_url = add_query_arg( $args, mailster_freemius()->checkout_url() );
			$pricing_url  = add_query_arg( $args, mailster_freemius()->pricing_url() );

			$msg  = '<h2>' . sprintf( esc_html__( 'Workflow limit reached!', 'mailster' ) ) . '</h2>';
			$msg .= '<p>' . sprintf( esc_html__( 'You have reached the maximum number of %1$d active workflows! Please upgrade %2$s to enable more workflows.', 'mailster' ), $this->get_limit(), '<a href="' . esc_url( $checkout_url ) . '">' . esc_html__( 'your plan', 'mailster' ) . '</a>' ) . '</p>';
			$msg .= '<p><a href="' . esc_url( $checkout_url ) . '" class="button button-primary">' . esc_html__( 'Upgrade now', 'mailster' ) . '</a> <a href="' . esc_url( $pricing_url ) . '" class="button button-secondary">' . esc_html__( 'Compare Plans', 'mailster' ) . '</a></p>';
			mailster_notice( $msg, 'warning', false, 'mailster-workflow-limit-reached', true );

		} else {
			mailster_remove_notice( 'mailster-workflow-limit-reached' );
		}
	}


	public function do_workflows( $workflow_ids, $trigger, $subscriber ) {
		foreach ( (array) $workflow_ids as $workflow_id ) {
			$this->do_workflow( $workflow_id, $trigger, $subscriber );
		}
	}

	public function do_workflow( $workflow_id, $trigger, $subscriber ) {

		return do_action( 'mailster_trigger', $workflow_id, $trigger, $subscriber );
	}

	public function save_workflow( $workflow_id, $post, $update, $post_before ) {

		if ( get_post_type( $post ) !== 'mailster-workflow' ) {
			return;
		}

		// not on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		mailster_remove_notice( 'workflow_error_' . $workflow_id );

		// get all triggers
		$triggers = $this->get_workflow_triggers( $workflow_id );

		if ( in_array( 'anniversary', $triggers ) ) {
			// delete from current pending entries so we can build them up again.
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}mailster_workflows WHERE workflow_id = %d AND `trigger` = 'anniversary' AND (`timestamp` < %d) AND `step` IS NULL", $workflow_id, time() + HOUR_IN_SECONDS ) );

			do_action( 'mailster_trigger_anniversary' );
		}
		if ( in_array( 'date', $triggers ) ) {
			do_action( 'mailster_trigger_date' );
		}

		$this->update_trigger_option();

		// cleanup of deleted steps
		$this->remove_missing_steps( $workflow_id );

		// run it once
		$this->wp_schedule( array( 'workflow_id' => $workflow_id ) );
	}


	public function get_trigger_option( $workflow, $trigger ) {

		$workflow = get_post( $workflow );
		$blocks   = parse_blocks( $workflow->post_content );

		$options = array();

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] !== 'mailster-workflow/triggers' ) {
				continue;
			}

			foreach ( $block['innerBlocks'] as $innerBlock ) {
				if ( empty( $innerBlock ) ) {
					continue;
				}
				// not a trigger
				if ( ! isset( $innerBlock['attrs']['trigger'] ) ) {
					continue;
				}
				// not the right trigger
				if ( $innerBlock['attrs']['trigger'] !== $trigger ) {
					continue;
				}
				// it is disabled
				if ( isset( $innerBlock['attrs']['disabled'] ) && $innerBlock['attrs']['disabled'] ) {
					continue;
				}

				// add this since it's required since 4.1
				if ( ! isset( $innerBlock['attrs']['id'] ) ) {
					$innerBlock['attrs']['id'] = null;
				}

				$options[] = $innerBlock['attrs'];

			}
		}

		return $options;
	}


	public function update_trigger_option() {

		global $wpdb;

		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'mailster-workflow' AND post_content LIKE '%s'";

		$sql = $wpdb->prepare( $sql, '%"trigger":"page_visit"%' );

		$workflow_ids = $wpdb->get_col( $sql );

		$store = array();

		foreach ( $workflow_ids as $workflow_id ) {

			$options = $this->get_trigger_option( $workflow_id, 'page_visit' );

			foreach ( $options as $option ) {
				if ( ! isset( $option['pages'] ) ) {
					continue;
				}
				foreach ( $option['pages'] as $page ) {
					$page = '/' . trim( $page, '/' );
					if ( ! isset( $store[ $page ] ) ) {
						$store[ $page ] = array();
					}
					$store[ $page ][] = $workflow_id;
					$store[ $page ]   = array_values( array_unique( $store[ $page ] ) );
				}
			}
		}

		// save this as it's faster to check
		update_option( 'mailster_trigger', $store );
	}

	/**
	 * Remove workflow entries where the step is no longer in the workflow
	 *
	 * @access public
	 * @return void
	 */
	public function remove_missing_steps( $workflow_id ) {

		$workflow = get_post( $workflow_id );

		if ( ! $workflow ) {
			return;
		}

		// get all step Ids from this workflow
		if ( ! preg_match_all( '/"id":"(.*?)"/', $workflow->post_content, $matches ) ) {
			return;
		}

		// ids are string so we need to escape them
		$existing_ids = array_map( 'esc_sql', $matches[1] );

		global $wpdb;

		// only keep the ones which are in the workflow
		$sql = "DELETE FROM {$wpdb->prefix}mailster_workflows WHERE workflow_id = %d AND step IS NOT NULL AND step NOT IN ('" . implode( "','", $existing_ids ) . "')";

		return false !== $wpdb->query( $wpdb->prepare( $sql, $workflow_id ) );
	}

	/**
	 * Find all initialed workflows and starte them if they are due
	 *
	 * @access public
	 * @return void
	 */
	public function wp_schedule( $where = array() ) {

		global $wpdb;

		// < 4.1.0 $where was the $order_id
		if ( ! is_array( $where ) ) {
			$where = array( 'id' => $where );
		}

		// time to schedule upfront in seconds
		$queue_upfront = HOUR_IN_SECONDS;

		// limit to not overload the WP cron
		$limit = 5000;

		$now = time();

		$sql = "SELECT workflow_id, `trigger`, step, IFNULL(`timestamp`, %d) AS timestamp FROM {$wpdb->prefix}mailster_workflows WHERE `finished` = 0 AND (`timestamp` <= %d OR `timestamp` IS NULL)";

		foreach ( $where as $key => $value ) {
			$values = array_map( 'esc_sql', array_filter( (array) $value ) );
			if ( empty( $values ) ) {
				continue;
			}
			$sql .= ' AND `' . esc_sql( $key ) . "` IN ('" . implode( "','", $values ) . "')";

		}

		$sql .= ' AND subscriber_id IS NOT NULL GROUP BY workflow_id, `timestamp` ORDER BY `timestamp` ASC LIMIT %d';

		$entries = $wpdb->get_results( $wpdb->prepare( $sql, $now, $now + $queue_upfront, $limit ) );

		if ( empty( $entries ) ) {
			return;
		}

		foreach ( $entries as $i => $entry ) {
			$args = array(
				'workflow_id' => (int) $entry->workflow_id,
				'trigger'     => $entry->trigger,
				'step'        => $entry->step,
			);

			// run now if timestamp is now
			if ( $entry->timestamp <= $now ) {
				call_user_func_array( array( $this, 'run_all' ), $args );
			} else {
				// add the timestamp to allow multiple events every minute
				$args['timestamp'] = (int) floor( $entry->timestamp / 60 ) * 60;
				wp_schedule_single_event( $entry->timestamp, 'mailster_workflow', $args, true );
			}
		}

		return true;
	}

	public function get_queue( $workflow_id, $step = null ) {
		global $wpdb;

		$sql = "SELECT workflows.*, subscribers.email, subscribers.status FROM {$wpdb->prefix}mailster_workflows AS workflows LEFT JOIN {$wpdb->prefix}mailster_subscribers AS subscribers ON subscribers.ID = workflows.subscriber_id WHERE 1=1";

		$sql .= $wpdb->prepare( ' AND workflows.workflow_id = %d', $workflow_id );
		if ( $step ) {
			$sql .= $wpdb->prepare( ' AND workflows.step = %s', $step );
		}
		$sql .= ' ORDER BY `timestamp`';

		$entries = $wpdb->get_results( $sql );

		return $entries;
	}

	public function get_queue_count( $workflow_id ) {
		global $wpdb;

		$sql = "SELECT COUNT(*) AS count, step FROM {$wpdb->prefix}mailster_workflows AS workflows WHERE 1=1 AND step IS NOT NULL";

		$sql .= $wpdb->prepare( ' AND workflows.workflow_id = %d', $workflow_id );

		$sql .= ' GROUP BY step';

		$entries = $wpdb->get_results( $sql );

		$result = array();

		foreach ( $entries as $entry ) {
			$result[ $entry->step ] = (int) $entry->count;
		}

		return $result;
	}

	public function remove_queue_item( $queue_id ) {
		global $wpdb;

		return $wpdb->delete( "{$wpdb->prefix}mailster_workflows", array( 'ID' => $queue_id ) );
	}

	public function finish_queue_item( $queue_id ) {
		global $wpdb;

		$data = array(
			'finished'  => time(),
			'error'     => '',
			'step'      => null,
			'timestamp' => null,
		);

		if ( $wpdb->update( "{$wpdb->prefix}mailster_workflows", $data, array( 'ID' => $queue_id ) ) ) {
			// process the queue for this item
			$this->wp_schedule( array( 'id' => $queue_id ) );
			return true;
		}

		return false;
	}

	public function forward_queue_item( $queue_id ) {
		global $wpdb;

		$data = array( 'timestamp' => time() );

		if ( $wpdb->update( "{$wpdb->prefix}mailster_workflows", $data, array( 'ID' => $queue_id ) ) ) {
			// process the queue for this item
			$this->wp_schedule( array( 'id' => $queue_id ) );
			return true;
		}

		return false;
	}

	// only used for mailster_workflow hook
	public function _run_delayed_workflow( $workflow_id, $trigger, $step ) {
		$this->run_all( $workflow_id, $trigger, $step );
	}




	public function schedule_async_jobs() {

		foreach ( $this->jobs as $i => $job ) {
			wp_schedule_single_event( time() + floor( $i / 5 ), 'mailster_workflow', $job );
		}
	}

	private function run_all( $workflow_id, $trigger, $step = null ) {

		require_once MAILSTER_DIR . 'classes/workflow.class.php';

		$workflow = get_post( $workflow_id );

		if ( ! $workflow ) {
			return;
		}

		global $wpdb;

		// do not run more than that at a time
		/**
		 * filter the limit of subscribers per workflow run
		 *
		 * @param int $limit default 10000
		 */
		$limit = apply_filters( 'mailster_workflow_limit', 10000 );

		/**
		 * filter the max runtime of a workflow
		 *
		 * @param int $max_execution in seconds default 15
		 */
		$max_execution = apply_filters( 'mailster_workflow_runtime', 15 );

		$sql  = "SELECT subscriber_id FROM {$wpdb->prefix}mailster_workflows";
		$sql .= $wpdb->prepare( ' WHERE workflow_id = %d AND finished = 0', $workflow->ID );

		// prepeare doesn't support NULL https://core.trac.wordpress.org/ticket/12819
		$sql .= ' AND `trigger` ' . ( $trigger ? $wpdb->prepare( '= %s', $trigger ) : 'IS NULL' );
		$sql .= ' AND `step` ' . ( $step ? $wpdb->prepare( '= %s', $step ) : 'IS NULL' );

		// timestamp is either null or max now
		$sql .= ' AND (`timestamp` <= %d OR `timestamp` IS NULL)';
		$sql .= ' LIMIT %d';

		$subscribers = $wpdb->get_col( $wpdb->prepare( $sql, time(), $limit ) );

		// maybe remove notices
		mailster_remove_notice( 'workflow_error_' . $workflow->ID );

		$start = microtime( true );
		foreach ( (array) $subscribers as $subscriber ) {

			$wf = new MailsterWorkflow( $workflow, $trigger, $subscriber, $step );
			// messure time
			$result = $wf->run();

			// stop if it takes to long to prevent timeouts
			$endtime = microtime( true ) - $start;
			if ( $max_execution && $endtime > $max_execution ) {
				break;
			}
		}
		add_action( '_shutdown', array( &$this, 'wp_schedule' ) );
	}

	public function get_numbers( $workflow ) {

		$workflow = get_post( $workflow );

		if ( ! $workflow ) {
			return;
		}

		if ( false === ( $numbers = mailster_cache_get( 'workflow_numbers_' . $workflow->ID ) ) ) {

			global $wpdb;

			// $entries = $wpdb->get_results( $wpdb->prepare( "SELECT step, COUNT( * ) as count FROM {$wpdb->prefix}mailster_workflows WHERE workflow_id = % d and step IS NOT null GROUP BY step;", $workflow->ID ) );

			$active = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( * ) as count FROM {$wpdb->prefix}mailster_workflows WHERE workflow_id = % d and timestamp IS NOT null and finished = 0 and step IS NOT null;", $workflow->ID ) );

			$finished = $wpdb->get_var( $wpdb->prepare( "SELECT  COUNT( * ) as count FROM {$wpdb->prefix}mailster_workflows WHERE workflow_id = % d and finished != 0;", $workflow->ID ) );

			$numbers = array(
				'active'      => (int) $active,
				'finished'    => (int) $finished,
				'total'       => (int) $finished + $active,
				'sent'        => 0,
				'opens'       => 0,
				'clicks'      => 0,
				'unsubs'      => 0,
				'bounces'     => 0,
				'open_rate'   => 0,
				'click_rate'  => 0,
				'unsub_rate'  => 0,
				'bounce_rate' => 0,
			);

			// foreach ( $entries as $entry ) {
			// if ( ! isset( $numbers['steps'][ $entry->step ] ) ) {
			// $return['steps'][ $entry->step ] = array(
			// 'count'  => 0,
			// 'errors' => array(),
			// );
			// }
			// $numbers['steps'][ $entry->step ]['count'] = (int) $entry->count;
			// }

			$workflow_campaigns = $this->get_workflow_campaigns( $workflow );

			foreach ( $workflow_campaigns as $workflow_campaign ) {
				$actions = mailster( 'actions' )->get_by_campaign( $workflow_campaign );

				$numbers['sent']    += $actions['sent'];
				$numbers['opens']   += $actions['opens'];
				$numbers['clicks']  += $actions['clicks'];
				$numbers['unsubs']  += $actions['unsubs'];
				$numbers['bounces'] += $actions['bounces'];
			}

			$numbers['open_rate']   = $numbers['sent'] ? $numbers['opens'] / $numbers['sent'] : 0;
			$numbers['click_rate']  = $numbers['opens'] ? $numbers['clicks'] / $numbers['opens'] : 0;
			$numbers['unsub_rate']  = $numbers['sent'] ? $numbers['unsubs'] / $numbers['sent'] : 0;
			$numbers['bounce_rate'] = $numbers['sent'] ? $numbers['bounces'] / $numbers['sent'] : 0;

			mailster_cache_set( 'workflow_numbers_' . $workflow->ID, $numbers );

		}

		return $numbers;
	}

	public function get_workflow_triggers( $workflow ) {

		$workflow = get_post( $workflow );

		if ( ! $workflow ) {
			return false;
		}

		// TODO CHECK CHANGES and remove triggers
		$blocks = parse_blocks( $workflow->post_content );

		// get all triggers
		$triggers = array();

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] !== 'mailster-workflow/triggers' ) {
				continue;
			}
			foreach ( $block['innerBlocks'] as $innerBlock ) {
				if ( empty( $innerBlock ) ) {
					continue;
				}
				if ( ! isset( $innerBlock['attrs']['trigger'] ) ) {
					continue;
				}
				$triggers[] = $innerBlock['attrs']['trigger'];
			}
		}

		return $triggers;
	}

	public function get_workflow_campaigns( $workflow ) {

		$workflow = get_post( $workflow );

		if ( ! $workflow ) {
			return false;
		}

		$raw_content = $workflow->post_content;

		$ids = array();

		if ( preg_match_all( '/mailster-workflow\/email(.*)"campaign":(\d+)/', $raw_content, $matches ) ) {
			$ids = array_unique( $matches[2] );
		}

		return $ids;
	}

	public function register_conditions_block() {

		$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

		$types = array_keys( $block_types );

		// TODO load only for automation post type

		if ( ! in_array( 'mailster-workflow/conditions', $types ) ) {
			register_block_type( MAILSTER_DIR . 'build/workflows/conditions', array( 'render_callback' => array( $this, 'render_conditions' ) ) );
		}
	}

	public function rest_api_init() {

		include MAILSTER_DIR . 'classes/rest-controller/rest.automations.class.php';

		$controller = new Mailster_REST_Automations_Controller();
		$controller->register_routes();
	}


	public function display_post_states( $post_states, $post ) {

		if ( 'mailster-workflow' != $post->post_type ) {
			return $post_states;
		}

		if ( $post->post_status == 'private' ) {
			$post_states['private'] = esc_html__( 'Inactive', 'mailster' );
		} elseif ( $post->post_status == 'publish' ) {
			$post_states['publish'] = esc_html__( 'Active', 'mailster' );
		}

		return $post_states;
	}


	public function wp_list_table_class_name( $class_name, $args ) {

		if ( $args['screen']->id !== 'edit-mailster-workflow' ) {
			return $class_name;
		}

		require_once MAILSTER_DIR . 'classes/automation.table.class.php';
		$class_name = 'Mailster_Automations_Table';

		return $class_name;
	}


	public function columns( $columns ) {

		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'title'   => esc_html__( 'Title', 'mailster' ),
			'status'  => esc_html__( 'Status', 'mailster' ),
			'total'   => esc_html__( 'Total', 'mailster' ),
			'open'    => esc_html__( 'Open', 'mailster' ),
			'click'   => esc_html__( 'Clicks', 'mailster' ),
			'unsubs'  => esc_html__( 'Unsubscribes', 'mailster' ),
			'bounces' => esc_html__( 'Bounces', 'mailster' ),
			'date'    => esc_html__( 'Date', 'mailster' ),
		);
		return $columns;
	}


	/**
	 *
	 *
	 * @param unknown $column
	 * @return unknown
	 */
	public function get_columns_content( $column ) {

		ob_start();

		$this->columns_content( $column );

		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}

	public function quick_edit_enabled_for_post_type( $enabled, $post_type ) {

		if ( get_post_type() !== 'mailster-workflow' ) {
			return $enabled;
		}

		return false;
	}


	/**
	 *
	 *
	 * @param unknown $column
	 */
	public function columns_content( $column, $post_id ) {

		global $post, $wp_post_statuses;

		if ( ! in_array( $column, array( 'status', 'total', 'open', 'click', 'unsubs', 'bounces' ) ) ) {
			// return;
		}

		$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		// if ( ! $is_ajax && $column != 'status' && wp_script_is( 'heartbeat', 'registered' ) ) {
		// echo '<span class="skeleton-loading"></span>';
		// if ( in_array( $column, array( 'open', 'click', 'unsubs', 'bounces' ) ) ) {
		// echo '<br><span class="skeleton-loading nonessential"></span>';
		// }
		// return;
		// }

		$error = ini_get( 'error_reporting' );
		error_reporting( E_ERROR );

		$now        = time();
		$timeformat = mailster( 'helper' )->timeformat();

		switch ( $column ) {

			case 'status':
				$numbers = $this->get_numbers( $post_id );

				printf( '<div class="s-status"><span>%s</span> %s</div>', number_format_i18n( $numbers['active'] ), esc_html__( 'active', 'mailster' ) );
				printf( '<div class="s-status"><span>%s</span> %s</div>', number_format_i18n( $numbers['finished'] ), esc_html__( 'finished', 'mailster' ) );
				printf( '<div class="s-status"><span>%s</span> %s</div>', number_format_i18n( $numbers['total'] ), esc_html__( 'total', 'mailster' ) );

				break;

			case 'total':
				$numbers = $this->get_numbers( $post_id );
				if ( ! $numbers['sent'] ) {
					return;
				}
				$total = $numbers['sent'];
				echo number_format_i18n( $total );

				break;

			case 'open':
				$numbers = $this->get_numbers( $post_id );
				if ( ! $numbers['sent'] ) {
					return;
				}

				echo '<span class="s-opens">' . number_format_i18n( $numbers['opens'] ) . '</span>/<span class="tiny s-sent">' . number_format_i18n( $numbers['sent'] ) . '</span>';
				$rate = round( $numbers['open_rate'] * 100, 2 );
				echo "<br><span title='" . sprintf( esc_attr__( '%s of sent', 'mailster' ), $rate . '%' ) . "' class='nonessential'>";
				echo ' (' . $rate . '%)';
				echo '</span>';
				echo '<br>';

				break;

			case 'click':
				$numbers = $this->get_numbers( $post_id );
				if ( ! $numbers['sent'] ) {
					return;
				}
				$clicks = $numbers['clicks'];
				$rate   = round( $numbers['click_rate'] * 100, 2 );
				echo number_format_i18n( $clicks );
				if ( $rate ) {
					echo "<br><span class='nonessential'>(<span title='" . sprintf( esc_attr__( '%s of sent', 'mailster' ), $rate . '%' ) . "'>";
					echo '' . $rate . '%';
					echo '</span>)</span>';
				} else {
					echo "<br><span title='" . sprintf( esc_attr__( '%s of sent', 'mailster' ), $rate . '%' ) . "' class='nonessential'>";
					echo ' (' . $rate . '%)';
					echo '</span>';
				}
				echo '<br>';

				break;

			case 'unsubs':
				$numbers = $this->get_numbers( $post_id );
				if ( ! $numbers['sent'] ) {
					return;
				}
				$unsubscribes = $numbers['unsubs'];
				$rate         = round( $numbers['unsub_rate'] * 100, 2 );

				echo number_format_i18n( $unsubscribes );
				if ( $rate ) {
					echo "<br><span class='nonessential'>(<span title='" . sprintf( esc_attr__( '%s of sent', 'mailster' ), $rate . '%' ) . "'>";
					echo '' . $rate . '%';
					echo '</span>)</span>';
				} else {
					echo "<br><span title='" . sprintf( esc_attr__( '%s of sent', 'mailster' ), $rate . '%' ) . "' class='nonessential'>";
					echo ' (' . $rate . '%)';
					echo '</span>';
				}
				echo '<br>';

				break;

			case 'bounces':
				$numbers = $this->get_numbers( $post_id );
				if ( ! $numbers['sent'] ) {
					return;
				}
				$bounces = $numbers['bounces'];
				$rate    = round( $numbers['bounce_rate'] * 100, 2 );
				echo number_format_i18n( $bounces );
				echo "<br><span title='" . sprintf( esc_attr__( '%s of totals', 'mailster' ), $rate . '%' ) . "' class='nonessential'>";
				echo ' (' . $rate . '%)';
				echo '</span>';
				echo '<br>';

				break;

		}
		error_reporting( $error );
	}

	/**
	 *
	 *
	 * @param unknown $actions
	 * @param unknown $workflow
	 * @return unknown
	 */
	public function quick_edit_btns( $actions, $workflow ) {

		if ( $workflow->post_type != 'mailster-workflow' ) {
			return $actions;
		}

		if ( ( current_user_can( 'duplicate_mailster-workflows' ) && get_current_user_id() == $workflow->post_author ) || current_user_can( 'duplicate_others_mailster-workflows' ) ) {
			$actions['duplicate'] = '<a class="duplicate" href="' . add_query_arg(
				array(
					'post_type'   => 'mailster-workflow',
					'duplicate'   => (int) $workflow->ID,
					'post_status' => isset( $_GET['post_status'] ) ? sanitize_key( $_GET['post_status'] ) : null,
					'_wpnonce'    => wp_create_nonce( 'mailster_duplicate_nonce' ),
				),
				admin_url( 'edit.php' )
			) . '" title="' . sprintf( esc_html__( 'Duplicate Workflow %s', 'mailster' ), '&quot;' . esc_attr( $workflow->post_title ) . '&quot;' ) . '">' . esc_html__( 'Duplicate', 'mailster' ) . '</a>';
		}
		if ( $workflow->post_status == 'private' && ( current_user_can( 'publish_mailster-workflows' ) ) ) {
			$actions['activate'] = '<a class="activate" href="' . add_query_arg(
				array(
					'post_type'   => 'mailster-workflow',
					'activate'    => (int) $workflow->ID,
					'post_status' => isset( $_GET['post_status'] ) ? sanitize_key( $_GET['post_status'] ) : null,
					'_wpnonce'    => wp_create_nonce( 'mailster_activate_nonce' ),
				),
				admin_url( 'edit.php' )
			) . '" title="' . sprintf( esc_html__( 'Activate Workflow %s', 'mailster' ), '&quot;' . esc_attr( $workflow->post_title ) . '&quot;' ) . '">' . esc_html__( 'Activate', 'mailster' ) . '</a>';
		}
		if ( $workflow->post_status == 'publish' && current_user_can( 'publish_mailster-workflows' ) ) {
			$actions['deactivate'] = '<a class="deactivate" href="' . add_query_arg(
				array(
					'post_type'   => 'mailster-workflow',
					'deactivate'  => (int) $workflow->ID,
					'post_status' => isset( $_GET['post_status'] ) ? sanitize_key( $_GET['post_status'] ) : null,
					'_wpnonce'    => wp_create_nonce( 'mailster_deactivate_nonce' ),
				),
				admin_url( 'edit.php' )
			) . '" title="' . sprintf( esc_html__( 'Deactivate Workflow %s', 'mailster' ), '&quot;' . esc_attr( $workflow->post_title ) . '&quot;' ) . '">' . esc_html__( 'Deactivate', 'mailster' ) . '</a>';
		}

		return $actions;
	}

	public function edit_hook() {

		if ( ! isset( $_GET['post_type'] ) || 'mailster-workflow' !== $_GET['post_type'] ) {
			return;
		}

		// duplicate workflow
		if ( isset( $_GET['duplicate'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'mailster_duplicate_nonce' ) ) {
			$id = (int) $_GET['duplicate'];
			if ( ( current_user_can( 'duplicate_mailster-workflows' ) && get_current_user_id() != $post->post_author ) && ! current_user_can( 'duplicate_others_mailster-workflows' ) ) {
				wp_die( esc_html__( 'You are not allowed to duplicate this workflow.', 'mailster' ) );
			} elseif ( $new_id = $this->duplicate( $id ) ) {
				$id = $new_id;
			}
			// activate workflow
		} elseif ( isset( $_GET['activate'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'mailster_activate_nonce' ) ) {
			$id = (int) $_GET['activate'];
			if ( ( current_user_can( 'activate_mailster-workflows' ) && get_current_user_id() != $post->post_author ) && ! current_user_can( 'activate_others_mailster-workflows' ) ) {
				wp_die( esc_html__( 'You are not allowed to activate this workflow.', 'mailster' ) );
			} else {
				$this->activate( $id );
			}
			// deactivate workflow
		} elseif ( isset( $_GET['deactivate'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'mailster_deactivate_nonce' ) ) {
			$id = (int) $_GET['deactivate'];
			if ( ( current_user_can( 'deactivate_mailster-workflows' ) && get_current_user_id() != $post->post_author ) && ! current_user_can( 'deactivate_others_mailster-workflows' ) ) {
				wp_die( esc_html__( 'You are not allowed to deactivate this workflow.', 'mailster' ) );
			} else {
				$this->deactivate( $id );
			}
		}

		if ( isset( $id ) && ! wp_doing_ajax() ) {
			$status = ( isset( $_GET['post_status'] ) ) ? '&post_status=' . $_GET['post_status'] : '';
			( isset( $_GET['edit'] ) )
			? mailster_redirect( 'post.php?post=' . $id . '&action=edit' )
			: mailster_redirect( 'edit.php?post_type=mailster-workflow' . $status );
			exit;
		}
	}



	public function register_post_type() {

		$labels = array(
			'name'                     => _x( 'Workflows', 'Post Type General Name', 'mailster' ),
			'singular_name'            => _x( 'Workflow', 'Post Type Singular Name', 'mailster' ),
			'menu_name'                => __( 'Automations', 'mailster' ),
			'attributes'               => __( 'Workflow Attributes', 'mailster' ),
			'all_items'                => __( 'Automations', 'mailster' ),
			'add_new_item'             => __( 'Add New Workflow', 'mailster' ),
			'add_new'                  => __( 'Add New Workflow', 'mailster' ),
			'new_item'                 => __( 'New Workflow', 'mailster' ),
			'edit_item'                => __( 'Edit Workflow', 'mailster' ),
			'update_item'              => __( 'Update Workflow', 'mailster' ),
			'view_item'                => __( 'View Workflow', 'mailster' ),
			'view_items'               => __( 'View Workflows', 'mailster' ),
			'search_items'             => __( 'Search Workflow', 'mailster' ),
			'not_found'                => __( 'Not found', 'mailster' ),
			'not_found_in_trash'       => __( 'Not found in Trash', 'mailster' ),
			'items_list'               => __( 'Workflows list', 'mailster' ),
			'items_list_navigation'    => __( 'Workflows list navigation', 'mailster' ),
			'filter_items_list'        => __( 'Filter workflow list', 'mailster' ),
			'item_published'           => __( 'Workflow published', 'mailster' ),
			'item_published_privately' => __( 'Workflow published privately.', 'mailster' ),
			'item_reverted_to_draft'   => __( 'Workflow reverted to draft.', 'mailster' ),
			'item_scheduled'           => __( 'Workflow scheduled.', 'mailster' ),
			'item_updated'             => __( 'Workflow updated.', 'mailster' ),

		);

		$args = array(
			'label'               => __( 'Automation', 'mailster' ),
			'description'         => __( 'Newsletter Automation', 'mailster' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'custom-fields' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => 'mailster-workflow',
			'show_in_menu'        => 'edit.php?post_type=newsletter',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
			'show_in_rest'        => true,

		);
		register_post_type( 'mailster-workflow', $args );
	}

	public function register_post_meta() {

		// register_post_meta(
		// 'mailster-workflow',
		// 'trigger',
		// array(
		// 'type'         => 'string',
		// 'show_in_rest' => true,
		// 'single'       => false,

		// )
		// );

		register_post_meta(
			'mailster-workflow',
			'enddate',
			array(
				'type'         => 'string',
				'show_in_rest' => true,
				'single'       => true,

			)
		);
	}

	public function get( $id ) {
		$post = get_post( $id );
		if ( 'mailster-workflow' !== $post->post_type ) {
			return false;
		}
		return $post;
	}

	public function block_init() {

		if ( ! is_admin() ) {
			return;
		}
		global $pagenow;
		$typenow = '';

		// from https://www.designbombs.com/registering-gutenberg-blocks-for-custom-post-type/
		if ( 'post-new.php' === $pagenow ) {
			if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) ) {
				$typenow = sanitize_key( $_REQUEST['post_type'] );
			}
		} elseif ( 'post.php' === $pagenow ) {
			$post_id = null;
			if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {
				// Do nothing
			} elseif ( isset( $_GET['post'] ) ) {
				$post_id = (int) $_GET['post'];
			} elseif ( isset( $_POST['post_ID'] ) ) {
				$post_id = (int) $_POST['post_ID'];
			}
			if ( $post_id ) {
				if ( $post = get_post( $post_id ) ) {
					$typenow = $post->post_type;
				}
			}
		}

		if ( $typenow === 'mailster-workflow' ) {

			$blocks = $this->get_blocks();

			foreach ( $blocks as $block ) {
				register_block_type( $block );
				// TODO: load translations of the block.json
			}
		}
	}

	public function render_conditions( $args, $content, WP_Block $block ) {

		$conditions = array();
		if ( isset( $block->attributes['conditions'] ) ) {
			$conditions = $block->attributes['conditions'];

			wp_parse_str( $conditions, $params );
			$conditions = isset( $params['conditions'] ) ? $params['conditions'] : array();

		}
		$render = isset( $block->attributes['render'] ) && $block->attributes['render'];
		$plain  = isset( $block->attributes['plain'] ) && $block->attributes['plain'];

		$mailster_conditions = mailster( 'conditions' );

		// set the campaings for the current workflow
		if ( isset( $block->attributes['emails'] ) ) {
			$mailster_conditions->set_workflow_campaigns( $block->attributes['emails'] );
		}

		ob_start();
		if ( $render ) {
			$mailster_conditions->render( $conditions, true, $plain );
		} else {
			$mailster_conditions->view( $conditions, false );
		}
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	private function get_blocks() {
		$blocks = glob( MAILSTER_DIR . 'build/workflows/*/block.json' );
		return $blocks;
	}

	public function overview_script_styles() {

		$post_type = get_post_type();
		if ( ! $post_type ) {
			$post_type = get_current_screen()->post_type;
		}

		if ( 'mailster-workflow' != $post_type ) {
			return;
		}

		$suffix = '';

		do_action( 'mailster_admin_header' );

		wp_enqueue_style( 'mailster-automations-overview', MAILSTER_URI . 'assets/css/automations-overview' . $suffix . '.css', array(), MAILSTER_VERSION );
	}

	public function block_script_styles() {

		if ( 'mailster-workflow' != get_post_type() ) {
			return;
		}

		$suffix = '';

		do_action( 'mailster_admin_header' );

		wp_enqueue_style( 'mailster-automations-block-editor', MAILSTER_URI . 'assets/css/automations-blocks-editor' . $suffix . '.css', array(), MAILSTER_VERSION );

		wp_enqueue_style( 'mailster-conditions', MAILSTER_URI . 'assets/css/conditions-style' . $suffix . '.css', array(), MAILSTER_VERSION );
		wp_enqueue_script( 'mailster-conditions', MAILSTER_URI . 'assets/js/conditions-script' . $suffix . '.js', array( 'mailster-script' ), MAILSTER_VERSION, true );
	}

	public function block_editor_settings( $editor_settings, $block_editor_context ) {

		if ( get_post_type( $block_editor_context->post ) !== 'mailster-workflow' ) {
			return $editor_settings;
		}

		// remove all third party styles (as mutch as possible)
		$editor_settings['defaultEditorStyles'] = array();
		$editor_settings['styles']              = array();

		// disable code editor
		$editor_settings['codeEditingEnabled'] = defined( 'WP_DEBUG' ) && WP_DEBUG;

		return $editor_settings;
	}

	public function force_block_editor( $bool, $post_type ) {

		// just pass through
		if ( $post_type !== 'mailster-workflow' ) {
			return $bool;
		}

		return true;
	}

	public function allowed_block_types( $allowed_block_types, $context ) {

		// just skip if not on our cpt
		if ( 'mailster-workflow' != get_post_type() || $context->name !== 'core/edit-post' ) {
			return $allowed_block_types;
		}

		$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
		$types       = array_keys( $block_types );

		// only mailster workflow blocks
		$types = preg_grep( '/^(mailster-workflow)\//', $types );
		$types = array_values( $types );

		return apply_filters( 'mailster_automations_allowed_block_types', $types );
	}

	public function block_categories( $categories ) {

		if ( 'mailster-workflow' != get_post_type() ) {
			return $categories;
		}

		return array_merge(
			array(
				array(
					'slug'  => 'mailster-workflow-steps',
					'title' => __( 'Workflow Steps', 'mailster' ),
				),
			),
			$categories
		);
	}

	public function register_conditions_variations( $args, $block_type ) {
		if ( $block_type !== 'mailster-workflow/condition' ) {
			return $args;
		}

		$args['variations'] = array(
			array(
				'name'       => 'fullfilled',
				'title'      => 'FULLFILLED',
				'icon'       => 'update',
				// 'scope'      => array( 'block' ),
				'attributes' => array(
					'fulfilled' => true,
				),
			),
			array(
				'name'       => 'not_fullfilled',
				'title'      => 'NOT FULLFILED',
				'icon'       => 'archive',
				// 'scope'      => array( 'block' ),
				'attributes' => array(
					'fulfilled' => false,
				),
			),

		);

		return $args;
	}

	public function register_variations( $args, $block_type ) {

		if ( $block_type !== 'mailster-workflow/action' ) {
			return $args;
		}

		$actions = mailster( 'automations' )->get_actions();

		$args['variations'] = array();

		foreach ( $actions as $action ) {

			$args['variations'][] = array(
				'name'       => $action['id'],
				'title'      => $action['label'],
				'icon'       => $action['icon'],
				// 'scope'      => array( 'block' ),
				'attributes' => array(
					'action' => $action['id'],
				),
			);
		}

		return $args;
	}

	public function register_block_patterns() {

		$query = wp_parse_url( wp_get_referer(), PHP_URL_QUERY );

		if ( ! $query || false === strpos( $query, 'post_type=mailster-workflow' ) ) {
			return;
		}

		register_block_pattern_category( 'mailster-automations', array( 'label' => __( 'Mailster Automations', 'mailster' ) ) );

		include_once MAILSTER_DIR . 'patterns/workflows.php';
	}

	private function update_metas( $subscriber_ids, $campaign_id = 0, $key = null, $value = null ) {
		if ( ! is_array( $subscriber_ids ) ) {
			$subscriber_ids = array( $subscriber_ids );
		}

		$subscriber_ids = array_filter( $subscriber_ids, 'is_numeric' );

		$success = true;

		foreach ( $subscriber_ids as $subscriber_id ) {
			$success = $success && $this->update_meta( $subscriber_id, $campaign_id, $key, $value );
		}

		return $success;
	}

	public function on_install( $new ) {

		if ( $new ) {
			update_option( 'mailster_trigger', '' );
		}
	}

	public function block_forms_shortcode( $atts, $content ) {

		return $this->render_form_with_options( $atts['id'], array(), false );
	}

	public function on_unsubscribe( $subscriber_id, $campaign_id, $status, $index ) {

		global $wpdb;

		// delete workflows with subscribers
		return $wpdb->delete( "{$wpdb->prefix}mailster_workflows", array( 'subscriber_id' => (int) $subscriber_id ) );
	}

	public function after_delete_post( $post_id, $post ) {

		if ( get_post_type( $post ) !== 'mailster-workflow' ) {
			return;
		}

		global $wpdb;

		// delete workflow entries from this post (workflow)
		return $wpdb->delete( "{$wpdb->prefix}mailster_workflows", array( 'workflow_id' => (int) $post_id ) );
	}


	public function activate( $id ) {

		$workflow = get_post( $id );

		if ( ! $workflow ) {
			return new WP_Error( 'no_workflow', esc_html__( 'This workflow doesn\'t exists.', 'mailster' ) );
		}

		return wp_publish_post( $workflow );
	}
	public function deactivate( $id ) {

		$workflow = get_post( $id );

		if ( ! $workflow ) {
			return new WP_Error( 'no_workflow', esc_html__( 'This workflow doesn\'t exists.', 'mailster' ) );
		}

		// make post private
		$workflow->post_status = 'private';
		return wp_update_post( $workflow );
	}

		/**
		 *
		 *
		 * @param unknown $id
		 * @param unknown $timestamp (optional)
		 * @return unknown
		 */
	public function duplicate( $id, $workflow_args = array(), $workflow_meta = array(), $timestamp = null ) {

		$workflow = get_post( $id );

		if ( ! $workflow ) {
			return new WP_Error( 'no_workflow', esc_html__( 'This workflow doesn\'t exists.', 'mailster' ) );
		}

		unset( $workflow->ID );
		unset( $workflow->guid );
		unset( $workflow->post_name );
		unset( $workflow->post_author );
		unset( $workflow->post_date );
		unset( $workflow->post_date_gmt );
		unset( $workflow->post_modified );
		unset( $workflow->post_modified_gmt );

		// save these as '&' otherwise the string will become'\u0026' => 'u0026'
		$workflow->post_content = str_replace( '\u0026', '&', $workflow->post_content );

		if ( preg_match( '# \((\d+)\)$#', $workflow->post_title, $hits ) ) {
			$workflow->post_title = trim( preg_replace( '#(.*) \(\d+\)$#', '$1 (' . ( ++$hits[1] ) . ')', $workflow->post_title ) );
		} elseif ( $workflow->post_title ) {
			$workflow->post_title .= ' (2)';
		}

		$workflow->post_status = 'draft';

		if ( ! empty( $workflow_args ) ) {
			$workflow_data = (object) wp_parse_args( (array) $workflow_args, (array) $workflow );
			$workflow      = new WP_Post( $workflow_data );
		}

		if ( ! empty( $workflow_meta ) ) {
			$meta = wp_parse_args( $workflow_meta, $meta );
		}

		kses_remove_filters();
		$new_id = wp_insert_post( (array) $workflow );
		kses_init_filters();

		$new = get_post( $new_id );

		if ( $new_id ) {

			do_action( 'mailster_workflow_duplicate', $id, $new_id );

			return $new_id;
		}

		return false;
	}
}
