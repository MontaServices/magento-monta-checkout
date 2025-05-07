<?php

namespace Montapacking\MontaCheckout\Helper;

use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Montapacking\MontaCheckout\Logger\Logger;

/**
 * Class DeliveryHelper
 *
 * @package Montapacking\MontaCheckout\Helper\DeliveryHelper
 */
class DeliveryHelper
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
    public function formatShippingOptions($frames)
    {
//        $items = [];
//
//        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));
//
//        if ($language != 'NL' && $language != 'BE' && $language != 'DE') {
//            $language = 'EN';
//        }
//
//        $hour_string = "h";
//        if ($language == 'NL') {
//            $hour_string = " uur";
//        }
//        if ($language == 'DE') {
//            $hour_string = " Uhr";
//        }
//
//        ## Currency symbol
//        $curr = '€';
//
//        if (is_array($frames) || is_object($frames)) {
//
//            foreach ($frames as $nr => $frame) {
//
//                foreach ($frame->options as $onr => $option) {
//
//                    $description = [];
//
//                    $from = null;
//                    $to = null;
//                    $date = null;
//                    $time = null;
//
//                    if ($frame->type == 'DeliveryDay') {
//                        $from = $option->from;
//                        $to = $option->to;
//                        $date = date("Y-m-d", strtotime($from));
//                        $time = date('H:i', strtotime($frame->from)) != date('H:i', strtotime($frame->to)) ? date('H:i', strtotime($frame->from)) . '-' . date('H:i', strtotime($frame->to)) : '';
//
//                        if (trim($option->description)) {
//                            $description[] = $option->description;
//                        }
//                        if (date('H:i', strtotime($from)) != '00:00') {
//                            $description[] = date('H:i', strtotime($from)) . " - " . date('H:i', strtotime($to)) . $hour_string; //phpcs:ignore
//                        }
//                        if ($frame->code != null && trim($frame->code)) {
//                            $description[] = __($frame->code);
//                        }
//                    }
//
//                    if ($frame->type == 'ShippingDay') {
//                        $from = date("Y-m-d", strtotime($option->date));
//                        $to = date("Y-m-d", strtotime($option->date));
//                        $date = date("Y-m-d", strtotime($from));
//
//                        if (trim($option->description)) {
//                            $description[] = $option->description;
//                        }
//
//                        if ($frame->code != null && trim($frame->code)) {
//                            $description[] = $frame->code;
//                        }
//
//                        $description[] = __("Ships on this date from the Netherlands");
//                    }
//
//                    if ($frame->type == 'Unknown') {
//
//                        if (isset($option->date) && strtotime($option->date) > 0) {
//                            $from = date("Y-m-d", strtotime($option->date));
//                            $to = date("Y-m-d", strtotime($option->date));
//                        } elseif ($option->code == 'RED_ShippingDayUnknown') {
//                            $from = date('d-m-Y', time());
//                            $to = $from = date('d-m-Y', time());
//                        } elseif ($option->code == 'Trunkrs_ShippingDayUnknown') {
//                            $from = date('d-m-Y', time());
//                            $to = $from = date('d-m-Y', time());
//                        }
//
//                        if (isset($from)) {
//                            $date = date("Y-m-d", strtotime($from));
//                        }
//
//
//                        if (trim($option->description)) {
//                            $description[] = $option->description;
//                        }
//
//                        if ($frame->code != null && trim($frame->code)) {
//                            $description[] = $frame->code;
//                        }
//
//                    }
//
//                    $description = implode(" | ", $description);
//
//                    $extras = [];
//                    if (isset($option->extras) && count($option->extras) > 0) {
//                        $extras = self::calculateExtras($option->extras, $curr);
//                    }
//
//                    $options = [];
//                    $created_option = self::calculateOptions($frame, $option, $curr, $description, $from, $to, $extras, $hour_string); //phpcs:ignore
//
//                    if (null !== $created_option) {
//
//                        $options[] = $created_option;
//                        if (!isset($items[$date])) {
//                            $items[$date] = [];
//                        }
//
//                        $items[$date][] = (object)[
//                            'code' => $frame->code,
//                            'date' => $from != null && strtotime($from) > 0 ? date('d-m-Y', strtotime($from)) : "",
//                            'time' => $time != null ? $time : "", //phpcs:ignore
//                            'description' => $frame->description,
//                            'price_currency' => $curr,
//                            'options' => $options
//                        ];
//                    }
//
//                }
//            }
//        }
//
//        ksort($items);
//        $list = [];
//        foreach ($items as $key => $values) {
//
//
//            foreach ($values as $key_value => $value) {
//                $list[] = $value;
//            }
//        }
//        $items = $list;
//
//        return $items;


        $items = (object)$frames;
        $itemsArray = [];
//        $items[] = $frames;

        $curr = '€';

        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        $hour_string = "h";
        if ($language == 'NL') {
            $hour_string = " uur";
        }
        if ($language == 'DE') {
            $hour_string = " Uhr";
        }


//        $freeShippingCouponCode = self::checkFreeShippingCouponCodes();

        foreach ($items as $frameItem) {
            foreach ($frameItem->options as $key => $options) {
//                if ((time() + 3600) >= strtotime($options->date)) {
//                    continue;
////                    $items[$key]->options[] = $options_object;
//                }

                if ($options->from != null && (date("Y-m-d", strtotime($options->from)) == date("Y-m-d") && $frameItem->code != 'SameDayDelivery')) {
                    return null;
                }
                $date_string = '';
                if ($options->from != null && strtotime($options->from) > 0) {
                    $date_string = __(date("l", strtotime($options->from))) . " " . date("d", strtotime($options->from)) . " " . __(date("F", strtotime($options->from)));
                }

                if (count($options->codes) > 2) {
                    $image_code = 'DEF';
                } else {
                    $image_code = trim(str_replace(",", "_", implode(",", $options->codes)));
                }


                if ($frameItem->from == null) {
//                    $frameItem->from  = date("Y-m-d", time() + 86400);
//                    $frameItem->to  = date("Y-m-d", time() + 86400);

//                    $frameItem->from = "2023-06-20T00:00";
//                    $frameItem->to = "2023-06-20T00:00";
//                    $frameItem->date = "2023-06-20T00:00";
                }

                $evening = '';
                $extras = [];

                if (isset($options->extras)) {
                    $extras = self::calculateExtras($options->extras, $curr);
                }

//                if ($freeShippingCouponCode) {
//                    $options->price = 0;
//                }

                if (count($options->optioncodes)) {
                    foreach ($options->optioncodes as $optioncode) {
                        if ($optioncode == 'EveningDelivery') {
                            $evening = " (evening delivery', 'montapacking-checkout')";
                        }
                    }
                }


                $frameItem->code = $options->code;
//                $frameItem->date = date('d-m-Y', strtotime($options->date));
//                $frameItem->datename = translate(date("l", strtotime($options->date)));

                $options->from = date('H:i', strtotime($options->from . ' +0 hour'));
                $options->to = date('H:i', strtotime($options->to . ' +0 hour'));

                $options->ships_on = "";

                if ($frameItem->type == 'DeliveryDay') {
                    $type_text = 'delivered';

                    $from = date('d-m-Y', strtotime($frameItem->from));
                    $options->date = $from;

//                    if (date('H:i', strtotime($from)) != '00:00') {
                    $hours = date('H:i', strtotime($options->from)) . " - " . date('H:i', strtotime($options->to)) . $hour_string;
                    $options->displayname = $options->displayName . " | " . $hours . $evening;
//                    }
                } elseif ($frameItem->type) {
                    $type_text = 'ShippingDay';

                    // Todo: Use translation line code
                    //$options->ships_on = "(" . translate('ships on', 'montapacking-checkout') . " " . date("d-m-Y", strtotime($options->date)) . " " . translate('from the Netherlands', 'montapacking-checkout') . ")";
                    $options->ships_on = "( Ships on" . date("d-m-Y", strtotime($options->date)) . " From the Netherlands )";


                    $from = date('d-m-Y', strtotime($options->date));
                    $options->date = $from;
                }


//                $frameOptions = [];
//                $created_option = self::calculateOptions($frameItem, $options, $curr, 'PostNL Pakket |  12:00 - 14:30 uur', '12:00', '14:30', $extras, '12:00-14:30'); //phpcs:ignore
//
//                if (null !== $created_option) {
//
//                    $frameOptions[] = $created_option;
//                    if (!isset($items[$from])) {
//                        $items[$from] = [];
//                    }
//                }


                $frameItem->date = date('d-m-Y', strtotime($options->date));

                // Todo: Use translation line code
                $frameItem->datename = date("l", strtotime($options->date));

//                $options->type_text = translate($type_text, 'montapacking-checkout');
//                $options->displayname = $options->displayName . "PostNL Pakket |  12:00 - 14:30 uur" . $evening;

                $options->price = $curr . ' ' . number_format($options->price_raw, 2, ',', '');

                $options->extras = $extras;
                $options->is_sustainable = $options->isSustainable;
                $options->discount_percentage = $options->discountPercentage;
                $options->is_preferred = $options->isPreferred;

                /** Temp to make Magento happy? */
                $options->image = $image_code;
                $options->image_replace = $image_code;
                $options->type = $frameItem->type;
                $options->date_from_to_formatted = '12:00 - 14:30 uur';
                $options->date_from_to = '17:30-22:30';
//                $options->date_string = 'Woensdag 130 juni';

                $options->date_string = $date_string;
                $options->name = $options->description;
            }
        }

        return (array)$items;
    }

    public function calculateExtras($extra_values = [], $curr = '&euro;')
    {
        $extras = [];
        if (count($extra_values) > 0) {
            foreach ($extra_values as $extra) {
                $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

                ## Extra optie toevoegen
                $extras[] = (array)[
                    'code' => $extra->code,
                    'name' => __($extra->code),
                    'price_currency' => $curr,
                    'price_string' => $curr . ' ' . number_format($extra->price, 2, ',', ''),
                    'price_raw' => number_format($extra->price, 2),
                    'price_formatted' => number_format($extra->price, 2, ',', ''),
                ];
            }
        }

        return $extras;
    }

    public function calculateOptions($frame, $option, $curr, $description, $from, $to, $extras, $hour_string)
    {
        if ($from != null && (date("Y-m-d", strtotime($from)) == date("Y-m-d") && $frame->code != 'SameDayDelivery')) {
            return null;
        }

        $date_string = "";

        if ($from != null && strtotime($from) > 0) {
            $date_string = __(date("l", strtotime($from))) . " " . date("d", strtotime($from)) . " " . __(date("F", strtotime($from)));
        }

        $description = str_replace("PostNL Pakket", "PostNL", $description);
        $name = $option->description;

        if ($option->displayName != null) {
            $parts = explode("|", $description);
            $parts[0] = $option->displayName;
            $description = implode(" | ", $parts);

            $name = $option->displayName;
        }

        if (count($option->codes) > 2) {
            $image_code = 'DEF';
        } else {
            $image_code = trim(str_replace(",", "_", implode(",", $option->codes)));
        }

        $options = (object)[
            'code' => $option->code,
            'codes' => $option->codes,
            'type' => $frame->type,
            'image' => trim(implode(",", $option->codes)),
            'image_replace' => trim($image_code),
            'optionCodes' => $option->optioncodes,
            'name' => $name,
            'description_string' => $description,
            'price_currency' => $curr,
            'price_string' => $curr . ' ' . number_format($option->price, 2, ',', ''),
            'price_raw' => number_format($option->price, 2),
            'price_formatted' => number_format($option->price, 2, ',', ''),
            'from' => $from != null && strtotime($from) > 0 ? date('H:i', strtotime($from)) : "",
            'to' => $to != null && strtotime($to) > 0 ? date('H:i', strtotime($to)) : "",
            'date' => $from != null && strtotime($from) > 0 ? date("d-m-Y", strtotime($from)) : "",
            'date_string' => $date_string,
            'date_from_to' => $from != null && strtotime($from) > 0 ? date('H:i', strtotime($from)) . "-" . date('H:i', strtotime($to)) : "",
            'date_from_to_formatted' => $from != null && strtotime($from) > 0 ? date('H:i', strtotime($from)) . " - " . date('H:i', strtotime($to)) . $hour_string : "", //phpcs:ignore
            'extras' => $extras,
            'is_preferred' => $option->isPreferred,
            'is_sustainable' => $option->isSustainable,
            'discount_percentage' => $option->discountPercentage
        ];

        return $options;
    }
}
