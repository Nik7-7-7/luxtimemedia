<?php

namespace FluentBookingPro\App\Services\Integrations\Woo;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        add_filter('fluent_booking/booking_data', function ($bookingData, $calendarSlot) {
            if ($calendarSlot->type != 'woo' || Arr::get($bookingData, 'source') != 'web' || !$this->isEnabled()) {
                return $bookingData;
            }

            $duration = Arr::get($bookingData, 'slot_minutes');
            
            $wooProductId = $this->getEventProductId($calendarSlot, $duration);

            if (!$wooProductId) {
                return $bookingData;
            }

            $wooProduct = wc_get_product($wooProductId);

            if (!$wooProduct || !$wooProduct->get_id()) {
                return $bookingData;
            }

            $bookingData['source'] = 'woo';
            $bookingData['payment_method'] = 'woocommerce';
            $bookingData['payment_status'] = 'pending';
            $bookingData['status'] = 'pending'; // we are making it pending

            add_filter('fluent_booking/booking_confirmation_response', function ($response, $booking) use ($wooProductId) {
                if ($booking->status != 'pending' || $booking->source != 'woo') {
                    return $response;
                }

                if (apply_filters('fluent_booking/will_refresh_woo_cart', true, $booking, $wooProductId)) {
                    WC()->cart->empty_cart();
                }

                $quantity = $booking->getMeta('quantity', 1);

                WC()->cart->add_to_cart($wooProductId, $quantity, 0, [], [
                    'fcal_id'        => $booking->id,
                    'booking_time'   => DateTimeHelper::convertFromUtc($booking->start_time, $booking->person_time_zone),
                    'guest_timezone' => $booking->person_time_zone
                ]);

                $redirect = add_query_arg([
                    'fluent-booking' => 'woo-checkout',
                    'fcal_hash'      => $booking->hash
                ], wc_get_checkout_url());

                $response['data']['redirect_to'] = $redirect;
                $response['data']['redirect_message'] = __('You are redirecting to checkout page to complete the appointment.', 'fluent-booking-pro');

                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'info',
                    'title'       => __('Redirect to WooCommerce checkout page', 'fluent-booking-pro'),
                    'description' => __('User redirected to Woo checkout page to comeplete the order.', 'fluent-booking-pro')
                ]);

                return $response;
            }, 10, 2);

            return $bookingData;
        }, 10, 2);

        add_action('fluent_booking/landing_page_route_woo-checkout', [$this, 'modifyCheckout']);
        add_filter('woocommerce_get_item_data', function ($item, $itemData) {
            if (!$this->isEnabled() || empty($itemData['fcal_id'])) {
                return $item;
            }
            $item['fluent_booking'] = [
                'key'     => __('Appointment', 'fluent-booking-pro'),
                'display' => Arr::get($itemData, 'booking_time') . ' (' . Arr::get($itemData, 'guest_timezone') . ')'
            ];
            return $item;
        }, 10, 2);

        add_action('woocommerce_order_status_changed', [$this, 'maybeBookingOrderStatusChanged'], 10, 4);

        add_filter('woocommerce_checkout_create_order_line_item_object', function ($item, $cart_item_key, $values, $order) {
            if (!$this->isEnabled() || empty($values['fcal_id'])) {
                return $item;
            }

            $fcalId = (int)Arr::get($values, 'fcal_id');
            if (!$fcalId) {
                return $item;
            }

            $order->update_meta_data('__fcal_booking_id', $fcalId);
            $item->add_meta_data('__fcal_booking_id', $fcalId);
            return $item;
        }, 10, 4);

        add_filter('woocommerce_hidden_order_itemmeta', function ($items) {
            $items[] = '__fcal_booking_id';
            return $items;
        });

        add_action('fluent_booking/booking_meta_info_main_meta_woo', [$this, 'pushOrderDataToBookingView'], 10, 2);

        add_action('woocommerce_thankyou', function ($orderId) {
            $order = wc_get_order($orderId);
            $fcalBookingId = (int)$order->get_meta('__fcal_booking_id');
            if (!$fcalBookingId || !$this->isEnabled()) {
                return;
            }

            $booking = Booking::find($fcalBookingId);
            if (!$booking) {
                return;
            }

            if ($redirectUrl = $booking->getRedirectUrlWithQuery()) {
                wp_redirect($redirectUrl);
                exit;
            }
            ?>
            <div class="fcal_booking_details">
                <h2 class="woocommerce-column__title"><?php esc_html_e('Booking Details', 'fluent-booking-pro'); ?></h2>
                <div class="fcal_booking_info">
                    <ul>
                        <li>
                            <b><?php esc_html_e('Meeting Info:', 'fluent-booking-pro'); ?></b> <?php echo esc_html($booking->getBookingTitle()); ?>
                        </li>
                        <li>
                            <b><?php esc_html_e('Date & Time:', 'fluent-booking-pro'); ?></b> <?php echo esc_html($booking->getShortBookingDateTime($booking->person_time_zone)); ?>
                            (<?php echo esc_html($booking->person_time_zone); ?>)
                        </li>
                        <li>
                            <b><?php esc_html_e('Status:', 'fluent-booking-pro'); ?></b> <?php echo esc_html(ucfirst($booking->status)); ?>
                        </li>
                        <li>
                            <a href="<?php echo esc_url($booking->getConfirmationUrl()); ?>"><?php esc_html_e('View Full Meeting Details', 'fluent-booking-pro'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
        });
    }

    public function modifyCheckout($data)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $bookingHash = sanitize_text_field($data['fcal_hash']);
        $booking = Booking::where('hash', $bookingHash)->first();
        if (!$booking) {
            return;
        }

        // set checkout field first name & last name
        add_filter('woocommerce_checkout_fields', function ($fields) use ($booking) {

            if (!empty($fields['billing']['billing_first_name'])) {
                $fields['billing']['billing_first_name']['default'] = $booking->first_name;
            }

            if (!empty($fields['billing']['billing_last_name']) && $booking->last_name) {
                $fields['billing']['billing_last_name']['default'] = $booking->last_name;
            }

            if (!empty($fields['billing']['billing_phone']) && $booking->phone) {
                $fields['billing']['billing_phone']['default'] = $booking->phone;
            }

            if (!empty($fields['billing']['billing_email']) && $booking->email) {
                $fields['billing']['billing_email']['default'] = $booking->email;
            }
            return $fields;
        }, 100);
    }

    public function maybeBookingOrderStatusChanged($orderId, $from, $to, $order)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $paidStatuses = wc_get_is_paid_statuses();
        if (in_array($to, $paidStatuses)) {
            $fcalBookingId = $order->get_meta('__fcal_booking_id');
            if (!$fcalBookingId) {
                return;
            }
            $booking = Booking::find($fcalBookingId);
            if (!$booking || $booking->source_id) {
                return;
            }

            if ($booking->status != 'pending') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'success',
                    'title'       => __('Woo: Booking status could not be changed', 'fluent-booking-pro'),
                    'description' => sprintf(
                    /* translators: Notification message indicating that the booking status could not be changed because it is in a certain status. %1$s is the current booking status, %2$s is a link to view the order */
                    __('Booking status could not be changed as it is in %1$s status. %2$sView Order%3$s', 'fluent-booking-pro'),
                    $booking->status,
                    '<a target="_blank" href="' . $order->get_edit_order_url() . '">',
                    '</a>')
                ]);
                return;
            }

            $isRequireConfirmation = $booking->calendar_event->isConfirmationRequired($booking->start_time, $booking->created_at);

            if (!$isRequireConfirmation) {
                $booking->status = 'scheduled';
            }

            $booking->payment_status = 'paid';
            $booking->source_id = $order->get_id();
            $booking->save();

            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'success',
                'title'       => __('Woo: Booking status changed to scheduled', 'fluent-booking-pro'),
                'description' => sprintf(
                    /* translators: Notification message for the change of Woocommerce order status and booking status to scheduled. %1$s is the new order status, %2$s is a link to view the order */
                    __('Woocommerce order status changed to %1$s and booking status changed to scheduled. %2$sView Order%3$s', 'fluent-booking-pro'),
                    $to,
                    '<a target="_blank" href="' . $order->get_edit_order_url() . '">',
                    '</a>')
                ]);

            $bookingData = [
                'name'  => $booking->first_name . ' ' . $booking->last_name,
                'email' => $booking->email,
                'phone' => $booking->phone
            ];

            // this pre hook is for early actions that require for remote calendars and locations
            do_action('fluent_booking/pre_after_booking_' . $booking->status, $booking, $booking->calendar_event, $bookingData);

            do_action('fluent_booking/after_booking_' . $booking->status, $booking, $booking->calendar_event, $bookingData);

            // Order Comment
            $order->add_order_note(
                sprintf(
                    /* translators: Notification message for the change of booking status to scheduled. %1$s is the booking ID, %2$s is the full booking date and time with timezone, %3$s is a link to view the booking */
                    __('Booking #%1$s status changed to scheduled at %2$s. %3$sView Booking%4$s', 'fluent-booking-pro'),
                    $booking->id,
                    $booking->getFullBookingDateTimeText($booking->calendar->author_timezone, true) . ' (' . $booking->calendar->author_timezone . ')',
                    '<a target="_blank" href="' . Helper::getAppBaseUrl('scheduled-events?period=upcoming&booking_id=' . $booking->id) . '">',
                    '</a>'
                )
            );
            return;
        }

        if ($to != 'refunded') {
            return;
        }

        // Let's cancel the booking if any
        $fcalBookingId = $order->get_meta('__fcal_booking_id');

        if (!$fcalBookingId) {
            return;
        }

        $booking = Booking::find($fcalBookingId);

        if (!$booking || $booking->status != 'scheduled') {
            return;
        }

        $booking->payment_status = 'refunded';
        $booking->save();

        $booking->cancelMeeting(__('Cancelled by WooCommerce Order', 'fluent-booking-pro'), 'guest', get_current_user_id());

        $order->add_order_note(
            sprintf(
                /* translators: 1: Booking ID, 2: Opening link tag, 3: Closing link tag */
                __('Booking #%1$s status changed to cancelled. %2$sView Booking%3$s', 'fluent-booking-pro'),
                $booking->id,
                '<a target="_blank" href="' . Helper::getAppBaseUrl('scheduled-events?period=upcoming&booking_id=' . $booking->id) . '">',
                '</a>'
            ));

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'error',
            'title'       => __('Woo: Booking status changed to cancelled', 'fluent-booking-pro'),
            'description' => sprintf(
                /* translators: Notification message for the change of Woocommerce order status and booking status to cancelled. %1$s is the new order status, %2$s is a link to view the order */
                __('Woocommerce order status changed to %1$s and booking status changed to cancelled. %2$sView Order%3$s', 'fluent-booking-pro'),
                $to,
                '<a target="_blank" href="' . $order->get_edit_order_url() . '">',
                '</a>'
            )
        ]);
    }

    public function pushOrderDataToBookingView($meta, $booking)
    {
        $orderId = $booking->source_id;
        if (!$orderId) {
            return $meta;
        }

        $order = wc_get_order($orderId);
        if (!$order) {
            return $meta;
        }

        // Get WooCommerce Order Summary as html
        ob_start();
        printf(
        /* translators: 1: order number 2: order date 3: order status */
            esc_html__('Order #%1$s was placed on %2$s and is currently %3$s.', 'fluent-booking-pro'),
            '<mark class="order-number">' . $order->get_order_number() . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            '<mark class="order-date">' . wc_format_datetime($order->get_date_created()) . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            '<mark class="order-status">' . wc_get_order_status_name($order->get_status()) . '</mark>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
        wc_get_template('order/order-details.php', [
            'order'    => $order,
            'order_id' => $orderId,
        ]);

        echo '<p><a href="' . esc_url($order->get_edit_order_url()) . '" target="_blank">' . esc_html_e('View Order', 'fluent-booking-pro') . '</a></p>';

        $orderSummary = ob_get_clean();

        $meta[] = [
            'id'      => 'woo-order-summary',
            'title'   => __('Order Summary', 'fluent-booking-pro'),
            'content' => $orderSummary
        ];

        return $meta;
    }

    private function isEnabled()
    {
        return defined('WC_PLUGIN_FILE') && Helper::isModuleEnabled('woo');
    }

    private function getEventProductId($calendarEvent, $duration = null)
    {
        $paymentSettings = $calendarEvent->getMeta('payment_settings');

        if (!$paymentSettings || Arr::get($paymentSettings, 'enabled') != 'yes' || Arr::get($paymentSettings, 'driver') != 'woo') {
            return null;
        }

        if ($calendarEvent->isMultiDurationEnabled() && Arr::get($paymentSettings, 'multi_payment_enabled') == 'yes') {
            if ($productId = Arr::get($paymentSettings, 'multi_payment_woo_ids.'. $duration)) {
                return (int)$productId;
            }
            return null;
        }

        if (empty($paymentSettings['woo_product_id'])) {
            return null;
        }

        return (int)$paymentSettings['woo_product_id'];
    }
}
