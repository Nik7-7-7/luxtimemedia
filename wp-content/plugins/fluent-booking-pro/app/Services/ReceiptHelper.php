<?php

namespace FluentBookingPro\App\Services;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\EditorShortCodeParser;

class ReceiptHelper
{

    public $app;

    public function getReceipt($hash = '')
    {
        if (!$hash) {
            return false;
        }

        $order = (new OrderHelper())->getOrderByHash($hash);
        if (!$order) {
            return false;
        }

        $this->app = App::getInstance();

        return $this->render($order);
    }

    public function render($order)
    {
        if (!$order) {
            return '<p class="fluent_booking_invalid_receipt">' . __('Invalid submission. No receipt found', 'fluent-booking-pro') . '</p>';
        }

        //to-do will be available from settings
        $receiptSettings = [
            'receipt_header' => '',
            'receipt_footer' => '',
        ];

        $booking = Booking::query()->where('hash', $order->uuid)->first();

        $receiptSettings['receipt_header'] = EditorShortCodeParser::parse($receiptSettings['receipt_header'], $booking);
        $receiptSettings['receipt_footer'] = EditorShortCodeParser::parse($receiptSettings['receipt_footer'], $booking);

        $html = $this->beforePaymentReceipt($order, $receiptSettings);
        $html .= $this->paymentReceptHeader($order, $receiptSettings);
        $html .= $this->paymentInfo($order, $receiptSettings);
        $html .= $this->itemDetails($order, $receiptSettings);
        $html .= $this->submissionDetails($order, $receiptSettings);
        $html .= $this->paymentReceptFooter($order, $receiptSettings);
        $html .= $this->afterPaymentReceipt($order, $receiptSettings);
        $html .= $this->loadStyles($order);

        return $html;
    }

    private function beforePaymentReceipt($order, $receiptSettings)
    {
        return '<div class="fluent_booking_payment_receipt">';
    }

    private function afterPaymentReceipt($order, $receiptSettings)
    {
        ob_start();
        do_action('fluent_booking/payment_receipt/after_content', $order, $receiptSettings);
        echo '</div>';
        return ob_get_clean();
    }

    private function paymentReceptHeader($order, $receiptSettings)
    {
        $preRender = apply_filters('fluent_booking/payment_receipt/pre_render_header', '', $order, $receiptSettings);
        if ($preRender) {
            return $preRender;
        }
        ob_start();
        $this->app->view->render('public.receipt.header', array(
            'order' => $order,
            'header_content' => $receiptSettings['receipt_header']
        ));
        return ob_get_clean();
    }

    private function paymentReceptFooter($order, $receiptSettings)
    {
        $preRender = apply_filters('fluent_booking/payment_receipt/pre_render_footer', '', $order, $receiptSettings);
        if ($preRender) {
            return $preRender;
        }

        if (!$receiptSettings['receipt_footer']) {
            return '';
        }

        return '<div class="fluent_booking_receipt_footer">' . $receiptSettings['receipt_footer'] . '</div>';
    }

    private function paymentInfo($order, $receiptSettings)
    {
        $preRender = apply_filters('fluent_booking/payment_receipt/pre_render_payment_info', '', $order);
        if ($preRender) {
            return $preRender;
        }

        if (!$order->items) {
            return '';
        }

        ob_start();
        $this->app->view->render('public.receipt.payment-info', array(
            'order' => $order
         ));
         return ob_get_clean();
    }

    private function itemDetails($order, $receiptSettings)
    {
        $preRender = apply_filters('fluent_booking/payment_receipt/pre_render_item_details', '', $order, $receiptSettings);

        if ($preRender) {
            return $preRender;
        }

        $header = '<div>';
        $header .= '<h4>' . __('Items Details', 'fluent-booking-pro') . '</h4>';
        ob_start();
        $this->app->view->render('public.receipt.order-items-table', array(
            'order' => $order,
        ));
        $html = ob_get_clean();

        if (!$html) {
            return '</div>';
        }
        return $header . $html . '</div>';
    }

    private function submissionDetails($order, $receiptSettings)
    {
        $preRender = apply_filters('fluent_booking/payment_receipt/pre_render_submission_details', '', $order, $receiptSettings);
        if ($preRender) {
            return $preRender;
        }

        $booking = Booking::query()->where('hash', $order->uuid)->first();

        $items = [
            [
                'label' => __('First Name', 'fluent-booking-pro'),
                'value' => $booking->first_name,
            ],
            [
                'label' =>  __('Last Name', 'fluent-booking-pro'),
                'value' => $booking->last_name,
            ],
            [
                'label' => __('Email', 'fluent-booking-pro'),
                'value' => $booking->email,
            ]
        ];

        ob_start();
        $this->app->view->render('public.receipt.input-fields', array(
            'items' => $items,
        ));
        return ob_get_clean();
    }

    private function loadStyles($order)
    {
        ob_start();
        $this->app->view->render('public.receipt.receipt-style', array('order' => $order));
        return ob_get_clean();
    }

}
