<?php

namespace Montapacking\MontaCheckout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier;

class Checkout extends Template
{
    /**
     * @var Carrier
     */
    private $carrier;

    /**
     * Constructor.
     *
     * @param Carrier $carrier
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Carrier $carrier,
        Context $context,
        array $data = []
    )
    {
        $this->carrier = $carrier;

        parent::__construct($context, $data);
    }

    /**
     * Return true if the google maps api key has been filled
     *
     * @return bool
     */
    public function hasGoogleMapsApiKey()
    {
        return !!$this->carrier->getGoogleApiKey();
    }

    /**
     * Returns the google maps api key in string format, might return empty string
     *
     * @return string
     */
    public function getGoogleMapsApiKey()
    {
        return $this->carrier->getGoogleApiKey();
    }
}