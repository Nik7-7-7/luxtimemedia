<?php
/**
 * Cron file process all or single campaign
 */

//set main timer for cron start and end
global $wp_automatic_timer;
$wp_automatic_timer = microtime(true);

// no cache
if ( ! headers_sent() ) {
	header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
	header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
}

//if fatal disabled OPT_NO_FATAL
$wp_automatic_options = get_option ( 'wp_automatic_options', array () );

// Verify valid ID
if (isset ( $_GET ['id'] )) {
	
	// Integer value from id , this is a specific campaign
	$id = intval ( $_GET ['id'] );
	if (! is_int ( $id ))
		exit ();
} else {

	//update when a last time an external cron job was triggered
	update_option ( 'wp_automatic_cron_last', time () );
	
	$id = false;
	echo '<strong>Welcome</strong> to WordPress Automatic cron job, current system time is:' . time() .  '...<br>';
	wp_automatic_log_new('EXTERNAL Cron job triggered', 'Cron job just started now..... ');

	// check if there is an already running cron job by checking if an option exist with the name wp_automatic_cron_running
	$wp_automatic_cron_running = get_option ( 'wp_automatic_cron_running', false );


	

	//if fatal disabled
	if( in_array('OPT_NO_FATAL',$wp_automatic_options) ){
		
		//fatal handler is disabled, the plugin will not report completed running, just do nothing

	}elseif ($wp_automatic_cron_running != false) {
		
		// minutes between now and the time the cron started
		$minutes = (time () - $wp_automatic_cron_running) / 60;
			 
		// check if the cron is running for more than 5 minutes
		if (time () - $wp_automatic_cron_running > 300) {

			
			// more than 10 minutes, kill it
			echo '<br><strong>Warning:</strong> There is an already running cron job that is running for more than 5 minutes ('. $minutes  .'), killing it now...';
			wp_automatic_log_new('EXTERNAL Cron job killed', 'Cron job was running for more than 5 minutes, killing it now..... ');
			
			// delete the option
			delete_option ( 'wp_automatic_cron_running' );
		} else {

			//minutes rounded to two decimal places
			$minutes_formated = number_format($minutes, 2);
			
			// less than 5 minutes, exit
			echo '<br><strong>Info:</strong> There is an already running cron job that is running for less than 5 minutes ('. $minutes_formated  .'), skipping it now...';
			wp_automatic_log_new('EXTERNAL Cron job skipped', 'Cron job was running for less than 5 minutes, skipping it now..... ');
			exit ();
		}
	}
	else
	{
		echo '<br><strong>Info:</strong> No running cron job found, starting now...';
		 
	}
	
	// set that a cron is running and set the value to the time it is running on 
	update_option ( 'wp_automatic_cron_running', time () );



}

 
 
//performance report function & prices update after price update
if(! in_array('OPT_NO_FATAL',$wp_automatic_options))
register_shutdown_function ( "wp_automatic_fatal_handler" );

function wp_automatic_fatal_handler() {
	
	global $wp_automatic_timer;
	$time_used = microtime( true ) - $wp_automatic_timer;
	
	/*
	 */
	$errfile = "unknown file";
	$errstr = "shutdown";
	$errno = E_CORE_ERROR;
	$errline = 0;
	$error = error_get_last ();
	
	if ($_SERVER ['HTTP_HOST'] == 'localhost' || isset ( $_GET ['debug'] )) {
		echo '<br>';
		print_r ( $error );
	}
	
	// updating an amazon product price
	$wp_automatic_options = get_option ( 'wp_automatic_options', array () );
	
	if (in_array ( 'OPT_AMAZON_PRICE', $wp_automatic_options ) && ! isset ( $_GET ['id'] )) {
		 
		if ( in_array('OPT_AMAZON_NOAPI', $wp_automatic_options) || wp_automatic_trim( get_option ( 'wp_amazonpin_apvtk', '' ) ) == '' ){
				// no API price updates
			wp_automatic_amazon_prices_update ( false );
		}else{
			
			//API
			wp_automatic_amazon_prices_update ( true );
			
		}
		
	}

	// update eBay products prices OPT_EBAY_PRICE
	if (in_array ( 'OPT_EBAY_PRICE', $wp_automatic_options ) && ! isset ( $_GET ['id'] )) {
		wp_automatic_ebay_prices_update ();
	}
	
	wp_automatic_log_new('The end', 'Plugin completed its work in (' . $time_used.  ') seconds and reached the end successfully, time to die' );

	//delete the option flag that the campaign is running
	delete_option ( 'wp_automatic_cron_running' );
	
	//report performance 
	echo '<br><i><small>Plugin completed running.. peak ram used was: ' . number_format ( memory_get_peak_usage () / (1024 * 1024), 2 ) . ' MB, current:' . number_format ( memory_get_usage () / (1024 * 1024), 2 ) . ', DB queries count:' . get_num_queries () . ', Time used: ' . $time_used . ' seconds</small></i>';
}



// table version
$wp_automatic_version = get_option ( 'wp_automatic_version', 199 );

if ($wp_automatic_version < 202) {
	
	$update_url = home_url ( '?wp_automatic=test' );
	echo 'Tables update required. Please visit the update URL <a target="_blank" href="' . $update_url . '">HERE</a>, it will keep refreshing, leave it till it tells you congratulation!';
	exit ();
}

 

// Inistantiate campaign processor class
require_once 'campaignsProcessor.php';

//wrap
$CampaignProcessor = new CampaignProcessor ();

// Trigger Processing
$CampaignProcessor->process_campaigns ( $id );

?>
