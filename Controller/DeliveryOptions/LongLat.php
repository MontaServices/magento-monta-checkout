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
        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        if ($language != 'NL' && $language != 'BE' && $language != 'DE') {
            $language = 'EN';
        }

        try {
            $longlat = $request->getParam('longlat') ? trim($request->getParam('longlat')) : "";

            if ($longlat == 'false') {
                $oApi = $this->generateApi($request, $language);
            } else {
                // TODO merge duplicate code, just pass expression directly
                $oApi = $this->generateApi($request, $language, true);
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
            $this->logger->critical("Webshop was unable to connect to Montapacking REST api. Please contact Montapacking", $context); //phpcs:ignore
        }

        return $this->jsonResponse($arr);
    }
}
