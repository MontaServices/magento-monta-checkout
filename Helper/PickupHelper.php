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
 * @deprecated
 */
class PickupHelper
{
    /**
     * @param LocaleResolver $localeResolver
     * @param Logger $logger
     */
    public function __construct(
        protected readonly LocaleResolver $localeResolver,
        protected readonly Logger $logger
    )
    {
    }
}
