<?php
/**
 * @author Jacco.Amersfoort <jacco.amersfoort@monta.nl>
 * @created 02/07/2025 16:15
 */
namespace Montapacking\MontaCheckout\Helper;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;

class Order
{
    /**
     * Order Comment field name
     */
    public const FIELD_NAME = 'montapacking_montacheckout_data';

    /**
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        protected readonly OrderExtensionFactory $extensionFactory,
    )
    {
    }

    /** Copy data from Order record to ExtensionAttribute for public access
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function extendOrder(OrderInterface $order)
    {
        $orderComment = $order->getData(self::FIELD_NAME);

        $extensionAttributes = $order->getExtensionAttributes() ?? $this->extensionFactory->create();
        $extensionAttributes->setMontapackingMontacheckoutData($orderComment);

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }
}
