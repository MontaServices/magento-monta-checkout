<?php

namespace Montapacking\MontaCheckout\Controller\DeliveryOptions;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Montapacking\MontaCheckout\Controller\AbstractDeliveryOptions;

/**
 * Class LongLat
 *
 * @package Montapacking\MontaCheckout\Controller\DeliveryOptions
 */
class LongLat extends AbstractDeliveryOptions
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws \Exception|GuzzleException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $language = $this->getLanguage();

        // instantiate response array
        $arr = [];
        $arr['longitude'] = 0;
        $arr['latitude'] = 0;
        $arr['language'] = $language;

        try {
            $longlat = trim($request->getParam(key: 'longlat', defaultValue: ""));

            $oApi = $this->generateApi(
                request: $request,
                language: $language,
                use_googlekey: ($longlat == 'false')
            );

            $arr['longitude'] = $oApi->address->longitude;
            $arr['latitude'] = $oApi->address->latitude;
        } catch (\Exception $e) {
            $arr['hasconnection'] = 'false';
            $arr['googleapikey'] = $this->getCarrierConfig()->getGoogleApiKey();

            $context = ['source' => 'Montapacking Checkout'];
            $this->logger->critical($e->getMessage(), $context);
            $this->logger->critical("Webshop was unable to connect to Montapacking REST api", $context); //phpcs:ignore
        }

        return $this->jsonResponse($arr);
    }
}
