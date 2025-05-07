<?php

namespace Montapacking\MontaCheckout\Plugin\Quote\Model\Quote\Address\Total;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignmentApi;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Montapacking\MontaCheckout\Logger\Logger;

class Shipping
{
    private $scopeConfig;

    /**
     * @var \Montapacking\MontaCheckout\Logger\Logger
     */
    protected $_logger;


    /** @var Session $checkoutSession */
    private $checkoutSession;

    /**
     * Shipping constructor.
     *
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        Logger $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->_logger = $logger;
    }

    /**
     * @param                       $subject
     * @param                       $result
     * @param Quote $quote
     * @param ShippingAssignmentApi $shippingAssignment
     * @param QuoteAddressTotal $total
     *
     * @return void|mixed
     */
    // @codingStandardsIgnoreLine
    public function afterCollect($subject, $result, Quote $quote, ShippingAssignmentApi $shippingAssignment, QuoteAddressTotal $total)
    {
        $shipping = $shippingAssignment->getShipping();
        $address = $shipping->getAddress();
        $rates = $address->getAllShippingRates();

        $fee = $this->scopeConfig->getValue('carriers/montapacking/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$rates) {
            return $result;
        }

        if (empty($rates)) {
            return $result;
        }


        $deliveryOption = $this->getDeliveryOption($address);

        if (!$deliveryOption) {
            return $result;
        }

        $deliveryOptionType = $deliveryOption->type;
        $deliveryOptionDetails = $deliveryOption->details[0];
        $deliveryOptionAdditionalInfo = $deliveryOption->additional_info[0];

        if ($deliveryOptionType != 'pickup' && $deliveryOptionType != 'delivery') {
            return $result;
        }

        if (!$this->checkoutSession->getLatestShipping()) {
            return $result;
        }

        if ($deliveryOptionType == 'pickup') {
            $method_title = $deliveryOptionAdditionalInfo->company;

            $desc = explode("|", $deliveryOptionAdditionalInfo->description);
            $desc = $desc[0];
            $fee = $deliveryOptionAdditionalInfo->price;
        }

        if ($deliveryOptionType == 'delivery') {
            if ($deliveryOptionAdditionalInfo->code == "MultipleShipper_ShippingDayUnknown") {
                if (isset($deliveryOptionAdditionalInfo->price)) {
                    $fee = $deliveryOptionAdditionalInfo->price;
                }
            } else {
                foreach ($this->checkoutSession->getLatestShipping()[0] as $timeframe) {
                    foreach ($timeframe->options as $option) {
                        if ($option->code == $deliveryOptionAdditionalInfo->code) {
                            $selectedOptionFromCache = $option;
                            $fee = $selectedOptionFromCache->price;
                        }
                    }
                }
            }

            $method_title = $deliveryOptionAdditionalInfo->name;

            $desc = [];
            if (trim($deliveryOptionAdditionalInfo->date)) {
                $desc[] = $deliveryOptionAdditionalInfo->date;
            }

            if (trim($deliveryOptionAdditionalInfo->time)) {
                $desc[] = $deliveryOptionAdditionalInfo->time;
            }

            // extra options
            if (isset($deliveryOptionDetails->options)) {
                foreach ($deliveryOptionDetails->options as $value) {
                    $desc[] = $value;
                    foreach ($selectedOptionFromCache->deliveryOptions as $extra) {
                        if ($extra->code == $value) {
                            $fee += $extra->price;
                        }
                    }
                }
            }

            $desc = implode(" | ", $desc);
        }

        $this->adjustTotals($method_title, $subject->getCode(), $address, $total, $fee, $desc);
    }

    /**
     * @param $address
     *
     * @return mixed|null
     */
    private function getDeliveryOption($address)
    {
        $option = $address->getMontapackingMontacheckoutData();

        if (!$option) {
            return null;
        }

        $option = json_decode($option);

        return $option;
    }

    private function adjustTotals($name, $code, $address, $total, $fee, $description)
    {
        $total->setTotalAmount($code, $fee);
        $total->setBaseTotalAmount($code, $fee);
        $total->setBaseShippingAmount($fee);
        $total->setShippingAmount($fee);
        $total->setShippingDescription($name . ' - ' . $description);
        $total->setShippingMethodTitle($name . ' - ' . $description);

        $address->setShippingDescription($name . ' - ' . $description);
    }
}
