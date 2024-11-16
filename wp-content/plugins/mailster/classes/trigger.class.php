<?php

class MailsterTrigger {

	public function __construct() {

		// subscriber added/removed to list
		add_action( 'mailster_list_confirmed', array( &$this, 'list_confirmed' ), 10, 2 );
		add_action( 'mailster_list_removed', array( &$this, 'list_removed' ), 10, 2 );

		add_action( 'mailster_form_conversion', array( &$this, 'form_conversion' ), 10, 3 );

		// subscriber added/removed to tag
		add_action( 'mailster_tag_added', array( &$this, 'tag_added' ), 10, 3 );
		add_action( 'mailster_tag_removed', array( &$this, 'tag_removed' ), 10, 3 );

		// Visited a page
		add_action( 'shutdown', array( &$this, 'front_page_hooks' ) );

		// campaign is opened
		add_action( 'mailster_open', array( &$this, 'open' ), 10, 3 );

		// link in campaign is clicked
		add_action( 'mailster_click', array( &$this, 'click' ), 10, 5 );

		// custom trigger
		add_action( 'mailster_trigger', array( &$this, 'trigger' ), 10, 4 );

		// check for date trigger hourly
		add_action( 'mailster_cron', array( &$this, 'hourly' ) );

		// post published
		add_action( 'wp_after_insert_post', array( &$this, 'published_post' ), 10, 4 );

		add_action( 'mailster_trigger_date', array( &$this, 'date' ) );

		add_action( 'mailster_trigger_anniversary', array( &$this, 'anniversary' ) );

		// add/remove/updated custom field
		add_action( 'mailster_add_custom_field', array( &$this, 'updated_field' ), 10, 2 );
		add_action( 'mailster_remove_custom_field', array( &$this, 'updated_field' ), 10, 2 );
		add_action( 'mailster_update_custom_field', array( &$this, 'updated_field' ), 10, 2 );
	}


	public function hourly() {
		$this->date();
		$this->anniversary();
	}

	public function trigger( $workflow_id, $trigger, $subscriber_id, $step = null ) {

		$this->add_job( $workflow_id, $trigger, $subscriber_id, $step );
	}

	public function hook( $hook, $subscriber_id, $workflow_id = null, $step = null ) {

		$workflows = $this->get_workflows_by_trigger( 'hook' );
		foreach ( $workflows as $workflow ) {

			if ( $workflow_id && $workflow_id != $workflow ) {
				continue;
			}
			$options = mailster( 'automations' )->get_trigger_option( $workflow, 'hook' );

			// TODO: check for problems
			foreach ( $options as $option ) {

				if ( ! isset( $option['hook'] ) ) {
					continue;
				}

				if ( $option['hook'] !== $hook ) {
					continue;
				}

				$this->add_job( $workflow, 'hook', $subscriber_id, $step );
			}
		}
	}

	public function open( $subscriber_id, $campaign_id, $campaign_index = null ) {

		$workflows = $this->get_workflows_by_trigger( 'opened_campaign' );
		foreach ( $workflows as $workflow ) {
			$options = mailster( 'automations' )->get_trigger_option( $workflow, 'opened_campaign' );
			foreach ( $options as $option ) {
			}
		}
	}

	public function click( $subscriber_id, $campaign_id, $target, $index, $campaign_index = null ) {

		$workflows = $this->get_workflows_by_trigger( 'link_click' );
		foreach ( $workflows as $workflow ) {
			$options = mailster( 'automations' )->get_trigger_option( $workflow, 'link_click' );

			foreach ( $options as $option ) {

				if ( ! isset( $option['links'] ) || empty( $option['links'] ) ) {
					continue;
				}

				$links = $option['links'];

				$matching_links = preg_grep( '|^' . preg_quote( $target ) . '$|', $links );

				if ( ! empty( $matching_links ) ) {
					$this->add_job( $workflow, 'link_click', $subscriber_id, $option['id'] );
				}
			}
		}
	}

	public function list_confirmed( $list_id, $subscriber_id ) {

		$this->run_list( 'list_add', $list_id, $subscriber_id );
	}

	public function list_removed( $list_id, $subscriber_id ) {

		$this->run_list( 'list_removed', $list_id, $subscriber_id );
	}

	private function run_list( $type, $list_id, $subscriber_id ) {

		$workflows = $this->get_workflows_by_trigger( $type );
		foreach ( $workflows as $workflow ) {
			$options = mailster( 'automations' )->get_trigger_option( $workflow, $type );

			foreach ( $options as $option ) {

				if ( ! isset( $option['lists'] ) ) {
					continue;
				}

				if ( in_array( $list_id, $option['lists'] ) ) {
					$this->add_job( $workflow, $type, $subscriber_id, $option['id'] );

					// any list
				} elseif ( in_array( '-1', $option['lists'] ) ) {
					$this->add_job( $workflow, $type, $subscriber_id, $option['id'] );
				}
			}
		}
	}

	public function form_conversion( $form_id, $subscriber_id, $post_id ) {

		$workflows = $this->get_workflows_by_trigger( 'form_conversion' );
		foreach ( $workflows as $workflow ) {
			$options = mailster( 'automations' )->get_trigger_option( $workflow, 'form_conversion' );

			foreach ( $options as $option ) {

				if ( ! isset( $option['forms'] ) ) {
					continue;
				}

				// the defined form
				if ( in_array( $form_id, $option['forms'] ) ) {
					$this->add_job( $workflow, 'form_conversion', $subscriber_id, $option['id'] );

					// any list
				} elseif ( in_array( '-1', $option['forms'] ) ) {
					$this->add_job( $workflow, 'form_conversion', $subscriber_id, $option['id'] );
				}
			}
		}
	}

	public function updated_field( $subscriber_id, $field ) {

		$workflows = $this->get_workflows_by_trigger( 'updated_field' );

		foreach ( $workflows as $workflow ) {
			$options = mailster( 'automations' )->get_trigger_option( $workflow, 'updated_field' );

			foreach ( $options as $option ) {

				if ( ! isset( $option['field'] ) ) {
					continue;
				}

				// any field or the defined one
				if ( '-1' == $option['field'] || $field === $option['field'] ) {
					$this->add_job( $workflow, 'updated_field', $subscriber_id, $option['id'] );
				}
			}
		}
	}

	public function tag_added( $tag_id, $subscriber_id, $tag_name ) {

		$this->run_tag( 'tag_added', $tag_id, $subscriber_id, $tag_name );
	}

	public function tag_removed( $tag_id, $subscriber_id, $tag_name ) {

		$this->run_tag( 'tag_removed', $tag_id, $subscriber_id, $tag_name );
	}

	private function run_tag( $type, $tag_id, $subscriber_id, $tag_name ) {

		$workflows = $this->get_workflows_by_trigger( $type );
		foreach ( $workflows as $workflow ) {
			$options = mailster( 'automations' )->get_trigger_option( $workflow, $type );

			foreach ( $options as $option ) {
				if ( ! isset( $option['tags'] ) || empty( $option['tags'] ) ) {
					continue;
				}

				if ( in_array( $tag_name, $option['tags'] ) ) {
					$this->add_job( $workflow, $type, $subscriber_id, $option['id'] );
				}
			}
		}
	}

	public function published_post( $post_id, $post, $update, $post_before ) {

		$new_status = $post instanceof WP_Post ? $post->post_status : false;
		$old_status = $post_before instanceof WP_Post ? $post_before->post_status : false;

		if ( $new_status == $old_status ) {
			return;
		}

		if ( 'newsletter' == $post->post_type ) {
			return;
		}

		$accepted_status = apply_filters( 'mailster_check_for_autoresponder_accepted_status', 'publish', $post );

		if ( ! is_array( $accepted_status ) ) {
			$accepted_status = array( $accepted_status );
		}

		if ( ! in_array( $new_status, $accepted_status ) ) {
			return;
		}

		$workflows = $this->get_workflows_by_trigger( 'published_post' );
		foreach ( $workflows as $workflow ) {
			$options = mailster( 'automations' )->get_trigger_option( $workflow, 'published_post' );

			foreach ( $options as $option ) {

				$query = isset( $option['query'] ) ? $option['query'] : null;

				// if query is there we need to check it
				if ( $query ) {

					// check correct post type
					if ( $query['postType'] != $post->post_type ) {
						continue;
					}

					// check for authors
					if ( $query['author'] ) {
						$authors = explode( ',', $query['author'] );
						if ( ! in_array( $post->post_author, $authors ) ) {
							continue;
						}
					}

					if ( $query['taxQuery'] ) {
						foreach ( $query['taxQuery'] as $taxonomy => $ids ) {
							$post_terms = get_the_terms( $post->ID, $taxonomy );
							$post_terms = wp_list_pluck( $post_terms, 'term_id' );

							// no post_terms set but required => give up (not passed)
							if ( ! count( array_intersect( $post_terms, $ids ) ) ) {
								break 2; // break out of both loops
							}
						}
					}
				}

				// TODO check for the right step if posts should be skipped
				$context = array( 'query' => $query );

				$this->add_job( $workflow, 'published_post', null, $option['id'], null, $context );
			}
		}
	}

	public function date() {

		$workflows = $this->get_workflows_by_trigger( 'date' );

		foreach ( $workflows as $workflow ) {
			$this->run_date( $workflow );
		}
	}

	private function run_date( $workflow ) {

		$options = mailster( 'automations' )->get_trigger_option( $workflow, 'date' );

		foreach ( $options as $option ) {
			$date  = isset( $option['date'] ) ? strtotime( $option['date'] ) : null;
			$field = isset( $option['field'] ) ? $option['field'] : null;

			// get timestamp from today at the time of $date if a usefield is set
			if ( $field ) {
				$date = strtotime( 'today ' . date( 'H:i', $date ) );
			}

			// if date is within one hour
			if ( $date && time() < $date && time() + HOUR_IN_SECONDS > $date ) {
				$this->add_job( $workflow, 'date', null, $option['id'] );
			}
		}
	}

	public function anniversary() {

		$workflows = $this->get_workflows_by_trigger( 'anniversary' );

		foreach ( $workflows as $workflow ) {
			$this->run_anniversary( $workflow );
		}
	}

	private function run_anniversary( $workflow ) {

		$options = mailster( 'automations' )->get_trigger_option( $workflow, 'anniversary' );

		foreach ( $options as $option ) {
			$date   = isset( $option['date'] ) ? strtotime( $option['date'] ) : null;
			$field  = isset( $option['field'] ) ? $option['field'] : null;
			$offset = isset( $option['offset'] ) ? (int) $option['offset'] : 0;

			// get timestamp from today at the time of $date if a userfield is set
			if ( $field ) {

				$date = strtotime( 'today ' . date( 'H:i', $date ) );

				// add offset in seconds
				$date += $offset;

			}

			// if date is within one hour
			if ( $date && time() < $date && time() + HOUR_IN_SECONDS >= $date ) {
				$this->add_job( $workflow, 'anniversary', null, $option['id'] );
			}
		}
	}


	// this runs running on every pageload so make it as fast as possible
	public function front_page_hooks() {

		global $wp;

		if ( ! $wp ) {
			return;
		}

		// not on backend
		if ( is_admin() ) {
			return;
		}

		$triggers = get_option( 'mailster_trigger' );
		// nothing to do
		if ( empty( $triggers ) ) {
			return;
		}

		$links = array_keys( $triggers );

		$matching_links = preg_grep( '|^' . preg_quote( '/' . $wp->request ) . '$|', $links );
		// no matching links
		if ( empty( $matching_links ) ) {
			return;
		}

		$subscriber_id = mailster_get_current_user_id();

		// no current subscriber
		if ( ! $subscriber_id ) {
			return;
		}

		// get the workflow ids
		$workflow_ids = $triggers[ $matching_links[0] ];

		foreach ( $workflow_ids as $workflow_id ) {
			// $options = mailster( 'automations' )->get_trigger_option( $workflow, 'page_visit' );

			$this->add_job( $workflow_id, 'page_visit', $subscriber_id );
		}
	}

	private function add_job( $workflow, $trigger, $subscriber_ids, $step = null, $timestamp = null, $context = null ) {

		// only some triggers allow all susbscribers
		if ( ! $subscriber_ids ) {
			if ( ! in_array( $trigger, array( 'date', 'hook', 'anniversary', 'published_post' ) ) ) {
				return false;
			}
		}

		// serialize context if needed
		if ( $context && ! is_string( $context ) ) {
			$context = serialize( $context );
		}

		if ( ! is_array( $subscriber_ids ) ) {
			$subscriber_ids = array( $subscriber_ids );
		}

		require_once MAILSTER_DIR . 'classes/workflow.class.php';

		foreach ( $subscriber_ids as $i => $subscriber_id ) {
			$job = array(
				'subscriber_id' => (int) $subscriber_id,
				'workflow_id'   => (int) $workflow,
				'trigger'       => $trigger,
				'step'          => $step,
				'timestamp'     => $timestamp,
				'context'       => $context,
			);

			$wf = new MailsterWorkflow( $workflow, $trigger, $subscriber_id, $step, $timestamp, $context );

			$wf->run();
		}

		return true;
	}



	public function bulk_add( $workflow, $trigger, $subscriber_ids, $step, $timestamp = null, $context = null ) {

		foreach ( (array) $subscriber_ids as $subscriber_id ) {
			$this->add_job( $workflow, $trigger, $subscriber_id, $step, $timestamp, $context );
		}
	}

	public function get_workflows_by_trigger( $triggers ) {

		if ( ! is_array( $triggers ) ) {
			$triggers = array( $triggers );
		}

		$key = implode( '_', $triggers );

		if ( ! ( $workflow_ids = mailster_cache_get( 'workflow_by_trigger_' . $key ) ) ) {

			global $wpdb;

			// TODO DO NOT USE get_posts
			// $workflow_ids = get_posts(
			// array(
			// 'fields'     => 'ids',
			// 'post_type'  => 'mailster-workflow',
			// 'meta_key'   => 'trigger',
			// 'meta_query' => array(
			// array(
			// 'key'     => 'trigger',
			// 'value'   => $triggers,
			// 'compare' => '=',
			// ),
			// ),
			// )
			// );

			// faster, if we really need the post query it later explicitly
			$workflow_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'mailster-workflow' AND post_content REGEXP '\"trigger\":\"(" . implode( '|', $triggers ) . ")\"'" );

			mailster_cache_set( 'workflow_by_trigger_' . $key, $workflow_ids );

		}

		return $workflow_ids;
	}
}
