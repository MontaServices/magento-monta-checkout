<?php

namespace Montapacking\MontaCheckout\Plugin\Quote\Model\Quote\Address\Total;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignmentApi;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Montapacking\MontaCheckout\Logger\Logger;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier;

class Shipping
{
    /**
     * @param Carrier $config
     * @param Session $checkoutSession
     * @param Logger $_logger
     */
    public function __construct(
        protected readonly Carrier $config,
        protected readonly Session $checkoutSession,
        protected readonly Logger $_logger
    )
    {
    }


    /**
     * @param QuoteAddressTotal\Shipping $subject
     * @param QuoteAddressTotal\Shipping $result
     * @param Quote $quote
     * @param ShippingAssignmentApi $shippingAssignment
     * @param QuoteAddressTotal $total
     * @return QuoteAddressTotal\Shipping|void
     */
    // @codingStandardsIgnoreLine
    public function afterCollect(QuoteAddressTotal\Shipping $subject, QuoteAddressTotal\Shipping $result, Quote $quote, ShippingAssignmentApi $shippingAssignment, QuoteAddressTotal $total)
    {
        $shipping = $shippingAssignment->getShipping();
        $address = $shipping->getAddress();

        // Apply return-early principle to validate some things
        if (!$this->config->isActive()) {
            return $result;
        }

        $rates = $address->getAllShippingRates();
        if (empty($rates)) {
            return $result;
        }

        $deliveryOption = $this->getDeliveryOption($address);
        if (!$deliveryOption) {
            return $result;
        }

        $latestShipping = $this->checkoutSession->getLatestShipping();
        if (!$latestShipping) {
            return $result;
        }

        // Get fallback fee from carrier config
        $fee = $this->config->getPrice();

        $deliveryOptionDetails = $deliveryOption->details[0];
        $deliveryOptionAdditionalInfo = $deliveryOption->additional_info[0];

        switch ($deliveryOption->type) {
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
                if (!empty($deliveryOptionDetails->options)) {
                    $extras = $selectedOptionFromCache->deliveryOptions ?? [];
                    foreach ($deliveryOptionDetails->options as $detailOption) {
                        // Append this extra to description
                        $desc[] = $detailOption;

                        // Apply fee from each extra
                        foreach ($extras as $extra) {
                            // If this is the selected option
                            if ($extra->code == $detailOption) {
                                $fee += $extra->price;
                                // Break loop, match found
                                break;
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

        // If code reaches here, delivery option is valid and totals must be adjusted
        $this->adjustTotals($method_title, $subject->getCode(), $address, $total, $fee, $desc);
    }

    /** Get stdClass object for selected delivery option
     *
     * @param $address
     * @return \stdClass|null
     */
    private function getDeliveryOption($address)
    {
        $option = $address->getMontapackingMontacheckoutData();

        if (!$option) {
            return null;
        }

        return json_decode($option);
    }

    /**
     * @param $name
     * @param $code
     * @param $address
     * @param $total
     * @param $fee
     * @param $description
     * @return void
     */
    private function adjustTotals($name, $code, $address, $total, $fee, $description): void
    {
        $total->setTotalAmount($code, $fee);
        $total->setBaseTotalAmount($code, $fee);
        $total->setBaseShippingAmount($fee);
        $total->setShippingAmount($fee);

        $shippingDescription = $name . ' - ' . $description;
        $total->setShippingDescription($shippingDescription);
        $total->setShippingMethodTitle($shippingDescription);

        $address->setShippingDescription($shippingDescription);
    }
}