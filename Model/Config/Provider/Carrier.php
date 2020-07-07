<?php

namespace Montapacking\MontaCheckout\Model\Config\Provider;

class Carrier extends AbstractConfigProvider
{
    const XPATH_CARRIER_ACTIVE = 'carriers/montapacking/active';
    const XPATH_CARRIER_WEBSHOP = 'carriers/montapacking/webshop';
    const XPATH_CARRIER_USERNAME = 'carriers/montapacking/username';
    const XPATH_CARRIER_PASSWORD = 'carriers/montapacking/password';
    const XPATH_CARRIER_GOOGLEAPIKEY = 'carriers/montapacking/googleapikey';
    const XPATH_CARRIER_LOGERRORS = 'carriers/montapacking/logerrors';
    const XPATH_CARRIER_DISABLEPICKUPPOINTS = 'carriers/montapacking/disablepickuppoints';
    const XPATH_CARRIER_LEADINGSTOCKMONTAPACKING = 'carriers/montapacking/leadingstockmontapacking';

    /**
     * @return bool
     */
    public function isCarrierActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_ACTIVE);
    }

    /**
     * @return string
     */
    public function getWebshop()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_WEBSHOP);
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_USERNAME);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_PASSWORD);
    }

    /**
     * @return string
     */
    public function getGoogleApiKey()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_GOOGLEAPIKEY);
    }

    /**
     * @return string
     */
    public function getLogErrors()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_LOGERRORS);
    }

    /**
     * @return string
     */
    public function getDisablePickupPoints()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_DISABLEPICKUPPOINTS);
    }

    /**
     * @return string
     */
    public function getLeadingStockMontapacking()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_LEADINGSTOCKMONTAPACKING);
    }
}
