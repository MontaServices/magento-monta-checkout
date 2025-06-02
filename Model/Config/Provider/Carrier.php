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
    const XPATH_CARRIER_MAXPICKUPPOINTS = 'carriers/montapacking/maxpickuppoints';
    const XPATH_CARRIER_DISABLEDELIVERYDAYS = 'carriers/montapacking/disabledeliverydays';
    const XPATH_CARRIER_HIDEDDHLPACKSTATIONS = 'carriers/montapacking/hidedhlpackstations';
    const XPATH_CARRIER_LEADINGSTOCKMONTAPACKING = 'carriers/montapacking/leadingstockmontapacking';
    const XPATH_CARRIER_SHOWZEROCOSTSASFREE = 'carriers/montapacking/showzerocostsasfree';
    const XPATH_CARRIER_IMAGEFORSTORECOLLECT = 'carriers/montapacking/imageForStoreCollect';
    const XPATH_CARRIER_CUSTOMNAMESTORECOLLECT = 'carriers/montapacking/customTitleStoreCollect';
    const XPATH_CARRIER_PRICE = 'carriers/montapacking/price';

    /**
     * @return bool
     */
    public function isCarrierActive()
    {
        return (bool)$this->getConfigValue(self::XPATH_CARRIER_ACTIVE);
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
    public function getDisableDeliveryDays()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_DISABLEDELIVERYDAYS);
    }

    /**
     * @return bool
     */
    public function getHideDHLPackStations()
    {
        // Cast to boolean for type safety (if config path is absent, returns NULL)
        return (bool)$this->getConfigValue(self::XPATH_CARRIER_HIDEDDHLPACKSTATIONS);
    }

    /**
     * @return string
     */
    public function getLeadingStockMontapacking()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_LEADINGSTOCKMONTAPACKING);
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_PRICE);
    }

    /**
     * @return integer
     */
    public function getMaxPickupPoints()
    {
        return (int)$this->getConfigValue(self::XPATH_CARRIER_MAXPICKUPPOINTS);
    }

    /**
     * @return string
     */
    public function getShowZeroCostsAsFree()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_SHOWZEROCOSTSASFREE);
    }

    /**
     * @return string
     */
    public function getImageForStoreCollect()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_IMAGEFORSTORECOLLECT);
    }

    /**
     * @return string
     */
    public function getCustomNameStoreCollect()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_CUSTOMNAMESTORECOLLECT);
    }
}
