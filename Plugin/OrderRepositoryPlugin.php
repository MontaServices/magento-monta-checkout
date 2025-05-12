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
     */
    const FIELD_NAME = 'montapacking_montacheckout_data';

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        protected readonly OrderExtensionFactory $extensionFactory)
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
        $orderComment = $order->getData(self::FIELD_NAME);

        $extensionAttributes = $order->getExtensionAttributes() ?? $this->extensionFactory->create();
        $extensionAttributes->setMontapackingMontacheckoutData($orderComment);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
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

        // TODO is pass-by-reference still necessary if Order object can be altered?
        foreach ($orders as &$order) {
            $orderComment = $order->getData(self::FIELD_NAME);

            $extensionAttributes = $order->getExtensionAttributes() ?? $this->extensionFactory->create();
            $extensionAttributes->setMontapackingMontacheckoutData($orderComment);

            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
