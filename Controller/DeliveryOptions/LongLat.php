<?php

namespace Montapacking\MontaCheckout\Controller\DeliveryOptions;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Store\Model\StoreManagerInterface;
use Montapacking\MontaCheckout\Controller\AbstractDeliveryOptions;
use Montapacking\MontaCheckout\Helper\System;
use Montapacking\MontaCheckout\Logger\Logger;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier as CarrierConfig;

/**
 * Class LongLat
 *
 * @package Montapacking\MontaCheckout\Controller\DeliveryOptions
 */
class LongLat extends AbstractDeliveryOptions
{
    /** @var LocaleResolver $scopeConfig */
    private $localeResolver;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var Cart
     */
    public $cart;

    protected $storeManager;

    protected $currency;

    /**
     * Services constructor.
     *
     * @param Context $context
     * @param LocaleResolver $localeResolver
     * @param CarrierConfig $carrierConfig
     * @param Logger $logger
     * @param Cart $cart
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $currencyInterface
     * @param System $systemHelper
     */
    public function __construct(
        Context $context,
        LocaleResolver $localeResolver,
        CarrierConfig $carrierConfig,
        Logger $logger,
        Cart $cart,
        StoreManagerInterface $storeManager,
        CurrencyInterface $currencyInterface,
        System $systemHelper,
    )
    {
        $this->_logger = $logger;
        $this->localeResolver = $localeResolver;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->currency = $currencyInterface;

        parent::__construct(
            $context,
            $carrierConfig,
            $cart,
            $storeManager,
            $currencyInterface,
            $systemHelper
        );
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws \Exception|GuzzleException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        if ($language != 'NL' && $language != 'BE' && $language != 'DE') {
            $language = 'EN';
        }

        try {
            $longlat = $request->getParam('longlat') ? trim($request->getParam('longlat')) : "";

            if ($longlat == 'false') {
                $oApi = $this->generateApi($request, $language, $this->_logger);
            } else {
                $oApi = $this->generateApi($request, $language, $this->_logger, true);
            }

            $arr = [];

            $arr['longitude'] = $oApi->address->longitude;
            $arr['latitude'] = $oApi->address->latitude;
            $arr['language'] = $language;
        } catch (\Exception $e) {
            $arr = [];
            $arr['longitude'] = 0;
            $arr['latitude'] = 0;
            $arr['language'] = $language;
            $arr['hasconnection'] = 'false';
            $arr['googleapikey'] = $this->getCarrierConfig()->getGoogleApiKey();

            $context = ['source' => 'Montapacking Checkout'];
            $this->_logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
        }

        return $this->jsonResponse($arr);
    }
}
