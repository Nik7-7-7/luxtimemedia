<?php
if (!defined('ABSPATH')) exit;
/**
 * @var $cartItems array
 * @var $currency string
 */

?>

<style>
    .fc-abandoned-cart-table table {
        border-spacing: 0;
        border-collapse: separate;
        width: 100%;
        border: 1px solid #D6DAE1;
        border-radius: 8px;
    }
    .fc-abandoned-cart-table table tbody tr td:first-child img {
        width: 50px;
        height: 50px;
        object-fit: contain;
        display: block;
    }
    .fc-abandoned-cart-table table thead tr td:first-child,
    .fc-abandoned-cart-table table thead tr th:first-child {
        width: 50px;
    }
    .fc-abandoned-cart-table table thead tr th:last-child {
        width: 90px;
    }
    .fc-abandoned-cart-table table thead tr th:nth-child(3) {
        width: 70px;
    }
    .fc-abandoned-cart-table table thead tr th {
        border-right:1px solid #e9ecf0;
        background: #EAECF0;
        padding: 8px 20px;
        color: #323232;
        line-height: 26px;
        font-weight: 700;
        font-size: 14px;
    }
    .fc-abandoned-cart-table table tbody tr td {
        padding: 8px 20px;
        border-top: 1px solid #e9ecf0;
        border-right: 1px solid #e9ecf0;
    }
    .fc-abandoned-cart-table table tbody tr td:last-child {
        border-right: none;
    }
    .fc-abandoned-cart-table table tbody tr td .table-head {
        display: none;
        min-width: 80px;
        max-width: 80px;
        background: rgb(234, 236, 240);
        font-weight: 600;
        font-size: 14px;
        padding: 10px 16px;
        line-height: 1rem;
    }
    @media (max-width: 600px) {
        .fc-abandoned-cart-table table thead {
            display: none;
        }
        .fc-abandoned-cart-table table {
            display: block;
            border: none !important;
        }
        .fc-abandoned-cart-table table tbody {
            display: block;
            width: 100%;
        }
        .fc-abandoned-cart-table table thead tr th:last-child,
        .fc-abandoned-cart-table table thead tr th:nth-child(3),
        .fc-abandoned-cart-table table thead tr td:first-child {
            width: 100%;
        }
        .fc-abandoned-cart-table table tbody tr td:first-child img {
            margin-top: 6px;
            margin-bottom: 6px;
        }
        .fc-abandoned-cart-table table tbody tr {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
            border: 1px solid rgb(214, 218, 225);
            border-radius: 4px;
        }
        .fc-abandoned-cart-table table tbody tr td:first-child {
            border-top: none;
        }
        .fc-abandoned-cart-table table tbody tr td {
            display: flex !important;
            border-right: none !important;
            gap: 10px;
            padding: 0 20px 0 0 !important;
        }
        .fc-abandoned-cart-table table tbody tr td .table-head {
            display: inline-block !important;
        }
    }
</style>


<div class="fc-abandoned-cart-table">
    <table>
        <thead>
            <tr>
                <th><?php esc_html_e('Image', 'fluentcampaign-pro'); ?></th>
                <th><?php esc_html_e('Item', 'fluentcampaign-pro'); ?></th>
                <th><?php esc_html_e('Quantity', 'fluentcampaign-pro'); ?></th>
                <th><?php esc_html_e('Price', 'fluentcampaign-pro'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cartItems as $cartItem) {

            $product = wc_get_product($cartItem['product_id']);
            $product_image_url = wp_get_attachment_url($product->get_image_id());
            if (!$product_image_url) {
                $product_image_url = wc_placeholder_img_src();
            }
            $price = \FluentCrm\Framework\Support\Arr::get($cartItem, 'line_total');
            $price = number_format($price, 2, '.', '');
            $price = $currency . ' ' . $price;
            ?>
            <tr>
                <td><div class="table-head"><?php esc_html_e('Image', 'fluentcampaign-pro') ; ?></div>
                    <img src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($cartItem['title']); ?>">
                </td>
                <td><div class="table-head"><?php esc_html_e('Item', 'fluentcampaign-pro') ; ?></div><?php echo esc_html($cartItem['title']); ?></td>
                <td><div class="table-head"><?php esc_html_e('Quantity', 'fluentcampaign-pro') ; ?></div><?php echo esc_html($cartItem['quantity']); ?></td>
                <td><div class="table-head"><?php esc_html_e('Price', 'fluentcampaign-pro') ; ?></div><?php echo esc_html($price); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>