<?php
/**
 * Montapacking B.V.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Montapacking
 * @package     Montapacking_MontaCheckout
 * @copyright   Copyright (c) 2020 Montapacking B.V.. All rights reserved. (http://www.montapacking.nl)
 */

namespace Montapacking\MontaCheckout\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class Montapacking extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'montapacking';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param LoggerInterface $customLogger
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        protected readonly ResultFactory $rateResultFactory,
        protected readonly MethodFactory $rateMethodFactory,
        protected readonly LoggerInterface $customLogger,
        protected readonly RequestInterface $request,
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['montapacking' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $items = $request->getAllItems();
        foreach ($items as $item) {
            $quote = $item->getQuote();
            break;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        /** @var Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier('montapacking');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('montapacking');
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getConfigData('price');

        $formpostdata = json_decode(file_get_contents('php://input'), true);

        // quickfix for onepagecheckout
        if (isset($formpostdata["shippingAddress"]["extension_attributes"]["montapacking_montacheckout_data"])) {
            $json = json_decode($formpostdata["shippingAddress"]["extension_attributes"]["montapacking_montacheckout_data"]);
            $amount = $json->additional_info[0]->total_price;

            if ($quote != null) {
                $address = $quote->getShippingAddress();
                $address->setMontapackingMontacheckoutData($formpostdata["shippingAddress"]["extension_attributes"]["montapacking_montacheckout_data"]);
                $address->save();
            }
        }

        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }
}
