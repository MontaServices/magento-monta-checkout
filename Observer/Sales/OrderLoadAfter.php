<?php

namespace Montapacking\MontaCheckout\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderExtension;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @param OrderExtension $orderExtension
     */
    public function __construct(
        protected readonly OrderExtension $orderExtension
    )
    {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtension;
        }

        $attr = $order->getData('montapacking_montacheckout_data');

        $extensionAttributes->setMontapackingMontacheckoutData($attr);

        $order->setExtensionAttributes($extensionAttributes);
    }
}
