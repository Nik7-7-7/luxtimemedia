<?php 
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Jitsi_Meet_WP_WooCommerce_Booking')) {
    final class Jitsi_Meet_WP_WooCommerce_Booking{
        public $prefix;
        private static $_instance = null;

        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public function __clone()
        {
            // Cloning instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'jitsi-pro'), '1.0.0');
        }

        public function __wakeup()
        {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'jitsi-pro'), '1.0.0');
        }

        public function __construct()
        {
            $this->prefix = 'jitsi_pro__';

            //Handle force login 
            add_action( 'template_redirect', [$this, 'jitsi_checkout_redirect_non_logged_to_login_access']);
            add_action( 'woocommerce_before_cart', [$this, 'jitsi_customer_redirected_displaying_message']);

            //Product data tabs
            add_action( 'woocommerce_product_data_tabs', [$this, 'jitsi_woocommerce_booking_option_tab'] );
            add_action('woocommerce_product_data_panels', [$this, 'jitsi_woocommerce_booking_option_panel']);
            add_action( 'woocommerce_process_product_meta_booking', [$this, 'jitsi_woocommerce_booking_option_fields_save'] );

            add_action( 'woocommerce_order_status_completed', array( $this, 'jitsi_woocommerce_booking_completed' ), 5 );
            add_action( 'woocommerce_order_status_processing', array( $this, 'jitsi_woocommerce_booking_completed' ), 5 );

            //Booking
            add_action( 'woocommerce_booking_paid', array( $this, 'jitsi_woocommerce_booking_paid' ), 10 );
            add_action( 'woocommerce_booking_confirmed', array( $this, 'jitsi_woocommerce_booking_paid' ), 10 );
            add_action( 'woocommerce_booking_cancelled', array( $this, 'jitsi_woocommerce_booking_cancelled' ), 10 );
            add_action( 'before_delete_post', [ $this, 'jitsi_woocommerce_before_delete_booking' ] );

            //Template
            add_filter( 'woocommerce_locate_template', [ $this, 'jitsi_booking_templates' ], 10, 3 );
        }

        function jitsi_checkout_redirect_non_logged_to_login_access() {
            if( is_checkout() && !is_user_logged_in()){     
                wp_redirect( get_permalink( get_option('woocommerce_myaccount_page_id') ) );
                exit;
            }
        }

        function jitsi_customer_redirected_displaying_message() {
            if( !is_user_logged_in() ){
                $message = __('To access checkout, you need first to be logged in', 'jitsi-pro');
                $button_text = __('Login area', 'jitsi-pro');        
                $cart_link = get_permalink( get_option('woocommerce_myaccount_page_id') );        
                wc_add_notice(  $message . '<a href="' . $cart_link . '" class="button wc-forward">' . $button_text . '</a>', 'notice' );
            }
        }

        public function jitsi_woocommerce_booking_option_tab($tabs){
            $tabs['meeting_booking_info'] = [
                'label' => __('Jitsi Meeting', 'jitsi-pro'),
                'target' => 'jitsi_meeting_product_data_booking',
                'class' => ['show_if_booking'],
                'priority' => 25
            ];
            return $tabs;
        }

        public function jitsi_woocommerce_booking_option_panel(){
            echo '<div id="jitsi_meeting_product_data_booking" class="panel woocommerce_options_panel hidden">';
            
            woocommerce_wp_checkbox( array(
				'id' 		=> '_connect_jitsi_meeting_for_booking',
				'label' 	=> __( 'Meeting product?', 'jitsi-pro' ),
			) );

            woocommerce_wp_select(
                array(
                    'id'          => '_jitsi_meeting_host',
                    'label'       => __( 'Default Host', 'jitsi-pro' ),
                    'options'     => jitsi_user_options(),
                    'description' => __( 'Select which host would be responsible for hosting this booking product. Add more users to show them here.', 'jitsi-pro' ),
                    'desc_tip'    => true,
                )
            );
         
            echo '</div>';
        }

        function jitsi_woocommerce_booking_option_fields_save( $post_id ) {
	
            $connect_jitsi_meeting = isset( $_POST['_connect_jitsi_meeting_for_booking'] ) ? 'yes' : 'no';
            update_post_meta( $post_id, '_connect_jitsi_meeting_for_booking', $connect_jitsi_meeting );
            
            if ( isset( $_POST['_jitsi_meeting_host'] ) ) :
                update_post_meta( $post_id, '_jitsi_meeting_host', $_POST['_jitsi_meeting_host'] );
            endif;
            
        }

        /**
         * Create booking meeting based on order ID
         *
         * @param $order_id
         */
        public function jitsi_woocommerce_booking_completed( $order_id ) {
            $booking_ids = \WC_Booking_Data_Store::get_booking_ids_from_order_id( $order_id );
            if ( ! empty( $booking_ids ) ) {
                foreach ( $booking_ids as $booking_id ) {
                    $exists = get_post_meta( $booking_id, '_jitsi_meet_woo_addon_meeting_exists', true );
                    if ( empty( $exists ) ) {
                        $wc_booking = get_wc_booking( $booking_id );
                        $host= get_post_meta($wc_booking->get_product_id(), '_jitsi_meeting_host', true);

                        //Create Meeting
                        $this->create_meeting( $wc_booking, $booking_id, $order_id, $host );
                    }
                }
            }
        }


        /**
         * Create Meeting if booking status is changed to Paid in wc-booking order page.
         *
         * @param $booking_id
         */
        public function jitsi_woocommerce_booking_paid( $booking_id ) {
            $wc_booking = get_wc_booking( $booking_id );
            $order_id   = $wc_booking->get_order_id();
            $host= get_post_meta($wc_booking->get_product_id(), '_jitsi_meeting_host', true);
            $exists = get_post_meta( $booking_id, '_jitsi_meet_woo_addon_meeting_exists', true );
            if ( empty( $exists ) ) {
                $this->create_meeting( $wc_booking, $booking_id, $order_id, $host );
            }
        }

        /**
         * Delete meeting When booking order is Cancelled from wc-booking page.
         *
         * @param $booking_id
         */
        public function jitsi_woocommerce_booking_cancelled( $booking_id ) {
            $booking                = get_wc_booking( $booking_id );
            $order_id               = $booking->get_order_id();
            $product_id             = $booking->get_product_id();
            $bookings_on_date_count = $this->get_bookings_on_date_count( $booking_id, $product_id );

            if ( $bookings_on_date_count >= 1 ) {
                return;
            }

            $this->delete_meeting( $booking_id, $order_id, $product_id );
        }

        /**
         * @param $booking_id
         * @param $product_id
         *
         * @return int|void
         */
        public function get_bookings_on_date_count( $booking_id, $product_id ) {
            $meeting_start_time = get_post_meta( $booking_id, '_booking_start', true );
            $meeting_end_time   = get_post_meta( $booking_id, '_booking_end', true );
            $booking_statuses   = (array) get_wc_booking_statuses();
            $booking_statuses[] = 'trash';
            $args               = [
                'object_id'    => $product_id,
                'object_type'  => 'product',
                'status'       => $booking_statuses,
                'limit'        => - 1,
                'date_between' => [
                    'start' => strtotime( $meeting_start_time ),
                    'end'   => strtotime( $meeting_end_time ),
                ],
            ];
            $bookings_on_date   = \WC_Booking_Data_Store::get_booking_ids_by( $args );

            return count( $bookings_on_date );
        }

        /**
         * Delete Zoom Meeting Finally !
         *
         * @param $booking_id
         * @param $order_id
         * @param $product_id
         */
        protected function delete_meeting( $booking_id, $order_id, $product_id ) {
            $meeting      = get_post_meta( $booking_id, '_jitsi_meet_woo_addon_meeting_exists', true );
            if ( ! empty( $meeting ) ) {
                wp_delete_post($meeting, true);
            }
        }

        /**
         * Delete Meeting when booking is deleted
         *
         * @param $booking_id
         */
        public function jitsi_woocommerce_before_delete_booking( $booking_id ) {
            if ( 'wc_booking' != get_post_type( $booking_id ) ) {
                return;
            }
            $booking = get_wc_booking( $booking_id );
            if ( is_object( $booking ) ) {
                $exists     = get_post_meta( $booking_id, '_jitsi_meet_woo_addon_meeting_exists', true );
                $product_id = $booking->get_product_id();
                if ( ! empty( $exists ) ) {
                    $bookings_on_date_count = $this->get_bookings_on_date_count( $booking_id, $product_id );

                    //if count is greater than 1 it indicates that there are still other bookings on this same time for this same product so bail early
                    if ( $bookings_on_date_count > 1 ) {
                        return;
                    }
                    $this->delete_meeting( $booking_id, $booking->get_order_id(), $product_id );
                }
            }
        }

        /**
         * Create Meeting Finally !
         *
         * @param \WC_Booking $wc_booking
         * @param             $booking_id
         * @param             $order_id
         * @param             $host
         */
        protected function create_meeting( $wc_booking, $booking_id, $order_id, $host = false ) {
            $product_id   = $wc_booking->get_product_id();
            $jitsi_enabled = get_post_meta( $product_id, '_connect_jitsi_meeting_for_booking', true );

            if ( empty( $jitsi_enabled ) ) {
                return;
            }

            $start_date = $wc_booking->get_start_date( 'Y-m-d', ' H:i' );
            $end_date   = $wc_booking->get_end_date( 'Y-m-d', ' H:i' );
            $duration   = ! empty( $end_date ) ? ( strtotime( $end_date ) - strtotime( $start_date ) ) / 60 : 60;
            $timezone = get_system_timezone();

            if ( ! empty( $host ) ) {

                $postarr = array(
                    'post_type'                     => 'meeting',
                    'post_author'                   => $host,
                    'post_title'                    => "Booking Session for " . get_the_title( $product_id ) . '-' . $booking_id,
                    'post_status'                   => 'publish',
                    'meta_input'   => array(
                        $this->prefix . 'meeting_settings' => array(
                            $this->prefix . 'host'                  => $host,
                            $this->prefix . 'recurring'             => 0,
                            $this->prefix . 'start_time'            => $start_date,
                            $this->prefix . 'booked_meeting'        => true,
                            $this->prefix . 'booked_for'            => $wc_booking->get_customer_id()
                        )
                    )
                );
                
                $postarr['meta_input'][$this->prefix . 'meeting_settings'][$this->prefix . 'duration'] = $duration / 60;

                $postid = wp_insert_post( $postarr, true, false );

                if(!is_wp_error($postid)){
                    update_post_meta( $booking_id, '_jitsi_meet_woo_addon_meeting_exists', $postid );
                }
            }
        }

        public function jitsi_booking_templates( $template, $template_name, $template_path ) {

            $basename          = basename( $template );
            $override_template = array(
                'bookings.php'            => JITSI_ULTIMATE_FILE_PATH . 'inc/bookings.php'
            );
        
            if ( isset( $override_template[ $basename ] ) ) {
                $template = $override_template[ $basename ];
            }
        
            return $template;
        }

        public static function get_join_link( $booking ) {
            $meeting = get_post_meta( $booking->get_id(), '_jitsi_meet_woo_addon_meeting_exists', true );
            $meeting_exist = get_post($meeting);
            if(!empty($meeting_exist) && $meeting_exist->post_status == 'publish'){
                if ( ! empty( $meeting ) ) {
                    if ( 'paid' === $booking->get_status() || 'confirmed' === $booking->get_status() || 'paid' === $booking->get_status() || 'complete' === $booking->get_status() ) {
                        $html     = '<a href="' . esc_url( get_permalink($meeting)) . '">' . esc_html__( 'Join', 'jitsi-pro' ) . '</a>';
                    } else {
                        $html = __( 'You have not completed your order yet.', 'jitsi-pro' );
                    }
                } else {
                    $html = __( 'You have not completed your order yet.', 'jitsi-pro' );
                }
            } else {
                $html = __( 'Meeting has been deleted.', 'jitsi-pro' );
            }
    
            return $html;
        }
    }

    Jitsi_Meet_WP_WooCommerce_Booking::instance();
}