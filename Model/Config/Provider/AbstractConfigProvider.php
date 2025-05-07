<?php

namespace Montapacking\MontaCheckout\Model\Config\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Module\Manager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractConfigProvider
{
    /** @var ScopeConfig $scopeConfig */
    private $scopeConfig;

    /** @var Manager $moduleManager */
    private $moduleManager;

    /**
     * AbstractConfigProvider constructor.
     *
     * @param ScopeConfig $scopeConfig
     * @param Manager $moduleManager
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        Manager $moduleManager,
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
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
