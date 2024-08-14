<?php

namespace Montapacking\MontaCheckout\Controller\DeliveryOptions;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Montapacking\MontaCheckout\Controller\AbstractDeliveryOptions;

use Montapacking\MontaCheckout\Helper\DeliveryHelper;
use Montapacking\MontaCheckout\Helper\PickupHelper;
use Montapacking\MontaCheckout\Logger\Logger;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier as CarrierConfig;

use Carbon\Carbon;
use Monta\MontaProcessing\NumberGenerator;

/**
 * Class Delivery
 *
 * @package Montapacking\MontaCheckout\Controller\DeliveryOptions
 */
class Delivery extends AbstractDeliveryOptions
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

    /**
     * @var \Montapacking\MontaCheckout\Helper\PickupHelper
     */
    protected $pickupHelper;

    /**
     * @var \Montapacking\MontaCheckout\Helper\DeliveryHelper
     */
    protected $deliveryHelper;

    /**
     * Services constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param CarrierConfig $carrierConfig
     */
    public function __construct(
        Context         $context,
        Session         $checkoutSession,
        LocaleResolver  $localeResolver,
        CarrierConfig   $carrierConfig,
        Logger          $logger,
        Cart            $cart,
        PickupHelper    $pickupHelper,
        DeliveryHelper  $deliveryHelper,
        \Magento\Store\Model\StoreManagerInterface    $storeManager
    )
    {
//        $tomorrow = Carbon::now()->addDay();
//        $processingstuff = new NumberGenerator();
//        print_r($processingstuff->Generate(0, 100)); die();

        $this->_logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->cart = $cart;
        $this->pickupHelper = $pickupHelper;
        $this->deliveryHelper = $deliveryHelper;
//
//        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
//        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
//        echo $mediaUrl;

        parent::__construct(
            $context,
            $carrierConfig,
            $cart,
            $storeManager
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        if ($language != 'NL' && $language != 'BE' && $language != 'DE') {
            $language = 'EN';
        }

        try {
            $oApi = $this->generateApi($request, $language, $this->_logger, true);
            $this->checkoutSession->setLatestShipping([$oApi['DeliveryOptions'], $oApi['PickupOptions'],  $oApi['CustomerLocation'], $oApi['StandardShipper']]);

            return $this->jsonResponse([$oApi['DeliveryOptions'], $oApi['PickupOptions'], $oApi['CustomerLocation'], $oApi['StandardShipper']]);
        } catch (Exception $e) {

            $context = ['source' => 'Montapacking Checkout'];
            $this->_logger->critical(json_encode($e->getMessage()), $context); //phpcs:ignore
            $this->_logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
            return $this->jsonResponse(json_encode([]));
        }
    }
}
