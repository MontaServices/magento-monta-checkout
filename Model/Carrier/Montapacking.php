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

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Montapacking extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'montapacking';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */

    protected $_customLogger;

    protected $_request;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Psr\Log\LoggerInterface $customLogger,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    )
    {
        $this->_request = $request;
        $this->_customLogger = $customLogger;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
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

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
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
