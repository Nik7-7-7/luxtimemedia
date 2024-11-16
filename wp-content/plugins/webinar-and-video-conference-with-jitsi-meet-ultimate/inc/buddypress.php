<?php 

defined( 'ABSPATH' ) || exit;

if(!class_exists('Jitsi_Pro_Buddypress')){
    class Jitsi_Pro_Buddypress{
        /** @var null */
		private static $instance = null;

        /**
         * Jitsi_Pro_Buddypress constructor
         */
        public function __construct(){
            add_action('bp_after_group_activity_post_form', [$this, 'jitsi_add_meeting_after_group_activity_post_form']);
        }

        public function jitsi_add_meeting_after_group_activity_post_form(){
            if ( is_user_logged_in() && bp_group_is_member() ) {
                $name = bp_get_group_name();
                if(!empty($_GET) && isset($_GET['room']) && $_GET['room'] == $name){
                    $uri = $_SERVER['REQUEST_URI'];
                    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://"; 
                    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $base_url = strtok($url, '?');
                    $parsed_url = parse_url($url); 
                    $query = $parsed_url['query'];              
                    parse_str( $query, $parameters );           
                    unset( $parameters['room'] );     
                    if(count($parameters) > 0){
                        $new_query = http_build_query($parameters); 
                        $new_url = $base_url.'?'.$new_query;
                    } else {
                        $new_url = $base_url;
                    }

                    echo do_shortcode('[jitsi-meet-wp name="'.$name.'" width="1280" height="720" enablewelcomepage="0"/]');
                    ?>
                    <div class="jitsi-group-meeting-form">
                        <a href="<?php echo esc_url($new_url) ?>" class="button large primary button-primary" role="button">Leave Room</a>
                    </div>
                    <?php 
                } else {
                    ?>
                    <form class="jitsi-group-meeting-form" method="get">
                        <input type="hidden" name="room" value="<?php echo esc_attr($name); ?>"/>
                        <button type="submit">Join Room</button>
                    </form>
                    <?php 
                }
            }
        }

        /**
		 * @return Jitsi_Pro_Buddypress|null
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
    }
}

Jitsi_Pro_Buddypress::instance();