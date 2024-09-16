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

    protected $storeManager;

    protected $currency;


    /**
     * Services constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param LocaleResolver $localeResolver
     * @param CarrierConfig $carrierConfig
     * @param Logger $logger
     * @param Cart $cart
     * @param PickupHelper $pickupHelper
     * @param DeliveryHelper $deliveryHelper
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
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
            $currencyInterface,
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
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $AFHImage_basepath = $mediaUrl . 'Images/';

            $this->checkoutSession->setLatestShipping([$oApi['DeliveryOptions'], $oApi['PickupOptions'],  $oApi['CustomerLocation'], $oApi['StandardShipper']]);

            return $this->jsonResponse([$oApi['DeliveryOptions'], $oApi['PickupOptions'], $oApi['CustomerLocation'], $oApi['StandardShipper'],$AFHImage_basepath ]);
        } catch (Exception $e) {

            $context = ['source' => 'Montapacking Checkout'];
            $this->_logger->critical(json_encode($e->getMessage()), $context); //phpcs:ignore
            $this->_logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
            return $this->jsonResponse(json_encode([]));
        }
    }
}
