<?php

namespace Montapacking\MontaCheckout\Controller\DeliveryOptions;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Montapacking\MontaCheckout\Controller\AbstractDeliveryOptions;
use Montapacking\MontaCheckout\Logger\Logger;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier as CarrierConfig;

/**
 * Class Delivery
 *
 * @package Montapacking\MontaCheckout\Controller\DeliveryOptions
 */
class Delivery extends AbstractDeliveryOptions
{
    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param LocaleResolver $localeResolver
     * @param CarrierConfig $carrierConfig
     * @param Logger $logger
     * @param Cart $cart
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $currencyInterface
     */
    public function __construct(
        Context $context,
        protected readonly Session $checkoutSession,
        protected readonly LocaleResolver $localeResolver,
        CarrierConfig $carrierConfig,
        protected readonly Logger $logger,
        public readonly Cart $cart,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly CurrencyInterface $currencyInterface
    )
    {
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
     * @throws GuzzleException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        if ($language != 'NL' && $language != 'BE' && $language != 'DE') {
            $language = 'EN';
        }

        try {
            $oApi = $this->generateApi($request, $language, $this->logger, true);
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $AFHImage_basepath = $mediaUrl . 'Images/';

            $this->checkoutSession->setLatestShipping([$oApi['DeliveryOptions'], $oApi['PickupOptions'], $oApi['CustomerLocation'], $oApi['StandardShipper']]);

            return $this->jsonResponse([$oApi['DeliveryOptions'], $oApi['PickupOptions'], $oApi['CustomerLocation'], $oApi['StandardShipper'], $AFHImage_basepath]);
        } catch (Exception $e) {
            $context = ['source' => 'Montapacking Checkout'];
            $this->logger->critical(json_encode($e->getMessage()), $context); //phpcs:ignore
            $this->logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
            return $this->jsonResponse(json_encode([]));
        }
    }
}
