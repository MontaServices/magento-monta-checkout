<?php

namespace Montapacking\MontaCheckout\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Montapacking\MontaCheckout\Helper\Order;

/**
 * Class OrderRepositoryPlugin
 */
class OrderRepositoryPlugin
{
    /**
     * @param Order $orderHelper
     */
    public function __construct(
        protected readonly Order $orderHelper,
    )
    {
    }

    /**
     * Add "order_comment" extension attribute to order data object to make it accessible in API data of order record
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        return $this->orderHelper->extendOrder($order);
    }

    /**
     * Add "order_comment" extension attribute to order data object to make it accessible in API data of all order list
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult): OrderSearchResultInterface
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $this->orderHelper->extendOrder($order);
        }

        return $searchResult;
    }

}
