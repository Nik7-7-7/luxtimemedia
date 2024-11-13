<?php
/**
 * Simply Schedule Appointments embed Booking Api.
 *
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments embed Booking Api.
 *
 */
class SSA_Embed_Booking_App_Api extends WP_REST_Controller
{
  /**
   * Parent plugin class
   *
   * @var   class
   * @since 1.0.0
   */
  protected $plugin = null;

  /**
   * Constructor
   *
   * @since  1.0.0
   * @param  object $plugin Main plugin object.
   * @return void
   */
  public function __construct($plugin)
  {
    $this->plugin = $plugin;
    $this->hooks();
  }

  /**
   * Initiate our hooks
   *
   * @since  1.0.0
   * @return void
   */
  public function hooks()
  {
    $this->register_routes();
  }


  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes()
  {
    $version = '1';
    $namespace = 'ssa/v' . $version;
    $base = 'embed_booking_app';
    register_rest_route($namespace, '/' . $base, array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => array($this, 'get_embed_code'),
        'permission_callback' => '__return_true',
        'args' => array(

        ),
      ),
    ));
  }

  /**
   * Get embed code
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_REST_Response
   */
public function get_embed_code($request)
  {
    $att = ssa()->shortcodes->get_ssa_booking_arg_defaults();
    $iframe = ssa()->shortcodes->ssa_booking($att);    
    // Define the iframe source and the required scripts
    // get site domain 
    $domain = home_url();
    $scripts = [
      $domain . "/wp-content/plugins/simply-schedule-appointments/assets/js/iframe-outer.js?ver=6.7.27",
      $domain . "/wp-content/plugins/simply-schedule-appointments/assets/js/ssa-tracking.js?ver=6.7.27"
    ];

    // Generate the embed code as a JavaScript script
    $embed_code = "(function(w, d) {
    var currentScript = d.currentScript;
    var iframe = new DOMParser().parseFromString('$iframe', 'text/html').body.firstChild;
    iframe.style.display = 'flex';
    currentScript.parentNode.insertBefore(iframe, currentScript);
    function loadScript(src, callback) {
        var script = d.createElement('script');
        script.type = 'text/javascript';
        script.src = src;
        script.onload = callback;
        d.head.appendChild(script);
    }
    var scripts = " . json_encode($scripts) . ";
    scripts.forEach(src => {
        loadScript(src);
    });
})(window, document);";

    $response = new WP_REST_Response($embed_code, 200);
    $response->header('Content-Type', 'application/javascript');
    $response->header('Access-Control-Allow-Origin', '*');
    $response->header('Access-Control-Allow-Methods', 'GET');
    $response->header('Access-Control-Allow-Headers', 'Content-Type');

    return $response;
  }
}
