<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Mailster_Automations_Table extends WP_Posts_List_Table {

	public function display() {

		if ( parent::has_items() || isset( $_REQUEST['post_status'] ) ) {
			parent::display();
			return;
		}

		include_once MAILSTER_DIR . 'views/automation/empty.php';
	}
}
