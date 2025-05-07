<?php

namespace Montapacking\MontaCheckout\Plugin\Quote\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagement as QuoteShippingAddressManagement;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier;

class ShippingAddressManagement
{
    /** @var CartRepositoryInterface $quoteRepository */
    private $quoteRepository;

    /** @var Carrier $carrierConfig */
    private $carrierConfig;

    /**
     * ShippingAddressManagement constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param Carrier $carrierConfig
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Carrier $carrierConfig
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->carrierConfig = $carrierConfig;
    }

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
