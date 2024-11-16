<?php
// Path: p_scripts.php
// this file is responsible for loading the scripts and styles for the plugin on front end and admin

//front end scripts

function wp_automatic_front_end_scripts()
{

	//jquery
	wp_enqueue_script('jquery');


	if (is_single())
	{
		//custom gallery script
		wp_enqueue_script(
		'wp_automatic_gallery', plugins_url('/js/main-front.js', __FILE__)
		);

		//custom gallery style
		wp_enqueue_style('wp_automatic_gallery_style', plugins_url('/css/wp-automatic.css', __FILE__), array(), '1.0.0');

	}
}


//wp automatic options
$wp_automatic_options = get_option('wp_automatic_options',array());

//if OPT_NO_FRONT_JS is in array do not load front end scripts
if ( ! in_array('OPT_NO_FRONT_JS', $wp_automatic_options))
{
	add_action('wp_enqueue_scripts', 'wp_automatic_front_end_scripts'); // wp_enqueue_scripts action hook to link only on the front-end
}

add_action('admin_print_scripts-' . 'post-new.php', 'wp_automatic_admin_scripts');
add_action('admin_print_scripts-' . 'post.php', 'wp_automatic_admin_scripts');

function wp_automatic_admin_scripts()
{

	global $post_type;

	if ('wp_automatic' == $post_type)
	{

		wp_enqueue_style('wp_automatic_basic_styles', plugins_url('css/style.css', __FILE__), array(), '1.1.3');
		wp_enqueue_style('wp_automatic_uniform', plugins_url('css/uniform.css', __FILE__));
		wp_enqueue_style('wp_automatic_gcomplete', plugins_url('css/jquery.gcomplete.default-themes.css', __FILE__));


		wp_enqueue_script('wp_automatic_unifom_script', plugins_url('js/jquery.uniform.min.js', __FILE__), array(), '1.2.0');
		wp_enqueue_script('wp_automatic_jqtools_script', plugins_url('js/jquery.tools.js', __FILE__));
		wp_enqueue_script('wp_automatic_main_script', plugins_url('js/main.js', __FILE__), array(), '1.10.13');

		//enqueue tutorial script
		wp_enqueue_script('wp_automatic_tutorial_script', plugins_url('js/tutorials.js', __FILE__), array(), '1.0.23');

		wp_enqueue_script('wp_automatic_jqcomplete_script', plugins_url('js/jquery.gcomplete.0.1.2.js', __FILE__));
	}

}


//log page
function wp_automatic_admin_head_log()
{

	echo '<script src="' . plugins_url('js/jquery.tools.js', __FILE__) . '" type="text/javascript"></script>';
	echo '<script src="' . plugins_url('js/jquery.uniform.min.js', __FILE__) . '" type="text/javascript"></script>';
	echo '<script src="' . plugins_url('js/main_log.js', __FILE__) . '" type="text/javascript"></script>';
}
add_action('admin_head-wp_automatic_page_gm_log', 'wp_automatic_admin_head_log');

//script for import button in wp_automatic posts page
// Enqueue JavaScript file on edit.php page for wp_automatic custom post type
add_action( 'admin_enqueue_scripts', 'wp_automatic_enqueue_script' );
function wp_automatic_enqueue_script( $hook ) {
    global $typenow;
    if ( 'edit.php' !== $hook || 'wp_automatic' !== $typenow ) {
        return;
    }
    wp_enqueue_script( 'wp_automatic_script_campaigns_page', plugins_url( 'js/wp_automatic_script_campaigns_page.js', __FILE__ ), array(), '1.0', true );
}


?>