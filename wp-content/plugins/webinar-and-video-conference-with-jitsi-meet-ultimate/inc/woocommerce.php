<?php 
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Jitsi_Meet_WP_WooCommerce')) {
    final class Jitsi_Meet_WP_WooCommerce{
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
            add_action( 'woocommerce_product_data_tabs', [$this, 'jitsi_woocommerce_option_tab'] );
            add_action('woocommerce_product_data_panels', [$this, 'jitsi_woocommerce_option_panel']);
            add_action( 'woocommerce_process_product_meta_simple', [$this, 'jitsi_woocommerce_option_fields_save'] );
            add_action( 'woocommerce_after_order_notes', [$this, 'jitsi_woocommerce_meeting_registration'] );
            add_action( 'woocommerce_checkout_update_order_meta', [$this, 'jitsi_woocommerce_meeting_enroll'] );
            add_action( 'woocommerce_email_after_order_table', [$this, 'jitsi_woocommerce_meeting_to_mail'], 10, 4 );
        }

        public function jitsi_woocommerce_option_tab($tabs){
            $tabs['meeting_info'] = [
                'label' => __('Jitsi Meeting', 'jitsi-pro'),
                'target' => 'jitsi_meeting_product_data',
                'class' => ['show_if_simple'],
                'priority' => 25
            ];
            return $tabs;
        }

        public function jitsi_woocommerce_option_panel(){
            echo '<div id="jitsi_meeting_product_data" class="panel woocommerce_options_panel hidden">';
            
            woocommerce_wp_checkbox( array(
				'id' 		=> '_connect_jitsi_meeting',
				'label' 	=> __( 'Meeting product?', 'jitsi-pro' ),
			) );

            woocommerce_wp_select([
                'id' => '_meeting_id',
                'label' => __('Meeting', 'jitsi-pro'),
                'options'   => $this->jitsi_get_post()
            ]);
         
            echo '</div>';
        }

        function jitsi_woocommerce_option_fields_save( $post_id ) {
	
            $connect_jitsi_meeting = isset( $_POST['_connect_jitsi_meeting'] ) ? 'yes' : 'no';
            update_post_meta( $post_id, '_connect_jitsi_meeting', $connect_jitsi_meeting );
            
            if ( isset( $_POST['_meeting_id'] ) ) :
                if($_POST['_meeting_id'] !== ($old_meeting = get_post_meta($post_id, '_meeting_id', true))){
                    update_post_meta( $old_meeting, '_product_id', false );
                }
                update_post_meta( $post_id, '_meeting_id', $_POST['_meeting_id'] );
                update_post_meta( $_POST['_meeting_id'], '_product_id', $post_id );
            endif;
            
        }
        
        public function jitsi_woocommerce_meeting_registration($checkout){
            $items = WC()->cart->get_cart();
            foreach($items as $item=>$value){
                $product = $value['data'];
                if($product->get_type() == 'simple'){
                    $id = $product->get_id();
                    if('yes' == $connect_meeting = get_post_meta($id, '_connect_jitsi_meeting', true)){
                        printf('<div class="jitsi_meeting_checkout"><h3>%2$s %1$s</h3>', esc_html__('Meeting Registration', 'jitsi-pro'), $product->get_name());
                        $quantity = $value['quantity'];
                        for($i = 1; $i <= $quantity; $i++){
                            printf('<div id="jitsi_meeting_checkout_fields_%1$s" class="jitsi_meeting_checkout_fields">', $i);
                            printf('<h4>%1$s %2$s</h4>', esc_html('Registrant', 'jitsi-pro'), $i);
                           
                            woocommerce_form_field( 'jitsi_meeting_registrant_name_' . $id . '_' . $i, array( 
                                'type' 			=> 'text', 
                                'label' 		=> __('Full Name', 'jitsi-pro'), 
                                'placeholder' 	=> __('Enter full name'),
                                'required'      => true,
                                ), $checkout->get_value( 'jitsi_meeting_registrant_name_' . $id . '_' . $i ));

                            woocommerce_form_field( 'jitsi_meeting_registrant_email_' . $id . '_' . $i, array( 
                                'type' 			=> 'email', 
                                'label' 		=> __('Email Adress', 'jitsi-pro'), 
                                'placeholder' 	=> __('Enter your email'),
                                'required'      => true,
                                ), $checkout->get_value( 'jitsi_meeting_registrant_email_' . $id . '_' . $i ));

                            echo '</div>';
                        }
                        echo '</div>';
                    }
                }
            }
        }

        public function jitsi_woocommerce_meeting_enroll($order_id){
            if ( ! $order_id ) {
                return;
            }

            $order = wc_get_order( $order_id );
            $items = $order->get_items();
            $attendee = array_intersect_key($_POST, array_flip(preg_grep('/^jitsi_meeting_registrant_email_/', array_keys($_POST))));
            foreach($items as $item=>$value){
                $id = $value->get_product_id();
                $product = wc_get_product($id);
                if($product->is_virtual()){
                    if('yes' == $connect_meeting = get_post_meta($id, '_connect_jitsi_meeting', true)){
                        $related_meeting = get_post_meta( $id, '_meeting_id', true );
                        $attendee_before = get_post_meta($related_meeting, 'registered_attendee', true) ? get_post_meta($related_meeting, 'registered_attendee', true) : [];
                        $thisAttendee = array_intersect_key($attendee, array_flip(preg_grep('/^jitsi_meeting_registrant_email_'.$id.'_/', array_keys($attendee))));
                        foreach($thisAttendee as $aitem){
                            $attendee_before[] = $aitem;
                            array_unique($attendee_before);
                            update_post_meta($related_meeting, 'registered_attendee', $attendee_before);
                        }
                    }
                }
            }
        }

        public function jitsi_woocommerce_meeting_to_mail($order, $sent_to_admin, $plain_text, $email){
            if($order->get_status() === 'completed'){
                $items = $order->get_items();
                $products = [];
                $attendee = array_intersect_key($_POST, array_flip(preg_grep('/^jitsi_meeting_registrant_email_/', array_keys($_POST))));
                foreach($items as $item=>$value){
                    $id = $value->get_product_id();
                    $productVal = wc_get_product($id);
                    if(false == ($thisAttendee = $value->get_meta('__jitsi_order_attendee'))){
                        $thisAttendee = array_intersect_key($attendee, array_flip(preg_grep('/^jitsi_meeting_registrant_email_'.$id.'_/', array_keys($attendee))));
                        $value->update_meta_data('__jitsi_order_attendee', $thisAttendee);
                        $value->save();
                    } 

                    if($productVal->get_type() == 'simple'){
                        if('yes' == $connect_meeting = get_post_meta($id, '_connect_jitsi_meeting', true)){
                            $related_meeting = get_post_meta( $id, '_meeting_id', true );
                            if($related_meeting){
                                $pitem['product'] = $productVal;
                                $pitem['type'] = 'simple';
                                $pitem['attendee'] = $thisAttendee;
                                $products[] = $pitem;
                            }
                        }
                    }
                    
                    if($productVal->get_type() === 'booking'){
                        $booking_id = WC_Booking_Data_Store::get_booking_ids_from_order_item_id($value->get_id())[0];
                        if('yes' == $connect_meeting = get_post_meta($id, '_connect_jitsi_meeting_for_booking', true)){
                            $related_meeting = get_post_meta( $booking_id, '_jitsi_meet_woo_addon_meeting_exists', true );
                            if($related_meeting){
                                $pitem['product'] = $productVal;
                                $pitem['type'] = 'booking';
                                $pitem['related_meeting'] = $related_meeting;
                                $products[] = $pitem;
                            }
                        }
                    }
                }

                if(is_array($products) && count($products) > 0){
                    ob_start();
                    ?>
                        <h2 style="color:#96588a;display:block;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left"><?php _e('Meeting Logins', 'jitsi-pro'); ?></h2>
                        <div style="margin-bottom:40px">
                            <table cellspacing="0" cellpadding="6" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif">
                                <thead>
                                    <tr>
                                        <th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php _e('Meeting Link', 'jitsi-pro') ?></th>
                                        <th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php _e('Login', 'jitsi-pro') ?></th>
                                        <th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php _e('Password', 'jitsi-pro') ?></th>
                                        <th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php _e('Time', 'jitsi-pro') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach($products as $product){
                                        $id = $product['product']->get_id();
                                        if($product['type'] == 'booking'){
                                            $related_meeting =  $product['related_meeting'];
                                            $meeting_metas = get_post_meta($related_meeting, 'jitsi_pro__meeting_settings', true);
                                            $meeting_time = $meeting_metas['jitsi_pro__start_time'];
                                            ?>
                                                <tr>
                                                    <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><a href="<?php echo get_permalink($related_meeting); ?>" target="_blank"><?php echo get_the_title($related_meeting) ?></a></td>
                                                    <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php _e('Woocommerce Login', 'jitsi-pro'); ?></td>
                                                    <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php _e('Woocommerce Password', 'jitsi-pro'); ?></td>
                                                    <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php echo $meeting_time; ?></td> 
                                                </tr>
                                            <?php
                                        } else {
                                            $related_meeting = get_post_meta( $id, '_meeting_id', true );
                                            $meeting_metas = get_post_meta($related_meeting, 'jitsi_pro__meeting_settings', true);
                                            $password = $meeting_metas['jitsi_pro__password'];
                                            $meeting_time = $meeting_metas['jitsi_pro__start_time'];
                                            foreach($product['attendee'] as $aitem){
                                                ?>
                                                    <tr>
                                                        <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><a href="<?php echo get_permalink($related_meeting); ?>" target="_blank"><?php echo get_the_title($related_meeting) ?></a></td>
                                                        <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php echo $aitem; ?></td>
                                                        <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php echo $password; ?></td>
                                                        <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php echo $meeting_time; ?></td> 
                                                    </tr>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php 
                    echo ob_get_clean();
                }
            } else {
                $items = $order->get_items();
                $products = [];
                $attendee = array_intersect_key($_POST, array_flip(preg_grep('/^jitsi_meeting_registrant_email_/', array_keys($_POST))));
                foreach($items as $item=>$value){
                    $id = $value->get_product_id();
                    if(false == ($thisAttendee = $value->get_meta('__jitsi_order_attendee'))){
                        $thisAttendee = array_intersect_key($attendee, array_flip(preg_grep('/^jitsi_meeting_registrant_email_'.$id.'_/', array_keys($attendee))));
                        $value->update_meta_data('__jitsi_order_attendee', $thisAttendee);
                        $value->save();
                    }                    
                }
            }
        }

        public function jitsi_get_post($id = null){
            if($id){
                return get_post($id);
            }
            $toSelect = [];
            $meetingposts = get_posts( array('numberposts' => -1, 'post_type'   => 'meeting') );
            foreach($meetingposts as $post){
                $toSelect[$post->ID] = $post->post_title;
            }
            return $toSelect;
        }
    }

    Jitsi_Meet_WP_WooCommerce::instance();
}