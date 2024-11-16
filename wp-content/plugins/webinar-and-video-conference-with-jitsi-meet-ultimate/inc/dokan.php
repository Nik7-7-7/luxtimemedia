<?php 

defined( 'ABSPATH' ) || exit;

if(!class_exists('Jitsi_Pro_Dokan')){
    class Jitsi_Pro_Dokan{
        /** @var null */
		private static $instance = null;

        /**
         * Jitsi_Pro_Dokan constructor
         */
        public function __construct(){
            add_action( 'init', array( $this, 'add_endpoint' ) );
            add_action( 'dokan_load_custom_template', [ $this, 'load_jitsi_meet_template' ] );
            add_filter( 'dokan_get_dashboard_nav', [ $this, 'add_jitsi_meet_menu_dokan' ] );
            add_action( 'jitsi_meet_content', [ $this, 'render_jitsi_meet_content' ], 10 );
        }

        public function add_endpoint() {
			add_rewrite_endpoint( 'jitsi-meet-meetings', EP_PAGES );
		}

        public function render_jitsi_meet_content() {
			echo '<div id="jitsi_meet">Jitsi Meet</div>';
		}

        public function load_jitsi_meet_template( $query_vars ) {
			if ( isset( $query_vars['jitsi-meet-meetings'] ) ) {
				do_action( 'dokan_dashboard_wrap_start' );
                ?>
                <div class="dokan-dashboard-wrap">
                    <?php
                    do_action( 'dokan_dashboard_content_before' );
                    ?>

                    <div class="dokan-dashboard-content jitsi-meet-content">

                        <article class="jitsi-meet-area">

                            <div class="entry-content">

                                <?php do_action( 'jitsi_meet_content' ); ?>

                            </div>

                        </article>
                    </div>

                    <?php
                    do_action( 'dokan_dashboard_content_after' );
                    ?>
                    </div>
                <?php 
                do_action( 'dokan_dashboard_wrap_end' );
			}
		}

        public function add_jitsi_meet_menu_dokan($urls){
            $urls['jitsi-meet-meetings'] = array(
                'title' => esc_html( 'Jitsi Meet' ),
                'icon'  => sprintf( '<img style="width: 20px;margin-right: 8px;" src="%s/assets/img/jitsi-white.png" />', JITSI_ULTIMATE_URL ),
                'url'   => dokan_get_navigation_url( 'jitsi-meet-meetings' ),
                'pos'   => 70,
            );

            return $urls;
        }

        /**
		 * @return Jitsi_Pro_Dokan|null
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
    }
}

Jitsi_Pro_Dokan::instance();