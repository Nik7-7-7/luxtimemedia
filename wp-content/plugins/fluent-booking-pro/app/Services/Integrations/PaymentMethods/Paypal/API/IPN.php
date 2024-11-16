<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Paypal\API;

use FluentBookingPro\App\Services\Integrations\PaymentMethods\Paypal\PaypalSettings;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\Booking;

class IPN
{
    public function verifyIPN()
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }

        status_header(200);

        $post_data = '';

        if (ini_get('allow_url_fopen')) {
            $post_data = file_get_contents('php://input');
        }

        $encoded_data = 'cmd=_notify-validate';

        $arg_separator = ini_get('arg_separator.output');

        if ($post_data || strlen($post_data) > 0) {
            $encoded_data .= $arg_separator . $post_data;
        } elseif ($_POST) {
            foreach ($_POST as $key => $value) {
                $encoded_data .= $arg_separator . "$key=" . urlencode($value);
            }
        } else {
            return;
        }

        // Convert collected post data to an array
        parse_str($encoded_data, $encoded_data_array);

        foreach ($encoded_data_array as $key => $value) {
            if (false !== strpos($key, 'amp;')) {
                $new_key = str_replace('&amp;', '&', $key);
                $new_key = str_replace('amp;', '&', $new_key);
                unset($encoded_data_array[$key]);
                $encoded_data_array[$new_key] = $value;
            }
        }

        $encoded_data_array = apply_filters('fluent_booking/process_paypal_ipn_data', $encoded_data_array);

        $defaults = array(
            'txn_type'       => '',
            'payment_status' => '',
            'custom'         => ''
        );

        $encoded_data_array = wp_parse_args($encoded_data_array, $defaults);

        $bookingId = intval(Arr::get($_GET, 'booking_id', ''));

        $booking = Booking::find($bookingId);

        if (!$booking) {
            return;
        }

        $paypalSettings = new PaypalSettings();

        $isEnabledIPN = $paypalSettings->isEnabledIPN();

        if ($isEnabledIPN) {
            $validate_ipn = wp_unslash($_POST); // WPCS: CSRF ok, input var ok.

            $validate_ipn['cmd'] = '_notify-validate';

            // Send back post vars to paypal.
            $params = [
                'body'        => $validate_ipn,
                'timeout'     => 60,
                'httpversion' => '1.1',
                'compress'    => false,
                'decompress'  => false,
                'user-agent'  => 'FluentBooking/' . FLUENT_BOOKING_VERSION,
            ];

            $sandbox = $paypalSettings->isTest() ? '.sandbox' : '';

            $paypalApi = 'https://www' . $sandbox . '.paypal.com/cgi-bin/webscr';

            // Post back to get a response.
            $response = wp_safe_remote_post($paypalApi, $params);
            if (is_wp_error($response)) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $bookingId,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Paypal IPN Error', 'fluent-booking-pro'),
                    'description' => __('Payment failed for paypal IPN error', 'fluent-booking-pro')
                ]);
                return;
            }
            if (wp_remote_retrieve_body($response) !== 'VERIFIED') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $bookingId,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Paypal IPN Not Verified', 'fluent-booking-pro'),
                    'description' => __('Payment failed for paypal IPN verification', 'fluent-booking-pro')
                ]);
                return;
            }
        }

        do_action('fluent_booking/ipn_paypal_action_web_accept', $encoded_data_array, $bookingId, $booking);

        exit(200);
    }
}
