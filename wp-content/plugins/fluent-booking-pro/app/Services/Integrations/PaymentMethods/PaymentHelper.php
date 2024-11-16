<?php
namespace FluentBookingPro\App\Services\Integrations\PaymentMethods;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\CurrenciesHelper;
use FluentBooking\Framework\Support\Arr;

class PaymentHelper
{
    public $slug = '';

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function listenerUrl($args = [])
    {
        $queryArgs = array_merge([
            'fluent_booking_payment_listener' => 1,
            'payment_method'                  => $this->slug
        ], is_array($args) ? $args : []);

        return add_query_arg($queryArgs, site_url('index.php'));
    }

    public function successUrl(Booking $booking, $calendarEvent, $args = null)
    {
        $queryArgs = array_merge([
                'payment_method' => $this->slug,
                'payment_success' => 'yes'
            ], is_array($args) ? $args: []
        );

        $redirectUrl = $booking->getRedirectUrlWithQuery();

        $confirmationUrl = $redirectUrl ?: $booking->getConfirmationUrl();

        return add_query_arg($queryArgs, $confirmationUrl);
    }

    public static function formatPaymentItem($string, $limit = 127)
    {
        $string = wp_strip_all_tags($string);

        $string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);

        $string = self::limitLength($string, $limit);

        return html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
    }

    public static function limitLength($string, $limit = 127)
    {
        if (strlen($string) > $limit) {
            $string = substr($string, 0, $limit - 3) . '...';
        }
        return $string;
    }

    public static function getTotalPaymentWidget($widget, $paymentWidget)
    {
        if (!isset($paymentWidget['totalPayment']) || $paymentWidget['totalPayment'] == 0) {
            return $widget;
        }

        $currencySign = CurrenciesHelper::getGlobalCurrencySign();

        return [
            'title'   => __('Total Payment', 'fluent-booking-pro'),
            'number'  => $currencySign . $paymentWidget['totalPayment'],
            'content' => $paymentWidget['paymentComparison'],
            'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M15.0235 10.5932C13.8049 10.9264 13.0704 11.8615 13.0704 12.7449C13.0704 13.6283 13.8049 14.5634 15.0235 14.8966V10.5932Z" fill="white"/>
                <path d="M16.9766 17.1036V21.407C18.1953 21.0738 18.9298 20.1387 18.9298 19.2553C18.9298 18.3719 18.1953 17.4368 16.9766 17.1036Z" fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M29.0209 16.0001C29.0209 23.1913 23.1913 29.0209 16.0001 29.0209C8.80887 29.0209 2.97925 23.1913 2.97925 16.0001C2.97925 8.80887 8.80887 2.97925 16.0001 2.97925C23.1913 2.97925 29.0209 8.80887 29.0209 16.0001ZM16.0001 7.21102C16.5394 7.21102 16.9766 7.64824 16.9766 8.18758V8.6C19.0996 8.98012 20.8829 10.5751 20.8829 12.7449C20.8829 13.2842 20.4457 13.7214 19.9063 13.7214C19.367 13.7214 18.9298 13.2842 18.9298 12.7449C18.9298 11.8615 18.1953 10.9264 16.9766 10.5932V15.1104C19.0996 15.4905 20.8829 17.0855 20.8829 19.2553C20.8829 21.4251 19.0996 23.02 16.9766 23.4002V23.8126C16.9766 24.3519 16.5394 24.7891 16.0001 24.7891C15.4607 24.7891 15.0235 24.3519 15.0235 23.8126V23.4002C12.9006 23.02 11.1173 21.4251 11.1173 19.2553C11.1173 18.716 11.5545 18.2787 12.0938 18.2787C12.6332 18.2787 13.0704 18.716 13.0704 19.2553C13.0704 20.1387 13.8049 21.0738 15.0235 21.407V16.8898C12.9006 16.5096 11.1173 14.9147 11.1173 12.7449C11.1173 10.5751 12.9006 8.98012 15.0235 8.6V8.18758C15.0235 7.64824 15.4607 7.21102 16.0001 7.21102Z" fill="white"/>
                </svg>',
            'stat'    => $paymentWidget['paymentStat']
        ];
    }

    public static function getReceiptTemplate($items)
    {
        $sign = CurrenciesHelper::getGlobalCurrencySign();

        $total = 0;
        $template = '';
        if (count($items) === 1) {
            $template .= '<p class="fcal_payment_item_single">' . $items[0]['title'] . ': ' . '<span class="amount">' . $sign . '<span class="fcal_payment_amount">' . $items[0]['value'] . '</span>' . '</span>' . '</p>';
            $total = $items[0]['value'];
        } else {
            $template = '<table>';
            $template .= '<thead><tr><th>' . __('Item', 'fluent-booking-pro') . '</th><th>' . __('Price', 'fluent-booking-pro') . '</th></tr></thead><tbody>';
            foreach ($items as $item) {
                $total += floatval($item['value']);
                $template .= '<tr><td>' . $item['title'] . '</td><td>' . $sign . '<span class="fcal_payment_amount">' . $item['value'] . '</span>' . '</td></tr>';
            }
            $template .= '</tbody><tfoot><tr><th>' . __('Total:', 'fluent-booking-pro') . '</th><th>' . $sign . '<span class="fcal_payment_amount">' . $total . '</span>' . '</th></tr></tfoot>';
            $template .= '</table>';
        }

        return [
            'total' => $total,
            'template' => $template,
        ];
    }

    public static function getPaymentField($paymentField, $calendarEvent, $existingFields = [])
    {
        if ($calendarEvent->type != 'paid' || !Helper::isPaymentEnabled()) {
            return $paymentField;
        }

        $paymentSettings = $calendarEvent->getMeta('payment_settings', []);
        $isEnabled = Arr::get($paymentSettings, 'enabled') === 'yes';

        if (!$isEnabled) {
            return $paymentField;
        }

        $currencySign = CurrenciesHelper::getGlobalCurrencySign();
        $paymentItems = self::getReceiptTemplate(Arr::get($paymentSettings, 'items'));

        $stripeEnabled  = Arr::get($paymentSettings, 'stripe_enabled') === 'yes' && Helper::isPaymentConfigured('stripe');
        $paypalEnabled  = Arr::get($paymentSettings, 'paypal_enabled') === 'yes' && Helper::isPaymentConfigured('paypal');
        $isMultiEnabled = Arr::get($paymentSettings, 'multi_payment_enabled') === 'yes';

        $exist = Arr::get($existingFields, 'payment_method', []);
        if (!$exist) {
            $exist = [
                'index'          => 20,
                'type'           => 'payment',
                'name'           => 'payment_method',
                'enabled'        => true,
                'system_defined' => true,
                'payment_items'  => $paymentItems,
                'label'          => __('Payment Summary', 'fluent-booking-pro'),
                'currency_sign'  => $currencySign,
            ];
        } else {
            $exist['currency_sign'] = $currencySign;
            $exist['payment_items'] = $paymentItems;
        }

        $exist['required'] = true;
        $exist['disable_alter'] = true;

        $assetUrl = App::getInstance()['url.assets'];

        if ($stripeEnabled) {
            $exist['payment_methods']['stripe'] = [
                'name' => 'stripe',
                'icon' => $assetUrl . 'images/payment-methods/stripe.png'
            ];
        }
        if ($paypalEnabled) {
            $exist['payment_methods']['paypal'] = [
                'name' => 'paypal',
                'icon' => $assetUrl . 'images/payment-methods/paypal.png'
            ];
        }

        if ($calendarEvent->isMultiDurationEnabled() && $isMultiEnabled) {
            $exist['multi_payment_items'] = Arr::get($paymentSettings, 'multi_payment_items');
        }

        return $exist;
    }
}
