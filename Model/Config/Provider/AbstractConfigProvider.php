<?php

namespace Montapacking\MontaCheckout\Model\Config\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractConfigProvider
{
    /** @var ScopeConfig $scopeConfig */
    private $scopeConfig;

    /** @var Manager $moduleManager */
    private $moduleManager;

    private $storeManager;

    /**
     * AbstractConfigProvider constructor.
     *
     * @param ScopeConfig $scopeConfig
     * @param Manager $moduleManager
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isModuleOutputEnabled()
    {
        return $this->moduleManager->isOutputEnabled('Montapacking_MontaCheckout');
    }
}
