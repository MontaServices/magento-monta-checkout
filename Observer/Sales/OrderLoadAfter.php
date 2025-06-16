<?php

namespace Montapacking\MontaCheckout\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;

class OrderLoadAfter implements ObserverInterface
{
    /** @var OrderExtensionFactory - TODO Replace with promoted property from constructor for PHP 8 */
    protected $orderExtensionFactory;

    /**
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    )
    {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /** Pass Monta Checkout data from Order field to ExtensionAttributes
     * TODO duplicate code in OrderRepositoryPlugin::extendOrder()
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        // Get ExtensionAttributes from Order or instantiate new
        $extensionAttributes = $order->getExtensionAttributes() ?? $this->orderExtensionFactory->create();

        $attr = $order->getData('montapacking_montacheckout_data');

        $extensionAttributes->setMontapackingMontacheckoutData($attr);

        $order->setExtensionAttributes($extensionAttributes);
    }
}
