<?php

namespace Montapacking\MontaCheckout\Plugin\Quote\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class QuoteManagement
{
    /**
     * QuoteManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        protected readonly CartRepositoryInterface $cartRepository,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly ResolverInterface $localeResolver
    )
    {
    }

    /**
     * @param $subject
     * @param $cartId
     *
     * @throws NoSuchEntityException
     */
    // @codingStandardsIgnoreLine
    public function beforePlaceOrder($subject, $cartId)
    {
        $quote = $this->cartRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $deliveryOption = $shippingAddress->getMontapackingMontacheckoutData();

        if (!$deliveryOption) {
            return;
        }

        try {
            $deliveryOption = json_decode($deliveryOption);
            $type = $deliveryOption->type;

            if ($type == 'pickup') {
                $newAddress = $deliveryOption->additional_info[0];

                $shippingAddress->setStreet($newAddress->street . ' ' . $newAddress->housenumber);
                $shippingAddress->setCompany($newAddress->company);
                $shippingAddress->setPostcode($newAddress->postal);
                $shippingAddress->setCity($newAddress->city);
                $shippingAddress->setCountryId($newAddress->country);
            }
        } catch (\JsonException $exception) {
            // catch and ignore
        }
    }

    /**
     * @param $subject
     * @param $orderId
     * @param $quoteId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    // @codingStandardsIgnoreLine
    public function afterPlaceOrder($subject, $orderId, $quoteId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getMontapackingMontacheckoutData()) {
            return $orderId;
        }

        $quote = $this->cartRepository->get($quoteId);
        $address = $quote->getShippingAddress();
        $deliveryOption = $address->getMontapackingMontacheckoutData();

        try {
            // Delivery
            $date_stripped_obj = json_decode($deliveryOption);
            if (isset($date_stripped_obj->additional_info[0]->date)) {
                $date_stripped = $date_stripped_obj->additional_info[0]->date;
                // Stap 1: Achterhaal de huidige locale (bijv. 'de_DE' of 'nl_NL')
                $locale = $this->localeResolver->getLocale();

                // Stap 2: Bepaal de bijbehorende tijdzone of gebruik een standaardtijdzone
                $timeZoneMap = [
                    // Europa
                    'nl_NL' => 'Europe/Amsterdam',
                    'de_DE' => 'Europe/Berlin',
                    'fr_FR' => 'Europe/Paris',
                    'it_IT' => 'Europe/Rome',
                    'es_ES' => 'Europe/Madrid',
                    'pt_PT' => 'Europe/Lisbon',
                    'pl_PL' => 'Europe/Warsaw',
                    'ru_RU' => 'Europe/Moscow',
                    'en_GB' => 'Europe/London',
                    'sv_SE' => 'Europe/Stockholm',
                    'be_BE' => 'Europe/Brussels',
                    'dk_DK' => 'Europe/Copenhagen',
                    'fi_FI' => 'Europe/Helsinki',
                    'ie_IE' => 'Europe/Dublin',
                    'hu_HU' => 'Europe/Budapest',
                    'cz_CZ' => 'Europe/Prague',
                    'sk_SK' => 'Europe/Bratislava',
                    'bg_BG' => 'Europe/Sofia',
                    'ro_RO' => 'Europe/Bucharest',
                    'hr_HR' => 'Europe/Zagreb',
                    'lt_LT' => 'Europe/Vilnius',
                    'lv_LV' => 'Europe/Riga',
                    'ee_EE' => 'Europe/Tallinn',
                    'at_AT' => 'Europe/Vienna',
                    'gr_GR' => 'Europe/Athens',
                    'cy_CY' => 'Asia/Nicosia',
                    'lu_LU' => 'Europe/Luxembourg',
                    'mt_MT' => 'Europe/Malta',
                    'si_SI' => 'Europe/Ljubljana',

                    // Noord-Amerika
                    'en_US' => 'America/New_York',
                    'es_MX' => 'America/Mexico_City',
                    'en_CA' => 'America/Toronto',
                    'fr_CA' => 'America/Toronto',

                    // Zuid-Amerika
                    'pt_BR' => 'America/Sao_Paulo',
                    'es_AR' => 'America/Argentina/Buenos_Aires',
                    'es_CO' => 'America/Bogota',
                    'es_CL' => 'America/Santiago',

                    // Afrika
                    'en_ZA' => 'Africa/Johannesburg',
                    'ar_EG' => 'Africa/Cairo',
                    'fr_DZ' => 'Africa/Algiers',
                    'sw_KE' => 'Africa/Nairobi',
                    'fr_SN' => 'Africa/Dakar',

                    // Azië
                    'zh_CN' => 'Asia/Shanghai',
                    'ja_JP' => 'Asia/Tokyo',
                    'ko_KR' => 'Asia/Seoul',
                    'hi_IN' => 'Asia/Kolkata',
                    'ar_SA' => 'Asia/Riyadh',
                    'th_TH' => 'Asia/Bangkok',
                    'ms_MY' => 'Asia/Kuala_Lumpur',

                    // Oceanië
                    'en_AU' => 'Australia/Sydney',
                    'en_NZ' => 'Pacific/Auckland',
                    'en_PG' => 'Pacific/Port_Moresby',

                    // Midden-Oosten
                    'fa_IR' => 'Asia/Tehran',
                    'he_IL' => 'Asia/Jerusalem',

                    // Universele Tijd (Fallback)
                    'default' => 'UTC', // Voor alle onbekende of ongebruikelijke locales
                ];
                $timeZone = $timeZoneMap[$locale] ?? 'UTC'; // Valt terug op UTC als de locale niet bekend is

                // Stap 3: Maak een DateTime-object met de juiste tijdzone
                $datetime = new \DateTime($date_stripped, new \DateTimeZone($timeZone));

                if ($datetime === false) {
                    throw new \Exception('Ongeldige datum ontvangen: ' . $date_stripped);
                }

                // Stap 4: Zet de tijdzone van het DateTime-object om naar UTC
                $datetime->setTimezone(new \DateTimeZone('UTC'));

                // Stap 5: Formatteer de datum zoals gewenst
                $formattedDate = $datetime->format('Y-m-d'); // Of een ander formaat zoals 'd-m-Y H:i:s'

                // Opslaan in JSON
                $date_stripped_obj->additional_info[0]->date = $formattedDate;
                $deliveryOption = json_encode($date_stripped_obj);

                if (!$deliveryOption) {
                    return $orderId;
                }
            } else {
                // Pickup/on-date flow
                if (!$deliveryOption) {
                    return $orderId;
                }
            }
            $order->setMontapackingMontacheckoutData($deliveryOption);
            $order->save();
        } catch (\Exception $e) {
            //                $this->logger->error('Error while processing Monta Delivery Date conversation to timezone: ' . $e->getMessage());
        }

        return $orderId;
    }
}
