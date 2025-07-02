<?php

namespace Montapacking\MontaCheckout\Controller\DeliveryOptions;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Montapacking\MontaCheckout\Controller\AbstractDeliveryOptions;

/**
 * Class Delivery
 *
 * @package Montapacking\MontaCheckout\Controller\DeliveryOptions
 */
class Delivery extends AbstractDeliveryOptions
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws GuzzleException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $language = $this->getLanguage();
        try {
            $oApi = $this->generateApi(
                request: $request,
                language: $language,
                use_googlekey: true
            );
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $AFHImage_basepath = $mediaUrl . 'Images/';

            $this->checkoutSession->setLatestShipping(
                [
                    $oApi['DeliveryOptions'],
                    $oApi['PickupOptions'],
                    $oApi['CustomerLocation'],
                    $oApi['StandardShipper']
                ]);
            return $this->jsonResponse(
                [
                    $oApi['DeliveryOptions'],
                    $oApi['PickupOptions'],
                    $oApi['CustomerLocation'],
                    $oApi['StandardShipper'],
                    $AFHImage_basepath,
                ]);
        } catch (Exception $e) {
            $context = ['source' => 'Montapacking Checkout'];
            $this->logger->critical(json_encode($e->getMessage()), $context); //phpcs:ignore
            $this->logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
            return $this->jsonResponse(json_encode([]));
        }
    }
}
