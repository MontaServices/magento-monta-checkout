<?php

namespace Montapacking\MontaCheckout\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryPlugin
 */
class OrderRepositoryPlugin
{
    /**
     * Order Comment field name
     * TODO use this constant throughout project where value is repeated
     */
    const FIELD_NAME = 'montapacking_montacheckout_data';

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
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
        return $this->extendOrder($order);
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
            $this->extendOrder($order);
        }

        return $searchResult;
    }

    /** Copy data from Order record to ExtensionAttribute for public access
     *  TODO this does the same as \Montapacking\MontaCheckout\Observer\Sales\OrderLoadAfter
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function extendOrder(OrderInterface $order)
    {
        $orderComment = $order->getData(self::FIELD_NAME);

        $extensionAttributes = $order->getExtensionAttributes() ?? $this->extensionFactory->create();
        $extensionAttributes->setMontapackingMontacheckoutData($orderComment);

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }
}
