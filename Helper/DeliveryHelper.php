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
     * @param LocaleResolver $localeResolver
     * @param Logger $logger
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
     * @return array
     */
    public function formatShippingOptions($frames)
    {
        $items = (object)$frames;

        $curr = '€';

        $language = strtoupper(strstr($this->localeResolver->getLocale(), '_', true));

        $hour_string = "h";
        if ($language == 'NL') {
            $hour_string = " uur";
        }
        if ($language == 'DE') {
            $hour_string = " Uhr";
        }

        foreach ($items as $frameItem) {
            foreach ($frameItem->options as $options) {
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

                $evening = '';
                $extras = [];

                if (isset($options->extras)) {
                    $extras = self::calculateExtras($options->extras, $curr);
                }

                if (count($options->optioncodes)) {
                    foreach ($options->optioncodes as $optioncode) {
                        if ($optioncode == 'EveningDelivery') {
                            $evening = " (evening delivery', 'montapacking-checkout')";
                        }
                    }
                }

                $frameItem->code = $options->code;

                $options->from = date('H:i', strtotime($options->from . ' +0 hour'));
                $options->to = date('H:i', strtotime($options->to . ' +0 hour'));

                $options->ships_on = "";

                if ($frameItem->type == 'DeliveryDay') {
                    $from = date('d-m-Y', strtotime($frameItem->from));
                    $options->date = $from;

                    $hours = date('H:i', strtotime($options->from)) . " - " . date('H:i', strtotime($options->to)) . $hour_string;
                    $options->displayname = $options->displayName . " | " . $hours . $evening;
                } elseif ($frameItem->type) {
                    // Todo: Use translation line code
                    //$options->ships_on = "(" . translate('ships on', 'montapacking-checkout') . " " . date("d-m-Y", strtotime($options->date)) . " " . translate('from the Netherlands', 'montapacking-checkout') . ")";
                    $options->ships_on = "( Ships on" . date("d-m-Y", strtotime($options->date)) . " From the Netherlands )";

                    $from = date('d-m-Y', strtotime($options->date));
                    $options->date = $from;
                }

                $frameItem->date = date('d-m-Y', strtotime($options->date));

                // Todo: Use translation line code
                $frameItem->datename = date("l", strtotime($options->date));

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
                // Extra optie toevoegen
                $extras[] = [
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

        return (object)[
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
    }
}
