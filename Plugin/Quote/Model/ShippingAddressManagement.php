<?php

namespace Montapacking\MontaCheckout\Plugin\Quote\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagement as QuoteShippingAddressManagement;

class ShippingAddressManagement
{
    /**
     * @param QuoteShippingAddressManagement $subject
     * @param                                $cartId
     * @param AddressInterface|null $address
     *
     * @return array|void
     */
    // @codingStandardsIgnoreLine
    public function beforeAssign(QuoteShippingAddressManagement $subject, $cartId, AddressInterface $address = null)
    {
        $result = [$cartId, $address];

        if (!$address) {
            return $result;
        }

        $extensionAttributes = $address->getExtensionAttributes();

        if (!$extensionAttributes || !$extensionAttributes->getMontapackingMontacheckoutData()) {
            return $result;
        }

        $deliveryOption = $extensionAttributes->getMontapackingMontacheckoutData();

        $address->setMontapackingMontacheckoutData($deliveryOption);
    }
}
