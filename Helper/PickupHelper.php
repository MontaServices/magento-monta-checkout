<?php

namespace Montapacking\MontaCheckout\Helper;

use DateTime;
use DateTimeZone;
use IntlDateFormatter;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Montapacking\MontaCheckout\Logger\Logger;

/**
 * Class PickupHelper
 *
 * @package Montapacking\MontaCheckout\Helper\PickupHelper
 */
class PickupHelper
{
    /** @var LocaleResolver $scopeConfig */
    private $localeResolver;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Services constructor.
     *
     */
    public function __construct(
        LocaleResolver $localeResolver,
        Logger $logger
    )
    {
        $this->_logger = $logger;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param $frames
     *
     * @return array
     */
    public function formatPickupOptions($frames)
    {
        $items = [];

        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        if ($language != 'NL' && $language != 'BE' && $language != 'DE') {
            $language = 'EN';
        }

        $hour_string = "h";
        if ($language == 'NL') {
            $hour_string = " uur";
        }
        if ($language == 'DE') {
            $hour_string = " Uhr";
        }

        ## Currency symbol
        $curr = '€';

        $marker_id = 0;
        ## Check of er meerdere timeframes zijn, wanneer maar één dan enkel shipper keuze zonder datum/tijd
        if (is_array($frames) || is_object($frames)) {
            foreach ($frames as $nr_temp => $frame) {
                $nr = $nr_temp + 1;

                ## Alleen als er van en tot tijd bekend is (skipped nu DPD en UPS)
                if ($frame->from != '' && $frame->to != '') {
                    ## Loop trough options
                    $selected = null;

                    ## Lowest price
                    $lowest = 9999999;

                    ## Shipper opties ophalen
                    $options = null;
                    foreach ($frame->options as $onr => $option) {
                        $from = $option->from;
                        $to = $option->to;

                        ## Check of maximale besteltijd voorbij is
                        if (time() < strtotime($option->date) && $selected == null) {
                            $selected = $option;
                        }

                        ## Shipper optie toevoegen

                        $description = [];
                        $description_storelocator = [];
                        if (trim($option->description)) {
                            $description[] = $option->description;
                            $description_storelocator[] = $option->description;
                        }

                        if (trim($frame->description->DistanceMeters)) {
                            $distance = round($frame->description->DistanceMeters / 1000, 2);
                            $description[] = str_replace(".", ",", $distance) . " km";
                        }

                        $description = implode(" | ", $description);

                        $marker_id++;

                        $extra_code = "";

                        $arr = array();
                        foreach ($option->optionsWithValue as $key => $value) {
                            $extra_code = $key . "_" . $value;
                            if (trim($extra_code)) {
                                $arr[] = $extra_code;
                            }
                        }
                        $extra_code = implode(",", $arr);

                        $dt = date_create($from);

                        $options[$onr] = (object)[
                            'marker_id' => $marker_id,
                            'code' => $option->code,
                            'code_pickup' => $extra_code,
                            'codes' => $option->codes,
                            'image' => trim(implode(",", $option->codes)),
                            'image_replace' => trim(str_replace(",", "_", implode(",", $option->codes))),
                            'optionCodes' => json_encode((array)$option->optioncodes),
                            'optionsWithValue' => json_encode((array)$option->optionsWithValue),
                            'name' => $option->description,
                            'description_string' => $description,
                            'description_string_storelocator' => $description_storelocator,
                            'price_currency' => $curr,
                            'price_string' => $curr . ' ' . number_format($option->price, 2, ',', ''),
                            'price_raw' => number_format($option->price, 2),
                            'price_formatted' => number_format($option->price, 2, ',', ''),
                            'from' => date('H:i', strtotime($from)),
                            'to' => date('H:i', strtotime($to)),
                            'date' => date("d-m-Y", strtotime($from)),
                            'date_string' => $this->formatLanguage($dt, 'd F Y', $language),
                            'date_from_to' => date('H:i', strtotime($from)) . "-" . date('H:i', strtotime($to)),
                            'date_from_to_formatted' => date('H:i', strtotime($from)) . " - " . date('H:i', strtotime($to)) . $hour_string //phpcs:ignore

                        ];

                        ## Check if we have a lower price
                        if ($option->price < $lowest) {
                            $lowest = $option->price;
                        }
                    }

                    $arr = [];

                    foreach ($frame->description->OpeningTimes as $key => $time) {
                        if (isset($time->Day)) {
                            $obj = (object)[
                                'Day' => __($time->Day),
                                'OpeningTimes' => $time->OpeningTimes,
                                'TimeNotation' => $hour_string
                            ];

                            $arr[$key] = $obj;
                        }
                    }
                    $frame->description->OpeningTimes = $arr;

                    ## Check of er een prijs is
                    if ($options !== null) {
                        $items[$nr] = (object)[
                            'code' => $frame->code,
                            'date' => date('d-m-Y', strtotime($frame->from)),
                            'time' => date('H:i', strtotime($frame->from)),
                            'description' => $frame->description,
                            'price_currency' => $curr,
                            'price_string' => $curr . ' ' . number_format($lowest, 2, ',', ''),
                            'price_raw' => number_format($lowest, 2),
                            'price_formatted' => number_format($lowest, 2, ',', ''),
                            'options' => $options
                        ];
                    }
                } else {
                    ## Loop trough options
                    $selected = null;

                    ## Lowest price
                    $lowest = 9999999;

                    ## Shipper opties ophalen
                    $options = null;
                    foreach ($frame->options as $onr => $option) {
                        ## Check of maximale besteltijd voorbij is
                        if (($option->date == null || time() < strtotime($option->date)) && $selected == null) {
                            $selected = $option;
                        }

                        ## Shipper optie toevoegen

                        $description = [];
                        $description_storelocator = [];
                        if (trim($option->description)) {
                            $description[] = $option->description;
                            $description_storelocator[] = $option->description;
                        }

                        if (trim($frame->description->DistanceMeters)) {
                            $distance = round($frame->description->DistanceMeters / 1000, 2);
                            $description[] = str_replace(".", ",", $distance) . " km";
                        }

                        $description = implode(" | ", $description);

                        $marker_id++;

                        $extra_code = "";

                        $arr = array();
                        foreach ($option->optionsWithValue as $key => $value) {
                            $extra_code = $key . "_" . $value;
                            if (trim($extra_code)) {
                                $arr[] = $extra_code;
                            }
                        }
                        $extra_code = implode(",", $arr);

                        $options[$onr] = (object)[
                            'marker_id' => $marker_id,
                            'code' => $option->code,
                            'code_pickup' => $extra_code,
                            'codes' => $option->codes,
                            'image' => trim(implode(",", $option->codes)),
                            'image_replace' => trim(str_replace(",", "_", implode(",", $option->codes))),
                            'optionCodes' => json_encode((array)$option->optioncodes),
                            'optionsWithValue' => json_encode((array)$option->optionsWithValue),
                            'name' => $option->description,
                            'description_string' => $description,
                            'description_string_storelocator' => $description_storelocator,
                            'price_currency' => $curr,
                            'price_string' => $curr . ' ' . number_format($option->price, 2, ',', ''),
                            'price_raw' => number_format($option->price, 2),
                            'price_formatted' => number_format($option->price, 2, ',', ''),
                            'from' => null,
                            'to' => null,
                            'date' => null,
                            'date_string' => null,
                            'date_from_to' => null,
                            'date_from_to_formatted' => null

                        ];

                        ## Check if we have a lower price
                        if ($option->price < $lowest) {
                            $lowest = $option->price;
                        }
                    }

                    ## Check of er een prijs is
                    if ($options !== null) {
                        $items[$nr] = (object)[
                            'code' => $frame->code,
                            'date' => null,
                            'time' => null,
                            'description' => $frame->description,
                            'price_currency' => $curr,
                            'price_string' => $curr . ' ' . number_format($lowest, 2, ',', ''),
                            'price_raw' => number_format($lowest, 2),
                            'price_formatted' => number_format($lowest, 2, ',', ''),
                            'options' => $options
                        ];
                    }
                }
            }
        }

        return $items;
    }

    function formatLanguage(DateTime $dt, string $format, string $language = 'en'): string
    {
        $curTz = $dt->getTimezone();
        if ($curTz->getName() === 'Z') {
            //INTL don't know Z
            $curTz = new DateTimeZone('UTC');
        }

        $formatPattern = strtr($format, array(
            'D' => '{#1}',
            'l' => '{#2}',
            'M' => '{#3}',
            'F' => '{#4}',
        ));
        $strDate = $dt->format($formatPattern);
        $regEx = '~\{#\d\}~';
        while (preg_match($regEx, $strDate, $match)) {
            $IntlFormat = strtr($match[0], array(
                '{#1}' => 'E',
                '{#2}' => 'EEEE',
                '{#3}' => 'MMM',
                '{#4}' => 'MMMM',
            ));
            $fmt = datefmt_create($language, IntlDateFormatter::FULL, IntlDateFormatter::FULL,
                $curTz, IntlDateFormatter::GREGORIAN, $IntlFormat);
            $replace = $fmt ? datefmt_format($fmt, $dt) : "???";
            $strDate = str_replace($match[0], $replace, $strDate);
        }

        return $strDate;
    }
}
