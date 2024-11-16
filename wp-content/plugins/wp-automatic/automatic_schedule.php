<?php


add_filter ( 'cron_schedules', 'wp_automatic_once_a_minute' );
function wp_automatic_once_a_minute($schedules) {
	
	// Adds once weekly to the existing schedules.
	$schedules ['once_a_minute'] = array (
			'interval' => 60,
			'display' => __ ( 'once a minute' ) 
	);
	return $schedules;
}

if (! wp_next_scheduled ( 'wp_automatic_hook' )) {
	wp_schedule_event ( time (), 'once_a_minute', 'wp_automatic_hook' );
}

add_action ( 'wp_automatic_hook', 'wp_automatic_function' );
function wp_automatic_function() {
	
  
	$opt = get_option ( 'wp_automatic_options', array ('OPT_CRON') );
	
	//log current url $_SERVER['REQUEST_URI']
	//sanitize url for storage $_SERVER['REQUEST_URI']
	$sanizied_url = sanitize_text_field($_SERVER['REQUEST_URI']);
	//wp_automatic_log_new('INTERNAL Cron job URI', $sanizied_url );

	//if opt cron is enabled or URL contains wp_cron.php and wp_automatic is in the URL
	if (in_array ( 'OPT_CRON', $opt ) || ( stristr($_SERVER['REQUEST_URI'] , 'wp-cron.php') && stristr($_SERVER['REQUEST_URI'] , 'wp_automatic')  )  ) {

		//log cron trigger
		wp_automatic_log_new('INTERNAL Cron job triggered', 'Cron job just started now..... ');

		//check when the last time an external cron job was called and if it is less then 5 minutes return 
		$wp_automatic_cron_last = get_option ( 'wp_automatic_cron_last', 0 );

		if (time () - $wp_automatic_cron_last < 300) {
			wp_automatic_log_new('INTERNAL Cron job skipped', 'External Cron job was triggered less than 5 minutes ago, skipping internal cron job, disable this internal cron job option on the plugin settings page ');
			return;
		}
		
		// Camapign processor 
		require_once dirname(__FILE__) . '/campaignsProcessor.php';
		$CampaignProcessor = new CampaignProcessor() ;
		
		
		//wrap in try catch and log errors if any
		try {
			
			// Trigger Processing
			$CampaignProcessor->process_campaigns(false);
			
		} catch ( Exception $e ) {
			
			//log error
			wp_automatic_log_new('INTERNAL Cron job error', $e->getMessage() );
			
		}
		 
		 
 	
	} else {
		
	}

}