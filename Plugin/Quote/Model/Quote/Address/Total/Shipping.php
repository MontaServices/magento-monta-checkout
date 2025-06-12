<?php

namespace Montapacking\MontaCheckout\Plugin\Quote\Model\Quote\Address\Total;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignmentApi;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Store\Model\ScopeInterface;
use Montapacking\MontaCheckout\Logger\Logger;

class Shipping
{
    private $scopeConfig;

    /**
     * @var Logger
     */
    protected $_logger;

    /** @var Session $checkoutSession */
    private $checkoutSession;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param Logger $logger
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
     * @param $subject
     * @param $result
     * @param Quote $quote
     * @param ShippingAssignmentApi $shippingAssignment
     * @param QuoteAddressTotal $total
     * @return mixed|void
     */
    // @codingStandardsIgnoreLine
    public function afterCollect($subject, $result, Quote $quote, ShippingAssignmentApi $shippingAssignment, QuoteAddressTotal $total)
    {
        $shipping = $shippingAssignment->getShipping();
        $address = $shipping->getAddress();
        $rates = $address->getAllShippingRates();

        $fee = $this->scopeConfig->getValue('carriers/montapacking/price', ScopeInterface::SCOPE_STORE);

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

        $latestShipping = $this->checkoutSession->getLatestShipping();
        if (!$latestShipping) {
            return $result;
        }

        switch ($deliveryOptionType) {
            case 'pickup':
                $method_title = $deliveryOptionAdditionalInfo->company;

                $desc = explode("|", $deliveryOptionAdditionalInfo->description);
                $desc = $desc[0];
                $fee = $deliveryOptionAdditionalInfo->price;
                break;
            case 'delivery':
                if ($deliveryOptionAdditionalInfo->code == "MultipleShipper_ShippingDayUnknown") {
                    if (isset($deliveryOptionAdditionalInfo->price)) {
                        $fee = $deliveryOptionAdditionalInfo->price;
                    }
                } else {
                    // Fallback to avoid null index pointer
                    foreach ($latestShipping[0] ?? [] as $timeframe) {
                        // Find selected option from timeframe's options
                        foreach ($timeframe->options as $option) {
                            if ($option->code == $deliveryOptionAdditionalInfo->code) {
                                $selectedOptionFromCache = $option;
                                $fee = $selectedOptionFromCache->price;
                                break; // Jump out of loop, match found
                            }
                        }
                    }
                }

                //Shipping method name is saved in name
                $method_title = $deliveryOptionAdditionalInfo->name;

                // Construct shipping description based on parts
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

                // Glue description back together
                $desc = implode(" | ", $desc);
                break;
            default:
                return $result;
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

        return json_decode($option);
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
