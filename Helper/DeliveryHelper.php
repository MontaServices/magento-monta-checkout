<?php

namespace Montapacking\MontaCheckout\Helper;

use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Montapacking\MontaCheckout\Logger\Logger;

/**
 * Class DeliveryHelper
 *
 * @package Montapacking\MontaCheckout\Helper\DeliveryHelper
 * @deprecated
 */
class DeliveryHelper
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
