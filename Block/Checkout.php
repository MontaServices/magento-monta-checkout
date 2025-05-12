<?php

namespace Montapacking\MontaCheckout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier;

class Checkout extends Template
{
    /**
     * Constructor.
     *
     * @param Carrier $carrier
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        protected readonly Carrier $carrier,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * Return true if the Google Maps api key has been filled
     *
     * @return bool
     */
    public function hasGoogleMapsApiKey()
    {
        return !!$this->carrier->getGoogleApiKey();
    }

    /**
     * Returns the Google Maps api key in string format, might return empty string
     *
     * @return string
     */
    public function getGoogleMapsApiKey()
    {
        return $this->carrier->getGoogleApiKey();
    }
}
