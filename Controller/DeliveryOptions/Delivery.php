<?php

namespace Montapacking\MontaCheckout\Controller\DeliveryOptions;

use Carbon\Carbon;
use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Monta\MontaProcessing\NumberGenerator;
use Montapacking\MontaCheckout\Controller\AbstractDeliveryOptions;
use Montapacking\MontaCheckout\Helper\DeliveryHelper;
use Montapacking\MontaCheckout\Helper\PickupHelper;
use Montapacking\MontaCheckout\Logger\Logger;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier as CarrierConfig;

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
     * @var Logger
     */
    protected $_logger;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var PickupHelper
     */
    protected $pickupHelper;

    /**
     * @var DeliveryHelper
     */
    protected $deliveryHelper;

    protected $storeManager;

    protected $currency;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param LocaleResolver $localeResolver
     * @param CarrierConfig $carrierConfig
     * @param Logger $logger
     * @param Cart $cart
     * @param PickupHelper $pickupHelper
     * @param DeliveryHelper $deliveryHelper
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $currencyInterface
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        LocaleResolver $localeResolver,
        CarrierConfig $carrierConfig,
        Logger $logger,
        Cart $cart,
        PickupHelper $pickupHelper,
        DeliveryHelper $deliveryHelper,
        StoreManagerInterface $storeManager,
        CurrencyInterface $currencyInterface
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
     * @return ResponseInterface|ResultInterface
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
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $AFHImage_basepath = $mediaUrl . 'Images/';

            $this->checkoutSession->setLatestShipping([$oApi['DeliveryOptions'], $oApi['PickupOptions'], $oApi['CustomerLocation'], $oApi['StandardShipper']]);

            return $this->jsonResponse([$oApi['DeliveryOptions'], $oApi['PickupOptions'], $oApi['CustomerLocation'], $oApi['StandardShipper'], $AFHImage_basepath]);
        } catch (Exception $e) {
            $context = ['source' => 'Montapacking Checkout'];
            $this->_logger->critical(json_encode($e->getMessage()), $context); //phpcs:ignore
            $this->_logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
            return $this->jsonResponse(json_encode([]));
        }
    }
}
