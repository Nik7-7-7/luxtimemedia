<?php

namespace FluentBookingPro\App\Services;

use FluentBookingPro\App\Models\Order;
use FluentBookingPro\App\Models\Transactions;
use FluentBooking\App\Services\CurrenciesHelper;
use FluentBooking\Framework\Support\Arr;

class OrderHelper
{
    public $items = [];
    public $order;

    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function processDraftOrder($booking, $calendarSlot = [], $bookingData = [])
    {
        $items = $calendarSlot->getPaymentItems($booking->slot_minutes);

        $quantity = Arr::get($bookingData, 'quantity', 1);

        $total = $this->getTotal($items) * $quantity;

        $currency = CurrenciesHelper::getGlobalCurrency();

        $data = [
            'status' => 'draft',
            'parent_id' => $booking->id,
            'order_number' => $booking->hash,
            'payment_method' => $booking->payment_method,
            'payment_method_title' => $booking->payment_method,
            'currency' => $currency,
            'total_amount' => $total,
            'uuid' => $booking->hash,
        ];

        $order = Order::query()->create($data);

        //create order Items
        $orderItem = [];
        foreach ($items as $item) {
            $itemPrice = intval($item['value'] * 100);
            $orderItem['booking_id'] = $booking->id;
            $orderItem['item_name'] = $item['title'];
            $orderItem['item_price'] = $itemPrice;
            $orderItem['quantity'] = $quantity;
            $orderItem['item_total'] = $itemPrice * $quantity;
            $orderItem['rate'] = 1;
            $orderItem['line_meta'] = wp_json_encode($item);
            $order->items()->create($orderItem);
        }

        $this->createDraftTransactions($order);
    }

    public function getTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += intval($item['value'] * 100);
        }
        return $total;
    }

    public function createDraftTransactions($order)
    {
        if (!$order) {
            return;
        }

        $data = [
            'object_id' => $order->id,
            'object_type' => 'order',
            'transaction_type' => 'online',
            'payment_method' => $order->payment_method,
            'status' => 'pending',
            'total' => $order->total_amount,
            'rate' => 1,
            'uuid' => $order->uuid
        ];

        Transactions::create($data);
    }

    public function getOrderByHash($hash)
    {
        return Order::where('uuid', $hash)->first();
    }

    public function updateOrderStatus($orderHash, $status = 'pending')
    {
        $order = $this->getOrderByHash($orderHash);

        if (!$order) {
            return;
        }
        
        $order->status = $status;
        $order->save();
    }

}
