<?php

namespace Montapacking\MontaCheckout\Controller\DeliveryOptions;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Montapacking\MontaCheckout\Controller\AbstractDeliveryOptions;

use Montapacking\MontaCheckout\Model\Config\Provider\Carrier as CarrierConfig;

use Montapacking\MontaCheckout\Api_REMOVETHIS\MontapackingShipping as MontpackingApi;

/**
 * Class LongLat
 *
 * @package Montapacking\MontaCheckout\Controller\DeliveryOptions
 */
class LongLat extends AbstractDeliveryOptions
{
    /** @var Session $checkoutSession */
    private $checkoutSession;

    /** @var LocaleResolver $scopeConfig */
    private $localeResolver;

    /**
     * @var \Montapacking\MontaCheckout\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    public $cart;

    protected $storeManager;

    protected $currency;

    /**
     * Services constructor.
     *
     * @param Context       $context
     * @param Session       $checkoutSession
     * @param CarrierConfig $carrierConfig
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        LocaleResolver $localeResolver,
        CarrierConfig $carrierConfig,
        \Montapacking\MontaCheckout\Logger\Logger $logger,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface
    ) {
        $this->_logger = $logger;

        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->currency = $currencyInterface;

        parent::__construct(
            $context,
            $carrierConfig,
            $cart,
            $storeManager,
            $currencyInterface
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        if ($language != 'NL' && $language != 'BE' && $language != 'DE') {
            $language = 'EN';
        }


//        try {
//            $longlat = $request->getParam('longlat') ? trim($request->getParam('longlat')) : "";
//
//            if ($longlat == 'false') {
//                $oApi = $this->generateApi($request, $language, $this->_logger, false);
//            } else{
//                $oApi = $this->generateApi($request, $language, $this->_logger, true);
//            }
//
//            $dbg = $oApi;
//            $shippers = $oApi->getShippers();
//
//            $arr = [];
//
//            $arr['longitude'] = $oApi->address->longitude;
//            $arr['latitude'] = $oApi->address->latitude;
//            $arr['language'] = $language;
//            $arr['googleapikey'] = $this->getCarrierConfig()->getGoogleApiKey();
//            $arr['shippers'] = $shippers;
//
//            if ($shippers != null) {
//                $arr['hasconnection'] = 'true';
//            } else {
//                $arr['hasconnection'] = 'false';
//            }
//
//        } catch (Exception $e) {
//
//            $arr = [];
//            $arr['longitude'] = 0;
//            $arr['latitude'] = 0;
//            $arr['language'] = $language;
//            $arr['hasconnection'] = 'false';
//            $arr['googleapikey'] = $this->getCarrierConfig()->getGoogleApiKey();
//
//            $context = ['source' => 'Montapacking Checkout'];
//            $this->_logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
//
//        }


            /* Copy paste */




        try {
            $longlat = $request->getParam('longlat') ? trim($request->getParam('longlat')) : "";

            if ($longlat == 'false') {
                $oApi = $this->generateApi($request, $language, $this->_logger, false);
            } else{
                $oApi = $this->generateApi($request, $language, $this->_logger, true);
            }


//            $shippers = $oApi->getShippers();
//            $shippers = $oApi['PickupOptions'];

            $dbg = $oApi;

            $arr = [];

            $arr['longitude'] = $oApi->address->longitude;
            $arr['latitude'] = $oApi->address->latitude;
            $arr['language'] = $language;
//            $arr['googleapikey'] = $this->getCarrierConfig()->getGoogleApiKey();
//            $arr['shippers'] = $shippers;

//            if ($shippers != null) {
//                $arr['hasconnection'] = 'true';
//            } else {
//                $arr['hasconnection'] = 'false';
//            }

        } catch (Exception $e) {
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
