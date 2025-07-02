<?php

namespace Montapacking\MontaCheckout\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Montapacking\MontaCheckout\Helper\Order;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @param Order $orderHelper
     */
    public function __construct(
        protected readonly Order $orderHelper,
    )
    {
    }

    /** Pass Monta Checkout data from Order field to ExtensionAttributes
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        $this->orderHelper->extendOrder($order);
    }
}
